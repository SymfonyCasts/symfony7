# Many To Many

Coming soon...

## Tying the Knot between Starships and Droids

Alrighty, my Symfony stars, we've got our `Starship` entity and our `Droid`
entity all set up and ready to mingle. But, just like a good space opera,
we've got to answer that age-old question: "How do we get these two
entities to connect?" 

Picture it this way: Each `Starship` is like a high-tech party bus, and
it's going to need a crew of `Droids` to keep things running smoothly. And
each `Droid`, in turn, should be able to serve on many `Starships`. Forget
about the database for a second, and just focus on the objects. Our `Droid`
is going to need a `Starship` property that's going to hold an array of all
the `Starship` party buses it can hop on to. 

So let's dive in. Head back to your Symfony console and run this:

```terminal
make:entity
```

## Creating a Galactic Social Network

We're about to give our `Starship` entity a social upgrade. We're going to
add a `droids` property, which is going to be an array of `Droid` objects,
or a whole collection of `Droid` party-goers, if you will. Back to our
friendly little wizard, the relation type. This time, we're going to opt
for a `ManyToMany` relationship. Just like a good space opera, each
`Starship` can have many `Droids`, and each `Droid` can serve on many
`Starships`. It's exactly the kind of interstellar networking we're after,
so type `ManyToMany`. 

Next, it'll ask us if we want to map the inverse side of the relationship.
This is basically asking if we want to give our `Droids` the ability to
list all the `Starships` they're connected to – `$droid->getShips()`.
Sounds useful, right? Let's go ahead and say yes. For the new field name
inside a `Droid`, `ships` will do just fine. 

Once that's done, you'll notice it's updated both the `Starship` and
`Droid` entities. Let's take a gander at the changes in each of those
entities.

## The 'ManyToMany' Magic

In `Starship`, we now have a new `droids` property, which is a
`ManyToMany`. It's also initialized `droids` to the `ArrayCollection` and
added `getDroids()`, `addDroid()`, and `removeDroid()` methods. If you're
thinking this looks a lot like a `OneToMany` relationship, give yourself a
pat on the back. You're spot on!

Over in `Droid`, it's a similar story. We have a `ships` property, which is
a `ManyToMany`, and it's initialized in the constructor. We have the same
`getStarships()`, `addStarship()`, and `removeStarship()`. 

Alrighty, let's go ahead and generate the migration for this. So, back to
the Symfony console, and let's run:

```terminal
make:migration
```

## Unveiling the Join Table

Marvelous! Now, let's take a peek at what we've created. It's quite
fascinating. We've got a new table called `starship_droid`, which features
a `starship_id` foreign key and a `droid_id` foreign key. This is how you
structure a `ManyToMany` relationship in the database, with a join table.
The real magic of Doctrine is that we only need to think about objects. A
`Starship` object has many `Droid` objects, and a `Droid` object has many
`Starship` objects. Doctrine is like our own personal droid, taking care of
all the tedious details of saving that relationship to the database.

Before we move on, let's run that migration. Back to the Symfony console,
and let's run:

```terminal
doctrine migrations:migrate
```

And voila, we have our shiny new join table. Now, the burning question: How
do we actually relate `Droids` to `Starship` objects? Well, my friends,
that's the adventure we're about to embark on next. Let's see how this all
saves into the database.