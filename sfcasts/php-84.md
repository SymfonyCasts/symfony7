# PHP 8.4 & Recipe Updates

Hey friends! Welcome to a new course on upgrading a Symfony app from Symfony 7
to Symfony 8. Luckily, newer versions of Symfony make this upgrade process
a breeze. But there are still some things to make the upgrade process smoother.

To follow along, download the course code from the top of this page, open the
`start` directory in your IDE, and you'll see what I have here. Follow
the README to get everything set up, when you're ready, in your terminal, run:

```terminal
symfony server:start -d
```

(the `-d` flag runs the server in the background). Click the link in the output
to open the app in your browser.

Welcome to the Starshop! If you've followed along with our previous Symfony 7 courses,
this should look familiar.

Down in the web debug toolbar, you can see we're on Symfony 7.3.5 and PHP 8.3.30.

Our goal? Update this bad boy to Symfony 8!

Since Symfony 8 requires PHP 8.4, a good first step is to upgrade this app to require
PHP 8.4. At the same time, we'll also upgrade to the latest version of Symfony 7.3.

If you've done these major upgrades before, you might be thinking we should upgrade
to 7.4, the last Symfony 7 version. We will, but I like to take small steps to avoid
getting overwhelmed with too many changes at once.

In your IDE, open `composer.json`. In the `require` section, change `>=8.3` to `>=8.4`:

[[[ code('4426dcd419') ]]]

This tells composer at least PHP 8.4 is required for this project.

Scroll down and find the `config.platform.php` section. This is what the Symfony CLI uses
to determine which local version of PHP to use when you run `symfony console` or `symfony php`.
Change this to `8.4`:

[[[ code('d1ae6bb136') ]]]

This step isn't required, but scroll down to the `replace` section. This is a list of packages
that composer will ignore when installing dependencies. You can see it's all *polyfill* packages.
These are packages that provide features from newer versions of PHP to older versions. Since we're
on PHP 8.4, we can add the polyfill packages for 8.3 and 8.4 to this list:

[[[ code('94ddfe93aa') ]]]

Saves a few bytes of disk space and a few milliseconds of install time...

Over in our terminal, let's confirm we're using PHP 8.4 now. Run:

```terminal
symfony php --version
```

Nice, 8.4.20!

Now, let's do a composer update to capture all the changes we made in our `composer.json` file
and upgrade to the latest allowed dependencies. Run:

```terminal
symfony composer update
```

No errors, great! Jump to the browser and refresh the page...

Gross, this is a nasty error. It doesn't look like the standard Symfony errors because
it couldn't even load Symfony. This is an error at the composer autoloader level.

It's saying we're running PHP 8.3 but our dependencies require PHP 8.4. Remember we ran the
Symfony server in the background earlier? We ran it with PHP 8.3. Simple fix, just
restart the server with:

```terminal
symfony server:stop
symfony server:start -d
```

Now refresh the page... Sweet! We're back in business. Notice in the web debug toolbar
we're now on Symfony 7.3.11 and PHP 8.4.20.

Now for deprecations! When Symfony, or PHP itself wants to remove or change a feature, they
don't just make the change. This could break tons of apps and cause a lot of problems.
Instead, they mark the feature as *deprecated* for a while, which means it still works as
expected, but it will trigger a warning. Usually, the warning explains how to fix the deprecation,
and upgrade to the new way of doing things.

Symfony has a really great deprecation policy. They guarantee that nothing will break when upgrading
to a new minor version, like 7.3 to 7.4. The breaking changes happen in the major version, like going
from 7 to 8. In 7.4, all breaking changes that will happen in 8 are marked as deprecations. This
gives you time and guidance to fix them. You only upgrade to 8 when no deprecations remain.

So how do we find deprecations! This deprecation logging panel is going to be your best
friend when upgrading Symfony.

Looks like we have 3. This first one is a PHP deprecation. The `addDroid()` method in our
`Starship` entity needs a parameter to be explicitly marked as nullable. Let's fix that
first.

Open `src/Entity/Starship.php` and scroll down to the `addDroid()` method. Ahh, even 
PhpStorm is warning me about this. The fix is simple, add a `?` before `DateTimeImmutable`:

[[[ code('2491d03716') ]]]

Back to the browser, refresh the homepage, and... still 3 deprecations... It didn't get
cleared. Sometimes when you fix deprecations, you need to clear the Symfony cache manually
for it to be picked up. This is because some deprecations are detected at compile time when
the container is being built. So in your terminal, run:

```terminal
symfony console cache:clear
```

