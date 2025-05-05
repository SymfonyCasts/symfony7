# Setting the Relation

So, you're probably scratching your head and wondering, "How in the world
does a particular `StarshipPart` know it belongs to a specific `Starship`?"
So far, we've been crafting all of our data in the cozy confines of our
`AppFixtures` — a nifty little tool that lets us play with objects
directly, and boy, it's been fun! But for now, we're going to ditch Foundry
and create some objects manually. Why? Just to make sure everything's
crystal clear. So, let's plunge in!

Down here, I'm going for `Starship`, but hold your horses! We're not
calling Foundry here. We're going old school with a classic `new
Starship()`. How retro is that? Now, to save us some time (because who
doesn't love a good time-saver?), I'm going to paste some code right below
that simply sets all the necessary properties on `Starship`. 

Now, remember, because we're handcrafting these objects (like artisanal
developers) and not using Foundry, we need to remember our manners and say
`manager->persist($starship)`. Cool, huh? Right below this, let's give
birth to a `Part` with `new StarshipPart()`. Perfect! And just as before,
I'll grab some code to fill in all the properties. Paste that in, and
voila! Ready to roll.

Let's wrap this up down here with our `manager->persist($part)`, and
finally, let's not forget our `manager->flush()`. The reason we're
painstakingly calling `persist()` and `flush()` here is that Foundry
usually does this heavy lifting for us. But since we're rolling up our
sleeves and getting our hands dirty, we're also going to call `persist()`
and `flush()` manually. 

Phew! So we've created a dashing new `Starship` and a shiny `StarshipPart`.
They're not dating yet, but let's try to load the fixtures anyway. Head
over to your terminal, flex those fingers, and run:

```terminal
symfony console doctrine:fixtures:load
```

Boom! We've got a problem! `starship_id` cannot be null on the
`starship_part` table. When we ran `make:entity`, we insisted that every
ship needed a part. You can see that in `StarshipPart` where we've got a
`ManyToOne` and a `JoinColumn`. This lets us control the foreign key column
in the database, and since we've set `nullable=false`, every `StarshipPart`
must have a `Starship`. Just as we want it.

## The Simple Magic of Doctrine Relations

So, how do we play matchmaker and say this part belongs to this Starship?
The answer is beautifully simple. Anywhere before flush, we're going to
whisper `part->setStarship($starship)`. That's it. The magic of doctrine
relations means we're not setting some `starship_id` property or even
passing an ID, like `Starship->getId()`. Doctrine smartly saves this
relationship, first saving the `Starship` object, then using its ID to set
the `starship_id` column on the `StarshipPart` table. 

Now, let's only create `StarshipPart`s manually to keep everything as clear
as a starry night sky. Ready for another spin? Let's reload those fixtures.
This time, we're error-free! To show off our achievement, let's run:

```terminal
symfony console doctrine:query:sql
```

We'll query `SELECT * FROM starship_part`. Voila! There's our single part,
happily linked to `Starship` ID 75. Let's look it up. We'll query `SELECT *
FROM starship WHERE id = 75`. Perfect. There's our `Starship` that we
created earlier. You can see they're now happily related in the database. 

## The Doctrine Magic: Working with Objects, Not IDs

Here's the big reveal: when you're working with Doctrine relationships,
you're in the magical world of objects. You're setting objects, linking
objects. Forget about IDs. Doctrine takes care of the tedious task of
saving those objects and their relationships in the database. 

The only hiccup I see is that we're sweating a bit too much here just to
create one `Starship` and one part and link them. Next, let's bring Foundry
back into the game to create a fleet of ships and parts and link them all
in one fell swoop. Trust me, Foundry really shines in these situations.
