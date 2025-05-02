# Order By Fetch

Coming soon...

From the homepage, head into one of the starships that actually is in
progress. One of  the starships that has some parts. So in
`templates/starship/show.html.twig`, we get these parts by saying `for part
in ship.parts`. So we're basically calling `ship->getParts()`  to get
these. Now the only problem is that we can't guarantee the order of these
parts.  So right now they're just kind of coming out of the database in
whatever order they want. What I'd like to do is order these by name. Does
that mean we can't use our cool  `ship.parts` anymore? Fortunately not. We
do have some control over this, over how these come out of the database. To
do that, find the `Starship` entity and go to the `parts`  property. Right
above `parts`, we're going to add a new attribute here. There we go. 
`#[ORM\OrderBy(['name' => 'ASC'])]`. Except we don't have a position
property. We are  going to order by our `name` property. `Name` ascending. 

Let's refresh the page. And it's alphabetical. Now if you're confused why T
is coming  after R, that's because Postgres is a case sensitive database.
And so the capital T is  actually alphabetically before the lowercase C. So
it is ordering alphabetically. 

Check out the query for this page. How many of you formatted a query?
Perfect. So you can  see we're querying from `starship.part`, where
`starship ID` equals our `ID`, order by  `name` ascending. So it's exactly
the query that we want. 

Head to the home page, and open up the template for this, which is going to
be  `templates/main/homepage.html.twig`. Down here, right after arrived,
let's add a new div.  We'll say parts. And we're just going to print out
how many parts there are. So this is  easy, right? `Ship.parts|length`.
Love it. 

Back on the home page, it works fine. Let's check out the queries for this
page. They  look a little crazy because of our pagination. That does some
wild stuff. But basically,  we have one query up here for the starship. And
then if we query for `starship.part`, if  we search for `starship.part`,
you'll find that we have about five extra queries here for  the starship
parts for each of the starships. 

So basically, what happens here is we grab the `Starship` at the very
beginning. And  then as soon as we try to count the `ship.parts`, it
doesn't have that data yet. So one  by one for each ship, it grabs all of
the parts for that ship. And then it counts them. 

This idea of having one query for the ship and then one extra query for
every single part  is called the N+1 problem. It's a small performance
problem that we're going to tackle  later. It's caused by Doctrine's really
cool lazy loading. The problem here is even  crazier. We're querying for
all of the data for every `starship.part` just to count them. We don't
actually need the part data, we just need to know how many parts there are.


Again, this is a minor performance issue, unless you have a ship with a lot
of parts. To  fix this, back in `Starship`, above on our one to many,
doesn't actually matter where,  but to keep my editor happy, right here,
I'm going to add a little... I'll fetch and  we're going to call this extra
lazy. `#[ORM\OneToMany(targetEntity: StarshipPart::class,  mappedBy:
'starship', fetch: 'EXTRA_LAZY', orphanRemoval: true)]`

Spin back over. Just before we had, let's see here, nine queries, we go to
home page now  and refresh, we still have nine queries. The difference is
not less queries, but check  out most of the parts queries. It's now just
`SELECT COUNT(*)`. So Doctrine is smart  enough to see that all we really
want is just the count of the parts. So instead of  querying for all of
their data, it just counts to see how many there are. Much more  efficient.
So you might be wondering, why don't we always use `fetch="EXTRA_LAZY"`? 

The first answer to that is that this is a tiny performance optimization
that I wouldn't  worry about unless a ship can have a lot of parts and you
just want to count them. Second, there are some cases where this can cause
an extra query. Again, this is a minor  performance issue, but that's why
it's not the default. 

Next, what if instead of getting all the related parts for a ship, we only
want the  related parts that cost above a certain price? Can we still use
the `ship.parts` shortcut  or do we need to do a custom query? Let's learn
about the criteria system next.