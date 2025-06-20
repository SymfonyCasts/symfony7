# Setup

Hey friends! Welcome back! And welcome back to me if I can be so bold. I'm
returning from my 14-month brain cancer "vacation". Unfortunately, I'm not all
better & sure, I type with one hand, like a Symfony pirate. But
but dang, I missed you all and I missed Symfony. And today is a good day.
*Thank you* for the support, love & patience. Now, to business!

In the previous tutorial, we did some impressive stuff. We
crafted an entity, set up migrations, created fixtures and queried like SQL nerds.
But let's face it, we can't build anything that will impress our friends *or* grandma
without understanding database relationships. For example,
"this pizza slice belongs to me" or "I have a lot of pizza
slices." Mmm, I like pizza.

To fully warp your relations game, download
the course code from this page. Once you've unzipped it, you'll find
a `start/` directory with the code you see here. Check out the handy
`README.md` file for all the setup goodies. The last step will be to
fire up a terminal, navigate into the project, and run: `symfony serve`.
Sometimes I run this with a `-d`, so it does its thing in the background.
But today, I'll run it loud & proud in the foreground.

```terminal
symfony serve
```

## Oh Hi There Server and Tailwind Logs

One useful side effect of running in the foreground is that we get to see
all the logs, though you can see these anytime by running `symfony server:log`.
This project uses Tailwind CSS and you can see it downloading Tailwind and
building in the background. Once that's done, I'm going to scroll up and
click the link to launch our app: Starshop!

## Introduction to Starshop

Starshop is all about repairing ships, a one-stop solution for all your
spaceship woes because nobody wants to float through intergalactic space with a
broken shower. Gross.
All these starships are coming straight from
the database. If you navigate to `src/Entity/`, you'll
find our *one* shiny entity: `Starship`.

[[[ code('bc4c539663') ]]]

## Next Steps: Tracking Ship Parts

It's time to spice things up by tracking the *parts* of a ship & their cost.
*Then* we'll assign each part to a ship in the database.
