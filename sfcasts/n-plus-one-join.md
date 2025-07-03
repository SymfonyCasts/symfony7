# Joining to Avoid the N+1 Trap

We've got a `parts` table, and we're using it! But now we want to rank the parts
by `price` descending, because if we're gonna upsell, might as well start
with the most expensive ones, right? This is a simple task, but we're going
to make it a more exciting by crafting a custom query. Open up
`src/Repository/StarshipPartRepository.php`.

See that stubbed method? Copy that, then uncomment it since: this
PHP doc is handy, and we don't want to lose it.
Remove the last stub, then name it `findAllOrderedByPrice()`. Remove
the `$value` argument, no need for that:

[[[ code('834c98b8ae') ]]]

Build a simple query: I'll use `sp` as the alias for `StarshipPart`. Ditch the
`andWhere()` and `setParameter()` below that. We *do*, however, need the `orderBy()`:
as `orderBy('sp.price', 'DESC')`. The `setMaxResults()` can also go:

[[[ code('7d25dc71ae') ]]]

Custom query, check! Copy the method name, then head over to
`PartController`. Use this instead of `findAll()`:

[[[ code('b6ce491735') ]]]

## Examining Our Queries

Check out the queries for this page: there are 9. The first is exactly what we
predicted: it queries for all `starship_part`s ordered by price descending.
But wait, what are all these other queries? There's an extra query per starship.
What gives?

## The N + 1 Problem

We query for all the parts, and then when we're in the template looping over
the parts, when we reference `part.starship`, Doctrine has a light bulb moment.
It realizes it has the `part` data, but not the `Starship` data *for* this `part`.
So it queries for it. We end up with one query for the parts, and then an
extra query for each `Starship` to fetch its parts. This is a notorious
villain known as the *N + 1 problem*.

Think of it this way: if we have 10 parts, we're going to end up with one
query for the parts and then 10 extra queries, one for the `Starship` for
each of those parts. This is a performance problem. It might not seem like
a big deal, but it's something we should keep an eye on. And we can
defeat it with a `join`.

## Joining Across the Relationship

Back in `StarshipPartRepository`, we're going to power up
`findAllOrderedByPrice()` with a join. Add `innerJoin('sp.starship', 's')`.
All we have to do is join on the *property*. Doctrine will figure out the details
for us, like which columns to join on. Then we're aliasing the entire `starship`
table over to `s`:

[[[ code('a89feb8cee') ]]]

Before, we had 9 database queries. Refresh and... we still have 9 database queries.
Why? Didn't we already join over to the `starship` table? Yes, but there
are two reasons to use a `join`. The first is to avoid this N + 1 problem,
and the second is to do a `where()` or `orderBy()` on the join table. We'll explore
that second reason soon.

To solve the N plus 1 problem, in addition to the `join`, we need to
*select* the data on `Starship`. It's as simple as saying `addSelect('s')`:

[[[ code('02b7c92de4') ]]]

We're aliasing the entire `Starship` table to `s`. Then with `addSelect()`,
we don't bother with individual columns. We just say:

> Hey, I want all that data.

## The Magic of `join` and `addSelect()`

We're now down to 1 database query from 9. That's some serious magic
right there. As you can see, we're selecting from `StarshipPart`, grabbing
all the data from both `Starship` *and* `StarshipPart`, with the `innerJoin()`
sitting pretty right there. The best part? We don't have to sweat the
details of joining on which columns. All we have to do is join on the
relation property, and Doctrine takes care of the boring details for us.

Next up, let's add a search to our page. When we do that, we're going to see the
second use of a `JOIN` and finally play with the `Request` object.
