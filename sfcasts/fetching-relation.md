# Fetching a Relation's Data

Navigate to our homepage and click on any of the starships
showcasing an 'In Progress' status. These are the ships from our
fixtures. 

You might notice that we're
already listing the parts. But don't let that fool you: these
parts are all hard-coded.

Now, how *do* we get the parts that are related to this ship?

Open the controller for this page:
`src/Controller/StarshipController.php`

## Say Hello to Auto-wiring

To query for parts, we typically by autowire the `StarshipPartRepository`.
Start the same way here with a `StarshipPartRepository $partRepository`
argument.

Next, set `$parts`, to
`$partRepository->findBy()`. 

This is pretty standard stuff: if you want to
query where some property equals a value, you  use `findBy()` and pass
the property name and the value. When it
comes to relationships, it's the same darn thing.
`$parts = $partRepository->findBy(['starship' => $ship])`.

And no, we're not doing `Starship ID` or anything of the sort. We're
keeping IDs out of this. Instead, we pass the `Starship` object itself.
going to pass the entire `ship` object. You *could* actually just
pass the `id` if you're feeling lazy, but in the spirit of doctrine, relationships, and
thinking about objects, passing the entire `Starship` object is the way to go.

## Debugging and Celebrating

Let's debug and see what we've got. `dd($parts)`.

Refresh, and voila! An array of 10 `StarshipPart` objects,
all related to this `Starship`. Pretty awesome, right? If you think so,
hold onto your pants.

Replace `$parts =` with `ship->getParts()`. 
Refresh! Instead of an array of `StarshipPart` objects, we get a
`PersistentCollection`. *object* that looks... empty
of an array of `StarshipPart` objects, we get a `PersistentCollection`.
Remember the `ArrayCollection` that `make:entity` added to our
`Starship` constructor? `PersistentCollection` and `ArrayCollection` are
all part of the same collection family. They're objects but they
act like arrays. Ok, but why does this collection look empty?
That's because Doctrine is smart: It doesn't query for the parts until we
need them. Loop over `$ship->getParts()` and dump `$part`


Suddenly that empty-looking collection is full of the 10 `StarshipPart` objects. 
Magic!

## Lazy Relation Queries

There are two queries at play here. The first one is for the `Starship`,
and the second one is for all the `StarshipPart`s. The first comes from
Symfony querying for the `Starship` based on the slug. The second
happens the moment we `foreach` over the `parts`. At *that* moment, Doctrine
says:

> I just remembered: I don't actually have the `StarshipPart`s data for this
> `Starship`. Let me go and get that for you.

Isn't that just amazing? Makes me want to throw a party for Doctrine. 

## Tidying Up and Looping Over Parts

Get rid of the `parts` variable entirely... and remove `StarshipPartRepository`: 
that was way too much work. Instead, set a `parts` variable to `$ship->getParts()`.

Now that we've got our shiny new `parts` variable, Loop over *that* in
the template. Open up `templates/starship/show.html.twig` and replace the
hard-coded part with our loop: `for part in parts %`, `part.name`, `part.price`,
`part.notes`, `endfor`

## Still too much Work?

And we've done it! We've managed to display all 10 of our related
parts, without doing any serious work thanks to `$ship->getParts()`.

But you know what? Even this is too much work. Get rid of the `parts`
variable entirely: `for part in ship.parts`.

And... *still* not broken! For kicks and giggles, let's also display the number 
of for this ship. `ship.parts|length`

We still have two queries, but Doctrine is smart. It knows we've already
queried for all the `StarshipPart`s, so when we count them, we don't need
to make another count query.

Next up: we'll talk about an often-misunderstood topic in Doctrine relations:
the owning vs inverse side of each relationship.
