# Installing Doctrine

Yo friends! It's time for episode 3 of our Symfony 7 series. And this is an *exciting*
one because we're bringing our app to life with a database. We don't *need* any new
libraries to do this, but be because it's awesome and smells like cookies, we'll
use a library called Doctrine. And while Doctrine and
Symfony _are_ separate projects, they fit together like quantum entangled particles.
Spooky action at a distance, baby!

I'm Kevin, and I'll be your starship captain on this journey. I've always wanted
to say that. Engage!

To adventure with me through database space, download the course code and follow the setup guide in
`README.md`. The last step, which I already did, is to run

```terminal
symfony serve -d
```

to start a local web server at https://127.0.0.1. Say hello to
the _Star Shop_ from our previous episodes. We have a "Ship Repair Queue" where
we list starships currently docked for repairs. Now, this may look like the data is coming from some kind
of database, but it's really just hardcoded. Lame!

Time to Warp 9 this app to the world of databases!

## Requiring Doctrine

First things first: we need to install Doctrine. Pop over to the terminal
and run:

```terminal
composer require doctrine
```

Whoa, this installed a bunch of stuff! We can see it also configured some _Flex recipes_.
We're being asked if we want to include Docker configuration from recipes. Choose `p`
to enable this permanently. We'll talk about Docker in the next chapter but don't
worry, Docker isn't required for this tutorial.

Scroll up a bit to see what happened. The `doctrine` package
we installed is actually a Flex alias for a Flex *pack* called `symfony/orm-pack`.
Remember, Flex packs are just a *collection* of libraries that work well together.
actually installs multiple Doctrine-related packages. The end result a super 
robust Doctrine setup.

The first interesting package is `doctrine/dbal`. DBAL stands for _Database Abstraction Layer_.
That's a fancy way of saying it provides a consistent way to work with different
database platforms. MySQL, PostgreSQL, SQLite, etc. It's super important, though
it mostly hides behind the scenes.

The second is `doctrine/orm`. ORM stands for _Object Relational Mapper_.
Fancy words for a library that helps us map PHP objects to database tables.
We'll dive hard into this.

Then there are a few others that tie Doctrine into Symfony and a migrations
library we'll use to add new tables and stuff like that.

The rest of these are background support packages for Doctrine and you can ignore
them.

But what's *really* interesting is what the Flex recipes for these packages
did. Run:

```terminal
git status
```

The modified files are standard Flex recipe stuff. `.env` was modified with some
Doctrine-specific environment variables - we'll see those soon - and `config/bundles.php` was updated to enable
the two bundles we installed.

These _untracked files_ are new files added by the Flex recipes. These `compose*.yaml`
files will help us start a database container in the next chapter.

In `config/packages/`, we have 2 new files - `doctrine.yaml` and `doctrine_migrations.yaml`.
These have good defaults, so we'll just check them out as needed.

The recipes added an empty `migrations/` directory, an empty `src/Entity/` directory,
and an empty `src/Repository/` directory. We'll dive into all of these one-by-one.

Ok! We have Doctrine installed, so we can talk to databases... except that... we don't
actually have a database server running yet. Let's get one going next!
