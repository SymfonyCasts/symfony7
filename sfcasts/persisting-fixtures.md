# Persisting & Fixtures

We have our database table, but now we need some data! When working in your
development environment, it's useful to have a set of fake, or fixture data to
*seed* you database. For our StarShop app, adding these Starships as fixtures
is a great use case.

And Doctrine has a package for that! At your terminal, run:

```terminal
composer require --dev orm-fixtures
```

We used `--dev` because we only require fixtures in our development environment.
Scroll up to see what was installed: `doctrine/data-fixtures` and
`doctrine-fixtures-bundle`. Run:

```terminal
git status
```

to see what the recipes added. Standard Flex stuff, added a bundle, but also
this `src/DataFixtures` directory. Let's check that out: open
`src/DataFixtures/AppFixtures.php`. This `load()` method is where we can create
our fixtures. Delete what's there so we can start fresh.

To add entities to the database, first, create the object like normal. Start with
`$ship1 = new Starship()` - import the one from `App\Entity`.

In a previous episode, we created this `StarshipRepository` service in `src/Model/`.
Open that up. We have this `findAll()` method that creates these Starship objects on
the fly. We'll use this data for our fixtures!

Copy the second argument of the first Starship - that's the name. Back in `AppFixtures`,
call our setter for `$name`: `$ship1->setName('USS LeafyCruiser (NCC-0001)')`. Do the
same for `$class`: `$ship1->setClass('Garden')`, `$captain`:
`$ship1->setCaptain('John Luke Pickles')`, `$status`:
`$ship1->setStatus(StarshipStatusEnum::IN_PROGRESS)` - don't forget to import the enum.
Finally, `$arrivedAt`: `$ship1->setArrivedAt(new \DateTimeImmutable('-1 day'))`.

For the other two ships, I'll copy and paste some code from the `tutorial/` directory.

We have our three ship objects created - now we need to save, or *persist* them to the
database. Check out the signature of this `load()` method: it's passed an `ObjectManager`.
This is like the heart of the Doctrine ORM and enables us to save, fetch, update, and
delete objects, our entities, from the database.

Below, after we've created our ship objects, write `$manager->persist($ship1)`,
`$manager->persist($ship2)`, and `$manager->persist($ship3)`. `persist()` doesn't actually save
them yet - it just queues them up to be saved, Doctrine calls it *persisted*.

To actually save them, write `$manager->flush()`. `flush()` is really cool: it looks at all
the objects that are queued to be persisted and writes them to the database with an efficient
SQL query. In this case, it will insert all three Starships in one query.

Fixtures done! Load them by running:

```terminal
symfony console doctrine:fixtures:load
```

It's double-checking that we *really* want to load our fixtures because it will erase
any existing data. Choose `yes` and... Success?

Run our raw SQL query again to check:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship'
```

There's our ships! Awesome!

*Phew*, we finally have a database *with* data! Next, we'll refactor our app's controllers
to pull Starships from database.
