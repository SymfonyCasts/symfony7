# Quantum Refactor: Rich Entities

Take a look at our `Starship` entity. It's a bunch of properties and getters and setters.
Kind of boring, right? It doesn't have to be! Since entities are standard PHP classes,
we can add meaningful, explicit methods that describe our business logic, like
`goToWarp(7)` or `enterOrbitAround($millersPlanet)`. These are called _rich entity methods_. 

Let's try this out and explore the benefits.

## Reduce Duplication

Our `Starship` check-in logic currently lives in the `ShipCheckInCommand::execute()` method.
After we fetch the ship, we update its `arrivedAt` and `status`.
What if, in the future, we add a check-in controller. We'd have to duplicate this logic
there. And if the logic for "checking in" changes - like we need to update another field, we'd have
to remember to change it in multiple places. That is super *not* sci-fi.

## Adding a `Starship::checkIn()` Method

The better way is to move, or _encapsulate_, this check-in logic into a method on the
entity. Open `src/Entity/Starship.php` and scroll to the bottom. Create a
new: `public function checkIn()`. Have it accept an optional
`?\DateTimeImmutable $arrivedAt = null` and return `static`, which is a fancy way of saying
"return the current object":

[[[ code('789c20c081') ]]]

`return $this`:

[[[ code('0763caad07') ]]]

Above, add the check-in logic: `$this->arrivedAt = $arrivedAt`, and
if it wasn't passed, `?? new \DateTimeImmutable('now')`.
Next, `$this->status = StarshipStatusEnum::WAITING`:

[[[ code('76a6069436') ]]]

## Using the `Starship::checkIn()` Method

Jump back to the `ShipCheckInCommand` and replace the logic with `$ship->checkIn()`:

[[[ code('9eca260808') ]]]

Wow, that's clear! The command now reads like a story: "Find the ship, then check it in".

To make sure it still works, head back to the homepage and refresh. Find a ship that's
not "waiting"... Here we go: "Stellar Pirate". Click that and copy the slug from the
URL. Back at your terminal, run:

```terminal
symfony console app:ship:check-in
```

paste the slug, and execute! Success! Back in the app, refresh. Perfect! The ship is
now marked as "waiting" and arrived 6 seconds ago.

If you find yourself repeating common operations on your entities, consider adding, then using,
a method that describes the work being done. It's an easy win for readability and maintainability.

Ok crew, that's it for this Doctrine Fundamentals course! If you're looking to upgrade your
Doctrine skills, [search for "Doctrine" on SymfonyCasts](https://symfonycasts.com/search?q=doctrine)
to find more advanced courses. The [Doctrine documentation](https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/index.html)
is a great resource too.
And as always, if you have any questions, we're here for you down in the comments.

'Til next time, happy coding!
