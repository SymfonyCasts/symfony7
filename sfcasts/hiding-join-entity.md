# Hiding the Join Entity

Refresh the homepage and... busted! In the homepage template,
we're referencing `ship.droidNames`. *We* know that this calls
`$starship->getDroidNames()`. But that's still trying to use the
`droids` property that we just deleted. Let's fix that first.

## Isn't It Still a Relationship between Starship and Droid?

Now, we could patch this up by looping over `$ship->starshipDroids`
and grabbing the name off of each one. But hold up! Ignore this method for
a minute. If you zoom out, this is still a relationship between `Starship` and `Droid`.
So wouldn't it be nice if we could still call `$ship->getDroids()` and
have it return a collection of `Droid` objects. Can it be done? Absolutely,
my friend, absolutely.

## Fixing the getDroids() Method

Use `$this->starshipDroids->map()` to transform each item in the `StarshipDroid`
collection into a `Droid` object:

[[[ code('bf9d77485f') ]]]

We now have a `getDroids()` method that once again returns a collection of `Droid`
objects. We rock!

Now that we have this method, down here in `getDroidNames()`. Instead of
using the `droids` property, switch to the `getDroids()` method:

[[[ code('3a007fda38') ]]]

Head back to the homepage template and refresh. Success! Fetching the droids for
a ship is still easy. And the rest of our code didn't need to change.

## Act 5: Future-Proofing Droids

Open up the `Droid` entity and find `getStarships()`. We haven't used
this method yet, but let's fix it up too. This should return a
collection of `Starship` objects. Use the same `map()` trick to transform
the `StarshipDroid` collection into a collection of `Starship` objects:

[[[ code('0a0a4fc660') ]]]

## Hiding the Join Entity When We Create the Relationship

There's one last thing we need to deal with. When we create the
relationship, we still need to do a bit of heavy lifting by creating this
join entity. It's not as simple as `$ship->addDroid($droid)`.
Let's tackle that in the next chapter.
