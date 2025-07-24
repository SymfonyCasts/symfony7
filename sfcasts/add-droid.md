# Re-adding addDroid(): Hide that Join Entity!

You might be asking yourself:

> Hold up, do I really need to create a `StarshipDroid` object every
> time I want to simply assign a droid to a starship?

I think that's too much work. Could we get back to the good old days
when we could just call `$ship->addDroid($droid)`?

Yes! This won't work yet, but that's never stopped us before! Load up the fixtures:

```terminal-silent
symfony console doctrine:fixtures:load
```

Ouch!

> Undefined property: `App\Entity\Starship::$droids`

Not exactly a surprise, as we're calling the long-dead `droids` property
in `addDroid()`.

## Remaking addDroid()

The first thing on our list is to check if the `Starship` already has the droid
in question. To make this happen, switch the property to use the `getDroids()`
method. But wait, `$this->getDroids()->add()` isn't going to cut it.
Instead, we're going to roll up our sleeves and create the join entity
right here: `$starshipDroid = new StarshipDroid()`
`$starshipDroid->setDroid($droid)` and `$starshipDroid->setStarship($this)`:

[[[ code('a0634b7760') ]]]

We've set the owning side of the relationship, but let's sync up the other side too.
We can do this by calling `$droid->starshipDroids->add($starshipDroid)`. 
Give the fixtures another whirl:

```terminal-silent
symfony console doctrine:fixtures:load
```

## Cascading the Persist

Aha, a new error. This one is pretty common in Doctrine,
though not always easy to understand:

> A new entity was found through the relationship `Starship#starshipDroids`
> that was not configured to cascade persist for the entity `StarshipDroid`.

This is a very fancy way of saying we've created a new `StarshipDroid`
object and told Doctrine to persist its related `Starship`. But we
never told Doctrine to persist the `StarshipDroid` object itself.

Here's the rub: we don't have access to the entity manager. So we can't
just say `$entityManager->persist($starshipDroid)`. Instead, we're going
to lean on something called `cascade=['persist']`, which I'll dive into that
right now.
