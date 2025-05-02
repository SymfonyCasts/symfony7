# N Plus One Join

Coming soon...

Okay, team, we have our `parts` table. The next thing you want to do is
order this by the price in descending order. This is simple enough. You
will use a custom query for this, which means you need to go into your
`PartRepository`. Navigate to `src/Repository/StarshipPartRepository.php`.
Let's create a new method here. You can copy this stubbed method. Uncomment
that because you already have some good PHP doc up here. Now clean up down
here by removing the last stub method. 

Let's name this method `findAllOrderedByPrice()`. You don't need the
`value` anymore. The query builder will be very straightforward. Call the
`StarshipPart` as `sp`. You don't need `andWhere` or the `setParameter()`
below that. You do want to keep `orderBy()`, though you're going to tweak
it a bit. So use `orderBy('sp.price', 'DESC')`. Get rid of
`setMaxResults()` as well. 

This is a very straightforward custom query. Copy the name of this method.
Now, head over to your `PartController`. You'll use
`findAllOrderedByPrice()` instead of `findAll()`. 

Now what you really want to look at here are the queries for this page. You
will notice there are nine database queries. The first one is what you'd
expect. It's querying for all the `StarshipPart`s with a price in
descending order. But what are all these other queries? 

You actually have one query per `Starship`. Here you're querying for the
`Starship`. So what's happening here is you query for all the parts. Then
when you're in the `index.html.twig` template looping over the parts, at
this moment, you print out `{{ part.starship.name }}`. So at this moment,
Doctrine says, oh no, I have the `part` data, but I don't have the
`Starship` data for this `part`. So I better go query for it. 

So you get one query for the parts. Then you get one extra query for the
`Starship` for every single `part` in there. This is called the N plus one
problem. So if you have 10 parts, you're going to end up with one query for
the parts and then 10 extra queries, one query for the `Starship` for each
of those parts. And again, this is just a performance problem and it's
maybe not even that big of a deal, but it's something to be aware of.

 And the way you fix it is with a join. So in `StarshipPartRepository.php`,
you're going to make your `findAllOrderedByPrice()` method a little bit
fancier. Use `innerJoin('sp.starship', 's')`. The important thing here is
that you are not worried about like the foreign key columns and joining
like `starship_id` to `id`. All you have to do is join on the property. So
you're just joining on `StarshipPart.starship` or joining on the `starship`
property. You're aliasing the entire `Starship` table over to `s`. 

Now, before you had nine database queries. Now you still have nine database
queries. Why? There are two reasons to do a join. The first is to avoid the
N plus one problem. And the second is to do a `where` or `orderBy()` on the
join table. We're going to talk about that second reason really soon. 

For the N plus one problem, in addition to the join, you need to select the
data over on `Starship`. To do that, it's really simple. You're going to
say `addSelect('s')`. So it's really cool. You're aliasing the entire
`Starship` table to `s`. Then with `addSelect()`, you don't select
individual columns. You just say, hey, I just want to select the whole darn
thing. 

So now you have nine database queries. Let's refresh. And you're down to
one. That's incredible. You can see here, you are selecting from
`StarshipPart`. You're grabbing all the data from both `Starship` and
`StarshipPart`. And you have the `innerJoin` right there. Again, you don't
have to worry about the details of joining on which columns. All you have
to do is just do the join on the property. And Doctrine is going to take
care of all those boring details for you. 

Next, let's add a search to our page. And when you do that, you're going to
see the second use of a join.