# Getting to Know our Tiny Project

Sprint back to your command center (aka terminal). This first tab is running the
web server. If you need to stop it, press Ctrl-C... then restart it with:

```terminal
symfony serve
```

***TIP
You can use `symfony serve -d` to run the command in the "background" so that
you can continue using this terminal tab.
***

We'll leave that alone and let it do its thing.

## Our Project's 15 Files

Open a second terminal tab in the same directory. When we ran the `symfony new`
command, it downloaded a tiny project *and* initialized a Git repository with
an initial commit. That was super nice! To see our files, I'm going to open this
directory in my favorite editor: PhpStorm. More on this editor in a few minutes.

Right now, I want you to notice just how *small* our project is! To see the full
list of committed files, back at your terminal, run:

```terminal
git ls-files
```

Yea, that's it. Only about 15 files committed to git!

## Where's Symfony?

So then... where the heck is Symfony? One of our 15 files is especially important:
`composer.json`. 

[[[ code('dd6d61790e') ]]]

Composer is the package manager for PHP. Its job is simple: read
the package names under this `require` key and download them. When we ran the
`symfony new` command, it downloaded these 15 files and *also* ran `composer install`.
That downloaded all of these packages into the `vendor/` directory.

So where is Symfony? It's in `vendor/symfony/`... and we're already using about
20 of its packages!

## Running Composer

The `vendor/` directory is *not* committed to git. It's ignored thanks to another
file we started with: `.gitignore`. 

[[[ code('9895cd1cc5') ]]]

This means that if a teammate clones our project, they will *not* have this directory. 
And that's okay! We can always repopulate it by running `composer install`.

Watch: I'll right-click and delete the entire `vendor/` directory. Gasp!

If we try our app now, it's busted. Bad feels! To fix it & save the day, at
your terminal,
run:

```terminal
composer install
```

And... presto! The directory is back.... and over here, the site works again.

## The 2 Directories you Care About

Looking back at our files, there are only two directories that we even need
to think about. The first is `config/`: this holds... configuration! We'll
learn about what these files do along the way.

The second is `src/`. This is where *all* your PHP code will live.

And that's really it! 99% of the time you're either configuring something or writing
PHP code. That happens in `config/` & `src/`.

What about the other 4 directories? `bin/` holds a single `console` executable
file that we'll try out soon. But we're never going to look at or modify that file.
The `public/` directory is known as your document root. Anything you put here - like
an image - will be publicly accessible. More about that stuff later.
It also holds `index.php`. 

[[[ code('fef370b8cc') ]]]

This is known as your "front controller": it's the main
PHP file that your web server executes at the start of every request. And while it
*is* super important... you'll never edit or even think about this file.

Up next is `var/`. This is *also* ignored from git: it's where
Symfony stores log files and cache files that it needs internally. So very important...
but not something we need to think about. And we already talked about `vendor/`.
That's everything!

## Prepping PhpStorm

Now before we get coding, I mentioned that I use PhpStorm. You're free to use whatever
editor you want. However, PhpStorm is *incredible*. And one big reason is the
unmatched Symfony *plugin*. If you go to PhpStorm -> Settings and search
for "Symfony", down here under Plugins and then Marketplace, you can find
it. Download & install the plugin if you don't already have it. *After*
installation, restart PhpStorm. Then there's one more step. Go back into settings
and search for Symfony again. This time you'll have a Symfony section. Be sure
to enable the plugin for each Symfony project you work on... otherwise you won't
see all the same magic I have.

Ok! Let's start coding and build our first page in Symfony next.
