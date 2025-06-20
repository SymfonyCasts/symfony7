# Part Entity

We already have starships showing up on the homepage thanks to the
`Starship` entity we built in the last tutorial. But now, it's time
to step up our game. We need to track the individual *parts* used in
each `Starship`. Here's the plan: each part will belong to exactly one
`Starship`, and each `Starship` will have many parts. But before we dive
into relationships, we need to start simple: we need a new
entity to keep *track* of these parts! Fire up your terminal, open a new
tab (since our server is humming along in the other), and run:

```terminal
symfony console make:entity
```

Call it `StarshipPart`, skip broadcasting, and give it a few fields: `name`
will be a string and won't be nullable, `price` will be an integer (in credits,
of course), and also won't be
nullable. Lastly, add a `notes` field which will be a `text` type (so
it can be longer), and *will* be nullable. Once you've added these fields,
create a new migration for the entity by copying and pasting
`symfony console make:migration`.

## Running Migrations and Adding Timestamps

Now, if you check out your migrations, you'll see just the new one we
created: I cleaned up the old migrations from the last course.

[[[ code('0ded265acf') ]]]

So this one is all about `StarshipPart`. Run it with:

```terminal
symfony console doctrine:migrations:migrate
```

The table is in the database! But there are two fields that I
like to add to *all* my entities: `createdAt` and `updatedAt`.
You can see these inside of `Starship`, under `TimestampableEntity`.

[[[ code('026a51f1be') ]]]

Copy that, and paste right on top of `StarshipPart`. Both properties
are automatically set thanks to a library we installed in the last
tutorial. And because we added two new fields, we need a migration!

```terminal
symfony console make:migration
```

Then migrate:

```terminal
symfony console doctrine:migrations:migrate
```

## Using Factories to Create Dummy Objects

In the last tutorial, we used a cool library called `Foundry` to quickly
create a bunch of dummy data. We're going to do the same for
`StarshipPart`. Step 1 - since we don't have one yet - is to generate a
factory for the entity with:

```terminal
symfony console make:factory
```

Go check it out in `src/Factory/StarshipPartFactory.php`.
It added some defaults for each field, but *we* can make it more
interesting. At the top of `StarshipPartFactory`, I'll paste in some code
with example parts (you can grab this from the code block on this page).
Also replace the return in `defaults()` with code that uses that
data. 

[[[ code('68657d878e') ]]]

Finally, use this in the fixtures. At the bottom, create 50 random parts using
`StarshipPartFactory::createMany(50)`. 

[[[ code('01ee7a226f') ]]]

Back in the terminal, run:

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
in the database. Next: let's start linking these parts to their respective ships
by creating our first relationship: the all-important `ManyToOne` relationship.
