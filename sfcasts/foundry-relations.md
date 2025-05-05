# Foundry Relations

Coming soon...

## Step into My Symfony Workshop

We've got a couple of parts and a few starships, but to really deck out our
data playground — and make our app development a blast — we need a lot
more stuff. This is a job perfectly suited for our trusty helper, Foundry.
Let's start by dusting off our manual code that created a part and a
starship and introduced them to each other. You ready? Let's dive in.

## Flexing the Foundry Factory

We're going to start fresh. It doesn't really matter where we're headed, so
let's scroll down to the bottom of our code. Let's flex our fingers and say
`StarshipPartFactory::createMany(100);`. That's right, we're going all in
and creating a whopping 100 parts. But let's not get ahead of ourselves, we
need to test our fixtures. 

```terminal
symfony console doctrine:fixtures:load
``` Uh-oh, we've hit a
familiar snag. Our error message reads `starship_id` cannot be null in
`StarshipPart`. This all traces back to our `StarshipPartFactory`. Down in
the `getDefaults()` function, this is the only data passed to our
`StarshipPart` when it's created. The golden rule here is to make
`getDefaults()` return a key for every required property on this object.
Right now, we're obviously missing the `starship` property, so let's
quickly add that. It's `starship` key, not `starship_id`. We'll set this to
a nifty method called `Starship::randomOrCreate()` and pass this in an
array. 

## Setting the Stage for Starship Parts

Let's make sure these parts actually show up on our homepage, where we're
only displaying starships with 'in progress' or 'waiting' status. Let's set
the status to `StarshipStatusEnum::IN_PROGRESS`. This is an impressive,
powerful method because it will first look in the database to find a
`Starship` that matches this criteria. If it finds one, it uses that. If it
doesn't, it creates one with that status. Alright, let's try those fixtures
now.

```terminal
symfony console doctrine:fixtures:load
``` Great, no errors!
Let's grab all the `StarshipPart`s. 

```terminal
SELECT * FROM starship_part
``` This is looking perfect. Look
closely, we've got 100 parts each tied to a random `Starship`, which should
be a `Starship` with an 'in progress' status.

## Taking Control in Foundry

But what if we want more control? What if we want to assign all 100 of
these parts to the same ship? I know it sounds a bit eccentric, but trust
me, it'll help us explain Foundry and relationships. 

Let's start by getting a ship variable, so `ship = Starship::createOne()`.
Then, in `StarshipPartFactory::createMany()`, we can pass a second argument
to specify that we want `starship` to be set to this specific ship. 

```terminal
symfony console doctrine:fixtures:load
``` And voila! All parts
are now related to the same ship. Also, if we query the `Starship`s, you'll
see we have 23 ships, which is correct because our fixture creates 20 at
the bottom, plus the extra 3 we added. Everything's coming together.

## The Foundry Plot Twist

Now, here's where things get interesting. In `StarshipPartFactory`, instead
of using `randomOrCreate()`, let's switch to `createOne()`. Trust me on
this one, you'll thank me later. Let's load the fixtures and query for all
the ships. Whoa, we suddenly have a fleet! 123 ships to be exact. 

The catch is, for each part, `getDefaults()` is called. So for all 100
parts, it's triggering this line, creating and saving a `Starship`, even
though we never use that specific `Starship` because we override it moments
later. The solution? Change this to `Starship::new()`. This is the secret
sauce — it creates a new instance of the factory, not an object in the
database.

```terminal
symfony console doctrine:fixtures:load
``` Now let's query the
ships. Perfect! We're back to our 23 ships. 

## The Factory Recipe

These factory instances act like secret recipes for creating objects.
`Starship::new(['status' => StarshipStatusEnum::STATUS_IN_PROGRESS])`
doesn't actually create an object in the database, it's just a blueprint
for one. When you pass a factory instance, Foundry delays creating the
object until it's needed. Only if the `Starship` isn't overridden will it
create a new `Starship` and save it. This is a best practice when setting
relationships — set them to a factory instance.

Let's clean up our fixtures by removing this override. We'll switch back to
`randomOrCreate()` because, let's be honest, it's a pretty cool method.

```terminal
symfony console doctrine:fixtures:load
``` Let's reload the
fixtures one last time to make sure we didn't break anything. Everything's
looking good. 

## Time for a Magic Show

Next up, we're going to fetch all of the parts for our ship and witness
some sweet doctrine magic. Get ready to be amazed!