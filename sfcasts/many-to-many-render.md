# Many To Many Render

Coming soon...

Okay, we have some starships that are related to some droids. The question
now is, how do we render this? So for example, on this starship here, I
want to list all of the droids that are assigned to it. This turns out to
be really, really easy. It's going to work just like a `OneToMany`
relationship. So let's open up the template, `template starship. Show to
HTML twig`. It doesn't matter where, but I'm going to grab the `H4` and the
`P` tag for arrived at. And then paste those below. Change arrived at to
droids. And then down here for the, let's clear out the arrived at down
here. I'll break that on off the lines. And this is dead simple. You can
say, we have a `starship` object, a, so for `droid` in, we just call it
`droid`. So `ship.droids`. It'll call the `getDroids()` method. And that
will return a collection of `Droid` objects. So we use none. This is now a
`Droid` object. So we can say things like `{{ droid.name }}`. Then I'm
going to get just a little bit fancier. So we can have commas, but without
that trailing comma. So over here, I'll say `{{% if not loop.last %}}, {{%
endif %}`. I'm actually going to show you a fancier way to do this in a
second, but that is the most straightforward way to do it. This is cool.
Down here, still inside the `for`, we can put an `else` tag. So if there
aren't any droids to loop over, then we can say "no droids on board. Clean
up your own mess." Which is kind of rude, but also true. This works because
`droid` is a `Droid` object. We're taking advantage of our `ManyToMany`
relationship. So it could not be simpler. And down here, can't forget my
`{{% endfor %}}`. And let's try it. Whoops. So no, you probably saw my
mistake. `Droids`. And it works. If you don't see it down here, make sure
you click on the first starship that actually has droids assigned to it. So
if this is empty, that's the reason. Okay, while we are showing off on the
homepage, I wanna list down here all of the names of the droids that are
related to this starship. This is actually pretty similar to what we just
did, but we're going to do it a different way. So open up the template for
this. So it's going to be `templates main homepage.html twig`. And right
after parts, we're going to do it here. I'm going to add another div, say
droids. And there we go. So once again, we could do our loop thing, we
could do our loop.last comma thing, but instead we've already needed the
droid names in two spots. So what I'm thinking is let's go into the
`Starship` class and add a new smart method inside of here. This could go
anywhere, but I'm going to go to the bottom of the class where we have our
droid methods already. Create a public function. called `getDroidNames()`.
This is gonna return a string. We could have a return there in a way that
would be useful also, but I'll return the string, the comma separated
string. Now to do this, we're going to return, oops, `implode`. So remember
that one's how we can do a little comma there. And it's gonna give us a
`ray map` here, which almost works. The problem is that if we pass this
arrow, we pass this, this arrow droids as the argument, that's not
technically an array, that is a collection object. So instead we're gonna
kind of rely on a method on the collection object itself called `map`.
We'll say `implode`. So we'll say, `implode this arrow droids`. It also has
a `map` function where it can basically change the droid objects into just
their name. And for the function, I'm gonna use the fancier new way of
doing it. So `fn`, good, it's gonna be a `droid`. And actually this is
exactly what I want. So it's gonna loop over this function for each of the
droids, and we're gonna return just the `droid arrow get name`. And the
reason we need this to array at the very end is technically right here,
we're gonna have a collection object full of the droid names. The `implode`
function needs a classic array. So by calling `to array` at the very end,
that translates it from a collection object to an array. So kind of a small
but important detail. And that's it. The cool part about this is now we
have this `getDroidNames()` method, and it's gonna be very simple on our
homepage to use that. We can say `{{ ship.droidNames ?: 'none' }}`. I'm
even getting a fancier and say `? : none`. But the fanciest index basically
means if this has a value then print it, else print none. And now we've got
it. So you see our droid is up here, and down here we have none. So that's
it. So nothing fancy with printing `ManyToMany`s, but it was cool to add
that smart method to our entity. So next, let's see how we can join across
`ManyToMany` relationships.