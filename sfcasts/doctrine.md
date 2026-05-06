# Upgrading Doctrine & Native Lazy Objects

We need to upgrade the doctrine-bundle... but before we do, I want to give
a refresher on a really cool Doctrine feature: lazy objects.

Open up `src/Entity/StarshipPart.php`. This entity has a `Starship` property
with a many-to-one relationship to our `Starship` entity. Each `StarshipPart`
has one `Starship`, and each `Starship` can have many `StarshipParts`. When
fetching entities with relationships, Doctrine uses some cool magic to
avoid unnecessary database queries. Let's see how it works in action.

## Setting Up a Temporary Controller

Over in your terminal, create a new controller:

```terminal
symfony console make:controller
```

Name it `LazyController`, and no need for tests.

Open the new controller in `src/Controller/LazyController.php`. Ok, we have
this `index()` method whose route is set to `/lazy`. Inject
`StarshipPartRepository $repository` into it. Then, grab the first part from the
repository with `$part = $repository->find(1)`. Dump it with `dump($part)`:

[[[ code('6978bbd0ba') ]]]

Now, head back to our app and manually navigate to the `/lazy` url.

## Exploring Doctrine's Lazy Objects

Looking at the web debug toolbar, we see a single query. That's the one fetching
the `StarshipPart`. If we open the dump profiler panel, we see the fetched
`StarshipPart` object. Note the `starship` property, it's type is this funky
Proxy CG thing. This is a Doctrine lazy object. Look inside. All the properties
are unset except for the ID. The ID is the only thing Doctrine knows about the
`Starship` until it queries the database for the rest of the data. Only when
accessing a property of the `Starship` does Doctrine trigger a second query
to fetch the rest.

Let's trigger this second query! In `LazyController::index()`, add
`$part->getStarship()->getName()` as the first argument to the `dump()`:

[[[ code('1a9adbf178') ]]]

Refresh the `/lazy` page... There are now two queries. The first one fetches the `StarshipPart`,
and the second one fetches the `Starship` because we accessed its name.

In the dump panel, we can see the `Starship` is now fully loaded with all its properties.

So this CG Proxy thing is a real class Doctrine generates on the fly. It contains all the
logic to fetch the data when needed. The key is, it *extends* the real `Starship` entity.
That's how it can be used as a stand-in for the `Starship` until the actual data is needed.
That's also why entities cannot be final, they need to be extendable by these proxy classes.

As you might imagine, the logic to do all this is pretty complex and difficult to maintain.
But... that all changed in PHP 8.4, which introduced *native* lazy objects to PHP itself.

And Doctrine Bundle 3, allows our Symfony apps to take advantage! So let's upgrade!

## Upgrading Doctrine Bundle

First, open our `composer.json`. Look for where we're requiring the
`doctrine-bundle`. Change its version to `^3.0`.

Now, in the terminal, run:

```terminal
symfony composer update
```

Oooo, a composer error. Our dependencies couldn't be resolved... `composer.json`
requires `doctrine-bundle` 3. Yeah... `doctrine-bundle` 3 requires `doctrine/dbal` 4
but this conflicts with our requirement for `doctrine/dbal` 3.

For reference, `doctrine/dbal` is the *database abstraction layer* that Doctrine uses to
communicate with different databases.

Let's check our `composer.json`. We're requiring just `dbal` version 3, but the
`doctrine-bundle` needs version 4 according to that error.

Ok, this is a bit of a legacy issue. Previous versions of `doctrine-bundle` didn't
support `dbal` 4, so we had to ensure version 3 was used. This is no longer
required, so we can just remove it entirely, and let `doctrine-bundle` decide
which version to use.

Try the update again...

Another error, but a different one. Scroll up a bit... we can see `doctrine-bundle`
3 and `dbal` 4 were successfully installed.

The error was caused when attempting to clear the cache. Looks like our
`doctrine` configuration is using some options that are no longer supported.

We could fix these manually, but I'm pretty sure upgrading the Flex recipe will
resolve this.

We need a clean git state before we upgrade the recipe, so let's check our
`git status`. We have some modified changes and some untracked files. Run:

```terminal
git add .
```

To track everything and run `git status` again. Now that everything is tracked,
we can commit it with:

```terminal
git commit -a -m "upgrade doctrine-bundle"
```

`git status` again to confirm we're clean. Finally, upgrade the recipe with:

```terminal
symfony composer recipe:update
```

Sure enough, it found an update for `doctrine-bundle`. Apply it!

Run a `git status` to see the changes. Perfect, it updated the `doctrine.yaml` file.

## Checking the Changes

Back in our code, open up `config/packages/doctrine.yaml`. First of all, it removed
3 config options. These were the ones that caused that error when clearing the cache.

Next, it looks like it changed the default naming strategy... and removed some
controller resolver config.

Down under the production config, it removed the `auto_generate_proxy_classes` and
`proxy_dir` options. With the original lazy object system, in production,
Doctrine generated files for the proxy classes to improve performance.

None of that is needed anymore with *native* lazy objects!

Ok, let's see what our lazy objects look like now. First, head back to
`LazyController:index()` and remove the first argument from the `dump()`:

[[[ code('5f1058052a') ]]]

This should be dumping the part with a starship instance that isn't
fully loaded.

Refresh the `/lazy` page in your browser. Ok, just one query, which is
expected. Open the debug panel and check the `starship` property. This
is now our normal Starship entity - not that generated proxy class.

But look inside! All the properties are unset except for the ID. This looks
just like we saw in the old proxy class. This is *native* lazy objects in action!

Back in `LazyController::index()`, re-add the `$part->getStarship()->getName()` to the `dump()`...

[[[ code('5b465d6980') ]]]

and refresh the `/lazy` page again. Two queries, and if we look at the `starship` property in the dump
panel. Still just our normal `Starship` entity... and if we expand it... all its properties
are loaded!

## Finalizing Entities

Ok... this is cool, but what does it really mean for me as a developer? Well, there probably
is some performance improvement with *native* lazy objects.

But the primary takeaway is we can now, finally, mark our entities as `final`. So, yeah, not
super life-changing, but it's nice we don't need a hacky workaround anymore.

So... let's mark our entities as final!

`src/Entity/Droid.php`: final:

[[[ code('81207aef19') ]]]

`Starship.php`: final:

[[[ code('325bfc7d86') ]]]

`StarshipDroid.php`: final:

[[[ code('348d6467e5') ]]]

and *finally* `StarshipPart.php`: final:

[[[ code('ae68f7dcbf') ]]]

Refresh our lazy page... and... it all still works!

We're almost ready to make the push to Symfony 8, but before we do, let's revisit
deprecations.
