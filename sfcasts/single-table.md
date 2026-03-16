# Single Table Inheritance

Alright, so in order to be able to fetch all the starships at once, we
need to use Doctrine's second type of inheritance: Single Table Inheritance.
With this feature, all entities that are part of the hierarchy are stored in
a single table. This means we don't have a unique table for each entity.
Instead, all entities within that hierarchy are part of the same table.

And implementing doesn't require much change to our code.

## Modifying the Starship Entity

The first thing you need to do is find the *parent* entity in your hierarchy.
This is the entity that all the other
entities in our hierarchy extend from.

In our case, it's `Starship`, so
open that up. Change the `MappedSuperclass` attribute back to `Entity`. It may seem
a bit strange because it's abstract, but for querying purposes, it *is* an entity.
Also, add back `repositoryClass: StarshipRepository::class` as the argument.

Next, add the attribute `#[ORM\InheritanceType()]` to the class. Oops, I have
an extra `ORM` here. The argument for this attribute is a string that specifies
the type of inheritance we're using. In our case, `SINGLE_TABLE`.

Because it's a single table, Doctrine needs to know which entity type is
associated with each row. In our case, it could be a `freighter` or a
`scout`. To accomplish this, we need to set up a *Discriminator column*.

Add the `#[ORM\DiscriminatorColumn]` attribute. Dig into this to take a look
at its signature. This looks pretty similar to the normal `Column` attribute
you're used to using. And, yep, it does define a column in the database. So you
can use this attribute to configure its schema.

Set the name to `ship_type` and the type to `string`.

One important thing to note. This column is only used internally by Doctrine.
It's not something you can access from your entity. This is kind of a bummer
because it would be nice to be able to access this from our code. We'll look
at a work-around for this later.

## Setting Up the Discriminator Map

Finally, we need the Discriminator Map. This is how Doctrine knows which entity class to
instantiate when it fetches a row from the database.

Add the `#[ORM\DiscriminatorMap]` attribute. It takes an array where the keys
are the value stored in the discriminator column, and the values are the entity classes.

So, add the `freighter` key and set it to `Freighter::class`. Then add the `scout` key
and set it to `Scout::class`.

Even though our `Starship` is abstract here (meaning you can't instantiate
it), that's not a limitation. If you wanted to, you could instantiate and
save `Starships` without a child type. All you would need to do is remove the
abstract and include it in the discriminator map.

## Updating the Database Schema

Let's see what this change looks like as a schema update to our database.
Over in your terminal, run:

```terminal
symfony console doctrine:schema:update --dump-sql
```

You'll notice that it's dropping the `freighter` and `scout` tables we had
when we were using `MappedSuperclass`. They're no longer necessary because we
now have a single `starship` table that contains all the properties of our
`starship` and their child entities.

Notice the `ship_type` field, this is the discriminator column
we configured. Also notice the `cargo_capacity` and
`sensor_range` fields. All the properties of our child entities are now
stored in the database this way. One thing to keep in mind is that when we
configured our `scout` and `freighter` entities, we made their properties
not nullable. With single table inheritance, Doctrine makes them nullable
because not all entities will have those properties, and need to be `null`
if this is the case.

## Reloading the Database with Fixtures

Let's reload our fixtures! Run:

```terminal
symfony console foundry:load-fixtures
```

Awesome, no changes needed for our fixtures.

To see what our `starship` table looks like, run:

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

Note that this command has been replaced with `dbal:run-sql` in later
versions of Doctrine. So, if this command doesn't work, use `dbal:run-sql`.

The output is pretty gross, but we can kind of make out what's going on.
The `ship_type` column is here, and we can see it's set to `freighter` for
the first 3 rows and `scout` for the last 3. The `cargo_capacity` column is only
populated for the `freighter` rows, and the `sensor_range` column only has values
for the `scout` rows. This is exactly what we expected!

## Adjusting the Homepage Controller

Ok, time to query them all at once! Head over to `MainController::homepage()` and
inject `StarshipRepository` instead of `ScoutRepository`.

This `$ships` variable will now be an array `Scout` and `Freighter` entities.

Jump to the browser and refresh the homepage. Sweet! 6 ships!

Open up the Doctrine profiler panel and expand the formatted query. This looks
similar to a normal `findAll` query, but it includes our `starship_type`
discriminator column and uses it to filter for both `freighter` and `scout` types.

You can still use the `ScoutRepository` and the
`FreighterRepository`. They can be injected and used like normal, but be
aware they'll only work with `Scout` entities if you're using the
`ScoutRepository`.

## Considering the Drawbacks

There are a few cons to single table inheritance. First, your database can't have
nullable fields for the child entity properties. Second, if you have many
different children each with their own custom properties, you'll end up with a
lot of empty fields in your database. These may not be significant issues,
but there is a solution to them with the next inheritance type, which we'll
explore next!
