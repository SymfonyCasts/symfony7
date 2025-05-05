# Part Entity

We've already got starships showing up on our homepage, thanks to the
nifty `Starship` entity we built in the last tutorial. But now, it's time
to step up our game. We need to tracking the individual parts used in
each `Starship`. Here's the plan: each part will belong to exactly one
`Starship`, and each `Starship` will have many parts. But before we dive
into relationships, we need to start with simple: we need a new
entity to keep track of these parts! Fire up your terminal, open a new
tab (since we have our server humming along in the other), and run:

```terminal
symfony console make:entity
```

Call it `StarshipPart`, skip broadcasting, and give it a few fields: `name`
will be a string and won't be nullable, `price` will be an integer (in credits,
of course), and also won't be
nullable. Lastly, add a `notes` field which will be a `text` type (so
it can be longer), and will be nullable. Once you've added these fields,
create a new migration for our new entity by copying and pasting
`symfony console make:migration`.

## Running Migrations and Adding Timestamps

Now, if you check out your migrations, you'll see the new one we just
created. I cleaned up the old migrations from the previous project,
so this one is all about `StarshipPart`. Run it with:

```terminal
symfony console doctrine:migrations:migrate
```

The table is in the database! But there are two fields that I
like to add to all my entities: `createdAt` and `updatedAt`.
You can see these inside of `Starship`, under `TimestampableEntity`.
Let's copy that, and paste it right on top of `StarshipPart`. Both properties
are automatically set thsnks to a library we installed in the last
tutorial. And because Since we've added two new fields, we need to run

```terminal
symfony console make:migration
```

again, and then migrate it:

```terminal
symfony console doctrine:migrations:migrate
```

## Using Factories to Create Dummy Objects

In the last tutorial, we used a cool library called `Foundry` to quickly
create a bunch of dummy data. We're going to do the same for
`StarshipPart`. Step 1, since we don't have one yet is to generate a factory for
the new entity with:

```terminal
symfony console make:factory
```

It's added some defaults for each of the fields, but we can make it more
interesting. At the top of `StarshipPartFactory`, I'll paste in some code
with example parts (you can grab this from the code block on this page).
Also replace the return in `defaults()` with code that uses our
random data we've added. Finally, use the factory in our fixtures.
At the bottom, create 50 random parts using
`StarshipPartFactory::createMany(50)`. Back in the terminal, run:

```terminal
symfony console doctrine:fixtures:load
```

Confirm that we want to vent the oxygen from our database, then check out the new
parts with:

```terminal
symfony console doctrine:query:sql
```

And then: `select * from starship_part`

And with just a few lines of delightful code, we have 50 random parts
in our database. Next: let's start linking these parts to their respective ships
by creating our first relationship: a `ManyToOne` relationship.
