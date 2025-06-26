# Setting the Relation

Ok, but how do we actually set the relation? How do we say:

> This `StarshipPart` belongs to this `Starship`?

So far, we've been working in `AppFixtures` with Foundry. We'll come back
to Foundry in a bit, but let's go old school for a minute to see how this all works.

Start with `new Starship()`... then I'll paste in some code to set the
required properties. Then add `$manager->persist($starship)`:

[[[ code('b1bcf47f80') ]]]

Next create a new `StarshipPart` and just like before, I'll paste code to fill in
the properties. Then make sure this *saves* with `$manager->persist($part)`, and
finally, `$manager->flush()`:

[[[ code('3f9ebee20f') ]]]

Foundry usually calls `persist()` and `flush()` for us. But since we're in manual
mode, we need to do it ourselves.

We have a `Starship` and a `StarshipPart`, but they're not related yet.
Pff, try to load the fixtures anyway. Head over to your terminal and run:

```terminal
symfony console doctrine:fixtures:load
```

Rutro:

> `starship_id` cannot be null on the `starship_part` table.

Why is that column required? In `StarshipPart`, the `starship` property has a `ManyToOne`
*and* a `JoinColumn()` attribute:

[[[ code('fa467d8ded') ]]]

This lets us control the foreign key column: `nullable: false` means that every
`StarshipPart` *must* belong to a `Starship`.

## Assigning the Part to the Starship

So how *do* we say that this part belongs to this `Starship`?
The answer is beautifully simple. Anywhere before `flush()`, say 
`$part->setStarship($starship)`:

[[[ code('e528363598') ]]]

That's it. With Doctrine, we're not setting some `starship_id` property or even
passing an ID, like `$starship->getId()`. Nope! We set *objects*. Doctrine
handles the boring details of inserting this: first saving the `Starship`,
then using its new `id` to set the `starship_id` column on the `starship_part` table. 

Smart!

Try the fixtures again:

```terminal-silent
symfony console doctrine:fixtures:load
```

Error-free! Check things out:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
```

Et voila! There's our single part, happily linked to `starship_id` 75. Look that up:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship WHERE id = 75'
```

There it is: `Starship` id 75 has a `StarshipPart` id 1. We're awesome!

## Doctrine: work with Objects, Not IDs

Here's the takeaway: with Doctrine relations, you're in the world of objects.
Forget about IDs. Doctrine takes care of that part for you. You set the object,
and Doctrine does the rest.

But ugh, this is a lot of work in `AppFixtures` to create a single
`Starship` and a single `StarshipPart`. So next, let's bring Foundry
back to create a *fleet* of ships and a *pile* of parts *and* link them all
in one fell swoop. This is where Foundry really shines.
