# Ship Upgrades: Updating an Entity

Our starship repair scheme, ahem, business is doing well! We now have some repeat customers who want some after
market upgrades. We need a way to _check in_ an existing starship whose status
is _completed_.

## Finding a Completed Ship

This list on our homepage only lists _incomplete_ starships, so we need to find a
completed one. In your terminal, run:

```terminal
symfony console doctrine:query:sql 'SELECT slug, status FROM starship'
```

`lunar-marauder-1` is a completed ship. Copy the slug, and back in the app,
visit `/starships/lunar-marauder-1`. Got it. To better see the update, let's
show the `arrivedAt` date on the show page.

In `templates/starship/show.html.twig`, copy these `h4` and `p` tags. Paste them below.
Update the `h4` content to `Arrived At` and the `p` to `{{ ship.arrivedAt|ago }}`:

[[[ code('ee6f31badc') ]]]

Back to the app, refresh, and there we go! This ship is completed and
arrived 1 month ago.

To check in a ship when it arrives, let's create another command.

## `app:ship:check-in` Command

At your terminal, run:

```terminal
symfony console make:command
```

For the name, use `app:ship:check-in`.

## Updating Command Boilerplate

Open the new command class: `src/Command/ShipCheckInCommand.php`. Update the description - `Check-in ship`:

[[[ code('9a2d044273') ]]]

and for the constructor, we need the same things as the remove command. Open that,
copy the constructor, and paste it over `ShipCheckInCommand::__construct()`:

[[[ code('07d2247385') ]]]

We'll also find the ship by slug, so copy the `configure()` method from `ShipRemoveCommand` and
paste it too:

[[[ code('8b0b64623c') ]]]

## Command Logic

The first part of `execute()`, finding the ship by slug, is also the same.
Copy that and paste. Update the IO comment to "Checking in starship...":

[[[ code('c1dd686ed0') ]]]

Time for the actual "check-in" logic. First, update the arrived at date to the current
time with `$ship->setArrivedAt(new \DateTimeImmutable('now'))`. Then set the status to
"waiting" with `$ship->setStatus(StarshipStatusEnum::WAITING)`:

[[[ code('4be4cfbea5') ]]]

These fields have been
updated on the object, but not *yet* in the database. To execute the `UPDATE` query, below,
call, you guessed it, `$this->em->flush()`:

[[[ code('7de5606e17') ]]]

## Just `flush()`?

Wait, wait, wait! When we persist or remove an entity, we had to call a method - like `persist` or `remove` on
the entity manager to let Doctrine know our intention. Here, we don't? Nope! Doctrine
is super smart. Above, when we found the entity, Doctrine started tracking it. When
we call `flush()`, it sees that it's been modified and determines the best SQL to
update the database. So awesome!

Finally, add a success message: "Starship checked-in":

[[[ code('e4b4963df6') ]]]

Back in our app, this is the ship we want to check in. Copy the slug from the url.

## Running the Command

In your terminal, run the new command with:

```terminal
symfony console app:ship:check-in
```

paste the slug and execute! Success! Back in the app, refresh. The ship is
now marked as "waiting" and arrived 9 seconds ago. It worked!

Jump back to the check-in logic inside `ShipCheckInCommand`. We're calling setters
to update two fields. Next, let's _encapsulate_ this logic into a method on the
`Starship` entity.
