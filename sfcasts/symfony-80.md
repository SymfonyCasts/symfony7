# Upgrading to Symfony 8.0!

Symfony 7.4? Check.
Deprecation-free? Check.

Nice, it's finally time to upgrade to Symfony 8! Thankfully, we've
done most of the hard work already, so the upgrade process should be
straightforward. Let's get started by opening our `composer.json` file.

We'll do the same thing we did when upgrading to 7.4. Edit... Find...
Replace... `7.4.*` and replace it with `8.0.*`. Replace all... Nice.

I'll take a moment to verify. `require` section looks good... Ah, great, our
`extra` `symfony` `require` option was updated. And `require-dev` looks good too!

[[[ code('f0e52d5b2a') ]]]

Head over to the terminal and run:

```terminal
symfony composer update
```

## Resolving Errors

Ooo, an error, let's scroll up a bit. Ok, our root composer.json requires
`symfonycasts/tailwind-bundle` `^0.9.0`. This bundle requires some Symfony packages,
but unfortunately, Symfony 8 versions aren't supported. Let's see if there's an updated version.

Over in the browser, go to packagist.org. Search for `tailwind-bundle`... Here
it is. On the right here, are all the versions, select `v0.9.0`, the version
we're on. Sure enough, Symfony packages it requires only support up to Symfony 7.
Here's the newest version: `v0.12.0`, and sweet, this one does support Symfony 8!

Back in our `composer.json`... find the `tailwind-bundle`... and change its version
to `^0.12.0`:

[[[ code('3f6a754752') ]]]

Let's try the update again.

```terminal-silent
symfony composer update
```

## Resolving More Errors

Huh, another error. Let's see what's going on. Our `composer.json` requires
`monolog-bundle ^3.0`, but it seems `monolog-bundle 3` doesn't support
Symfony 8. Let's quickly jump back to Packagist and search for `monolog-bundle`.

Yep, version 3 doesn't support Symfony 8... but version 4 does!

Back to `composer.json`, find the `monolog-bundle`... and change its version to `^4.0`:

[[[ code('404b6a4b0d') ]]]

***NOTE
If you're wondering why the `monolog-bundle` uses a different
versioning strategy than the rest of Symfony, it's because it has its own
repository and release cycle. It isn't part of the core Symfony monorepo.
***

Will the third update be the charm?

```terminal-silent
symfony composer update
```

Perfect! It worked this time! Let's confirm by running:

```terminal
symfony console --version
```

Excellent! Symfony 8.0.10!

## Checking for Outdated Dependencies

Now let me show you a handy composer command that checks if you have any
outdated dependencies. That's dependencies that have newer versions available.

```terminal
symfony composer outdated -D
```

The `-D` option stands for *direct* dependencies, which means, only the packages that
are in our app's `composer.json` file. Not *transitive* dependencies, which are the
dependencies of our dependencies. I only really care about the direct ones.

Looks like we have 3 packages that have newer versions available. `phpdocumentor/reflection-docblock`,
`symfony/stimulus-bundle` and `symfony/ux-turbo` can all be upgraded to the next major version. Let's go
ahead and do that in our `composer.json` file.

Find `phpdocumentor/reflection-docblock` and change to `^6.0`. Next, `stimulus-bundle`, change to `^3.0`.
Finally, `ux-turbo`, change to `^3.0`:

[[[ code('38cf8b54ad') ]]]

Let's update!

```terminal-silent
symfony composer update
```

Excellent! No errors means there are no conflicts with the upgraded
packages, so we're good to go. Running `git status` confirms that it's only
our `composer.lock`, `composer.json`, and `reference.php` files that have
changed. Remember, that `reference.php` file helps your IDE with array-based
Symfony configuration and is auto-generated. We can ignore it as we use
YAML-based config.

Commit our changes with:

```terminal
git commit -a -m "upgrade to Symfony 8!"
```

Hmm, the terminal didn't like my enthusiasm with the exclamation point... I'll cancel
this command and try again without it:

```terminal
git commit -a -m "upgrade to Symfony 8"
```

There we go!

## Checking for Recipe Updates

Now that we're on a clean slate, check for recipe updates with:

```terminal
symfony composer recipe:update
```

Nope, no new recipes!

## Verifying the Upgrade

Head back to the browser and refresh the homepage. Sweet! We're on Symfony 8 with
no errors!

Since we upgraded `ux-turbo`, I'll just confirm that it's working by clicking a link...
Turbo requests happen via AJAX, and sure enough, our web debug toolbar shows an AJAX request
was made.

We also upgraded `stimulus-bundle`, so let's check that it's working correctly. Open the developer
tools and check the console tab... These logs show that Stimulus and our Stimulus controllers
are being initialized correctly!

That's it folks! I hope you learned something valuable from this tutorial
and manage to upgrade *your* apps to Symfony 8 smoothly.

Til next time, Happy coding!
