# Doctrine Inheritance and Relationships

Time to talk about associations, or relationships between entities that
use Doctrine inheritance. For the most part, they work like you'd expect,
but there's a catch related to lazy loading. As a refresher, when you have
an entity that has a relationship to another entity, by default, Doctrine
won't actually load that entity until you call method on it. This helps
with performance, by keeping the number of queries and the number of hydrations,
(instantiating objects) to a minimum.

Under certain conditions, lazy loading an entity in an inheritance hierarchy
doesn't work. This doesn't break your app, but it does mean extra queries
that could impact performance.

To understand these conditions, let's create a relationship! Our Starships
will have Starship Parts.

Over in your terminal, run:

```terminal
symfony console make:entity StarshipPart
```

## Creating Relationship with the Starship Entity

We'll keep it super simple, it'll just have the Starship relation. For the
new property name, use `starship`. The type? Use a `?` to see all available
field types. Choose `relation` to use the handy-dandy relation wizard. What
class should this be associated with? `Starship`.

Now, what type of relationship... This first one is what we want. "Each `StarshipPart`
relates to (has) one `Starship` object". And "Each `Starship` can relate to (can have)
many `StarshipPart` objects". Yep, `ManyToOne`, so choose that.

"Is the `StarshipPart.starship` property allowed to be null?", sure, go with the default.

Do we want to add a `starshipParts` property to the `Starship` class? Yep!

The new field name in `Starship`? `starshipParts` is good. We don't need another property
so just hit enter to exit.

Next, create the Foundry factory for this entity.

```terminal
symfony console make:factory
```

`1` for all (and all for 1). Sweet, let's take a look at the generated code.

## Exploring the Created Entities and Relationships

In `src/Entity`, here's our new `StarshipPart` entity and the `ManyToOne` relationship
to `Starship`:

[[[ code('eef24d8e51') ]]]

And in `Starship`, we have the reverse `OneToMany` relationship to `StarshipPart`
as a `Collection` of `StarshipParts`:

[[[ code('33c34960a3') ]]]

Our `StarshipPartFactory` is pretty bare bones since we don't have any required fields at
the moment:

[[[ code('1a774a9ce1') ]]]

Let's add some parts to our `AppStory`. Make sure this `FreighterFactory::createMany()` is first.

For the second argument, pass an anonymous function that returns an array. Inside,
`'starshipParts' => StarshipPartFactory::createRange(1, 3)`. This will create between 1 and 3
`StarshipParts` for each `Freighter` and associate them to it. The reason we are using an
anonymous function is that we want to create a new set of `StarshipParts` for each `Freighter`.
If we just passed the array directly, it would create one set of `StarshipParts` and try to
associate them to all the Freighters, which would be a problem...

Copy the second argument and paste it for the other two factory calls:

[[[ code('2597d2907b') ]]]

Now reload the fixtures.

```terminal
symfony console foundry:load-fixtures
```

## Understanding Lazy Loading Collections

Head back to the homepage and refresh. This all looks the same and we still have just a
single query. The query to select all the starships. Even though each starship has
several parts, these aren't loaded because we haven't tried to access them, they're
lazy!

Now, open our `MainController::homepage()` and `dump($myShip->getStarshipParts())`:

[[[ code('1667f94a27') ]]]

Refresh... and... still just one query? Yep! If we look at the dump, we can see it's
a `PersistentCollection` with `initialized: false`. This means it's a lazy collection. Again,
it won't initialize the collection until we call a method on it! So let's do that. Add
`->first()` to grab the first element.

Refresh... Now we have two queries. The first is the main findAll query for Starships, and the
second is to select the first `StarshipPart` for `myShip`. In the debug panel, sure enough,
here's the `StarshipPart` instance.

So, when you have an entity that's in an inheritance hierarchy, and you have a relationship to
another entity that is not in an inheritance hierarchy, lazy loading works exactly as you'd expect.

