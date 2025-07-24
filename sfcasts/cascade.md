# Cascade Persist

Check out this error: it's a doozy!

> An entity was found through the relationship `Starship.droids` that was not
> configured to "cascade persist" operations for entity `StarshipDroid`.

Let me translate that for you:

> Hey, you're saving this `Starship` and it's got a `StarshipDroid`
> attached to it. That's great, but you forgot to tell me to persist the
> `StarshipDroid`. What do you want me to do?

But again, from inside `Starship`, we can't get the entity manager
to say `$manager->persist($starshipDroid)`. 

## Harnessing the Power of `cascade=['persist']`

The solution is to use something called *cascade persist*. 

Scroll up to the `$starshipDroids` property, and find the `OneToMany`. Add a new
option: `cascade`. I'll type it in manually. Set it to an array with
`persist` inside:

[[[ code('db5674a8de') ]]]

We're setting up a sort of domino effect. If anyone persists this `starship`,
we're going to cascade that persist down to any attached relationships. 

A word of caution though: use this power wisely. It makes your code
more automatic, which is great, but it can also make it harder to
spot bugs.

But in this case, it's exactly the fix we need.

Give those fixtures another whirl:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Back to Adding Droids

We're back in business. We can use `ship->addDroid()` once more. But
I still want to create a fleet of `starships` with `droids` attached to
them.

Remove all the manual code and bring back the `droids` property on the
`StarshipFactory`:

[[[ code('855236b449') ]]]

Fire up the fixtures again:

```terminal-silent
symfony console doctrine:fixtures:load
```

Guess what? They work! 

Behind the scenes, Foundry is calling `addDroid()` on each `Starship` for
each `droid`. And we just proved that `addDroid()` is back in action.

The creation of the `StarshipDroid` join entity is now hidden
from our entire codebase!

## Finer Control with `assignedAt`

But, what if you want to add a `droid` to a `starship` and control the `assignedAt`
property? Add an argument for `addDroid()` in `Starship`: a `DateTimeImmutable`.
Make it optional to keep things flexible. Then, after creating the `StarshipDroid`,
set the `$assignedAt` if we passed it in:

[[[ code('902c577144') ]]]

Cool... but there's a slight issue. Foundry won't let us control the `assignedAt`
field. So, if you want to assign some `droids` at a specific time, you'll need
to take the wheel manually. 

## Displaying `assignedAt`

Finally, let's make that `assignedAt` visible on our site. We'll need the
`StarshipDroid` join entity object to do that. It's a bit more work,
but totally doable.

Change the loop to `for starshipDroid in ship.starshipDroids`. Then
`starshipDroid.droid.name` and `starshipDroid.assignedAt` with the `ago`
filter for flair:

[[[ code('e882aa26f3') ]]]

Refresh and... we can now see when each `droid` was assigned. 

That's it, friends! We've explored the deepest corners of Doctrine
relationships, even the elusive many-to-many with extra fields. As always,
if you have questions, drop them in the comments below. We're all in this
together!
