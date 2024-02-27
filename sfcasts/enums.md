# PHP Enums

Inside the loop, making things dynamic is nothing new... which is great! For
in progress, say `{{ ship.status }}`. When we refresh, it prints! Though, yikes!
The statuses are running *way* out of their space. Our data doesn't match the
design!

Plot twist! Someone changed the project's requirements... right in the middle!
That "never" happens! The new plan is this: each ship should have a status of
`in progress`, `waiting`, or `completed`. Over in
`src/Repository/StarshipRepository.php`, our ships *do* have a `status` - it's
this argument - but it's a string that can be set to *anything*.

## Creating an Enum

So we need to do some refactoring to fit the new plan. Let's think: there
are exactly three valid statuses. This a *perfect* use case for a PHP *enum*.

If you're not familiar with enums, they're lovely and a great way to organize
a set of statuses - like published, unpublished & draft - or sizes - small, medium
or large - or anything similar.

In the `Model/` directory - though this could live anywhere... we're creating the
enum for *our* own organization - create a new class and call it `StarshipStatusEnum`.
As soon as I typed the word enum, PhpStorm changed the template from `class` to an
`enum`. So we're not creating a class, as you can see, we created an `enum`

Add a `: string` to the enum to make what's called a "string-backed enum". We won't
go too deep, but this allows us to define each status - like `WAITING` *and* assign
that to a string, which will be handy in a minute. Add a status for `IN_PROGRESS`
and finally one for `COMPLETED`.

That's it! That's all an enum is: a set of "states" that get centralized in
one place.

Next: open up the `Starship` class. The last argument is currently a `string` status.
Change it to be a `StarshipStatusEnum`. And at the bottom, the `getStatus` method
will now return a `StarshipStatusEnum`.

Finally, in `StarshipRepository` where we create each `Starship`, my editor is angry.
It says:

> Hey! This argument accepts a `StarshipStatusEnum`, but you're passing a string!

Let's calm it down. Change this to `StarshipStatusEnum::`... and it
autocomplete the choices! Let's make the first one `IN_PROGRESS`. And that *did*
add the `use` statement for the `enum` to the top of the class. For the next one,
make it `COMPLETED`... and for the last, `WAITING`.

Refactoring done! Well... *maybe*. When we refresh, busted! It says:

> object of class `StarshipStatusEnum` could not be converted to string

And it's coming from the `ship.status` Twig call.

That makes sense: `ship.status` is now an enum... which can't be directly printed
as a string. The easiest fix, in `homepage.html.twig`, is to add `.value`.

Because we made our enum *string-backed*, it has a `value` property, which will be
the string that we assigned to the current status. Try it now. It looks great! In
progress, completed, waiting.

Next: let's learn how we can make this *last* change a bit more elegant by creating
smarter methods on our `Starship` class. Then we'll put the finishing touches on
our design.
