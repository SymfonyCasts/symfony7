# Setting up our Symfony App

Welcome to the first Symfony 7 tutorial! My name is Ryan - I live here in the
fantasy world of Symfonycasts and... I am *beyond* excited to be *your* guide through
this series all about Symfony, web development... bad jokes... space animations,
and most importantly, building *real* things we can be proud of. For me, it feels
like *I'm* the lucky person that gets to give you a personal tour of the Enterprise...
or whatever nerdy thing gets *you* most excited.

And that's because, I *love* this stuff. Bootstrapping databases, building
beautiful user interfaces, writing high-quality code... it gets me out of bed in
the morning. And Symfony is *the* best tool to do all of this... and become a better
developer along the way.

And that's really my goal: I want *you* to enjoy all of this as much as I do
... and to feel empowered to build all the amazing things you have floating around
in your mind.

## What Makes Symfony Special

Now, one of my *favorite* things about teaching Symfony is that our project is going
to start *tiny*. That makes it easy to learn. But then, it'll scale up
automatically as we need more tools via a unique *recipe* system. Symfony
is actually a collection of over *200* small PHP libraries. So that's a *ton* of
tools... but *we* get to choose what we need.

Because, you might be building a pure API... or a full web app, which is what we'll
focus on in *this* tutorial. Though, if you *are* building an API, follow the first
few tutorials in this series, then pop over to our API Platform tutorials. API Platform
is a mind-blowingly fun & powerful system for making APIs, built right on top
of Symfony.

Symfony is also *blazingly* fast, has long-term support versions and works a lot
on creating a delightful developer experience *while* keeping to programming
best-practices. This means we get to write high-quality code and *still* get our
work done quickly.

Ok, enough of me gushing about Symfony. Ready to get to work? Then beam aboard.

## Installing the Symfony Binary

And head over to https://symfony.com/download. This page has instructions on how
to download a standalone binary called `symfony`. Now this is *not* Symfony itself...
it's just a little tool that'll help us do things, like start new Symfony projects,
run a local web server or even deploy our app to production.

Once you've downloaded and installed it, open a terminal and move into *any*
directory. Check that the `symfony` binary is ready to go by running:

```terminal
symfony --help
```

It's got a *bunch* of commands, but we'll just need a few. Before we start a project,
also run

```terminal
symfony check:req
```

which stands for check requirements. This makes sure that we have everything
on our system needed to run Symfony, like PHP at the correct version and
some PHP extensions.

Once this is happy, we can start a new project! Do it with `symfony new` and then
a directory name. I'll call mine `starshop`. More on that later.

```terminal-silent
symfony new starshop
```

This will give us a *tiny* project with only the *base* things installed.
Then, we'll add more stuff little-by-little along the way. It's gonna be great!
But later, when you feel comfortable with Symfony, if you want to get started more
quickly, you can run the same command, but with `--webapp` to get a project with
*much* more stuff pre-installed.

Anyway, move into the directory - `cd starshop` - then I'll type `ls` to check
things out. Cool! We'll get to know these files in the next chapter, but
this is *our* project... and it's already working!

## Starting the symfony Web Server

To *see* it working in a browser, we need to start a web server. You can use
*any* web server you want - Apache, Nginx, Caddy, whatever. But for local
development, I highly recommend using the `symfony` binary we just installed.
Run:

```terminal
symfony serve
```

The first time you do this, it might ask you to run another command to set up
an SSL certificate, which is nice because then the server supports https.

And... bam! We have a new web server for our project running at https://127.0.0.1:8000.
Copy that, spin over to your most favorite browser, paste and... welcome to
Symfony 7! That's what I was going to say!

Next, let's sit down, order some Earl Grey tea, and become friends with every
file in our new app... which isn't very many.
