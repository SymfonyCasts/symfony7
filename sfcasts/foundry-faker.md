# Better fixtures with Foundry & Faker

In development, we're using this `src/DataFixtures/AppFixtures.php` class to create
some fake fixture data. This works totally fine, but it can be cumbersome to create
dozens or more entities this way. Further, coming up with fake data yourself can be
tedious.

We'll use a couple of libraries to help with this! In your terminal, run:

```terminal
composer require --dev foundry
```

Scroll up to see what was installed. The important packages are `zenstruck/foundry` which
gives us a quicker way to create entities, and `fakerphp/faker`, which helps generate
fake data.

Run:

```terminal
git status
```

To see what the recipes created. A bundle and configuration was added. The
default config works well out of the box, so we don't need to look at it.

With Foundry, every entity can have a *factory* class that helps with generating it.
It comes with a maker to help create factories. We'll create our first factory by
running:

```terminal
symfony console make:factory
```

This lists all of our entities that don't yet have a factory. Choose `Starship` and...
success! We see it created a new `src/Factory/StarshipFactory.php` file. Check that out.
In our IDE, open `src/Factory/StarshipFactory.php`.

First, look at this `class()` method. This tells Foundry which entity class this factory
represents. The `defaults()` method is where we can add defaults for fields. Look at this!
The maker added defaults for our Starship fields! It's best practice to have this method
return defaults for all the required fields in your entity. We'll see why in a minute.

Check out these `self::faker()` calls. This is how Foundry generates random data. For
`name`, `captain` and `class`, it's generating random text. For `status`, it's selecting
a random element from our `StarshipStatusEnum` cases. The `arrivedAt` field is generating
any random date, but we need to modify this to always generate a date in the past. Time travel
hasn't been invented yet!

Replace `self::faker()->dateTime()` with `self::faker()->dateTimeBetween('-1 year', 'now')`.

The generated text for `captain`, `class` and `name` will be kind of boring. Let's make it more fun! In the
`tutorial/` directory, copy these constants and paste them at the top of the factory class.

Now, for `captain`, change from `text()` to `randomElement()` and pass in `self::CAPTAINS`. For
`class`, use `randomElement(self::CLASSES)` and for `name`, use `randomElement(self::SHIP_NAMES)`.

Time to use this factory! In `src/DataFixtures/AppFixtures.php`, at the start of the `load()` method,
write `StarshipFactory::createOne()` with an array properties for the first starship. Copy these
from the existing code. For the other two, I'll paste them in and remove the old code.

We no longer need these `persist()` and `flush()` calls - Foundry handles this for us!

Reload our fixtures by running:

```terminal
symfony console doctrine:fixtures:load
```

Choose `yes` and... success! Back in our app, refresh the page and... it looks the same. That's
a good sign. Now, let's create a whole bunch more starships!

With the first three, we passed an array of all required properties. If we didn't pass one of them,
the factory would use the default from `StarshipFactory::defaults()`. If we don't pass any,
it will use all the defaults. Leverage this to create an additional 20 starships with
`StarshipFactory::createMany(20)`. For each one it creates, it will now use all the defaults and
because these are using faker, each one will use random data.

Back in the terminal, load the fixtures again:

```terminal
symfony console doctrine:fixtures:load
```

Back in the app, refresh the page and... check it out! We have a whole fleet of ships here
now, and yep, they all have random data!

What if this app was running on a huge starbase and we had hundreds or thousands of ships?
This page would be *huge* and take forever to load. Next, we'll *paginate* these
results into smaller chunks.
