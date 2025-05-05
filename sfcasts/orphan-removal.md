# Orphan Removal

Coming soon...

## Introduction: Going Galactic with Symfony

Let's embark on a bit of an interstellar adventure with Symfony. We're
tackling a fun, yet common scenario within the realm of fixtures. Let's
dive right in. We'll be creating a new `StarshipPart` using the line
`$starshipPart = StarshipPartFactory::createOne()`. To make it stand out,
I'll christen it with a name that's crucial for any space voyage: "Toilet
Paper." Yes, you heard me right! A cheeky nod to our pandemic times.

Now, I'll assign this part to the `Starship` above. Let's set that to
`ship` (I overlooked this assignment earlier, my bad!). You might say,
"Houston, we have a solution!" because we'll ensure this part is tied to
that specific `Starship`. 

```terminal
ship = StarshipFactory::createOne
```

After that, let's dump our `StarshipPart`. So far, so good. Nothing fancy,
right? But hold on to your space helmets. Let's try reloading our fixtures.
There are no errors, and voila! For the first time, we're introduced to
that proxy object I've been teasing you about. 

## Unveiling the Proxy Object

When you create an object via Foundry, it hands you back your shiny new
object, but it's bundled up in this thingamabob called a proxy. Most of the
time, it's not a big deal. But because I want to make things crystal clear,
we'll extract the real object from both `ship` and `StarshipPart` using
`_real`. 

```terminal
_real
```

Let's run the fixtures again, and they're operating smoothly. This time
without the proxy, we can confirm that our `StarshipPart` is indeed tied to
the correct `Starship`, the USS Espresso, which we created earlier. So far,
it's all systems go!

## Deleting a Starship Part: The Plot Thickens

But what if we wish to delete a `StarshipPart`? Normally, it's a cakewalk.
We'd say `manager->remove($starshipPart)`, then `manager->flush()` to save
that to the database. But let's stir things up. What if we want to remove
this part from the ship? In this case, we'd use
`ship->removePart($starshipPart)`. 

```terminal
ship->removePart($starshipPart)
```

Let's find out what happens when we reload the fixtures. Boom! It blows up
with our favorite error that `starship_id` cannot be null. 

## Fixing the Null Error

Why did this happen? When we call `removePart()`, it sets the `Starship` to
null, but we've forbidden that. So, how do we fix this? In some scenarios,
you might want to allow parts to become orphaned when removed from the
ship. This change requires setting `nullable` to true in `StarshipPart`,
generating a migration, and then running it. 

Alternatively, if a part should always belong to a ship and is suddenly
removed, we might want to obliterate that part entirely. To do this, head
to `Starship` and add `orphanRemoval: true` to the `OneToMany`. 

```terminal
orphanRemoval: true
```

Let's whirl back, reload the fixtures, and voila! No errors in sight.
Notice how the ID of our part is now null because it was entirely ejected
from the database. In other words, `orphanRemoval` essentially states:
"Hey, if any of these parts become orphaned, go ahead and toss them into
the cosmic void of the database." 

And there you have it, a nifty trick to have in your developer's toolkit,
just like a Swiss army knife in an astronaut's pocket. Next, we'll venture
into another fascinating topic. So, stay tuned, space cadets!