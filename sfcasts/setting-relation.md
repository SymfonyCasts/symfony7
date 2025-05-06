# Setting the Relation

Ok, but how do we actually set the relation? How do we say "this
`StarshipPart` belongs to this `Starship`"? So far, we've been crafting all
in `AppFixtures` with Foundry. We're going to return to Foundry in a
bit, but let's go old school for a moment to see how this all works.

Start with `new Starship()`, then I'll paste in some code to set all the
required properties. Because we're in manual mode call
`$manager->persist($starship)`.
Now create a new `StarshipPart` object and just like before,
I'll paste some code to fill in the properties. Then make sure this *saves*
with `$manager->persist($part)`, and
finally, `$manager->flush()`. Foundry
usually calls `persist()` and `flush()` for us. But since we're in manual
mode, we need to do it ourselves.

We have a `Starship` and a `StarshipPart`, but they're not related yet.
Let's try to load the fixtures anyway. Head
over to your terminal and run:

```terminal
symfony console doctrine:fixtures:load
```

Boom! We've got a problem! `starship_id` cannot be null on the
`starship_part` table. Why is that column required? In `StarshipPart` the
`starship` property has a `ManyToOne` *and* a `JoinColumn` attribute. This lets
us control the foreign key column: `nullable: false` means that every `StarshipPart`
*must* belong to a `Starship`.

## Assigning the Part to the Starship

So how do we say that this part belongs to this Starship?
The answer is beautifully simple. Anywhere before `flush()`, say 
`$part->setStarship($starship)`. That's it. The magic of doctrine
relations means we're not setting some `starship_id` property or even
passing an ID, like `$starship->getId()`. Nope! We set *objects*. Doctrine
handles the boring details of inserting this:vfirst saving the `Starship` object,
then using the new `id` to set
the `starship_id` column on the `starship_part` table. 

Try the fixtures again:

```terminal
symfony console doctrine:fixtures:load
```

We're error-free! To check things out, run:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
```

Et voila! There's our single part,
happily linked to `Starship` id 75. Let's look it up. 

```terminal
symfony console doctrine:query:sql 'SELECT * FROM starship WHERE id = 75'
```
There it is: `Starship` id 75 has a `StarshipPart` with id 1.

## Doctrine: work with Objects, Not IDs

Here's the takeaway: when you're working with Doctrine relationships,
you're in the world of objects. Forget about IDs. Doctrine takes care of that
part for you. You set the object, and Doctrine does the rest.

But ugh, this is a lot of work in `AppFixtures` to create a single
`Starship` and a single `StarshipPart`. So next, let's bring Foundry
back to create a fleet of ships and parts and link them all
in one fell swoop. This is where Foundry really shines.
