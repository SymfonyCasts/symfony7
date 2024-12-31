# Black Hole: Deleting Entities

Oh-oh, we just got word that this ship, the USS Leafy Cruiser, has fallen
into a black hole. Luckily, no long-term, beloved characters were on board, but this ship is now spaghettified.
Since it no longer exists in this reality, we need to remove it from our database.

## `app:ship:remove` Command

Let's create a command to handle this. At your terminal, run:

```terminal
symfony console make:command
```

For the name, use `app:ship:remove`. This created a new command class.

## Command Constructor

Open it! `src/Command/ShipRemoveCommand.php`. The maker added some
boilerplate code for us. Update the description to `Delete a starship`:

[[[ code('0aa83c68dc') ]]]

In the constructor, we need to inject two things: `private ShipRepository $shipRepo` 
and `private EntityManagerInterface $em`:

[[[ code('f4b2e2ec67') ]]]

Whenever you need to _find_ or _fetch_ entities,
use the repository. When you need to _manage_ entities, like persisting, updating,
or deleting, use the entity manager, or "EM" for short.

## Command Configuration

In the `configure()` method, remove `addOption()`. For `addArgument()`,
change the name to `slug`, set `InputArgument::REQUIRED`, and update the description
to `The slug of the starship`:

[[[ code('70a5d454be') ]]]

## Command Logic

Down in `execute()`, replace this `$arg1 =` with `$slug = $input->getArgument('slug')`:

[[[ code('20966f236b') ]]]

Next, we need to find the ship by this slug. Each `EntityRepository` already has the perfect
method for this. Write `$ship = $this->shipRepo->findOneBy()` passing an array
where the key is the property to search on and the value is the value to search
for: `['slug' => $slug]`:

[[[ code('f95b2f3919') ]]]

When using these out-of-the-box find methods, Doctrine _automatically_
escapes the values, so you don't need to worry about SQL injection attacks.

Adjust this `if` statement to `if (!$ship)`. `findOneBy()` returns `null` if an entity
wasn't found. Inside, write `$io->error('Starship not found.')` and return `Command::FAILURE`:

[[[ code('c5330da086') ]]]

Write a comment to let the user know we're about to remove the starship.
`$io->comment(sprintf('Removing starship %s', $ship->getName()))`:

[[[ code('8850664f5f') ]]]

Remove this extra boilerplate and write `$this->em->remove($ship)`. Like with `persist()`,
`remove()` doesn't actually delete the entity directly, it adds it to a queue of entities
that will be deleted when we call `$this->em->flush()`:

[[[ code('8b3a99b7a0') ]]]

Add a success message with `$io->success('Starship removed.')`:

[[[ code('21f4c427aa') ]]]

Command done!

Jump back to our app and refresh the page to make doubly sure the ship is still there.
Now, copy the slug from the URL.

## Running the Command

Back in your terminal, run:

```terminal
symfony console app:ship:remove
```

Paste the copied slug and execute. Success! Starship removed. Run the same command
again.

```terminal-silent
symfony console app:ship:remove leafy-cruiser-ncc-0001
```

"Starship not found." Perfect! Back in the app, refresh the page. 404. The ship is gone
from the database!

We've seen _persisting_ and _removing_ entities. Next, we'll see how to _update_ the starship
entity.
