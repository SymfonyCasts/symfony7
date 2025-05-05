# Order By Fetch

Coming soon...

## Starting With Starships

Alright folks, let's dive into one of our starships that's not just sitting
idle, but is a work in progress. You know, the kind of starship that's got
some parts to play with. So, let's go to
`templates/starship/show.html.twig` and say `for part in ship.parts`.
Basically, what we're doing here is calling `ship.getParts()` to get our
hands on these parts. 

But hang on, there's a hitch. We can't quite guarantee the order these
parts show up in. They're pretty much just popping out of the database in
whatever order they fancy. I'd rather have these parts lined up by their
names. But does this mean we can't use our handy `ship.parts` anymore? Fear
not, my friends. We still have control over this. 

## Rearranging the Parts

So, to tame these parts, head over to the `Starship` entity and find the
`parts` property. Just above `parts`, we're going to plant a new attribute.
Voila! `#[ORM\OrderBy(['name' => 'ASC'])]`. But wait, we don't have a
position property. So, we're going to order by our `name` property instead.
Ascending order, because that's how we roll. Refresh the page, and there
you have it - ordered alphabetically. 

Now, if you're scratching your head wondering why T is coming after R,
don't worry, you haven't forgotten your ABCs. It's just that Postgres is a
case sensitive database. So the uppercase T actually comes before lowercase
C in the alphabetical order.

## Querying Like a Pro

Now, how about we check the query for this page? How many of you have ever
formatted a query? Perfect, let's delve into it. As you can see, we're
querying from `starship.part`, where `starship ID` equals our ID, ordered
by `name` ascending. Just the query we wanted.

## Coming Home to More Parts

Now, let's head back to the homepage and open up the template for it,
`templates/main/homepage.html.twig`. After 'arrived', let's add a new div
to display the parts. And we're simply going to print out the number of
parts. Easy peasy, right? `ship.parts|length`. 

And voila, back on the homepage, it works like a charm. The queries for
this page look a bit wild because of our pagination, which can be pretty
adventurous. Essentially, we have one query for the starship, and if we
search for `starship.part`, we find about five extra queries here for the
starship parts for each of the starships. 

## The N+1 Problem

What's happening here is that we grab the starship at the start and then,
as soon as we try to count the `ship.parts`, it realises it doesn't have
that data yet. So it fetches all of the parts for each ship one by one and
counts them. This quirky situation, where we have one query for the ship
and then one extra query for every single part, is known as the N+1
problem. It's a minor performance problem that we're going to tackle later.
This is a byproduct of Doctrine's cool lazy loading feature. 

## Efficient Querying

The issue here is that we're querying all the data for every
`starship.part` just to count them. We really don't need the part data, we
just need to know how many parts we've got. That's a bit like asking for
the life history of every fish in the sea when you just want to know how
many there are. A minor issue, unless you have a ship with a ton of parts. 

To fix this, in our `OneToMany` in the Starship entity, we're going to add
something called `fetch` and we're going to call this `EXTRA_LAZY`. This is
Doctrine's way of saying "Hey, I'll only fetch the data when I absolutely
need it". Thus, making our process more efficient. 

## Counting the Parts

Let's head back to our homepage. Earlier, we had nine queries, but now,
even though we still have nine, the parts queries have changed. Instead of
querying for all their data, it just counts how many parts there are. Much
smarter, right?

You might be wondering, why don't we use `fetch="EXTRA_LAZY"` all the time?
Well, first, this is a small performance optimization that you don't need
to worry about unless you have a ship full of parts and just want to count
them. Second, there are some cases where this might lead to an extra query,
which is a minor performance issue.

## The Criteria System

Now, onto our next challenge! What if we only want the related parts for a
ship that cost above a certain price? Can we still use the `ship.parts`
shortcut or do we need to do a custom query? Stay tuned, we're going to
explore the criteria system next. Buckle up!