# Alien Tech for Fixtures: Foundry & Faker

We're using `src/DataFixtures/AppFixtures.php` to create
fake fixture data. This *works* fine. But where's the cool and fun? Do we really want to
write manual code to add dozens or more entities? Points to you if you answered:
hell no!

To take this from tedious to terrific, find your terminal and run:

```terminal
composer require --dev foundry
```

Scroll up to see what was installed. The important packages are `zenstruck/foundry` -
as way to create many entities quickly - and `fakerphp/faker` - a library to make
fake data so we don't need to rely on lorem ipsum and our own lack of creativity.

Run

```terminal
git status
```

to see what the recipes did: it enabled a bundle and added a config file.
That config works well out of the box, so no need to look at it.

With Foundry, every entity can have a *factory* class.
To get these going run:

```terminal
symfony console make:factory
```

This lists all entities that don't yet have a factory. Choose `Starship` and...
success! It created a new `StarshipFactory` class. Go check that out:
`src/Factory/StarshipFactory.php`.

This class will be really good at creating `Starship` objects - handy
in case the Borg come back.
First, look at this `class()` method. This tells Foundry which entity class this factory
helps with.  The `defaults()` is where we define default values
to use when creating starships. I recommend adding defaults for all required fields:
it'll make life easier.

Hey! Check out these `self::faker()` calls! This is how we generate random data. For
`name`, `captain` and `class`, it's random text, `status`, is a random
`StarshipStatusEnum` and `arrivedAt` defaults to any random date
Since time travel *still* hasn't been invented,
replace `self::faker()->dateTime()` with `self::faker()->dateTimeBetween('-1 year', 'now')`.

Faker's `text()` method *will* give us random text, but not necessarily interesting
text. Instead of serving under Captain "apple pie breakfast",
in the `tutorial/` directory, copy these constants and paste them at the top of the factory class.
Then, for `captain` use `randomElement(self::CAPTAINS)`. For
`class`, `randomElement(self::CLASSES)` and for `name`, `randomElement(self::SHIP_NAMES)`.

Time to use this factory! In `src/DataFixtures/AppFixtures.php`, in `load()`,
write `StarshipFactory::createOne()`. Pass this an array of property values for the
first ship: copy these
from the existing code. I'll paste the other two... and remove the old code.

Bonus! Remove the `persist()` and `flush()` calls: Foundry handles that for us!

Let's see what this does! Reload the fixtures:

```terminal
symfony console doctrine:fixtures:load
```

Choose `yes` and... success! Back over, refresh and... it looks the same. That's
a good sign! Now, let's create a fleet of ships!

For the first three, we passed an array of values... but we didn't need to do that.
If we *don't* pass a value, it'll use the `StarshipFactory::defaults()` method.
Watch how dangerous this makes us: a Borg cube just showed up? Whip up 20 new ships
with `StarshipFactory::createMany(20)`.

Back in the terminal, load the fixtures again:

```terminal
symfony console doctrine:fixtures:load
```

And over in the app, refresh and... check it out! A whole fleet of ships here
now, and yep, they all have random data!

Now that the fake data is looking more real, it makes me wonder: what if our app
was running on a huge star base with hundreds or thousands of ships?
This would be a *long* page. Next, we'll *paginate* these results into smaller chunks.
