# Magical Flex Recipes

I have a secret. When our project was created, it wasn't 15 files. It was...
*one* file. If you peeked inside the code for the `symfony new` command, you'd
discover that it's a shortcut for just two things. First, it clones a
repository called `symfony/skeleton`... which is just *one* file if you ignore the
license. And second, it runs `composer install`.

That's it! But hold on, if that's the case, where in the world did all these other
files come from? Like, the stuff in `bin/`, `config/` and `src/`?
The answer starts with a special package inside our `composer.json` file called
`symfony/flex`. Flex is a Composer *plugin* that adds two superpowers to
Composer: aliases and recipes.

## Flex Aliases

Aliases are simple. To add a *new* package to your app - which we'll do in a minute -
you run `composer require` then the name of the package like `symfony/http-client`.
Flex gives the most important packages in the Symfony ecosystem a *shorter* name,
called an alias. For example, `symfony/http-client` has an alias called
`http-client`. Yup, we could run `composer require http-client` and
Flex would translate that to the final package name. It's just a shortcut
when adding packages.

If you want to see all the available aliases, go to a repository called
[symfony/recipes](https://github.com/symfony/recipes)... then click the link
to `RECIPES.md`. On the right, there they are!

## The Recipes System

The second superpower that Symfony Flex adds to Composer is *recipes*. These
are fascinating. When you add a new package, it *may* have a recipe, which is
basically a set of files that will be added to your project. And it turns out that
*every* file that we started with - in `bin/`, `config/`, `public/` -
these *all* came from the recipes of the packages that were originally
installed.

For example, `symfony/framework-bundle` is the "core" package of the Symfony Framework.
You can check out its recipe by going to the `symfony/recipes` repository
and navigating to `symfony`, `framework-bundle`, then the latest version. Boom!
Check out `config/packages/`: most of the stuff we started with
came from this recipe!

Another way to see the recipes is at your command line. Run:

```terminal
composer recipes
```

Apparently the recipes of *four* different packages were installed. And we could
get info about any of these by adding its name to the end of the command.

Anyway, recipes are amazing because we can install a package and instantly get any
files we need. Instead of fussing around with configuration, we get right to work.

## Installing PHP CS Fixer

Let's try this out: let's add a new package called PHP-CS-Fixer that will give us an
executable file to fix the *styling* of our code. For example, in
`src/Controller/MainController.php`, if you follow PHP coding standards,
the curly brace should live on the next line after a function. If we did something
like this, our file now violates those standards. That wouldn't hurt anything,
but you know, we want to keep our code looking clean. And PHP-CS-Fixer can help
us do that.

To install it, run:

```terminal
composer require cs-fixer-shim
```

And yes, this is an *alias*. On top, the true package is `php-cs-fixer/shim`.

Did this package come with a recipe? It did! The `Configuring php-cs-fixer/shim`
tells us that. But, we can *also* see it by running:

```terminal
git status
```

The fact that `composer.json` and `composer.lock` are modified is 100% normal
Composer behavior. You can see that `composer.json` has the new library under
the `require` key. But every *other* modified or new file *is* thanks to the
package's recipe.

## Investigating the Recipe

Let's investigate these! Open up `.gitignore`. Cool! At the bottom, it added two
new entries for two common files that you want to ignore when you use PHP CS fixer.
The recipe also added a new `.php-cs-fixer.dist.php` file. This is CS Fixer's
configuration file. And check it out! It's pre-built to work for our Symfony app.
It tells it to fix all files in the current directory, but ignore the `var/` directory
because that's where Symfony stores its cache files. It also tells it to use a
ruleset called Symfony. That means that we want our code style to
match Symfony's style. The point is: instead of *us* wasting time hunting down this
default config... we just get it!

The last modified file is `symfony.lock`. This keeps track of which recipes we have
installed and at what version. And yes, we *are* going to commit all these files
to our repository.

## Using PHP-CS-Fixer

Now that we've installed the package, let's use it. Do that by running:

```terminal
./vendor/bin/php-cs-fixer
```

That'll show all the available commands. The one we want is called fix. Try it:

```terminal-silent
./vendor/bin/php-cs-fixer fix
```

And... yes! It found the violation in `MainController.php`! When we
go to that file... yea! It moved my curly brace from the end of the line back down
to the next line. That's awesome.

Next up, let's meet and install one of my favorite libraries in all of PHP: the
Twig templating engine.
