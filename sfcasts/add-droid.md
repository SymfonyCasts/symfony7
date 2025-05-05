# Add Droid

Coming soon...

## StarshipDroid: Your Ticket to Interstellar Travel

Now, you might be thinking: "Hold up, do I really need to create a
`StarshipDroid` object every time I want a droid to hop aboard a starship?"
Well, sure. It's a bit more elbow grease, but it's also as clear as a
summer's day on Tatooine. But hey, let's kick things up a notch, shall we?
How about we get the `starship->add()`, `ship->addDroid()` method back in
the game? 

That's right. I'm talking about tossing out the old, and getting back to
using `ship->addDroid(droid)` just like the good ol' days. Now, this might
not work right off the bat, but let's give it a shot. Load up the fixtures.
Ouch! We're hit with an 'Undefined property:
`App\Entity\Starship::$droids`' straight from `Starship`. Not exactly a
surprise, considering we called upon the long-gone `droids` property in
`addDroid()`.

## Refactoring: It's Like Spring Cleaning, But for Code

So, what now? It's refactoring time! The first thing on our list is to
check if our `Starship` already has a ticket for the droid in question. To
make this happen, we need to switch the property to use the `getDroids()`
method. But wait, `$this->getDroids()->add()` isn't going to cut it.
Instead, we're going to roll up our sleeves and create the join entity
right here. 

```terminal
StarshipDroid = new StarshipDroid();
StarshipDroid->setDroid(droid); StarshipDroid->setStarship(this);
```

Now we need to get our `starships` and `StarshipDroid` on board this
object. So, `$this->starshipDroids->add(starshipDroid)`, because this is the
captain of the ship, the owning side of the relationship. We need to make
sure that this is set. Let's give the fixtures another whirl.

## The Doctrine of Errors

Ah ha, a new error. This one's a frequent flyer in Doctrine: 'A new entity
was found through the relationship `App\Entity\Starship#starshipDroids`
that was not configured to cascade persist for the entity `StarshipDroid`'.
This is just a fancy way of saying we've created a new `StarshipDroid`
object and told Doctrine to keep this `Starship` in its records. But we
forgot to mention the `StarshipDroid`. 

Here's the rub: we don't have access to the entity manager. So we can't
just tell it `entityManager->persist(starshipDroid)`. Instead, we're going
to lean on something called `cascade={"persist"}`, which I'll dive into in
just a moment. Hang tight, we're about to make the Kessel Run in less than
twelve parsecs!