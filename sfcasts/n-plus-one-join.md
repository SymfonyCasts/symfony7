# N Plus One Join

Coming soon...

## Let's Level Up Our `parts` Table

Alright, fellow code warriors, we've got our `parts` table all set up and
ready for action. But now we want to rank our parts by `price` in a
descending order, because who doesn't love a good bargain hunt, right? This
is a fairly simple task, but we're going to make it a bit more exciting by
crafting a custom query. To do this, we'll have to dive deep into our
`PartRepository`.

## Introducing the `PartRepository`

So, buckle up and navigate your way to
`src/Repository/StarshipPartRepository.php`. Once we've landed, we're going
to forge a new method. You see that stubbed method over there? Go ahead and
copy it, then uncomment it since we already have a handy PHP doc right
above. Give your code a little spring clean by removing the last stub
method. Let's christen our new method `findAllOrderedByPrice()`. The
`value` here is excess baggage we won't be needing, so let's toss that out.

Now, we're going to build a simple query. You can call `sp` for
`StarshipPart`. We can ditch the `andWhere` and the `setParameter` below
that. We do, however, need the `orderBy`, but let's give it a little twist
as `orderBy('sp.price', 'DESC')`. The `setMaxResults` can also take a hike.
And voila, we've got a snazzy custom query. Let's copy the name of this
method and jet off to our `PartController`. We'll use our shiny new method
instead of the old `findAll()`.

## Examining Our Queries

Let's take a moment to examine the queries for this page. You'll notice
that there are nine database queries. The first one is exactly what we
predicted — it's querying for all the `StarshipPart`s with a price
descending. But wait, what are all these other queries? We actually have
one query per `Starship`. So, we're querying for the `Starship` here.

## The Mystery of the N Plus One Problem

Here's the plot twist: we query for all the parts, and then when we're in
the template looping over the parts, the moment we print out
`part.starship`, Doctrine has a light bulb moment. It realises it has the
`part` data, but it's missing the `Starship` data for this `part`. So it
needs to query for it. We end up with one query for the parts, and then an
extra query for the `Starship` for each part in there. This is a notorious
villain known as the N plus one problem.

Think of it this way: if we have 10 parts, we're going to end up with one
query for the parts and then 10 extra queries, one for the `Starship` for
each of those parts. This is a performance problem. It might not seem like
a big deal, but it's something we should keep an eye on. And the secret
weapon to defeat this is a `join`.

## The Power of `join`

Back in our `StarshipPartRepository`, we're going to power up our
`findAllOrderedByPrice()` with a `join`. We'll add
`innerJoin('sp.starship', 's')`. All we have to do is join on the property.
We're joining on `StarshipPart.starship` or joining on the `starship`
property. We're aliasing the entire `starship` table over to `s`.

Previously, we had nine database queries. Let's refresh. And... we still
have nine database queries. Now, you might be scratching your head and
wondering why. Well, there are two reasons to use a `join`. The first is to
avoid the N plus one problem, and the second is to do a `where` or
`orderBy` on the join table. We'll explore that second reason soon.

To solve the N plus one problem, in addition to the `join`, we need to
select the data on `Starship`. It's as simple as saying `addSelect('s')`.
So we're aliasing the entire `Starship` table to `s`. Then with
`addSelect()`, we don't bother with individual columns. We just say, "Hey,
I want the whole shebang".

## The Magic of `join` and `addSelect()`

We're now down to one database query from nine. That's some serious magic
right there. As you can see, we're selecting from `StarshipPart`, grabbing
all the data from both `Starship` and `StarshipPart`, with the `innerJoin`
sitting pretty right there. And the best part? We don't have to sweat the
details of joining on which columns. All we have to do is just perform the
`join` on the property, and Doctrine takes care of the boring details for
us.

Stay tuned, because next up, we're going to add a search function to our
page. And when we do that, we're going to see the second use of a `join`.
It's going to be a wild ride, so hang on tight!