# Setting Many To Many

Coming soon...

## Mastering the ManyToMany relationship

Alright, let's dive into the final part of our `ManyToMany` deep sea
adventure. We have our valiant `Starship` entity, which is locked in a
`ManyToMany` relationship with our faithful `Droid` entity. Remember, this
relationship results in a join table, which is like a cosmic directory,
keeping track of which droids have hitched a ride on which starships. But
how do we actually assign a `Droid` to a `Starship`, you ask? Well, let's
jump into our `AppFixtures` spaceship and I'll show you how to do it
manually. 

## Adding some droids

First, let's toss a few droids into the mix. We'll add some code that
injects three droids into our system. Let's import the class with a quick
tap of option enter and voila, we have our droids. Nothing fancy here -
just creating a new `Droid`, setting the required properties, persisting
and finally, flushing. 

## Assigning Droids to Starships

Now, let's get to the fun part: assigning a `Droid` to a `Starship`. Let's
create a `Starship` variable. Now, get ready for the magic. The way to
relate these two entities is surprisingly simple, and it's going to feel
like a déjà vu from our `OneToMany` relationship. I bet you can even
guess. 

Before we flush, let's add our droids to the starship. We'll say
`Starship->addDroid($droid1)`. Easy, right? We'll do the same for the other
two droids — `Starship->addDroid($droid2)` and
`Starship->addDroid($droid3)`. And just like that, we've done it!

Our crew is getting a bit peckish for pancakes, so let's wrap up the
assignment of the `Droid` to the `Starship`. 

## Testing the Fixtures

Let's fire up the fixtures and see if everything's in order. Run:

```terminal
symfony console doctrine:fixtures:load
```

Cool, no errors. But let's confirm everything is as it should be in the
database. We'll run:

```terminal
symfony console doctrine:query:sql 'SELECT * FROM droid'
```

As expected, we see three rows in the table, one for each droid we created.
Now, let's peek at that join table, `starship_droid`. And there it is,
three rows, one for each droid we assigned to our `Starship`. 

## The Magic of Doctrine

The real magic here is that with `Doctrine`, all we need to worry about is
relating the `Droid` to the `Starship`. `Doctrine` takes care of the rest,
handling the adding and deleting of rows in the join table. 

After our flush, we have three rows in the join table, one for each droid.
But let's shake things up a bit. After the flush, let's try removing a
droid — `Starship->removeDroid($droid1)`. 

Reload the fixtures and let's check out our join table. Voila, only two
rows remain. `Doctrine` magically removed the row for our removed droid. 

## Owning vs Inverse Sides

One final touch on `ManyToMany` — remember when we discussed owning
versus inverse sides of a relationship? As we saw, our methods synchronise
the other side of the relationship, adding the `Droid` when we call
`addDroid()`. In `ManyToMany`, either side of the relationship can be the
owning side. 

The way to figure out who's the boss is by looking at the `inverseBy`
attribute. It says `ManyToMany` and `inverseBy` `starships`, which means
that the `Droid` `starships` property is the owning side. 

Now, this is mostly trivia, but if you're a control freak and want to
dictate the name of the join table, you can add a `JoinTable` annotation.
But remember, it has to go on the owning side. Other than that, don't sweat
it — it's not a big deal. Just another day in the Symfony galaxy!