Refresh the homepage again... Whoa, now we have 4 deprecations. Open the panel. Our `addDroid()`
deprecation is gone, so we did fix that one. Sometimes when you clear the cache manually, it
triggers other deprecations. Ok, 3 of these are from Doctrine. We'll have a whole chapter on
upgrading Doctrine a little later, so we'll ignore these for now.

The fourth one is a deprecated config option from zenstruck/foundry.

Before manually fixing this, let's first update our Symfony Flex recipes. Sometimes, just updating
these fixes deprecations for you.

To update the recipes, over in your terminal, run:

```terminal
symfony composer recipe:update
```

Hmm, that didn't work. It says we have uncommitted changes. The recipe update command needs to be
run on a clean slate - no pending changes. Run:

```terminal
git status
```

To see our changes. `composer.json`, `composer.lock`, and our `Starship.php`. Makes sense, let's
commit these with:

```terminal
git commit -a -m "composer update"
```

The `-a` adds all modified files to the changelist before committing and `-m` lets us set a commit message.

Clean slate, so run the recipe update command again:

```terminal-silent
symfony composer recipe:update
```

Ooo, we have a bunch to update. We'll go through them one-by-one. Hitting enter will
use the first one in the list, `doctrine/deprecations`.

"No file changed...". Hmm, run `git status` so see what's up. Just the `symfony.lock` file was modified.
This is the internal config file Symfony Flex uses to keep track of your recipes. We can just commit
this and move on:

```terminal
git commit -a -m "update recipes"
```

Onward! Run the recipe update command again:

```terminal-silent
symfony composer recipe:update
```

`asset-mapper` is next... Just the `symfony.lock` file again.

When I update recipes, I like to keep all the updates in a single commit. So we'll amend the
new changes with the previous commit with:

```terminal
git commit -a --amend
```

This opens a text editor to optionally update the commit message. I'll just exit to keep it the same.

Now update the `framework-bundle`'s recipe... Ooo, this one has some real changes. A cool thing about
this command is it shows the CHANGELOG from the symfony/recipe repository, so you can easily dig in
to understand why a change was made.

Run `git status` to see the changes. Aside from the `symfony.lock` file, `public/index.php` was modified.
Let's check it out! Open `public/index.php` and see the change... Looks like the `static` keyword was
added. I think this is a helpful micro-optimization, we'll keep it!

This is a good time to mention that you should never blindly update recipes. Always check the changes and
if you're unsure about a change, follow that changelog back to the symfony/recipe repository to find the
reasoning.

Amend the commit and move onto the next recipe update. The `monolog-bundle` also has some changes. Looks
like package config, so open up `config/packages/monolog.yaml` to see them. Just some comments removed.
No big deal... amend the commit.

Next, update the `stimulus-bundle` recipe and see the changes. Open `assets/stimulus_bootstrap.js`. Just
added some boilerplate stuff. Amend the commit and move onto updating the `twig-bundle` recipe. Our
base template was modified, so open `templates/base.html.twig`. Some FrankenPHP stuff was added. We're
not using FrankenPHP at the moment but doesn't hurt to leave it in case we do in the future!

Amend the commit and update the last recipe: `zenstruck/foundry`. The config file was modified, so
open `config/packages/zenstruck_foundry.yaml`. Just a comment was updated, so no big deal.

Amend the commit and we should be done! Run the recipe update command again to confirm. Nice!
"All packages appear to be up to date."

Before we check the app, clear our cache again...

Now refresh the homepage. 2 deprecations. The Doctrine autoloader one is still here, but we'll
ignore that until we upgrade Doctrine. The Foundry one is still here too, so the recipe update didn't
fix it. Let's fix it ourselves. It's saying this `enable_auto_refresh_with_lazy_objects` config
is going to be forced to `true` in 3.0, so let's set it to `true` now. Copy the option and open 
`config/packages/zenstruck_foundry.yaml`. Under `zenstruck_foundry`, paste, and set to `true`:

[[[ code('fe8d185e21') ]]]

Totally an aside, but if you're curious what this weird `&dev` and `*dev` syntax is, this is a YAML
anchor and alias. The `&dev` marks this config as an anchor named `dev`, and then the `*dev` is an alias that
references that anchor. So this is saying, "for the `test` environment, use the same config as `dev`".
It's a cool way to avoid duplication in your YAML files.

Ok, back to the browser and refresh the homepage. There are no deprecations now!

But... let's clear the cache... and refresh again... 3 now but these are the Doctrine
ones we'll fix later.

Ok, we've successfully upgraded our app to PHP 8.4. Next, we'll upgrade to Symfony 7.4, the last
Symfony 7 version.
