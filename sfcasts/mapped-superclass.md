# Mapped Superclasses

Hey Friends! Let's dive into the world of Doctrine Inheritance
in Symfony. You already know how Doctrine classes represent tables in our
database, and their instances, or objects, represent rows. But did you know
we can take it even further? Doctrine ORM gives us a nifty inheritance feature.
This lets us create entities that extend abstract classes or even other entities.
This is super useful for sharing common properties. There are
three ways to go about this, and we'll explore each one and weigh their
pros and cons. Buckle up, let's get started!

## Setting Up the Project

To kick things off, download the course code provided at the top of this
video. Inside the start directory, you'll find a shiny new project. Just
follow the readme to get it up and running on your local machine. It might
look familiar if you've taken some of our prior courses, but make sure you
download this fresh copy instead of using an existing project.

Once you run the Symfony server, you'll see our Starshop app. This simple
app just lists Starships from our database.

Back in our code, in the `src\Entity` directory, you'll find our `Starship` entity with general starship-related
properties. Our mission today is to create different Starship types, a
freighter and a scout ship.

You might think we could just add a `type` column to the `Starship` entity and use that to
differentiate between the types. The thing is, each different type has its own set
of properties specific to it. For example, a freighter will have a `cargoCapacity` property,
while a scout ship will have a `sensorRange` property.

So we want these as their own entities, but they also need to share the common properties of a Starship.

## Creating Sub Starships

First, we need to create some sub Starships. Hop over to the terminal and
let's get our hands dirty creating some entities. Run the following
command:

```terminal
symfony console make:entity Scout
```

This Scout entity will have a unique property called `sensorRange`, which
will be an integer... and cannot be null. Once that's done, hit enter to exit.

Next, let's create a Freighter entity:

```terminal
symfony console make:entity Freighter
```

The unique property for this entity will be `cargoCapacity`, also an
integer... and not nullable. That's all for now, so wrap it up.

Back in our code, we now have two shiny new entities.

## Exploring Mapped Superclasses

The first type of inheritance we're going to explore is the mapped
superclass. It's like an abstract class in PHP, or more accurately, an
abstract entity. Our `Starship` will be the Mapped Superclass.
Make it `abstract`, meaning it can't be instantiated directly
but must be extended to be used:

[[[ code('27de96a78e') ]]]

Next, replace the `ORM\Entity` attribute with `ORM\MappedSuperclass`:

[[[ code('957310e89f') ]]]

That's it!

Next, our `Freighter` entity. Have it `extends Starship`. We
don't need the `id`, since that's inherited from `Starship`, so we'll
delete it along with the `getId()` method:

[[[ code('4da13ec44f') ]]]


Everything else is specific to the `Freighter`. Repeat these steps for the
`Scout` entity:

[[[ code('ca22e1c73e') ]]]

## Updating the Schema and Loading Fixtures

Now, let's switch over to the console and see what our schema update will
look like. Run:

```terminal
symfony console doctrine:schema:update --dump-sql
```

The `dump-sql` flag just outputs the SQL that would be executed.

At the end here, notice that we're dropping the `starship` table. Mapped
superclasses don't have their own tables. Instead, their properties are
inherited by the child entities, which do have their own tables. So, in our
case, the `freighter` and `scout` tables will have all the properties of a
`Starship`, along with their unique properties: `cargo_capacity` for the `freighter`
and `sensor_range` for the `scout`.

Let's see how this plays out when we reload the fixtures for our app. Run:

```terminal
symfony console foundry:load-fixtures
```

Here's a snag: we can't instantiate the abstract class `Starship`. We're
using Foundry factories, and since `Starship` is now abstract, it can't
create those entities. Don't worry, in the next part of our journey, we'll
adjust our Foundry fixtures to handle Doctrine Inheritance. Stay tuned!
