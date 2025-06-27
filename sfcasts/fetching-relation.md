# Fetching a Relation's Data

Navigate to the homepage and click on any of the starships
with an 'In Progress' status.

Hey! We're already listing the parts... sort of... these
are all hard-coded!

Now, how *do* we get the parts that are related to this ship?

Open the controller for this page: `src/Controller/StarshipController.php`

## Querying for Related Parts Like any Other Property

To query for parts, we would typically autowire the `StarshipPartRepository`.
Start the same way here with a `StarshipPartRepository $partRepository`
argument:

[[[ code('9e7bd2a0c6') ]]]

Next, set `$parts`, to `$partRepository->findBy()`:

This is pretty standard stuff: if you want to query where some property equals
a value, use `findBy()` and pass the property name and the value. When it
comes to relationships, it's the same darn thing!

`$parts = $partRepository->findBy(['starship' => $ship])`.

And no, we're not doing `Starship ID` or anything of the sort. Keep IDs out of
this! Instead, pass the `Starship` object itself. You *could* pass the `id` if
you're feeling lazy, but in the spirit of Doctrine, relationships, and
thinking about objects, passing the entire `Starship` object is the way to go.

Let's debug and see what we've got: `dd($parts)`:

[[[ code('d958a46207') ]]]

Refresh, and voila! An array of 10 `StarshipPart` objects,
all related to this `Starship`. Pretty awesome, right? If you think so,
hold onto your pants.

## The Grabbing the Related Parts the Easy Way

Replace `$parts` with `$ship->getParts()`:

[[[ code('f9a8dc8c59') ]]]

Refresh! Instead of an array of `StarshipPart` objects, we get a
`PersistentCollection` *object* that looks... empty.
Remember the `ArrayCollection` that `make:entity` added to our
`Starship` constructor? `PersistentCollection` and `ArrayCollection` are
part of the same collection family. They're objects but they
act like arrays. Cool... but why does this collection look empty?
It's because Doctrine is smart: it doesn't query for the parts until we
need them. Loop over `$ship->getParts()` and dump `$part`:

[[[ code('c18659391b') ]]]

Suddenly that empty-looking collection is full of the 10 `StarshipPart` objects. 
Magic!

## Lazy Relation Queries

There are two queries at play here. The first one is for the `Starship`,
and the second one is for all its `StarshipPart`s. The first comes from
Symfony querying for the `Starship` based on the slug. The second is more
interesting: happens the moment we `foreach` over the `parts`. At *that*
exact instance Doctrine says:

> I just remembered: I don't actually have the `StarshipPart`s data for this
> `Starship`. Let me go and get that.

Isn't that just amazing? Makes me want to throw a party for Doctrine. 

## Tidying Up and Looping Over Parts

Get rid of the `parts` variable entirely... and remove `StarshipPartRepository`: 
that was way too much work. Instead, set a `parts` variable to `$ship->getParts()`:

[[[ code('010ec22e30') ]]]

Now that we've got our shiny new `parts` variable, Loop over *that* in
the template. Open up `templates/starship/show.html.twig` and replace the
hard-coded part with our loop: `for part in parts`, `part.name`, `part.price`,
`part.notes`, `endfor`:

[[[ code('f7d3f1ce87') ]]]

## Still too much Work?

And we've done it! We've managed to display all 10 related
parts, without doing any serious work thanks to `$ship->getParts()`.

But you know what? Even this is too much work. Get rid of the `parts`
variable entirely:

[[[ code('240c2f7d37') ]]]

`for part in ship.parts`:

[[[ code('45d580cbe9') ]]]

And... *still* not broken! For kicks and giggles, let's also display the number 
of parts for this ship. `ship.parts|length`:

[[[ code('1249479e09') ]]]

We still have two queries, but Doctrine, *again* is smart. It knows we've already
queried for all the `StarshipPart`s, so when we count them, we don't actually need
to make another count query.

Next up: we'll talk about an often-misunderstood topic in Doctrine relations:
the owning vs inverse side of each relationship.
