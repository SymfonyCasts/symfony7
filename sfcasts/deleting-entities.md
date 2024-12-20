# Black Hole: Deleting Entities

Oh-oh, we've just gotten word that this ship, the USS Leafy Cruiser, has fallen
into a black hole. Luckily, no one was on board, but this ship is now spaghettified.
Since it no longer exists in this reality, we need to remove it from our database.

We'll create a command to handle this. At your terminal, run:

```terminal
symfony console make:command
```

For the name, use `app:ship:remove`. This created a new command class.

In your IDE, open `src/Command/ShipRemoveCommand.php`. The maker added some
boilerplate code for us. First, update the description to `Delete a starship`.
In the constructor, we need to inject two things: `private ShipRepository $shipRepo`
and `private EntityManagerInterface $em`. Whenever you need to _find_ or _fetch_ entities,
use the repository. When you need to _manage_ entities, like persisting, updating,
or deleting, use the entity manager, or "EM" for short.

In the `configure()` method, remove this `addOption()` call. For `addArgument()`,
change the name to `slug`, set `InputArgument::REQUIRED`, and update the description
to `The slug of the starship`.

Down in `execute()`, replace this `$arg1 =` with `$slug = $input->getArgument('slug')`.

Now, we need to find the ship by this slug. Every entity repository has the perfect
method for this. Write `$ship = $this->shipRepo->findOneBy()`. Inside, pass an array
where the key is the property to search on and the value is the value to search that field
for: `['slug' => $slug]`. When using these out-of-the-box find methods, Doctrine _automatically_
escapes the values, so you don't need to worry about SQL injection attacks.

Adjust this `if` statement to `if (!$ship)`. `findOneBy()` returns `null` if an entity
wasn't found. Inside, write `$io->error('Starship not found.')` and return `Command::FAILURE`.

Write a comment to let the user know we're about to remove the starship.
`$io->comment(sprintf('Removing starship %s', $ship->getName()))`.

Remove this extra boilerplate and write `$this->em->remove($ship)`. Like with `persist()`,
`remove()` doesn't actually delete the entity directly, it adds it to a queue of entities
that will be deleted when we call `$this->em->flush()`.

Add a success message with `$io->success('Starship removed.')`. Command done!

Jump back to our app and refresh the page to make doubly sure the ship is still there.
Now, copy the slug from the URL. Back in your terminal, run:

```terminal
symfony console app:ship:remove
```

Paste the copied slug and execute. Success! Starship removed. Run the same command
again.

```terminal
symfony console app:ship:remove leafy-cruiser-ncc-0001
```

"Starship not found." Perfect! Back in the app, refresh the page. 404. The ship is gone
from the database!

We've seen _persisting_ and _removing_ entities. Next, we'll see how to _update_ the starship
entity.
