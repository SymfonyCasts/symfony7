# Inserting Data via Fixtures

We have our database table, but now we need some data! When working in your
development environment, it's useful to have a set of fake data to
*seed* you database: stuff you can play around with while building. We call this
data *fixtures*.

In our case, it would be great to pre-fill our table with some Starships!
Doctrine even has a package adding this fake fixtures data! At your terminal, run:

```terminal
composer require --dev orm-fixtures
```

We used `--dev` because we only need fixtures in our development environment.
Scroll up to see what was installed: `doctrine/data-fixtures` and
`doctrine-fixtures-bundle`. Run

```terminal
git status
```

to see what the recipes added. Standard Flex stuff, added a bundle, but also
this `src/DataFixtures` directory. Let's check that out: open
`src/DataFixtures/AppFixtures.php`. This `load()` method is where we can create
our fixtures. Delete what's there so we can start fresh.

## Create Entities

To add entities to the database no matter *where* you are, it's refreshingly simple!
First, create the object like normal:
`$ship1 = new Starship()` - the one from `App\Entity`.

[[[ code('a55e56fca1') ]]]

In a previous episode, we created this `StarshipRepository` service in `src/Model/`.
Open that up. We have a `findAll()` method that creates these Starship objects on
the fly. We'll use this data for our fixtures!

Copy the second argument of the first Starship - that's the name. Back in `AppFixtures`,
call `$ship1->setName('USS LeafyCruiser (NCC-0001)')`. Do the
same for `$class`: `$ship1->setClass('Garden')`, `$captain`:
`$ship1->setCaptain('John Luke Pickles')`, `$status`:
`$ship1->setStatus(StarshipStatusEnum::IN_PROGRESS)` and don't forget to import the enum.
Finally, `$arrivedAt`: `$ship1->setArrivedAt(new \DateTimeImmutable('-1 day'))`.

[[[ code('40b5bda087') ]]]

For the other two ships, I'll copy and paste some code from the `tutorial/` directory.

[[[ code('e21d621b7b') ]]]

We now have three ship objects, but nothing has been saved - or *persisted* to the
database yet. But interesting, Doctrine passes us an `ObjectManager`.
This is the *heart* of Doctrine. We'll use it to save, fetch, update, and
delete objects, our entities, from the database. What an overachiever!

## Persist Entities

To use it, after we've created our ship objects, write `$manager->persist($ship1)`,
`$manager->persist($ship2)`, and `$manager->persist($ship3)`. But `persist()` doesn't
actually insert them yet: it just *queues* them to be saved.

[[[ code('bc75e7f648') ]]]

## Flush

To execute some INSERT queries and get these ships docked, write `$manager->flush()`.

[[[ code('c8249a651d') ]]]

`flush()` is really cool: it looks at all
the objects that are queued to be persisted and writes them to the database with an efficient
SQL query. In this case, it will insert all three Starships in one query.
Super cool!

## Load Fixtures

Fixtures done! How do we execute this code? Run:

```terminal
symfony console doctrine:fixtures:load
```

It's double-checking that we *really* want to load our fixtures because it will
also erase all existing data. Choose `yes` and... Success?

Run that raw SQL query again:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship'
```

We have ships! Awesome!

*Phew*, we now have a database *with* data! Next, we'll refactor our app's controllers
to pull Starships from our database and show them on the page. This will be much easier
than you might imagine!
