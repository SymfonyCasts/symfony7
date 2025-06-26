# Many To One: The King of Relationships

Alright folks, we've successfully built `Starship` and `StarshipPart`
entities, and... they're sitting pretty in the database. But here's the
puzzle: how do we *attach* these parts to their respective
starship? How do we give every `StarshipPart` its rightful `Starship`
home? That's where our trusty `make:entity` command comes back into
play. What a showoff. Find your terminal and run:

```terminal
symfony console make:entity
```

## Building Relationships: Think Objects, Not IDs

Now, if you're thinking in traditional database terms, you might
imagine a `starship_id` column appearing in your `starship_part` table.
And it *will*, but that's not how we think about things in Doctrine.
Instead, we focus on relating *objects*. So updating the `StarshipPart`
*entity* to add a field for `Starship`.

So when it comes to naming the field, don't call it
`starshipId`. Doctrine wants us to think in terms of classes & objects.
And because a `StarshipPart` will belong to a `Starship`
give the `StarshipPart` entity a `starship` property. 

For the field type, use a fake type called "relation". That 
kick-starts a wizard! Which class are we relating to? 
Say it with me: `Starship`.

## Choosing the Right Relationship Type

The wizard walks us through the *four* different *types* of
relations. Check the descriptions: we want a `ManyToOne` 
where each part belongs to one `Starship`, and each `Starship`
can have many parts. 

When asked if the `starship` property can be null, we'll say "no". We
want every part to belong to a starship: no randomly floating parts allowed.

## Adding Convenience with a New Property

Next, the wizard asks an interesting question:

> Do we want to add a new property to `Starship` that would allow us
> to say `$starship->getParts()`?

This is entirely optional, but it *would* be nice to have such a simple way to get
all the parts for a ship. There's also no downside. So this is a "yes" for me dawg.
Call the property `parts`: short and sweet. For orphan removal, say "no". We'll dive
into that later. 

Hit enter to finish. I committed before recording, so I'll check the changes with:

```terminal
git status
```

## New Properties in StarshipPart and Starship

Well, well, well. It looks like *both* entities got an update! In `StarshipPart`,
we have a new `starship` property. But instead of `ORM\Column`, it's
`ORM\ManyToOne` and its value will be a `Starship` *object*. We also have
fresh `getStarship()` and `setStarship()` methods:

[[[ code('acafa3d74c') ]]]

Over in `Starship` , we have a new `parts` property with
`ORM\OneToMany`. Scrolling down, we see a handy `getParts()` method.
But instead of `setParts()`, we've been gifted `addPart()` and
`removePart()` methods:

[[[ code('e545ebfb1f') ]]]

These will come in handy when you work with Foundry, the form system or if you're
building an API with Symfony's serializer.

In the constructor, it added `$this->parts = new ArrayCollection()`:

[[[ code('744d1c32b4') ]]]

This is a detail we need, but it's not super important: `ArrayCollection` is
an object looks and acts like an array, meaning we can `foreach` over it
or do other array-like things.

Oh, and if you think about it: `OneToMany` and `ManyToOne` are really two views of
the *same* one relation. If a part belongs to one starship, then a starship
has many parts. We've added *one* relationship, but we can see it from two
different perspectives. 

But we're not done yet. Because `make:entity` added new properties, I bet we need
to update our database. Create a migration:

```terminal
symfony console make:migration
```

## Checking Out the Migration

This is one of my *favorite* migrations. It alters `starship_part` to add a `starship_id`
column, which is a foreign key over to `starship`. This happened because
Doctrine is a smarty-pants. We added a `starship` property to
`StarshipPart`, but Doctrine knew that the column should be called
`starship_id`. It's even going to help us *set* that as we'll see in the next
chapter. Let's migrate:

```terminal
symfony console doctrine:migrations:migrate
```

## Preparing for the Migration

It explodes!

> Column "starship_id" in table "starship_part" cannot be null.

Remember the `starship_part` table? It already has 50 rows in it!
The migration tries to add a new `starship_id` column and set it to `null`.
But that's not allowed, thanks to the `nullable: false`. Clear those 50 rows with:

```terminal
symfony console doctrine:query:sql "DELETE FROM starship_part"
```

And run the migration again:

```terminal-silent
symfony console doctrine:migrations:migrate
```

## Next Up: Connecting the Dots

So, how do we go about linking a `StarshipPart` object with its `Starship`?
Buckle up, because that's next!
