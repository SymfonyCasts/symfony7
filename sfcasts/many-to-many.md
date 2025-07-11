# Many-To-Many Relationship

Alrighty, we've got a `Starship` entity and a `Droid`
entity set up and ready to mingle. How do we get these two
entities to connect?

Picture it this way: Each `Starship` is going to need a crew of `Droids` to keep
things running smoothly... and for the occasional comic relief.
Each `Droid`, in turn, should be able to serve on many `Starships`. Forget
about the database and just focus on the objects. Our `Starship`
entity needs a `droids` property that holds a collection of all
the `Droid`s assigned to it.

Cool! Head back to your terminal and run:

```terminal
symfony console make:entity
```

Update `Starship` and add a `droids` property. Use "relation" to get into
the handy wizard. This time, we need a `ManyToMany` relationship:

> Each `Starship` can have many `Droids`, and each `Droid` can serve on many
> `Starships`. That sounds perfect!

Next, it asks us if we want to map the *inverse* side of the relationship.
This is asking if we want to give our `Droids` the ability to
list all the `Starships` they're connected to: `$droid->getShips()`.
That sounds useful. So let's say "yes". For the new field name
inside `Droid`, `starships` will do just fine. 

Notice it's updated *both* `Starship` and `Droid`. Take a peek at the changes
in each.

## The 'ManyToMany' Magic

In `Starship`, we now have a new `droids` property, which is a
`ManyToMany`. It also initialized `droids` to the `ArrayCollection` and
added `getDroids()`, `addDroid()`, and `removeDroid()` methods:

[[[ code('d248b94310') ]]]

If you're thinking this looks a lot like a `OneToMany` relationship, ding, ding!
Order yourself a pizza! Because it totally is!

Over in `Droid`, it's a similar story. We have a `starships` property, which is
a `ManyToMany`, and it's initialized in the constructor. Then we have the same
`getStarships()`, `addStarship()`, and `removeStarship()`:

[[[ code('bac832adec') ]]]

Generate the migration for this. Go back to the terminal and run:

```terminal
symfony console make:migration
```

## Unveiling the Join Table

Marvelous! Take a peek at what it generated: it's fascinating. We have a new
table called `starship_droid`! It features a `starship_id` foreign key to
`starship` and a `droid_id` foreign key to `droid`:

[[[ code('f94c11804b') ]]]

This is how you structure a `ManyToMany` relationship in the database: with a join table.
The real magic of Doctrine is that we only need to think about objects. A `Starship`
object has many `Droid` objects, and a `Droid` object has many `Starship` objects.
Doctrine handles the tedious details of saving that relationship to the database.

Before we move on, run that migration. Spin back to the terminal and do it:

```terminal
symfony console doctrine:migrations:migrate
```

Cool! We now have a shiny new join table. Ok... but how
do we *relate* `Droid` objects to `Starship` objects? That's
next... and you're gonna love it!
