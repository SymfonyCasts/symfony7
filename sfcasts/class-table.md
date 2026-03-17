# Class Table Inheritance

Time to let's dive into the final type of Doctrine Inheritance:
Class Table Inheritance. This one goes back to using a single table
per entity in the hierarchy. But here's the rub - only properties
specific to that entity are stored in the table
(plus the id). When fetching, Doctrine does the necessary joins behind
the scenes to get all the data for the entity you're fetching.

Over in starship, in the `InheritanceType` attribute, change `SINGLE_TABLE`
to `JOINED`. Doctrine traditionally used `JOINED` to refer to this type of
inheritance, but it's more commonly known as Class Table Inheritance. That's it!

***NOTE
While this code change is super easy, database changes are not. If you had an
already established database with data in it, you'd need to do some careful
migrations.
***

To better show how the SQL changes, let's drop the schema and start fresh.
Over in the terminal, run

```terminal
symfony console doctrine:schema:drop --force
```

Now, create the schema with:

```terminal
symfony console doctrine:schema:create --dump-sql
```

## Observing the Changes

Let's see what we've got here. The freighter table just has an `id` and `cargo_capacity`.
The `starship` table has all the common properties, and the `scout` table has just an `id`
and `sensor_range`.

Let's load our fixtures to ensure everything still works.

```terminal
symfony console foundry:load-fixtures
```

Looks good. Jump over to the app and refresh. Perfect! We still have six ships!

## Checking the Database

Now, let's peek into the database to see what we're dealing with. Back in the terminal,
run:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

***NOTE
Remember, this command has changed to `dbal:run-sql` in newer versions of Doctrine.
***

As you can see, this only contains the columns for the `Starship` entity, not
`Freighter` and `Scout`. However, it still does include a `ship_type`.
And yep, we have three freighters and three scouts. If we want to see the specific
properties for these, they are in their respective tables.

Let's look at the freighter table. First, notice the `id` column for the freighters,
1, 2, and 3. These should match up with the ids in the freighter table. Run:

```terminal
symfony console doctrine:query:sql 'select * from freighter'
```

This just has the `cargo_capacity`'s for the three freighters, and sure enough, the
ids match up with the ids in the starship table. So, when Doctrine is building (or hydrating)
a starship, it knows that the ship with id 1 is a freighter. It then instantiates a `Freighter`
object, grabs the common properties from the starship table, joins with the freighter table on the id,
and grabs the cargo capacity. Phew, I'm glad Doctrine is doing all that for us!

## Profiler Inspection

Jumping back to the app, let's take a moment to check the profiler and see this in action.
View the formatted query to get a better view. The query is a bit more complex than before,
because of the joins, but it's doing exactly what we expected.

## Adding a New Entity

Let's up the ante with a slightly more advanced scenario. We're
going to create another ship - a mining freighter. It will be a subclass
of `Freighter`, taking us another level deep in the inheritance tree.

Back in the terminal, create the entity with:

```terminal
symfony console make:entity MiningFreighter
```

Give it one property called `laserPower`, an integer, not nullable, and done.
Now the Foundry factory:

```terminal
symfony console make:factory
```

Choose `all` to create the missing `MiningFreighter`.

Back in our IDE, open up our new `MiningFreighter` entity. Have it extend `Freighter` and
remove the `id` property and getter.

## Updating the Factory

Now, open the `MiningFreighterFactory`. We need to make a few adjustments
to the `FreighterFactory` before we can extend it, so open up that.

Remove the `final` so we *can* extend it. Next, we need to add a template so sub-factories
can specify the type of entity they are creating. In the class docblock, add
`@template T of Freighter`. Then, in the `@extends`, scope it to `T`.

Back in `MiningFreighterFactory`, have the class extend `FreighterFactory`. Do the same
for `@extends` in the docblock.

Down in `defatuls()`, add our `array_merge` trick. `array_merge(parent::defaults()`, second
argument, the array, and don't forget to close the function.

We have some cool `array_merge` inception going on here. The `MiningFreighterFactory` is merging
with the defaults from the `FreighterFactory`, which is merging with the defaults from `StarshipFactory`.

## Creating Some Data

Next, over in `AppStory`, create a few mining freighters with `MiningFreighterFactory::createMany(2)`.

Back in the terminal, reload the fixtures.

```terminal-silent
symfony console foundry:load-fixtures
```

Oops, we have an error... "Entity MiningFreighter has to be part of the discriminator map of Starship
to be properly mapped in the inheritance hierarchy."

This is a common step to forget when adding new entities to an inheritance hierarchy.

Back in `Starship`, in the `DiscriminatorMap` array, add our new entity with
`'mining_freighter' => MiningFreighter::class`. I like to use snake case for the keys,
but you can use whatever you want. The important thing is that the value is the class name
of the entity.

Now load our fixtures again.

```terminal-silent
symfony console foundry:load-fixtures
```

Nice, that worked! Refresh our app... and voila! We now have eight ships!

## Conclusion

Take a peek at the query in the profiler again. Another join was added for
the `mining_freighter` table.

This is one of the cons of class table inheritance, queries get more complex, the more
entities you have in your hierarchy. This can lead to reduced performance.

Another con you may not think of is, if you use external tools to query and manipulate your database,
it's more difficult to work with. You have to duplicate the joins and logic Doctrine
uses internally.

Like most things, there are trade-offs with each type of inheritance.

Next, we'll look at how to query the different types of starships.
