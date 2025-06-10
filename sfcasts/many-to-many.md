# Many-To-Many Relationship

Alrighty, we've got a `Starship` entity and a `Droid`
entity set up and ready to mingle. But how do we get these two
entities to connect?

Picture it this way: Each `Starship` is going to need a crew of `Droids` to keep
things running smoothly... and for the occasional comic relief.
Each `Droid`, in turn, should be able to serve on many `Starships`. Forget
about the database for a second, and just focus on the objects. Our `Starship`
entity needs a `droids` property that holds a collection of all
the `Droid`s assigned to it.

Head back to your Symfony console and run:

```terminal
symfony console make:entity
```

## Creating a Galactic Social Network

Update `Starship` and add a `droids` property. Use "relation" to get into
our handy wizard. This time, we need a `ManyToMany` relationship:
each `Starship` can have many `Droids`, and each `Droid` can serve on many
`Starships`. That sounds perfect!

Next, it'll ask us if we want to map the inverse side of the relationship.
This is basically asking if we want to give our `Droids` the ability to
list all the `Starships` they're connected to – `$droid->getShips()`.
Sounds useful, right? Let's go ahead and say yes. For the new field name
inside a `Droid`, `ships` will do just fine. 

Notice it's updated *both* the `Starship` and
`Droid` entities. Take a peek at the changes in each.

## The 'ManyToMany' Magic

In `Starship`, we now have a new `droids` property, which is a
`ManyToMany`. It also initialized `droids` to the `ArrayCollection` and
added `getDroids()`, `addDroid()`, and `removeDroid()` methods. If you're
thinking this looks a lot like a `OneToMany` relationship, give yourself a
pat on the back. You're spot on!

Over in `Droid`, it's a similar story. We have a `ships` property, which is
a `ManyToMany`, and it's initialized in the constructor. Then we have the same
`getStarships()`, `addStarship()`, and `removeStarship()`. 

Go ahead and generate the migration for this. Back to the console
and run:

```terminal
symfony console make:migration
```

## Unveiling the Join Table

HERE

Marvelous! Take a peek at what it generated: it's fascinating. We have a new
table called `starship_droid`! It features a `starship_id` foreign key to
`starship` and a `droid_id` foreign key to `droid`. This is how you
structure a `ManyToMany` relationship in the database: with a join table.
The real magic of Doctrine is that we only need to think about objects. A
`Starship` object has many `Droid` objects, and a `Droid` object has many
`Starship` objects. Doctrine handles the tedious details of saving that relationship
to the database.

Before we move on, run that migration. Spin back to the terminal,
run:

```terminal
symfony console doctrine:migrations:migrate
```

And voilà, we have our shiny new join table. Cool, but how
do we *relate* `Droid` objects to `Starship` objects? That's
next... and you're gonna love it!
