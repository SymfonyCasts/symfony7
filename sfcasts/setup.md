# Setup

Welcome back, my friends! In the last tutorial about Doctrine, we
accomplished some things that, honestly, I'm pretty darn proud of. We created an
entity, migrations, fixtures, wrote custom queries and with a little bit more
time I could've taught you how to bake sourdough bread. However, we can't really build
the app of our dreams without talking about database relationships. For example,
that slice of sourdough bread belongs to me, or I have
many slices of sourdough bread.

## Project Setup

As usual, to fully relate to what I'm doing, you
should download the course code from this page. After you unzip the file,
you should have a `start/` directory with the same code that you see here.
Follow this nifty `README.md` file for all the setup details. The last
step is to open a terminal, move into your project, and run

```terminal
symfony serve
```

Sometimes I run this with a `-d`, to run in the background as a
daemon. This time, I'll run it in the foreground. One of the
cool things is we can see all these logs down here: these are the
server logs. Notice that we're using
Tailwind: we can see it downloading Tailwind and building in the
background. When that's done, scroll up and click this link
to open up our app: Starshop! It's all about repairing ships. All these Starships
are coming from the database... cause we're awesome. As a
reminder, in `src/Entity/` we have one entity: `Starship`.
Boring. In this tutorial, we need to track which parts we've ordered for each ship
and how much they cost. A part belongs to a ship and a ship has many parts...
see where we're going with this? By the end, we'll have some pretty serious relationships
that *will* let you build that dream app. Let's get that started next.
