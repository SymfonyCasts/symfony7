# Many To Many Foundry

Coming soon...

## Mass Assigning Droids to Starships: A Space Opera

Remember back in `AppFixtures` when we manually assigned a `droid` to a
`starship`? That was a fun little adventure! But now, let's take things to
the next level and create a whole army of `droids` and a fleet of
`starships` and assign them all at once. Hang on to your space helmets!

First things first. Let's hit the delete button on our manual `starship`
and `droid` code. Yes, you heard right, just nuke all those `droid` and
`starship` assignments. Now, we're back to our basic, vanilla code. A clean
slate, if you will. 

## Creating the Droid Army and Starship Fleet

Next, let's zoom to the bottom where we're creating our `starships` and
`parts`. Our first mission? Create a bunch of `droids`! For this, we'll
need our trusty old friend, `DroidFactory::createMany(100);`. 

And right below this, we're creating our `starships`. But we're not going
crazy and creating 100 of those. We'll control the number of `droids` that
get assigned to each one. So, let's call upon `DroidFactory::randomRange(1,
5);`. This will assign anywhere between 1 to 5 random `droids` to each
`starship`. It's like a lottery, but with droids!

## The Magic of Symfony

Now, eagle-eyed coders might notice something. We have a `droids` property
here, but in `starship`, we do not have a `setDroids` method. Normally,
this would cause an error. But guess what? Symfony is like a code magician.
It sees we have an `addDroid()` method and it calls that instead, one by
one for each of those `droids`.

## Test Run

Ready to see your code in action? Let's run our fixture load with the
following command:

```terminal
symfony console doctrine:fixtures:load
```

No errors? Brilliant! 

Now, let's take a peek at the `droids`. Fire up this command:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

We should be looking at a whopping 100 `droids`. And let's also check out
the `starship_droid` table, our lovely join table. It should look like
there's a random `droid` assigned to each of the different `starships`.

## Hold Up, Something's Not Right!

But wait a minute. They say variety is the spice of life, but these random
`droids` are actually the same 3, over and over again. It's like déjà vu
in space! The culprit? Our `randomRange(1, 5)` function. It's only called
once, which means it's assigning the same 1 to 5 `droids` to every single
one of our 100 `starships`. Not quite the variety show we were hoping for.

## The Fix

But don't worry, we can fix this by passing a closure:

```php StarshipFactory::createMany(100, fn() => [ 'droids' =>
DroidFactory::randomRange(1, 5), ]); ```

Like a helpful droid, this code will call our callback for all 100 of those
`starships`. This means `randomRange(1, 5)` will be called 100 times,
giving us a truly random range for each of those 100 ships. 

Let's give it a whirl. Re-run the fixture load and the SQL query. Now we
can bask in the glory of a truly random set of `droids` assigned to
`starships`.

We could have also fixed this by moving the `droids` key into
`starshipFactory` down on the `getDefaults()` method. But I like to keep
`getDefaults()` for the important stuff, the required properties we need to
save to the database. And since `droids` are not actually required, I like
to keep them out of `getDefaults()` and set them where we're actually using
`starshipFactory`.

## What's Next?

Phew! With that fixed, we're ready to move on. So buckle up, because next
up, we're diving into `OneToMany` joins. Stay tuned!