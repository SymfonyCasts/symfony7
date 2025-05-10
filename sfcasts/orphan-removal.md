# Orphan Removal

When we've used `make:entity` to add a relation, it's asked us about
`orphanRemoval`. Time to find out what that is & when to use it.

In the fixtures, start with `$starshipPart = StarshipPartFactory::createOne()`.
To make it stand out,
I'll make this a *crucial* item for any space voyage: "Toilet
Paper." Yes, a cheeky nod to the pandemic times.

Assign this part to the `Starship` above (add the missing `$ship =`)
`ship`, then dump `$starshipPart`.

So far, so good: nothing fancy. Try reloading our fixtures:

```terminal-silent
symfony console doctrine:fixtures:load
```
No errors, and for the first time, we see that proxy object I've been mentioning.

## Unveiling the Proxy Object

When you create an object via Foundry, it hands you back your shiny new
object, but it's bundled up inside *another object this called a proxy.
Most of the time: you don't notice or care: all method calls on the proxy
are *forwarded* to the real object.

But because I want to make things crystal clear, extract the real object from both
`$ship` and `$starshipPart` using `_real()`.

Run the fixtures again:

```terminal-silent
symfony console doctrine:fixtures:load
```

And... all smooth. Without the proxy, we can see that the `StarshipPart` is
*indeed* tied to the correct `Starship` - the USS Espresso—which we created
earlier. So far, it's all systems go!

## Deleting a Starship Part: The Plot Thickens

But what if we wish to delete a `StarshipPart`? Normally, it's a cakewalk.
We'd say `$manager->remove($starshipPart)`, then `manager->flush()`. But let's
mix things up: let's simply *remove* the part from its ship:
`$ship->removePart($starshipPart)`.

What do you think will happen? Will it delete the part? Or just remove it from
the ship? In that case, the part would be an orphan! Try the fixtures:

```terminal-silent
symfony console doctrine:fixtures:load
```

It blows up with our favorite error: `starship_id` cannot be null. 

## Fixing the Null Error

Why did this happen? When we call `removePart()`, it sets the `Starship` to
null. *But* we made that not allowed with `nullable: false`: every
part *must* belong to a ship. The solution depends on what you want:
do we want to allow parts to become orphaned? Cool! Change `nullable` to true
in `StarshipPart` and make a migration.

Or maybe if a part is suddenly removed from its ship, we might want to delete
that part entirely from the database. Maybe the ship owner isn't a big fan of
recycling. To do this, head to `Starship` and add `orphanRemoval: true` to the `OneToMany`.

Let's whirl back, reload the fixtures:

```terminal-silent
symfony console doctrine:fixtures:load
```

No errors in sight! The ID is null because it was entirely deleted
from the database. So `orphanRemoval` means:

> Hey, if any of these parts become orphaned toss them into the
> incinerator.

Next up: we'll explore a way to control the *order* of a relation,
like making `$ship->getParts()` return alphabetically.
