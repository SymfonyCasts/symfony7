# Ordering a Relation and "fetch" type

Click into an "in progress" starship. Then open:
`templates/starship/show.html.twig`.
To list the parts, use `for part in ship.parts`.

This will work like a charm. But with a catch: the order of the parts isn't
guaranteed. They pop out of the database in whatever order they fancy.

I'd rather have these ordered by name. Does this mean we need to 
write a custom query... and can't use our handy `ship.parts` anymore? Fear

Fear not, friends. We *do* have a few tricks up our sleeves.

## Rearranging the Parts

Head over to the `Starship` entity and find the `parts` property.
Above `parts`, add a new attribute: `#[ORM\OrderBy(['name' => 'ASC'])]`,
not `position`. Refresh the page, and got it!

If you're scratching your head wondering why T is coming after R,
don't worry: you haven't forgotten your ABCs. It's just that Postgres is a
case-sensitive database. So the uppercase T actually comes before lowercase
C in the alphabetical order.

## Smart Queries

Check the queries for this page and view the formatted SQL.
As you can see, it queries from `starship_part`, where `starship_id` equals
our ID, ordered by `name` ascending: it's exactly the query we want!

## The N+1 Problem

Head back to the homepage and open up its template:
`templates/main/homepage.html.twig`. After "arrived", add a div
then print out the part count: `ship.parts|length`. 

Back on the homepage, it works like a charm. Check out the queries for
this page, they're interesting.
Some of these look a bit wild because of our pagination, but essentially,
we have one query for the starship, and if we
search for `starship_part`, there are 5 extra queries for the parts for each of
the starships.

Here's what's happening: we grab the starships, then
as soon as we count `ship.parts`, Doctrine realizes it doesn't have
that data yet. So it fetches all the parts for each ship one by one and
counts them. This is a common situation: we have one query for the ships
and then one extra query for the parts of *each* ship. It's known as the N+1:
1 query for the starships, and N queries for the parts of each ship. It's a minor
performance problem that we're going to tackle later.

## Efficient Querying

But there's a bigger performance issue here: we query for every
`starship_part` just to *count* them. We don't need the part data, we
just need to know how *many* we've got. It's a minor issue, until you have a ship
with a *ton* of parts. 

To fix this, in the `OneToMany` in the `Starship` entity, add
a `fetch` option set to `EXTRA_LAZY`. Let's go see what that did!

## Counting the Parts

Head back to the homepage. Earlier, we had nine queries... Now???
*Still* nine queries, but the query for the parts changed. Instead of
querying for all their data, it just *counts* the parts. Much
smarter, right?

You might be wondering - I certainly did - why we don't use `fetch="EXTRA_LAZY"`
all the time? First, this is a small performance optimization that you don't need
to worry about unless you have a ship *full* of parts and you just want to count
them. More importantly, depending on if you count or loop over the parts first,
it could cause an *extra* query.

## The Criteria System

Onto our next challenge! What if we only want the related parts for a
ship that cost above a certain price? Can we still use the `ship.parts`
shortcut or do we need to do a custom query? Stay tuned, we're going to
explore the criteria system next.