Now let's go the other way...

## Exploring the Implications of Doctrine Inheritance

In `MainController::homepage()`, inject `StarshipPartRepository $starshipPartRepository` and
`dump($starshipPartRepository->find(1))`:

[[[ code('a72550d644') ]]]

`1` should be the ID of the first `StarshipPart` in our database.

Refresh the page... and we have two queries: the main select and the `find()`
query where we're loading the `StarshipPart`. Look at the `StarshipPart` that was
dumped. Note that the `starship` property is a `Freighter` instance. Doctrine
knows this `Freighter` was already instantiated with our first query, the findAll,
so it reused it here.

To demonstrate lazy loading, let's ensure this Freighter won't be reused.

In the controller, replace the `StarshipRepository` with `MiningFreighterRepository`:

[[[ code('615ee0c9bc') ]]]

This reduces the fetched starships to just `MiningFreighters`.

Go back to the homepage. Remember, in our last request, we loaded a `StarshipPart` and
the `Freighter` its related to was already instantiated.

Refresh the page. Just two ships now, the `MiningFreighters`. Look at the query count: it's 3 now.

First is the findAll for the `MiningFreighters`. Second is our `find(1)` for the `StarshipPart`.
And the third is to fetch the `Starship` for the found `StarshipPart` above. It's being
*eager loaded*. If `Starship` was not using Doctrine Inheritance, this third query wouldn't
be required. It would only load the `Starship` when we call a method on it.

This is the caveat of using this type of relationship with Doctrine Inheritance... It can't
really be avoided, and I'll explain why later.

## A Potential Fix

There is a scenario where you can get lazy loading to work for this relationship. The
associated entity in the hierarchy must be a leaf, meaning it can't have any child entities.

To see this in action, we'll have our `StarshipPart` be related to `Scout` instead of `Starship`.
`Scout` is a leaf in our hierarchy.

In `Starship`, cut the relationship property and constructor. Paste it into `Scout`. Back
in `Starship`, cut the 3 methods related to the relationship and paste these into `Scout` as well:

[[[ code('d6fb5a6be1') ]]]

Now, in `StarshipPart`, change the relationship typehint from `Starship` to `Scout` in all
places:

[[[ code('37ea3cf47c') ]]]

Finally, in `AppStory`, remove the second argument from the `FreighterFactory` and `MiningFreighterFactory`
`createMany`'s as these can't work anymore:

[[[ code('76d97a0710') ]]]

Now, reload our fixtures again...

```terminal-silent
symfony console foundry:load-fixtures
```

Go back to the homepage and... Woo! We're back to just two queries. We don't have that
third *eager* query anymore.

In the debug panel, the `starship` property is this weird `Proxies/__CG__` class. This
is a proxy for our Scout that enables lazy loading. Only when we call a method on it
will it actually query the database to load the associated `Scout`.

So... why this limitation?

## Why Can't It Always Be Lazy?

The reason why entities that have children in an inheritance hierarchy can't be lazy loaded
is because of how database relationships, and PHP object instantiation work. Only the ID of the related entity is stored
in the table. In the case of an entity with children, the ID alone isn't enough to determine
what entity it is. When it's `Starship`, is it a `Freighter`, `MiningFreighter`, or `Scout`?
It needs to know this in order instantiate the correct object. And the only way to know is to query
the relationship's table to get the discriminator column value. So, it needs to load the relationship
immediately, or eagerly.

Ok, but why don't entities without children have this limitation? Because Doctrine knows exactly what type
of entity the relationship is! When it's a `Scout`, Doctrine can create the lazy proxy for it. This
would be the same for the `MiningFreighter`.

## Final Thoughts

This wraps up our course on Doctrine Inheritance. As long as you're aware of the limitations,
this can be a powerful tool for modeling your domain! Just be mindful of your relationships
and how they might impact performance.

If you have any questions, we're here for you down in the comments.

Until next time, Happy Coding!
