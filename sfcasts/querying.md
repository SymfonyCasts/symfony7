# Querying Classes

Let's see how we can query specific types of ships. Here, we see a list of
all starships. But what if we only want to display
scouts, or freighters, or mining freighters? There's some DQL for that!

First though, let's add some visual distinction between the different types of ships.
So we know which ones are scouts, freighters, etc.

Remember, we can't directly access the discriminator column to get this
value. Right now, our only option is to check the instance of the ship returned from
`findAll` and see if it's a freighter, scout, etc. This is easy to do in PHP,
but in Twig, not so much...

I'd like to be able to call `$starship->getType()` and have it return the discriminator value.
Here's a trick! Open our starship entity, and in the `DiscriminatorMap` attribute, cut the map array.
In the class, add `private const TYPE_MAP =`... paste, don't forget the semicolon. Back up in
the attribute, set the argument to `self::TYPE_MAP`:

[[[ code('c05b193f30') ]]]

Now, down below, add a new method, `final public function getType()`, this will return a string.
We're using `final` here to prevent any subclasses from modifying the logic.

Our `TYPE_MAP` is keyed by the discriminator values we want to return, and the values are the class names. To get
the discriminator value for the current starship object, we can flip the `TYPE_MAP` array, so the class names are the
keys. Down in `getType`, `return array_flip(self::class)[static::class]`. As a refresher, `self::class` always
returns the class where the code is written, so it would return `Starship::class` for all ships. `static::class`
returns the class of the actual object, so it would return `Scout::class` for a scout, `Freighter::class` for a
freighter, etc. So, it's important to use `static::class` for the key here:

[[[ code('f794e09ed1') ]]]

## Updating the UI

With our `getType()` method ready, we'll update the Twig template to show it. Open `templates/main/homepage.html.twig`.
Before the ship name, I'll add some space, and add `{{ ship.type }}`:

[[[ code('97f0df84b8') ]]]

Over in our browser, refresh, and sweet! We can see the ship name prefixed with the type.
It could look nicer though...

Here's a neat tip: even if your site isn't multilingual, you
can still use the translation component. Use these as type strings as keys for your default
language and customize them in your translation files.

To keep things simple, I'll just run these through some string filters to make them prettier.
Back in the template, add `|replace({'_': ' '})|title`:

[[[ code('d95c61c68a') ]]]

This replaces underscores with spaces and uppercases the first letter of each word.

Refresh the browser and... much better!

## Filtering the Ships

Now, let's get into the actual filtering. Open up the `StarshipRepository` and add a new method
`public function filterShips()`, return type: `array`. Above, add a docblock to specify that this
returns an array of `Starship`'s:

[[[ code('f292af87ed') ]]]

Inside, `return $this->createQueryBuilder()`, alias `s`. Add `->where('s INSTANCE OF '.Scout::class)`.
This `INSTANCE OF` is a special DQL operator that checks if the entity is an instance of the
specified class or any subclass. Just like PHP's `instanceof` operator! So this will return all scouts
and any subclasses of scout (if we had any).

Finish with `->getQuery()->execute()`:

[[[ code('2fce2ca986') ]]]

In `MainController::homepage()`, switch from using `findAll()` to `filterShips()`:

[[[ code('fa836fdf3e') ]]]

Refresh the homepage and... Nice, we just see the scouts!

## Using Parameters

When building queries, to prevent SQL injection, we use parameters instead of hardcoding values
in the where clause. So let's do that.

Change the where clause to `s INSTANCE OF :class`, and below, add `->setParameter('class', Scout::class)`:

[[[ code('1885194f6e') ]]]

Refresh the page and... an error. "array_rand cannot be empty". Hmm, ok this is coming
from our `MainController`. We're using `array_rand` to get a random ship, and it's failing
because the `$ships` array is empty.

It's kind of a bummer, but we can't use the class name directly as a parameter. There is a workaround though!

Back in the `StarshipRepository`, in `setParameter()`, for the second argument, use
`$this->getEntityManager()->getClassMetadata(Scout::class)`. 

[[[ code('ce9fc7517b') ]]]

This returns a special
metadata object for the Scout class and is what Doctrine needs to properly handle the `INSTANCE OF`
operator with parameters.

Refresh the browser... and nice, just the scouts again!

## NOT INSTANCE OF

Try switching the class to `Freighter::class` and refresh the homepage.

[[[ code('0c48fee1cd') ]]]

We now
see freighters, but also the mining freighters. This is because in
PHP, instance of returns true for the class you're looking at and any
subclasses.

To display only the normal freighters, we need another where clause. In our `filterShips()` method,
add `->andWhere('s NOT INSTANCE OF :notclass')`. Below, duplicate the `setParameter` line and
change `class` to `notclass` and `Freighter::class` to `MiningFreighter::class`:

[[[ code('a8b02dda45') ]]]

Refresh the homepage and... there we go, just the normal freighters!

Unfortunately, if the freighter had 10 subclasses, we'd have to add 10 `NOT
INSTANCE OF`'s - one for each subclass. I don't believe there's a more elegant
way to achieve this out of the box, but let me know in the comments if you know a way!

Before we move on, head back to the `MainController` and switch from
`filterShips` back to `findAll` and confirm that we get everything again:

[[[ code('3fcdfbeca3') ]]]

Next, we'll look at how we can display the specific properties for each type of ship
in the Twig template.
