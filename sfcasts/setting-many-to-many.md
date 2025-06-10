# Setting Many To Many Relations

Alright, let's dive into the final part of `ManyToMany`. In one
corner, we have the `Starship` entity, which is linked via a
`ManyToMany` relationship with the `Droid` entity. This
relationship gives us an extra table called a "join table"
keeping track of which droids have hitched a ride on which starships. But
how do we assign a `Droid` to a `Starship`? Jump into our `AppFixtures`.

## Adding some droids

First, let's toss a few droids into the mix. I'll add code that
constructs three droids. Import the class with a quick
option or alt + enter and... we have droids! Nothing fancy though: creating a new
`Droid`, setting the required properties, persisting and flushing. 

## Assigning Droids to Starships

Now, let's get to the fun part: assigning a `Droid` to a `Starship`. Create
a `Starship` variable and get ready for the magic. The way to
relate these two entities is surprisingly simple, and it's going to feel
like a déjà vu from our `OneToMany` relationship. I bet you can even
guess!

Before the `flush()` add: `$starship->addDroid($droid1)`.
Easy, right? Do the same for the other two droids — `$tarship->addDroid($droid2)` and
`$starship->addDroid($droid3)`.

We've done it! The crew is ready for their droid pancakes, so let's try this!

```terminal
symfony console doctrine:fixtures:load
```

Cool, no errors. To see if it's really working, run:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

As expected, we have three rows, one for each droid we created.
Now, peek at that join table, `starship_droid`. Woot! Three rows, one for
each droid assignment.

## The Magic of Doctrine

The real magic is that with `Doctrine`, all we need to worry about is
relating a `Droid` object to a `Starship` object. Doctrine takes care of the rest,
handling the adding *and* deletion of rows in the join table. 

After the flush, we know we have three rows in the join table.
*Now*, after the flush, removing an assignment:
`$starship->removeDroid($droid1)`. 

Reload the fixtures and check out our join table.

```terminal-silent
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

Only two rows remain! Doctrine *removed* the row for our removed droid. 

## Owning vs Inverse Sides

One final touch on `ManyToMany` — remember when we discussed owning
versus inverse sides of a relationship? As we saw, our methods synchronize
the other side of the relationship, adding the `Droid` when we call
`addDroid()`. So the owning side doesn't matter much.

But which side *is* the owning side? In a `ManyToMany`, either side
*could* be the owning side. 

To figure out who's the boss, look at the `inverseBy`
option. It says `ManyToMany` and `inverseBy: starships`, which means
that the `Droid.starships` property is the *inverse* side. 

Now, this is mostly trivia, but if you're a control freak and want to
dictate the name of the join table, you can add a `JoinTable` attribute.
But remember, it has to go on the owning side. Other than that, don't sweat
it: no big deal.

Next, let's *use* the new relationship to render the droids assigned to each ship.
