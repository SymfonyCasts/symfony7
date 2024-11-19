# Fetching with DQL, the QueryBuilder & find()

Now that we have these starships in our database, let's refactor the homepage to
fetch them!

First, spin over to your terminal. Remember that raw SQL query we ran to select
all starships?

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

That's raw SQL but Doctrine ORM has its own query language called DQL: Doctrine Query
Language - go figure! If SQL is like a query language for tables, DQL is a query
language for objects. Run the same query as above but as DQL:

```terminal
symfony console doctrine:query:dql 'select s from App\Entity\Starship s'
```

This looks a bit funky but this is PHP dumping our Starship objects - and there's
three of them, just like the raw SQL query.

We're going to leverage this to refactor our homepage controller. Open
`src/Controller/StarshipController.php` and find the `homepage()` method. Instead of
injecting this `StarshipRepository` (this is the old one from the `Model` directory),
replace with `EntityManagerInterface $em`.

You might recall in the last chapter, our `AppFixture::load()` method injected an
`ObjectManager`. `EntityManagerInterface` is also an `ObjectManager` instance but has
additional, ORM-specific methods. This will be the one we use most and Symfony
can autowire it.

Below, replace this with
`$ships = $em->createQuery()`. Here's where we can write the DQL to fetch all
starships: `SELECT s FROM App\Entity\Starship s`. Finally, call `->getResult()` -
this actually executes the query and returns an array of Starship objects. Leave the
rest of the method as is.

Spin over the app and refresh the homepage. It looks basically the same... that's a good
sign! Look closely at the web debug toolbar - there's a new "Doctrine" section. Click
it to open the "Doctrine" profiler panel. This shows all the queries that were executed
during the last request. We see just one, that makes sense. There's some great debugging
information here:

"View formatted query" shows the query in a more readable format.

"View runnable query": you can copy and paste this into your favourite SQL tool to
run the exact same query there.

"Explain query": shows database-specific information about how the query was executed.

"View query backtrace": this one is my favourite! It shows the call stack that led to
this query being executed. Super useful for tracking down rogue queries. Look, here's
our `homepage()` method that caused this query.

One thing that's a bit of a bummer about DQL is that it's a bit cumbersome to write.
Luckily, Doctrine has a "query builder" that allows us to write these queries with
a slick object. Back in our `homepage()` method, replace `$em->createQuery()` with
`$em->createQueryBuilder()`. Off it, chain `->select('s')`, then 
`->from(Starship::class, 's')`. Be sure to import `Starship` from `App\Entity`. Look,
we can use the `class` constant now! That'll help us avoid typos. Finally, before
`->getResult()`, call `->getQuery()`.

Back in the app, refresh the homepage... still works!

We still need to refactor one thing. Click on one of the ships... oh no!

> Starship not found.

Ahh, our `StarshipController::show()` action is still using the old `StarshipRepository`.
We need to fix that!

Open `src/Controller/StarshipController.php` and find the `show()` method. Replace
`StarshipRepository $repository` with `EntityManagerInterface $em`. Because we're fetching a
single entity by its ID, instead of using DQL, we can use `EntityManagerInterface::find()`.

Replace this with `$ship = $em->find(Starship::class, $id)`.
Again, be sure to import the `Starship` class from `App\Entity`. The first argument of `find()`
is the entity class, so Doctrine knows what entity we want to fetch, and the second is the ID.
Leave the rest of the method as is.

Back to the app and... refresh. It works! Look at the web debug toolbar - a single query
was run to fetch the starship.

We are done with our old `Model` directory. Well, almost, the `StarshipStatusEnum` is still
needed so move it to the `Entity` directory. PhpStorm will handle all the renaming. Now,
delete `src\Model` and celebrate! I *love* deleting unused code!

Next, we're going to check out entity repositories as a way to move Doctrine-specific
querying logic out of our controllers. This will keep our controllers nice and lean!
