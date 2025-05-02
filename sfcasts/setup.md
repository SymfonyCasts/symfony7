# Setup

Coming soon...

Welcome back, my friends! In our last tutorial about Doctrine, we did some
stuff that I'm pretty darn proud of. We created an entity, migrations,
fixtures, all kinds of good stuff. However, you can't really build anything
that's that impressive without talking about database relationships. For
example, that slice of pizza belongs to me, or I have many slices of pizza.
As usual, to fully relate to what I'm doing, you should download the course
code from this page. After you unzip the file, you should have a start
directory with the same code that you see here. Follow this nifty readme.md
file for all the setup instructions. The last step will be to open a
terminal, move into your project, and run `symfony serve`. Sometimes you
run this with a "-d", to run in the background of the daemon. This time,
I'm going to run it in the foreground. One of the cool things is you'll see
all these logs coming from here. These are the server logs. One important
one down here is you'll see we're using Tailwind. You can actually see it
downloading Tailwind and building in the background. When that's done, I'm
going to scroll up and click this link to open up Starshop, our
application. It's all about repairing ships at Repairing Ships. All these
Starships are coming from the database. As a reminder, if you go to Source
Entity, we only have one entity, `Starship`. Kind of boring. Snooze. In
this tutorial, we're going to start tracking the parts of a ship. As we're
repairing a ship, we're going to keep track of which parts we've ordered
for the ship and how much they cost. By the end, we're going to have some
pretty serious relationship set up that is going to unlock you to build
whatever is in your dreams. Let's get that started next.