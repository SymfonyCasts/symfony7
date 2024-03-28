# Maker Bundle: Let's Generate Some Code!

Hats off for nearly making it through the first Symfony tutorial. You've taken
a huge step toward building whatever you want on the web. To celebrate, I want to
play with MakerBundle: Symfony's awesome tool for code generation.

## Composer require vs require-dev

Let's get it installed:

```terminal
composer require symfony/maker-bundle --dev
```

We haven't seen that `--dev` flag yet, but it's not *that* important. Move over and
open `composer.json`. Thanks to the flag, instead of `symfony/maker-bundle` going
under the `require` key, it was added down here under `require-dev`. 

[[[ code('958656467c') ]]]

By default, when you run `composer install`, it will download everything under *both* `require`
and `require-dev`. But `require-dev` is meant for packages that *don't* need
to be available on production: packages that you only need when you're developing
locally. That's because, when you do deploy, if you want, you can tell Composer:

> Hey! Only install the packages under my `require` key: don't install the
> `require-dev` stuff.

That can give you a small performance boost on production. But mostly, it's not a
big deal.

## The Maker Commands

Now, we just installed a *bundle*. Do you remember the main thing that bundles
give us? That's right: *services*. This time, the services that MakerBundle gave
us are services that provide new *console* commands. Drumroll please. Run:

```terminal
php bin/console
```

Or, actually, I'll start running `symfony console`, which is the same thing. Thanks
to the new bundle, we have a ton of commands that start with `make`! Commands for
generating a security system, making a controller, generating doctrine entities to
talk to the database, forms, listeners, a registration form.... lots and lots of
stuff!

## Generating a Console Command

Let's use one of these to make our *own* custom console command. Run:

```terminal
symfony console make:command
```

This will interactively ask us about our command. Let's call it: `app:ship-report`.
Done!

This created exactly one file: `src/Command/ShipReportCommand.php`. Let's go check
that out! 

[[[ code('73dc758763') ]]]

Cool! This is a normal class - it *is* a service, by the way - but with
an *attribute* above: `#[AsCommand]`. This tells Symfony:

> Yo! See this service? It's not *just* a service: I would like you to include it
> in the list of console commands.

The attribute includes the name of the command and a description. Then the class itself
has a `configure()` method where we can add arguments and options. But the main part
is that, when somebody *calls* this command, Symfony will call `execute()`.

This `$io` variable is cool. It lets us output things - like `$this->note()`
or `$this->success()` - with different styles. And though we don't see it here,
we can also ask the user questions interactively.

The best part? *Just* by creating this class, it's ready to use! Try it out:

```terminal
symfony console app:ship-report
```

That's so cool! The message down here comes from the success message at the
bottom of the command. And thanks to `configure()`, we have one *argument* called
`arg1`. Arguments are string that we pass *after* the command, like:

```terminal
symfony console app:ship-report ryan
```

It says: 

> You passed an argument: ryan

... which comes from this spot in the command.

## Building a Progress Bar

There are a *lot* of fun things you can do with commands... and I want to play
with one of them. One of the superpowers of the `$io` object is to create
animated progress bars.

Imagine we're building a ship report... and it requires some heavy queries.
So we want to show a progress bar on the screen. To do that, say `$io->progressStart()`
and pass it however many rows of data we're looping through and handling. Let's
pretend we're looping over 100 rows of data for this report.

Instead of looping over real data, create a fake loop with `for`. I'm even
going to include the `$i` variable in the middle! Inside, to advance the
progress bar, say `$io->advance()`. Then, here is where we would do our heavy query
or heavy work. Fake that with a `usleep(10000)` to create a short pause.

After the loop, finish with `$io->progressFinish()`.

[[[ code('c6afb73551') ]]]

That's it! Spin over and give that a try:

```terminal-silent
symfony console app:ship-report ryan
```

Oh, that is *so* cool.

And... that's it people! Give yourself a high five... or, better, surprise a co-worker
with a jumping high five! Then celebrate with a well-deserved beer, tea, walk
around the block or frisbee match with your dog. Because... you did it! You took
the first big step into being dangerous with Symfony. Then, come back and try
this stuff out: play with it, build a blog, create a few static pages, *anything*.
That will make a huge difference.

And if you ever have any questions, we watch the comment section below each video
closely and answer everything. Also keep going! In the next tutorial, we're going
to become even *more* dangerous by diving deeper into Symfony's configuration and
services: the systems that drive *everything* you'll do in Symfony.

Alright, friends, see you next time!
