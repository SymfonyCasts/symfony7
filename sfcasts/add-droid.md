# Add Droid

Coming soon...

Creating a `StarshipDroid` object every time we want to relate a `Droid` to
a `Starship` is fine. It's a bit more work, but it's really clear. Let's
make the `starship->addDroid()` method work again. I want to be able to do
here is just say`$ship->addDroid($droid)` and have that work just like
before. But let's try it. Run the fixtures. It fails with `Undefined
property: App\Entity\Starship::$droids` coming from `Starship`. No big
surprise here because in `addDroid()`, we referenced the old `droids`
property. Let's refactor this to create our join entity. First, we're
checking if this `Starship` already has this `Droid`. To get that to work,
we need to do is change the property to use the `getDroids()` method.
Instead of `$this->getDroids()->add`, which isn't going to work anymore.
We're going to need to create that join entity here. `$starshipDroid = new
StarshipDroid();` `$starshipDroid->setDroid($droid);`
`$starshipDroid->setStarship($this);` And the last thing here is we need to
set the `StarshipDroid` onto this object.
`$this->starshipDroids->add($starshipDroid)`, because this is the owning
side of the relationship. We need to make sure that this is set.

Let's try the fixtures again.

```terminal
symfony console doctrine:fixtures:load
```

We've moved on to the next error, which is a common error in Doctrine. The
error says "a new entity was found through the relationship
'App\Entity\Starship#starshipDroids' that was not configured to cascade
persist for the entity StarshipDroid". We've created a new `StarshipDroid`
object and we've told Doctrine to persist this `Starship`. We never told it
to persist this `StarshipDroid`. The problem is that we don't have access
to the entity manager. So we can't just say
`entityManager->persist($starshipDroid)`. Instead, we're going to rely on
something called `cascade={"persist"}`, which we'll talk about next.