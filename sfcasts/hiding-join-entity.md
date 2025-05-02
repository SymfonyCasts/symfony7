# Hiding Join Entity

Coming soon...

So over on the homepage, things are busted. It's coming from our homepage
template, `ship.droidnames`. Now we know when we call `ship.droidnames`,
that's actually calling `Starship->getDroidNames()`, which of course is
broken because it's referencing the old non-existent `droids` property. Now
we could get this to work by looping over `$this->StarshipDroids`, and then
getting the `droid` object off of each `StarshipDroid`, and then getting
the `name` off of that. But hold up, things are getting confusing. It's
getting a little bit confusing. I still want to be able to say
`Starship->getDroids()`, because if you think about it on a high level, the
relationship is still between `Starship` and `droid`. The fact that we have
this join entity `StarshipDroid` here is really an implementation detail
that most of the time we're going to want to hide. So I still want to be
able to call `Starship->getDroids()` and have that return a collection of
`droid` objects. Can we do that? Absolutely. So `$this->StarshipDroids`,
we're going to use the handy `map` function. To convert each
`StarshipDroid`, let's call that `StarshipDroid`. That's a better name for
it. We'll call `StarshipDroid->getDroids()`. This is going to convert this
from a collection of `StarshipDroid` objects, once again, to a collection
of `droid` objects. And if we have this method down here, down in
`getDroidNames`, instead of referencing the `droids` property, let's
reference the `getDroids()` method, which should now work. All right, head
over to the homepage and things work again. So fetching the `droids` for a
`ship` is right back to what it was before. Super easy. The cool thing is
we made this change and the rest of our code didn't really need to, we made
this change, the rest of our code didn't really need to change. All right,
in `droid`, let's go down to `getStarships()`. And we're not using this
method yet, but let's make that same change in here to make it
future-proof. So `$this->StarshipDroids->map(StarshipDroid)`. All right.
`StarshipDroid`. That's not quite right. We need the equal arrow. Over here
it's going to be `StarshipDroid->getDroid()`. We don't need that
`toArray()`, I don't know where that's coming from. That's it. All right.
So this is going to convert this collection of `StarshipDroid` objects into
a collection of `droid` objects, just like we did a second ago in
`Starship`. So again, looping over this relationship is now exactly the
same way it was before. This change is kind of hidden from the rest of our
code, which is freaking awesome. The last thing that's different is when we
make the relationship, we still need to do quite a bit of work by creating
this join entity. It's no longer as simple as just saying
`ship->addDroid(droid)`. Let's fix that next.