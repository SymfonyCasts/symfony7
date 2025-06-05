# Droid Entity for ManyToMany Relationship

We've had a good taste of relationship types by now. We've been
formally introduced to `ManyToOne` and `OneToMany`, which are really
the *same* relationship type, just viewed from different sides.
So, in reality, we've only explored *one* type of relationship so far:
`ManyToOne`.

What about that `OneToOne` relationship you've maybe heard about? Well...
surprise! It's just a `ManyToOne` in disguise: the database looks just like
a `ManyToOne` ecept it has a unique constraint on the foreign key to
make sure that each side of the relationship can only relate to *one*
item.

The point is: `ManyToOne`, `OneToMany`, and `OneToOne` are all a type of
the same *one* relationship type.

## Enter the Droids

Let's talk space repair. For us meat sack humans, it's dangerous work!
There's the vacuum of space, the cold, the lack of oxygen and occasional
asteroid showers. That's not even mentioning when Bob forgot to secure
his harness and went floating off into the void. It took *hours* to find him.

So then who better to tackle this than our trusty droids? 

You command an army of droids, each is assigned to multiple starships and
each starship has multiple droids. This is where the second and *final* relationship
type comes in: `ManyToMany`.

To set the stage, we need a `Droid` entity. At this point, you know the drill:

```terminal
symfony console make:entity Droid
```

And just like that, we're in business. This needs just a few properties:
`name` and `primaryFunction`. The defaults will do just fine. That's it,
easy peasy. 

But a developer's work is never done. After you've made your
changes, it's migration time. Copy the command, because we're developers, and
developers are efficient (not lazy!)... and paste:

```terminal-silent
symfony console doctrine:migrations:migrate
```

Voilà! We've got a shiny new `droid` table in the database. It's not yet
in a relationship with `ship`, but hey, every relationship has to start
somewhere.

## Populating the Universe with Droids

Before we set that up, let's manufacture some droids! Run:

```terminal
symfony console make:factory Droid
```

Open up `src/Factory/DroidFactory.php`. It's ready to go, 
but let's add some personality to these droids.
I'll replace the array with more interesting data.

Reload the fixtures with:

```terminal
symfony console doctrine:fixtures:load
```

And there you have it! A `droid` table filled to the brim with
droids that are ready to help *and* not die in the lonely vacuum of space.
But a droid can't be assigned to a ship yet. Let's change that with our
final type of relationship: `ManyToMany`.
