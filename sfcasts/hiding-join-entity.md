# Hiding Join Entity

Coming soon...

## Act 1: The Homepage Bites the Dust

Alright, so you've been cruising around and you suddenly notice that your
homepage is in shambles. The culprit? Our homepage template,
`ship.droidnames`. Now, if you've been following along, you know that
calling `ship.droidnames` is actually our secret code for
`Starship->getDroidNames()`. But oh, the horror! It's broken. Why? Because
it's still pining for the long-gone `droids` property. 

## Act 2: The Plot Thickens

Now, we could patch this up by running a merry little loop over
`$this->StarshipDroids`, grabbing each `StarshipDroid`, and then
pickpocketing the name off of each one. But wait, let's hit the brakes.
This is turning into a programmer's spaghetti. It's like trying to navigate
the Millennium Falcon through an asteroid field. 

I don't know about you, but I still want to be able to summon
`Starship->getDroids()`. Because, let's face it, at 30,000 feet, it's still
a love story between `Starship` and `Droid`. The fact that we have this
third wheel, `StarshipDroid`, sneaking around is just a nitty-gritty detail
most of the time we'd rather sweep under the rug.

So, my goal, should I choose to accept it (and I do), is to be able to call
`Starship->getDroids()` and have it return a collection of `Droid` objects.
Can it be done, you ask? Absolutely, my friend, absolutely.

## Act 3: The Gift of Map()

Enter `$this->StarshipDroids`, which we'll run through the magical `map()`
function. We're going to convert each `StarshipDroid` — let's christen it
`StarshipDroid`, a much better name. We'll call
`StarshipDroid->getDroids()`. This is going to metamorphose this from a
collection of `StarshipDroid` objects into a collection of `Droid` objects.

 Now, we've got this method down here in `getDroidNames`. Instead of
mourning the `droids` property, let's switch to the `getDroids()` method,
which should now be up and running. 

## Act 4: The Homepage Strikes Back

Alright, now brace yourself and head over to the homepage. And voila!
Things are back to normal. Fetching the droids for a ship is as easy as
pie. And guess what? We made this change and the rest of our code didn't
even bat an eye. 

## Act 5: Future-Proofing Droids

Let's take a stroll to `Droid` and locate `getStarships()`. We haven't used
this method yet but let's tweak it here to make it ready for anything the
future might throw at us. 

Just like we did in `Starship`, we're going to transform this collection of
`StarshipDroid` objects into a collection of `Droid` objects. So,
navigating this relationship is back to being a cakewalk. And the best
part? This change is like a stealth bomber — completely invisible to the
rest of our code. 

## Epilogue: The Last Hurdle

There's one last thing we need to deal with. When we create the
relationship, we still need to do a bit of heavy lifting by creating this
join entity. It's not as simple as whispering `$ship->addDroid($droid)`.
But worry not, we're going to tackle that in the next chapter. Stay tuned!