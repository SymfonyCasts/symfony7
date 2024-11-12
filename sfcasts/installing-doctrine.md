# Installing Doctrine

Hey friends, welcome to episode 3 of our Symfony 7 series. This one's going to be all
_engaging_ a database with Doctrine. Like Symfony, Doctrine is a collection of PHP
packages. They're all about working with databases in PHP. While Doctrine and
Symfony _are_ separate projects, they fit together like quantum entangled particles.

I'm Kevin, and I'll be your starship captain on this journey. Let's get started!

To follow along with me, download the course code and follow the setup guide in the
`README.md` to get going. I've already done this, so let's spin over to our app. It's
the _Star Shop_ from our previous episodes. We have this "Ship Repair Queue" where
we list tracked starships. Now, this may look like the data is coming from some kind
of database, but it's really just built on demand for each page.

We're going to take this app to Warp 9 and make it database-driven!

## Requiring Doctrine

First things first: we need to install Doctrine. Pop over to the terminal
and run:

```bash
composer require doctrine
```

Whoa, this installed a bunch of stuff! We can see it also configured some _Flex recipes_.
We're being asked if we want to include Docker configuration from recipes. Choose `p`
to enable this permanently. We'll talk about Docker in the next chapter but don't
worry, Docker isn't required for this tutorial.

Ok, the command finished. Scroll up a bit to see what happened. The `doctrine` package
we installed is actually a Flex alias for a Flex pack called `symfony/orm-pack`.
Remember, Flex packs are commonly used collections of related libraries. The `orm-pack`
_unpacks_ into all these packages above.

The first one of note is `doctrine/dbal`. DBAL stands for _DataBase Abstraction Layer_.
That's a fancy way of saying it provides a consistent way to work with different
database platforms. MySQL, PostgreSQL, SQLite, etc.

The second important package is `doctrine/orm`. ORM stands for _Object Relational Mapper_.
Again, fancy words for a library that helps us map PHP objects to database tables.

`symfony/doctrine-bridge` and `doctrine/doctrine-bundle` are the glue that integrates
Doctrine with Symfony.

`doctrine/migrations` and `doctrine/doctrine-migrations-bundle` are for working with
database migrations. We'll talk about these later.

The rest of these are just support packages for Doctrine.

To see what was added to our project, run:

```bash
git status
```

The modified files are standard Flex recipe stuff. `.env` was modified with some
Doctrine-specific environment variables and `config/bundles.php` was updated to enable
the two bundles we installed.

These _untracked files_ are new files added by the Flex recipes. These `compose*.yaml`
files are here because we enabled Docker. Again, we'll talk about Docker in the next
chapter.

We can see in `config/packages/` that we have `doctrine.yaml` and `doctrine_migrations.yaml`
files. These are some default configurations for the two bundles.

There's an empty `migrations/` directory which will house our database migrations.

And there's an empty `src/Entity/` directory. This is where the classes that Doctrine
ORM uses to represent database tables are kept. `src/Repository/` will be where corresponding
entity repositories are held.

Alright! We have Doctrine installed, so we can talk to databases... but... we don't
actually have a database yet. Let's add one next!
