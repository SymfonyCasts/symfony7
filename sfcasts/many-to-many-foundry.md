# Many To Many with Foundry

Remember back in `AppFixtures` when we manually assigned a `Droid` to a
`Starship`? That was fun! But now, I want to create an *army* of `droids`,
a fleet of `starships` and assign them all at once.

Get rid of those manual `Droid` and `Starship` assignments in `AppFixtures`.

## Creating the Droid Army and Starship Fleet

Zoom to the bottom where we create `starships` and `parts`. We also now
need a bunch of `droids`:`DroidFactory::createMany(100)`. 

Below, set `droids` to `DroidFactory::randomRange(1, 5)`:

[[[ code('3d67ea2ef7') ]]]

This will assign anywhere between 1 and 5 random `droids` to each `Starship`.

## The Magic of Symfony

Maybe you noticed something: we're setting a `droids` property here, but in
`Starship`, we do *not* have a `setDroids()` method! Normally,
this would trigger an angry error. But it *will* work!
Foundry sees that we have an `addDroid()` method, and it calls *that* instead,
one-by-one for each `Droid`.

## Test Run

Time to see this in action! Find your terminal and run:

```terminal
symfony console doctrine:fixtures:load
```

No errors? I'm a bit surprised, ahem, delighted too. Take a peek at the `droids` with:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

100 zany, adorable droids. Also check out the `starship_droid` table:

```terminal-silent
symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
```

It should look like there's a random set of droids assigned to each `starship`

## Hold Up, Something's Not Right!

But wait a minute. These "random" droids - could you sense my 
sarcastic air quotes? - aren't random at all! They're the 3 same droids
over and over again. The problem is that `randomRange(1, 5)` is only
called *once*: so it's assigning the same 1 to 5 random droids to every
`Starship`. Not quite the variety we were hoping for.

## Closures & Foundry

Fix this by passing a closure: `StarshipFactory::createMany()`, 100,
`fn() => [ 'droids' => DroidFactory::randomRange(1, 5)])`:

[[[ code('cec7afa4d8') ]]]

Foundry will execute the callback for all 100 starships. This means `randomRange(1, 5)`
will be called 100 times, giving us a truly random range for each ship. 

Give it a whirl! Re-run the fixtures and load the SQL query:

```terminal-silent
symfony console doctrine:fixtures:load
symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
```

Then bask in the glory of a truly random set of droids assigned to starships.

We could have also fixed this by moving the `droids` key into
`StarshipFactory` down in the `defaults()` method. But I like to keep
`defaults()` for the required properties. And since `droids` are not
technically required - good luck cleaning the bathroom without them! -
I like to keep them out of `defaults()` and set them where we're using
`StarshipFactory`.

Next, we'll learn how to JOIN across `ManyToMany` relationships. Once again,
Doctrine handles the heavy lifting for us.
