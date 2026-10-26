# Upgrading to Symfony 8.2

Hey friends! Welcome to our tour of what's new in the Symfony 8.2 security system.
Symfony 8.2 is turning out to be one of the biggest releases ever -
probably the biggest since the jump from 1.4 to 2.0! A big reason is that AI-assisted
workflows have really sped up how fast features land and bugs get fixed. A lot of those
changes hit the security component, and since we're fresh off some security courses, it's
the perfect time to look at them. Many of these features really harden the security of
your app, so let's see how they're used.

## Setting Up

This course picks up right where our [Symfony Security: Going Further](https://symfonycasts.com/screencast/symfony8-security-extra)
course left off. If you worked through that one and still have the code locally, you can keep going right on
top of it. To start fresh, download the course code from this page. In the `start/`
directory, you'll find a `README.md` file with all the setup steps. Once you start the
server with:

```terminal
symfony serve -d
```

You'll see our Starshop app. Down in the web debug toolbar, notice we're on Symfony 8.0.
Step one: upgrade to 8.2!

## Bumping the Symfony Versions

Over in `composer.json`, the easiest way to do this is with find and replace. Find
`8.0.*` and replace it with `8.2.*`. There are 23 matches - hit "Replace all":



Now jump over to the terminal and run:

```terminal
symfony composer update
```

## Allowing Dev Stability

And... we have an error! Scroll up. Depending on when you're following along, you might
not see this one. It's saying none of the Symfony 8.2 versions can be installed because
they don't match our minimum stability. In `composer.json`, we have
`"minimum-stability": "stable"`. If 8.2 is already officially released when you watch
this, you won't hit this error. But since I'm recording before the release, change it to
`dev`:



That's how you can always try out pre-release Symfony versions - and the more people that
do, the more it helps the core team.

Run the update again:

```terminal-silent
symfony composer update
```

## Raising the PHP Platform Version

More errors! This one is a bit more subtle: `symfony/asset` 8.2-dev requires PHP 8.4.1 or
higher. Down under `config.platform.php`, we're set to just `8.4`. That needs to be at least
`8.4.1`, so set it to a version higher than that... `8.4.26`:



Run the composer update again:

```terminal-silent
symfony composer update
```

See those warnings at the top? That's the Symfony CLI, not Composer. It reads
`config.platform.php` to decide which PHP binary to run, and I don't have exactly 8.4.26
installed, so it falls back to the latest 8.4 I *do* have. Everything still works, but to
silence it, you can set `config.platform.php` to your exact version.

## Upgrading to Symfony UX 3.0

Ok, we have another composer update error. This one says we're trying to install `symfony/asset-mapper` 8.2, but the
version of `symfony/ux-turbo` in our lock file doesn't allow it. Good news: the 3.0
versions of Symfony UX have been released! Find `symfony/ux-turbo` in `composer.json` and
change it to `^3.0`:



Run again... and we need to do the same for `symfony/stimulus-bundle`.
Find it and set it to `^3.0` too:



One more time:

```terminal-silent
symfony composer update
```

There we go! Perfect. Back in the app, refresh... and we're on `8.2.x-dev`. Success!

## Ignoring `config/schema.json`

Back in the terminal, check what changed:

```terminal
git status
```

`assets/controllers.json` changed from the Stimulus update, plus the normal
`composer.json` and `composer.lock`. But there's also a new `config/schema.json` file.
This is auto-generated and helps your editor auto-complete and validate your YAML config
files. Since it's generated, I like to ignore it. Open `.gitignore` and, just like we did
for `config/reference.php`, add `config/schema.json`:



Run `git status` again:

```terminal-silent
git status
```

Perfect. Commit everything with:

```terminal
git commit -a -m "Upgrade to Symfony 8.2"
```

## Updating Flex Recipes

The next step in any upgrade is updating your Flex recipes:

```terminal
symfony composer recipe:update
```

This gives you a big list - and it *is* a lot. I won't make you watch me go through them
one by one, but working through them should be part of your upgrade process. That said,
since we're only going from 8.0 to 8.2, it's not strictly required today. Recipe updates
between minor versions are mostly things like enabling new features and fixing deprecations. Where they really
matter is when you jump to the next major version - like 8.4 to 9.0 - because that's when
things actually break. So I'll leave them for now. To see a full major upgrade, recipes
and all, check out our [Symfony 8 upgrade](https://symfonycasts.com/screencast/symfony8-upgrade)
course.

Next up: let's look at some improvements to the logout system!
