# Migrations

We have a `Starship` entity... but no `starship` table!
The solution? Database migrations!

Create our first migration by running:

```terminal
symfony console make:migration
```

Success! This didn't add the actual table, but it _did_ create a
new file in the `migrations/` directory. Let's check it out!

Ooh, it's a PHP class where the `up()` method holds the SQL to create our table.
What's neat is how this was created: Doctrine compared the current state of our
entities to the database and generated the SQL needed to make them match.
Wow!

There's also a `down()` method... because migrations can be reversed, but I've
never done that, so I don't worry about `down()`.

One thing to note about the SQL: it's in the format of the database
platform you're using. In our case, Postgres-specific SQL. If using SQLite,
you'd see SQLite-specific SQL.

If you want, add a note about what this does in `getDescription()`:
`return 'Add starship table'`.

## Checking the Migration Status

Pop over to the terminal and run:

```terminal
symfony console doctrine:migrations:list
```

The output is a bit wonky but we can see our migration class and its description.
The status is `not migrated` because we haven't executed it yet. Let's do that!

```terminal
symfony console doctrine:migrations:migrate
```

Are we sure we want to continue? Yes! Success! Try:

```terminal
symfony console doctrine:migrations:list
```

again. Status: `migrated`!

## How Migrations Work

But how does Doctrine track which migrations have been run? It creates a
`doctrine_migration_versions` table, then inserts a row for each migration after 
it's executed.

We can see it! Run:

```terminal
symfony console doctrine:query:sql 'select * from doctrine_migration_versions'
```

Look at that! There's our migration class, when it was executed, how long it took,
and the migration's favorite color! Ok, not that last one.

Does this mean we have our `starship` table? Run another raw SQL query to find out!

```terminal
symfony console doctrine:query:sql 'select * from starship'
```

> The query yielded an empty result set.

Green means good, right? Yup! This tells us that there's no data in the `starship`
table... but it *does* exist!

Entity class check: ✅ Database table check: ✅ 
Data in the database? Let's learn how to do that next!
