# Quantum Refactor: Rich Entities

Take a look at our `Starship` entity. It's a bunch of properties and getters and setters.
Kind of boring, right? It doesn't have to be! Since entities are standard PHP classes,
we can add meaningful, explicit methods that describe our business logic.
`goToWarp(7)` or `enterOrbitAround($millersPlanet)`. These are called _rich entity methods_. 

Let's create one!

In the last chapter, we added this ship check-in command. Our check-in logic is here
in the `execute()` method. After we fetch the ship, we update it's `arrivedAt` and `status`.
Imagine, in the future, we add a check-in controller. We'd have to duplicate this logic
there. Further, if this logic changes, like we need to update another field, we'd have
to remember to update it in multiple places. Gross!

The better way is to move, or _encapsulate_, this check-in logic into a method on the
`Starship` entity. Open `src/Entity/Starship.php` and scroll to the bottom. Create a
new method: `public function checkIn()`. Have it accept an optional
`?\DateTimeImmutable $arrivedAt = null` and return `static`. Inside, first,
`return $this`. Above, add our check-in logic: `$this->arrivedAt = $arrivedAt`, and
if it wasn't passed, `?? new \DateTimeImmutable('now')` to default to the current time.
Next, `$this->status = StarshipStatusEnum::WAITING`.

Jump back to the `ShipCheckInCommand` and replace the logic with `$ship->checkIn()`. Nice!

To ensure it still works, go back to the app homepage and refresh. Find a ship that's
not "waiting"... Here we go: "Stellar Pirate". Click that and copy the slug from the
URL. Back in your terminal, run:

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

'Til next time, happy coding!
