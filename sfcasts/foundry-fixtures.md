# Foundry Fixtures for Inheritance

Ok, we left off with an error when we tried to load our fixtures. Open
`src/Factory/StarshipFactory`. Basically, now that we made `Starship` an
abstract MappedSuperclass, it can't be instantiated and persisted itself anymore.

We need to create separate factories for our two new sub-starship entities:
the `Freighter` and the `Scout`. Easy-peasy! Over in the terminal, run:

```terminal
symfony console make:factory
```

One nifty feature of Foundry is that you can create factories individually,
or you can just go with "all" and it will make factories for all
entities that don't yet have one. Select "all" and... voila! Now, if we
hop back to our code, we have two shiny new factories.

## Handling Duplicated Defaults

You'll notice that our defaults are basically duplicated, except for the cargo
capacity for the `Freighter` and the sensor range for the `Scout`.

We can pull the shared defaults up to the `StarshipFactory` and then just override
the specific defaults in the `FreighterFactory` and `ScoutFactory`.

To do, this, we need to mirror the inheritance structure we have with our entities
for Foundry factories.

## Making StarshipFactory Abstract

Start with the `StarshipFactory`. Make it abstract so it's clear it shouldn't
be used directly. In the docblock, we'll sprinkle in some PHP generics to help
with auto-completion. Add our own template with `@template T of Starship`.
This indicates that our template `T` can only be of type `Starship`, or any
subclass of `Starship`. Then, modify the `@extends` to `PersistentProxyObjectFactory<T>` to
*use* our template:

[[[ code('748965f26b') ]]]

Now, any sub-factory that extends `StarshipFactory` can specify their own
Starship type for `T`, and we'll get auto-completion for that Starship type when we use the factory.

***TIP
If you use static analysis tools like PHPStan, this will also help it understand the types
better and catch any type-related issues.
***

Also, since this is abstract, we can ditch the `class()` method - it'll always need to be
overridden from the sub factories.

## Modifying FreighterFactory and ScoutFactory

Time to one of my favorite things to do, removing duplicated code!

In `FreighterFactory`, have it `extends StarshipFactory`,
and in the docblock, change the `@extends` to `StarshipFactory<Freighter>`:

[[[ code('9df947dcad') ]]]

Down in `defaults()`, wrap the returned array in an `array_merge()`. First argument:
`parent::defaults()`, don't forget to close the parentheses! For the second argument,
we can strip down to just the `cargoCapacity`, since that's the only thing that differs
from the defaults in `StarshipFactory`:

[[[ code('f0e9345f84') ]]]

Same thing for the `ScoutFactory`, `extends StarshipFactory`, `@extends StarshipFactory<Scout>`,
and in `defaults()`, merge with `parent::defaults()` and only include the `sensorRange`:

[[[ code('da3fd0b8d4') ]]]

Nice!

## Loading Fixtures Again

Back in the terminal, let's give loading these fixtures another shot!

```terminal
symfony console foundry:load-fixtures
```

Hmm, same error. Oh, duh... we created the new factories, but we aren't yet using them!

Open up `src/Story/AppStory`. This is the default *story* that Foundry loads when
loading fixtures. Down in the `build()` method, replace `StarshipFactory::createMany(3)`
with `FreighterFactory::createMany(3)`. Also, duplicate this line and change it to
`ScoutFactory::createMany(3)` to load some Scouts as well. 6 starships total:

[[[ code('ea54781502') ]]]

Run the load fixtures command again:

```terminal-silent
symfony console foundry:load-fixtures
```

Fantastic, it's fully loaded!

## Fixing Controller Error and Displaying Starships

Navigate back to our home page where we list the starships and see what we have.

Oops, we have an error. Our controller is still trying to load starships
from our `StarshipRepository`. This doesn't work because `Starship` is no
longer a valid entity. Same problem as we had with using the `StarshipFactory`
directly earlier.

To fix this, open `src/Controller/MainController`. In the `homepage()` method, replace the injected
`StarshipRepository` with `ScoutRepository`:

[[[ code('acf8d6bf0b') ]]]

This should now fetch all the Scout starships.

Refresh the homepage... and there we go! These are our three Scouts.

But what about the Freighters? Well, we could also inject the `FreighterRepository`,
fetch those too, and merge them with the Scouts. But this isn't really ideal though,
it would not scale well. Imagine we had 20 different types of starships - we'd have
to inject 20 different repositories... Gross!

This is how you'd have to do it with Mapped Superclasses. It's not really a
limitation of Mapped Superclasses, it's just not their purpose. They're more meant
for sharing common properties and mappings between entities, not for querying across
a hierarchy of entities.

Wouldn't it be cool if we could still inject the `StarshipRepository` and have it return
both Scouts and Freighters? Well, we can! But to do that, we need to use a different type
of inheritance. That's next!
