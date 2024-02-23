# Tailwind CSS

What about CSS? You're free to add whatever CSS you want to `app/styles/app.css`.
That file is already loaded on the page.

Want to use Bootstrap CSS? Check out the Asset Mapper docs on how to do
that. Or, if you want to use Sass, there's a [symfonycasts/sass-bundle](https://github.com/symfonycasts/sass-bundle)
to make that easy. Though, I recommend not jumping into Sass *too* quickly.
A lot of the features that Sass is famous for can now be done in native CSS, like
CSS variables and even CSS nesting.

## Hello Tailwind

What's my personal choice for a CSS framework? Tailwind. And part of the reason is
that Tailwind is *insanely* popular. So if you're looking for resources or pre-built
components, you're going to have a lot of luck if you use Tailwind.

But Tailwind *is* a bit odd in one way: it's not simply a big CSS file that you plop
onto your page. Instead, it has a *build* process that scans your code for
all the Tailwind classes you're using. It then dumps a final CSS file that *only*
contains the code you need.

In the Symfony world, if you want to use Tailwind, there's a bundle that makes it
really easy. Spin over your terminal and install a new package: `composer require`
`symfonycasts` - hey I know them - `tailwind-bundle`:

```terminal-silent
composer require symfonycasts/tailwind-bundle
```

For this package, the recipe doesn't do anything other than enable the new bundle.
To get Tailwind rocking, *one* time in your project, run:

```terminal
php bin/console tailwind:init
```

This does three things. First, it downloads a Tailwind binary in the background,
which you'll never really need to think about. Second, it creates a `tailwind.config.js`
file at the root of our project. This tells Tailwind *where* it needs to look in
our project for Tailwind CSS classes. And third, it updated our `app.css` to add
these three lines. These will be replaced by the *real* Tailwind code in the
background by the binary.

## Running Tailwind

Finally, Tailwind needs to be *built*, so we need to run a command to do that:

```terminal
php bin/console tailwind:build -w
```

This scans our templates and output the final CSS file in the background.
The `-w` puts it in "watch" mode: instead of building *once* and exiting, it
watches our templates for changes. When it notices any updates, it will
automatically rebuild the CSS file. We'll see that in minute.

But we should already see a difference. Let's go to the homepage. Did you see that?
The base Tailwind code did a reset. For example, our `h1` is now tiny!

## Seeing Tailwind in Action

Let's try this out for real. Open `templates/main/homepage.html.twig`. Up
on the `h1`, make this bigger by adding a class: `text-2xl`.

As soon as we save that, you can see that tailwind *noticed* our change and
rebuilt the CSS. And when we refresh, it got bigger!

Our source `app.css` file is *still* super simple - just those few lines
we saw earlier. But view the page source and open the `app.css` that's being sent
to our users. It's the built version from Tailwind! Behind the scenes, some magic
exists that replaces those three Tailwind lines with the *real* Tailwind CSS code.

## Automatically Running Tailwind with the symfony Binary

And... that's kind of it! It just works. Though there *is* an easier and more
automatic way to run Tailwind. Hit Ctrl+C on the Tailwind command to stop it.
Then, at the root of our project, create a file called `.symfony.local.yaml`.
This is a config file for the `symfony` binary web server that we're using.
Inside, add `workers`, `tailwind`, then `cmd` set to an array with each part
of a command: `symfony`, `console`, `tailwind`, `build`, `--watch`, or you
could use `-w`: it's the same.

I haven't talked about it yet, but instead of running `php bin/console`, we can
also run `symfony console` followed by any command to get the same result. We'll
talk about *why* you might want to do that in a future tutorial. But for now,
consider `bin/console` and `symfony console` the same thing.

Also, by adding this `workers` key, it means that instead of *us* needing
to run the command manually, when we start the `symfony` web server, *it* will
run it *for* us in the background.

Watch. In your first tab, hit Ctrl+C to stop the web server... then re-run

```terminal
symfony serve
```

so it sees the new config file. Watch: there it is! It's running
the tailwind command in the background!

We can take advantage of this immediately. In `homepage.html.twig`, change this to
`text-4xl`, spin over and... it works! We don't even need to *think* about the
`tailwind:build` command anymore.

And since we'll be styling with Tailwind, remove the blue background.

## Copying in Styled Templates

Ok, this tutorial is *not* about Tailwind or how to design a website. Trust me,
you do *not* want Ryan leading the web design charge. But I *do* want to have a
nice-looking site... and it's *also* important to go through the process of working
with a designer.

So let's pretend that someone else has created a design for our site. And they've
even given us some HTML with Tailwind classes *for* that design. If you download
the course code, in a `tutorial/templates/` directory, we have 3 templates.
One-by-one, I'm going to copy each file and paste it over the original. Don't
worry, we'll look at what's happening in each of these files. Do
`homepage.html.twig`... and finally `show.html.twig`.

***TIP
If you copy the files (instead of the file contents), Symfony's cache system
may not notice the change and you won't see the new design. If that happens,
clear the cache by running `php bin/console cache:clear`.
***

I'm going to delete the `tutorial/` directory entirely so I don't get confused and
edit the wrong templates.

Ok, let's see what this did! Refresh. It looks beautiful! I *love* working inside
a nice design. But... some parts are broken. In `homepage.html.twig`, this is our ship
repair queue... which looks nice... but there's no Twig code! The
status is hardcoded, name is hardcoded and there's no loop.

Next: let's take our new design and make it *dynamic*. We'll also learn how to
organize things into template partials *and* introduce a PHP enum, which are fun.
