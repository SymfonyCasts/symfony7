# Persisting the More Complex Many-to-Many Relationship

We refactored our many-to-many relationship to include a join entity
called `StarshipDroid`, instead of relying on Doctrine to create
the join table for us. Reload our fixtures, but hold on to your
hats:

```terminal-silent
symfony console doctrine:fixtures:load
```

Error!

> Undefined property: `App\Entity\Starship::$droids`

This error is being spat out from`Starship` line 205. The culprit? Our
`getDroids()` method. Well duh, we just removed the `droids` property!
The quick fix, duh again, just comment it out. And huzzah! The fixtures
are back in action:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Creating the Join Entity

To discover the right fix, let's do a few things manually:
`$ship = StarshipFactory`, we could use `createOne()`, but let's
grab a random one instead. Also use the `_real()` trick to get
grab the actual object, not a proxy. Then we'll do the same for
`$droid = DroidFactory`, again grabbing a random one and calling
`_real()` on that.

## Relating via the Join Entity

Previously, we could use `$ship->addDroid($droid)` to add a droid to a
But not anymore! It's referencing the obsolete `droids` property.
It's now called `starshipDroids`, and as you might've guessed, it's a
collection of `StarshipDroid` entities. Ditch
`$ship->addDroid()` and instead say `$starshipDroid` equals new
`new StarshipDroid()`, then `$starshipDroid->setDroid()`, not `$ship` but `$droid`.
And set `$starshipDroid->setStarship($ship)`.
We're manually creating the entity and setting those many-to-one relationships.
Finally, because we're assembling these by hand, we need to persist and flush
them using `$manager->persist($starshipDroid)`, and `$manager->flush()`.

It's definitely more work, but it's simple enough. Give the fixtures a spin:
```terminal-silent
symfony console doctrine:fixtures:load
```

And peek at the database with:

```terminal-silent
symfony console doctrine:query:sql "SELECT * from starship_droid"
```

We're selecting from that join table and yes! One entry
for the one `Starship`, and the one `droid`. So far, so good. Refresh the
homepage. Oh dear, another error! It's `[Semantical Error] line 0, col 55
near 'droids WHERE': Error: Class App\Entity\Starship has no association
named droids`. Looks like we've got a query issue on our hands.

## Fixing the Query Issue

Time to roll up our sleeves and dive into
`src/repository/StarshipRepository`. Our join here is having a bit of a
meltdown. We're joining at `s.droids`, but `droids` property has left the
building. We need to join on `StarshipDroids`. So let's change `s.droids`
to `s.starship. StarshipDroids`. And for clarity, let's call it
`StarshipDroid`, because that's what it really is. I like to keep things
singular, so we'll stick with `StarshipDroid` and simply count them instead
of the nonexistent `droids`.

With that sorted, we'll refresh the homepage and... we've got another
error. It's `Warning: Undefined property: App\Entity\Starship::$droids`.
This is coming from our `ship.droidNames` in the homepage template. We know
that when we call `ship.droidNames`, it's calling
`StarshipArrowGetDroidNames` and we're still referencing the ghost of the
`droids` property.

## Making Magic Happen

Now, let's sprinkle some Symfony magic dust. We'll fix this in a way where
we only have to make changes in one file, and the rest of our application
will just start working. Now that's what I call coding magic!
