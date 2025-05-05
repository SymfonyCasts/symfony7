# Setup

Hey there, friends! Welcome back. In the previous tutorial, we
did some impressive stuff, if I do say so myself. We crafted an entity, set up
migrations, created fixtures and queried like SQL nerds. But let's face it, you
can't build anything truly impressive without understanding
and leveraging database relationships. For example, consider the
relationship "this pizza slice belongs to me" or "I have a lot of pizza
slices." Mmm, I like this analog

To fully get on board with what we're doing, make sure you've downloaded
the course code from this page. Once you've unzipped the file, you'll find
a `start/` directory that mirrors the code you see here. Check out the handy
README.md file for all the setup instructions. Your last step will be to
fire up a terminal, navigate into the project, and run: `symfony serve`.
Sometimes, you might run this with a `-d`, to let it run quietly in
the background. But  today, I'll run it loud & proud in the foreground.

```terminal
symfony serve
```

## Oh Hi There Server and Tailwind Logs

One useful side effect of running in the foreground is you'll see all these logs
streaming in. These are your server logs. This project uses Tailwind CSS and
you can see it downloading Tailwind and
building in the background. Once that's done, I'm going to scroll up and
click the link to launch our app: Starshop!

## Introduction to Starshop

Starshop is all about repairing ships, a one-stop solution for all your
spaceship woes cause nobody wants to float through intergalactic space with a
broken shower.
All these starships you see here are coming straight from
the database. If you navigate to `src/Entity/`, you'll
find our *one* shiny entity: `Starship`.

## Next Steps: Tracking Ship Parts

It's time to spice things up by tracking the *parts* of a ship & their cost.
By the end we'll have some seriously useful relationships that will let you build
the app of your dreams. Let's dive in!
