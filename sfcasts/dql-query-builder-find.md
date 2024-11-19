# Fetching with DQL, the QueryBuilder & find()

Our database is now full of shiny, dummy starships! But this homepage is still
showing the hardcoded ships. Lame! Time to load these from the database. That'll 10x
the awesomeness of our app!

Spin over to your terminal. Remember that SQL query to select
all starships? Run it again:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

That's raw SQL but Doctrine ORM has its *own* query language called DQL: Doctrine Query
Language! It's like SQL, but instead of querying from tables, withv DQL you think
in terms of querying for the entity *objects*. Run the same query as above but as DQL:

## Writing Manual DQL

```terminal
symfony console doctrine:query:dql 'select s from App\Entity\Starship s'
```

This looks a bit funky, but it's PHP dumping our `Starship` objects - and there's
three of them, just like the raw query.

Let's leverage this in our homepage controller. Open
`src/Controller/StarshipController.php` and find the `homepage()` method. Instead of
injecting *this* `StarshipRepository` (this is the old one from the `Model` directory),
replace with `EntityManagerInterface $em` from Doctrine.

In the last chapter, we saw that Doctrine passes an `ObjectManager` to the `AppFixture::load()`
method. This `EntityManagerInterface` is a *type* of `ObjectManager` and it's what
we'll use to autowire the Doctrine entity manager.

Below, say:
`$ships = $em->createQuery()` and pass the DQL string:
`SELECT s FROM App\Entity\Starship s`. Finally, call `->getResult()`.
This *executes* the query, grabs the data but returns an array of `Starship` objects
instead of the raw data, which is amazing!

Leave the rest of the method as is.

Spin over and refresh the homepage. It's basically the same... that's a good
sign! Look closely at the web debug toolbar - there's a new "Doctrine" section.
OooooooOooo. Click to open the "Doctrine" profiler panel. *So* cool. This shows all
the queries that were executed
during the last request. We see just one: that makes sense!

We can see a formatted query that's more readable, a runnable query that we can copy
and paste into our favourite SQL tool, an  "Explain query" button to see database-specific
info about how the query was executed, and a "View query backtrace".

This is my favorite! It shows the call stack that led to
this query. Super useful to track down *what* code triggered the query, in this case,
our `homepage()` method.

## Using the QueryBuilder

One bummer is that DQL isn't *that* pretty!
Luckily, Doctrine has a "query builder". This thing is awesome: instead of writing
the DQL string manually, we *build* it with an object.
Back in our `homepage()` method, replace `$em->createQuery()` with
`$em->createQueryBuilder()`. Off it, chain `->select('s')`, then 
`->from(Starship::class, 's')` hitting tab add the `use` statement from `App\Entity`.
Bonus that we can use `Starship::class` instead of the string.

Finally, call `->getQuery()` and `->getResult()`.

Back in the app, refresh the homepage... still works!

We still need to refactor one thing. Click on one of the ships... oh no!

> Starship not found.

Ahh, our `StarshipController::show()` action is still using the old `StarshipRepository`
with the hardcoded data. We need to fix that!

Open `src/Controller/StarshipController.php` and find the `show()` method. Since
we need to query for data, replace
`StarshipRepository $repository` with `EntityManagerInterface $em`. In this case,
the query is so simple there's a shortcut method.

Say `$ship = $em->find(Starship::class, $id)`.
The first argument of `find()` is the entity class want to fetch, and the second is the ID.
Easy!

Back to the app and... refresh. It works! Look at the web debug toolbar - a single query
was run to fetch the starship.

We're done with our old `Model/` directory. Well, almost, the `StarshipStatusEnum` is still
needed so move it to `Entity/` to keep things organized. PhpStorm will handle all the renaming. Now,
delete `src\Model` and celebrate! I *love* deleting unused code!

Next up! Let's check out entity repositories as a way to move
querying logic out of our controllers and organized.
