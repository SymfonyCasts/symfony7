# Ship Upgrades: Updating an Entity

Our repair service is doing well! We have some repeat customers who want some after
market upgrades. We need a way to _check in_ an existing starship whose status
is _completed_.

This list on our homepage only lists _incomplete_ starships, so we need to find a
completed one. In your terminal, run:

```terminal
symfony console doctrine:query:sql 'SELECT slug, status FROM starship'
```

`lunar-marauder-1` is a completed ship. Copy the slug, and back in the app,
visit `/starships/lunar-marauder-1`. Got it. To better see the update, let's first
show the `arrivedAt` date on the show page. In `templates/starship/show.html.twig`,
copy these `h4` and `p` tags. Paste them below. Update the `h4` content to `Arrived At`
and the `p` to `{{ ship.arrivedAt|ago }}`.

Back to the app, refresh, and there we go! We can see this ship is completed and
arrived 1 month ago.

To check in a ship, we'll create a new command. In your terminal, run:

```terminal
symfony console make:command
```

For the name, use `app:ship:check-in`. This created a new command class. In your IDE,
open `src/Command/ShipCheckInCommand.php`. Update the description to `Check-in ship`.
For the constructor, we need the same things as our remove command. Open `ShipRemoveCommand`,
copy the constructor, and paste it over `ShipCheckInCommand::__construct()`. We'll also
find the ship by slug, so copy the `configure()` method from `ShipRemoveCommand` and
paste it too.

The first part of `execute()`, finding the ship by slug, is also the same as `ShipRemoveCommand`.
Copy that and paste. Update the IO comment to "Checking in starship...".

Time for the actual "check in" logic. First, update the arrived at date to the current
time with `$ship->setArrivedAt(new \DateTimeImmutable('now'))`. Next, set the status to
"waiting" with `$ship->setStatus(StarshipStatusEnum::WAITING)`. These fields have been
updated, so, below, call `$this->em->flush()` to save the changes to the database.

Wait, wait, wait! When we persist or remove an entity, we had to call a method on
the entity manager to let Doctrine know our intention. Here, we don't? Nope! Doctrine
is super smart. Above, when we found the entity, Doctrine started tracking it. When
we call `flush()`, it sees that it's been modified and determines the best SQL to
update the database. So awesome!

Finally, add a success message for the command: "Starship checked-in".

Back in our app, this is the ship we want to check in. Copy the slug from the url.
In your terminal, run our new command with:

```terminal
symfony console app:ship:check-in
```

paste the slug and execute! Success! Back in the app, refresh the page. The ship is
now marked as "waiting" and arrived 9 seconds ago. It worked!

Jump back to the check-in logic inside `ShipCheckInCommand`. We're calling setters
to update two fields. Next, let's _encapsulate_ this logic into a method on the
`Starship` entity.

All right, our repair service has been successful and we have some repeat customers.
So these are completed ships that have that have finished their repairs or upgrades
and now want to come back. So what we need is a way to check in an existing completed
ship. So what that will do is update the arrived at and the status from completed to
waiting. So the first thing we can do, the first thing we should do is let's find a
completed ship. You'll remember we don't list the completed ships here. We only list
the in progress and waiting ships in the queue. So we will jump over to our terminal
and run a query to find completed ships. So let's just do this. Symfony doctrine
query sql select slug status from starship. Symfony console doctrine query sql select
slug status from starship. Okay, so let's grab this lunar marauder-1. Let's grab this
slug and copy it because it's a completed ship. And then back in our browser, we'll
do starship /lunar marauder-1 to get its show page. Do starships lunar marauder-1 to
get its show page. Perfect. To better see the update happen, I'd like us to show the
arrived at date down here on the show page. So let's quickly add that. So if we go
back to our app and in templates, starship show, at the very bottom here where we're
writing these details, copy these two lines, this h4 and p tag, and then down below
paste. And we will update the ship status to arrived at. And the data below, the data
for this will be ship.arrived at ago to show that pretty date. Now let's refresh.
Okay, perfect. So we can see this ship is completed and it arrived one month ago. So
to perform the check-in, we're going to create another command. So in your terminal,
Symfony console make command. And we'll call this app ship check-in. Okay, and we can
see it created it at source command ship check-in command. So back in our IDE, let's
find that source command ship check-in command. Perfect. So we already have the name
here for the description. Call it check-in in ship. For the constructor, it's going
to be the same. We need the star ship repository and the entity manager interface. So
we can copy that from the command we created in the last chapter and copy the whole
constructor. And then here, paste and import these. Perfect. And we're going to check
in by slug, just like we did in the ship remove command. So we can also copy the
configure method and paste it over top of the one from the maker. And now for
execute, it's actually the first part of this command is going to be quite is going
to be the same. We're going to grab the slug from the argument. We're going to throw
an error if the ship wasn't found. We're going to still have a comment here. So let's
just copy that and then we'll adjust it. So grab all this and copy. And then in the
ship check-in, we will paste here. Okay. So same thing. We're trying to find the
ship. If it's not found, we throw an error. And instead of removing star ship, we'll
call it checking in star ship. Okay. Now what we need is the logic. So like we said,
the check-in step, the check-in logic is going to be taking the ship and set its
arrive that to new date time immutable. Now the current date. And then we're going to
do also up the ship set status to star ship status enum waiting, because that's the
first status when a ship is checked in, it's just marked as waiting. And for the
success. Okay. So these fields have been updated on our star ship entity, but they
haven't yet been saved to the database. So how can we do that? You maybe guessed it.
This em flush. So wait a second here. In the persistent remove commands, we had to
call specific methods on the entity manager persist and remove, but we didn't have to
here. But we don't have to here. This is because whenever we change an entity, it's
actually stored in memory and tracked by doctrine. And it tracks all the changes that
are made in your code to the entity. So when we call flush, it will detect that this
ship has been changed, and it will automatically create the queries to save that
change to the database. Pretty cool. So now for our success message at the end,
starship checked in. Perfect. Okay, so command done. So let's we want to check in
this ship. So we'll copy the slug like we did before. And back in our terminal, we'll
run our command Symfony console app ship check in lunar marauder one. All right,
starship checked in. And if we go back and refresh the page, we should see some
updates here. And we do. It's now marked as waiting and arrived at nine seconds ago.
If we go back to so the our up our check in command is working as expected. If we go
back to our code for the check in command, you can see the logic that we have this,
the logic that we've created to in to execute a check in, we can see we're changing
some fields using the setters on on the ship. I think it would be better to
encapsulate this into a specific method on the entity to make it more descriptive and
explicit as to what we're doing. Additionally. So let's, let's, let's do that next.
