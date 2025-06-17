# Many-to-Many but with Extra Data

Coming soon...

Let's dive into the unique world of many-to-many relationships in Doctrine.
Imagine we have a table in our database, say `StarshipDroid`. Now, here's
the cool part: there's no corresponding entity in your project! It's like a
phantom table that Doctrine handles all by itself. Kinda awesome, huh?

But there's a catch — we can't add extra columns to that join table.
Picture trying to track the date a droid was assigned to a starship. In the
database universe, we might add an `assignedAt` column to that join table.
But in the Doctrine realm, we can't. 

## When Many-to-Many Becomes Too Much 

As soon as you need extra data on the join table, you're going to have to
roll up your sleeves and start handling things more manually. It's like
trying to fit a square peg in a round hole — you're going to need a new
strategy. 

You'll stop using the many-to-many relationship entirely and instead, we're
going to generate a new entity that represents that join table. First
thing's first, let's undo the many-to-many relationship (but only worry
about the properties, not the methods). In `Starship`, wave goodbye to the
`droids` property, and over in `Droid`, do the same for the `Starship`'s
many-to-many property. Clear out the constructor code in both — it's like
a spring cleaning for your code. 

Now, let's whip out your terminal and run this nifty little command: 

```terminal
symfony console doctrine:schema:update --dump-sql
```

This is like a crystal ball for your code. It shows you what your migration
would look like if you generated it right now. Pretty cool, huh?

## Creating a New Join Entity

Before we actually make that migration, let's create the new join entity,
`StarshipDroid`. Run this command:

```terminal
symfony console make:entity StarshipDroid
```

Sure, `DroidAssignment` might be a more fitting name, but `StarshipDroid`
helps us visualize what we're doing: recreating the same exact database
relationship. 

Now, let's add a few properties. Remember that `assignedAt` column we
wanted? Let's add that, along with two more properties to create
relationships from this join table to `Starship` and `Droid`. 

These are going to be `ManyToOne` relationships. They're like the lifeblood
of our code, connecting `StarshipDroid` to `Starship` and `Droid`.

## Bringing it All Together

Now, let's generate that migration. Run `symfony console make:migration`
and check it out. Yes, it might look like there's a lot of changes, but if
you squint your eyes, you'll see it's just dropping the foreign key
constraints, adding a primary key, and recreating the foreign key
constraints. It's like a magic trick — a lot of flash, but in the end,
everything's right where it was.

But we're not done yet. Let's run this migration with `symfony console
doctrine:migrations:migrate`. And boom! `Column assignedAt cannot contain
null values`. It's like Doctrine is throwing a tantrum because of the
existing rows in our `StarshipDroid` table. But don't worry, we can pacify
it with a default value. Update the migration manually to say `DEFAULT
NOW() NOT NULL`. 

## The Finishing Touches

Let's add a final touch to `StarshipDroid`. This `assignedAt` isn't really
something we should have to worry about. It's like a ticking clock — it
should just keep time on its own. Let's create a constructor to set this
automatically: `assignedAt = new \DateTimeImmutable();`. 

And voila! We now have the exact same relationship in the database as
before. But we've taken control of the join entity, so we can add new
fields to it. Next, we'll see how to assign droids to Starships with this
new entity setup. And eventually, we'll get fancy and hide this
implementation detail entirely. Buckle up, it's going to be a fun ride!
