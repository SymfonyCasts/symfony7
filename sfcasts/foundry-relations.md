# Setting Relations in Foundry

OK, we have a couple of parts and a few starships, but to fill our testing
data fleet: I want a *lot* more. This is a job perfectly suited for our good
friend: Foundry. Remove the manual code, then anywhere, say:
`StarshipPartFactory::createMany(100)`:

[[[ code('6cacafa665') ]]]

And try the fixtures:

```terminal
symfony console doctrine:fixtures:load
```

Uh-oh!

> `starship_id` cannot be null in `starship_part`. 

This traces *all* the way back to `StarshipPartFactory`, down in
the `defaults()` method. This is the data passed to each new `StarshipPart`
when it's created. The golden rule is to make `defaults()` return a key
for every *required* property on the object. Right now, we're obviously missing
the `starship` property, so let's add that. Set `starship`, *not* `starship_id`,
to a nifty method called `Starship::randomOrCreate()` and pass an array:

[[[ code('137fb4ea84') ]]]

## Setting the Stage for Starship Parts

On the homepage, we're only listing starships with 'in progress' or 'waiting'
status. To make sure these parts are related to a ship with 'in progress' status,
add a `status` key in the array set to `StarshipStatusEnum::IN_PROGRESS`:

[[[ code('9e219841ff') ]]]

This `randomOrCreate()` is an impressive method: it first looks in the database
to find a `Starship` that matches these criteria (an "in progress" ship"). If it
finds one, it uses that. If it does *not*, it creates one *with* that status.

Try the fixtures now.

```terminal-silent
symfony console doctrine:fixtures:load
```

No errors! Check the database:

```terminal
symfony console doctrine:query:sql "SELECT * FROM starship_part"
```

Look closely... Ok! we have 100 parts each tied to a random `Starship`, which
should be a `Starship` with an 'in progress' status. I think that was my most
productive 5 minutes ever!

## Taking Control in Foundry

But what if we need more control? What if we want to assign all 100 of
these parts to the *same* ship? I know it sounds a bit *not* useful, but it'll help
us understand Foundry and relationships even better.

Start by getting a ship variable: `$ship = StarshipFactory::createOne()`:

[[[ code('ebffee3193') ]]]

Then, in `StarshipPartFactory::createMany()`, pass a second argument
to specify that we want `starship` to be set to this *specific* ship:

[[[ code('132318b79c') ]]]

Load up those fixtures again. 

```terminal-silent
symfony console doctrine:fixtures:load
```

And done! All parts are now related to the same *one* ship. And if we query
the `Starship`, we have 23: the 20 at the bottom, plus the extra 3 we added.
Everything's coming together!

## The Foundry Plot Twist

Here's where things get interesting. In `StarshipPartFactory`, instead
of using `randomOrCreate()`, switch to `createOne()`:

[[[ code('2a92dbb62a') ]]]

Load the fixtures again. 

```terminal-silent
symfony console doctrine:fixtures:load
```

And... query for all the ships. 

```terminal-silent
symfony console doctrine:query:sql "SELECT * FROM starship"
```

Whoa, we suddenly have a fleet! 123 ships to be exact. 
What happened?

For *each* part, `defaults()` is called. So for all 100
parts, it's triggering this line, which creates and saves a `Starship`, even
though we never use that `Starship` because we override it moments
later.

The solution? Change this to `StarshipFactory::new()`:

[[[ code('73e9fcc7b6') ]]]

This is the secret sauce: it creates a new instance of the *factory*,
not an object in the database. Try it:

```terminal
symfony console doctrine:fixtures:load
```

Query the ships.

```terminal-silent
symfony console doctrine:query:sql "SELECT * FROM starship"
```

Perfect! We're back to 23. 

## Factories are Object Recipes

Fun fact! We can use these factory instances like *recipes* for creating objects.
`StarshipFactory::new(['status' => StarshipStatusEnum::STATUS_IN_PROGRESS])`
does *not* create an object in the database. Nope: `new()` means a new
instance of the factory. And when you pass a
*factory* for a property, Foundry delays creating that
object until and if it's needed. So, only if the `Starship` is *not* overridden
will it create a new `Starship` with status "in progress" and save it. This is
actually the best-practice when setting relationships in Foundry: set them to a
factory instance.

Clean up our fixtures by removing the override:

[[[ code('0162e686b1') ]]]

And... switch back to `randomOrCreate()`

[[[ code('ad363a91f5') ]]]

Because, let's be honest, it's a pretty useful method.

Reload the fixtures one last time to make sure we didn't break anything

```terminal-silent
symfony console doctrine:fixtures:load
```

Nope! We'll try harder next time.
