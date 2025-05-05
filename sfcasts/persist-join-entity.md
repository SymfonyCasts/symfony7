# Persist Join Entity

Coming soon...

## Fixing the Error Caused by Refactoring

Alrighty folks, our Symfony journey continues! After we've dutifully
refactored our many-to-many relationship to include a join entity called
`StarshipDroid`, we're going to reload our fixtures. Hold onto your hats,
here comes an error! We're greeted with an `Undefined property:
App\Entity\Starship::$droids` message. This error is being spat out from
`StarshipLine205`. The culprit? It's our `getDroids()` method. 

You might be wondering, "Why is that?" Well, let me shed some light on it.
Think of our `Starship` as a spaceship carrier that's trying to figure out
how many droids it has. When we're linking the droids to the `Starship` in
the fixtures, `StarshipArrow getDroids()` gets called behind the scenes.
It's like a roll call for droids. But, we've still got a reference to a
phantom property which no longer exists, so naturally, it's going to trip
up. The quick fix? We're going to comment it out and huzzah! The fixtures
are back in action.

## Creating Objects by Hand

Now, to really drive home what changed here and what's causing our problem,
I'm going to create a couple of objects by hand. It's like digital origami.
We'll start by declaring `ship` equals `StarshipVector`. We could use
`crate1`, but let's mix things up and grab a random one. And just to keep
things crystal clear, we're going to use the underscore real trick so we
have the real McCoy, not some shadowy proxy. We'll rinse and repeat to grab
a droid with `droid` equals `droidFactory`, grabbing a random one again and
calling `real()` on that.

## Highlighting the Key Change

Here's where the plot thickens. Previously, we could use `shipArrow
addDroidDroid` with no issues. Now, it's as useful as a chocolate teapot.
Why? Because it's referencing the obsolete `droids` property. It's now
called `StarshipDroids`, and as you might've guessed, it's just a join
entity. This means we need to get a little more hands-on. It's like
swapping out an auto-pilot for a manual control stick. We'll ditch
`shipArrow addDroid` and instead declare `StarshipDroid` equals new
`StarshipDroid`, then `StarshipDroidArrowSetDroid`, not `ship` but `droid`.
Next, we'll set `StarshipDroidArrowSetStarship` to `ship`. We're manually
creating this entity and setting up those many-to-one relationships. And
finally, since we're assembling these by hand, we need to persist and flush
them using `managerArrow persist StarshipDroid`, and `managerArrow flush`.

Sure, it's a bit of work, but think of it as a simple, somewhat dull, but
necessary pit stop on our Symfony road trip. Give the fixtures a spin and
let's peek at the database with `doctrine query SQL`, `select star from
StarshipDroid`. We're selecting from that join table and voila! One entry
for one `Starship`, and one `droid`. So far, so good. Let's refresh the
homepage. Oh dear, another error! It's `[Semantical Error] line 0, col 55
near 'droids WHERE': Error: Class App\Entity\Starship has no association
named droids`. Looks like we've got a query issue on our hands.

## Fixing the Query Issue

Time to roll up our sleeves and dive into
`src/repository/StarshipRepository`. Our join here is having a bit of a
meltdown. We're joining at `s.droids`, but `droids` property has left the
building. We need to join on `StarshipDroids`. So let's change `s.droids`
to `s.starship. StarshipDroids`. And for clarity, let's call it
`StarshipDroid`, because that's what it really is. I like to keep things
singular, so we'll stick with `StarshipDroid` and simply count them instead
of the nonexistent `droids`.

With that sorted, we'll refresh the homepage and... we've got another
error. It's `Warning: Undefined property: App\Entity\Starship::$droids`.
This is coming from our `ship.droidNames` in the homepage template. We know
that when we call `ship.droidNames`, it's calling
`StarshipArrowGetDroidNames` and we're still referencing the ghost of the
`droids` property.

## Making Magic Happen

Now, let's sprinkle some Symfony magic dust. We'll fix this in a way where
we only have to make changes in one file, and the rest of our application
will just start working. Now that's what I call coding magic!