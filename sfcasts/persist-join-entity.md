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

This error is being spat out from `Starship` line 205. The culprit? Our
`getDroids()` method. Well duh, we just removed the `droids` property!
The quick fix, duh again, just comment it out:

[[[ code('5f74c3e3e2') ]]]

And huzzah! The fixtures are back in action:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Creating the Join Entity

To discover the right fix, let's do a few things manually:
`$ship = StarshipFactory`, we could use `createOne()`, but let's
grab a random one instead. Also use the `_real()` trick to get
the actual object, not a proxy. Then do the same for
`$droid = DroidFactory`, again grabbing a random one and calling
`_real()` on that:

[[[ code('906ff98ce0') ]]]

## Relating via the Join Entity

Previously, we could use `$ship->addDroid($droid)` to add a droid to a
`Starship`. But not anymore! It's referencing the obsolete `droids` property.
It's now called `starshipDroids`, and as you might've guessed, it's a
collection of `StarshipDroid` entities. Ditch
`$ship->addDroid()` and instead say `$starshipDroid` equals
`new StarshipDroid()`, then `$starshipDroid->setDroid()`, not `$ship` but `$droid`.
And set `$starshipDroid->setStarship($ship)`.

We're manually creating the entity and setting those many-to-one relationships.
Finally, because we're assembling these by hand, we need to persist and flush
them using `$manager->persist($starshipDroid)`, and `$manager->flush()`:

[[[ code('eb8ff242d6') ]]]

It's definitely more work, but it's simple enough. Give the fixtures a spin:

```terminal-silent
symfony console doctrine:fixtures:load
```

And peek at the database with:

```terminal
symfony console doctrine:query:sql "SELECT * FROM starship_droid"
```

We're selecting from that join table and yes! One entry
for the one `Starship`, and the one `Droid`. So far, so good. Refresh the
homepage. Another error!

> [Semantical Error] line 0, col 55 near 'droids WHERE':
> Class App\Entity\Starship has no association named droids.

Looks like we've got a query issue on our hands.

## Fixing the Query Issue

Time to roll up our sleeves and dive into `src/Repository/StarshipRepository`.
Our join is having a bit of a meltdown. We're joining on `s.droids`, but the `droids`
property has left the building. We need to join on `starshipDroids`. Change `s.droids`
to `s.starshipDroids`. And for clarity, call it `starshipDroid`, because that's what
it really is. Now count *them* instead of the nonexistent `droids`:

[[[ code('5decc8b9f4') ]]]

With that sorted, we'll refresh the homepage and... *another* error! It's: 

> Warning: Undefined property: `App\Entity\Starship::$droids`.

This is coming from `ship.droidNames` in the homepage template. We know
that when we call `ship.droidNames`, it calls `$starship->getDroidNames()`
and we're still referencing the `droids` property:

[[[ code('c63582b136') ]]]

## Hide that Join Entity

Next, we're going to *hide* the join entity and make this work exactly
like the ManyToMany relationship we had before. Magic!
