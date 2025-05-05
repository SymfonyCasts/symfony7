# Many To One

Alright folks, we've successfully built `Starship` and `StarshipPart`
entities, and they're now sitting pretty in our database. But here's the
puzzle: how do we *attach* these starship parts to their respective
starship? How do we give every `StarshipPart` its rightful `Starship`
home? Well, that's where our trusty `make:entity` command comes back into
play. What a superstar. Fire up your terminal and let's get this party started:

```terminal
symfony console make:entity
```

## Building Relationships: Think Objects, Not IDs

Now, if you're thinking in traditional database terms, you might be
imagining a `starship_id` column appearing in your `starship_part` table.
But that's not how we roll in the Doctrine
universe! Instead, we focus on updating the `StarshipPart`
*entity* to add a field for this, not its table. 

When it comes to naming the new field, we're *not* going to call it
`starshipId`. Doctrine wants us to think in terms of classes & objects,
not IDs. And because a `StarshipPart` will have a `Starship` attached to it,
give the `StarshipPart` entity a `starship` property. 

For the field type, we're going to use a placeholder called "relation". That 
kick-starts a wizard to guide us. Which class are we relating to? 
Say it with me: `Starship`.

## Choosing the Right Relationship Type

The wizard then walks us through the *four* different *types* of
relationships. Check the descriptions: we're looking for a `ManyToOne` 
where each part belongs
to one `Starship`, and each `Starship` can have many parts. 

When asked if the `starship` property can be null, we'll firmly say no. We
want every part to belong to a starship: no randomly floating parts allowed.

## Adding Convenience with a New Property

Next, the wizard asks an interesting question: do we want to add a new property to
`Starship` that would allow us to say `$starship->getParts()`? This is entirely
optional,  but it *would* be nice to have such a simple way to get all the parts
for a ship. There's also no downside. So this is a yes for me dawg.
Call this field `parts` - short and sweet. For orphan removal, say no. We'll dive
into that later. 

Hit enter to finish. I committed before recording so I'll check the changes with:

```terminal
git status
```

## New Properties in StarshipPart and Starship

Well, well, well, what do we have here? It looks like *both* entities got an
update! In `StarshipPart`, we have a new `starship` property.
But instead of `ORM\Column`, we're now using `ORM\ManyToOne`. We also have
fresh `getStarship()` and `setStarship()` methods. 

Over on the `Starship` side, we have a new `parts` property with a
`ORM\OneToMany`. Scrolling down, we see a handy `getParts()` method.
But instead of `setParts()`, we've been gifted an `addPart()`
`removePart()` method. Trust me, these will come in handy when we work
with Foundry, the form system or if you're building an API with
Symfony's serializer.

In the constructor, it added `$this->parts = new ArrayCollection()`. This is a detail
we need, but it's not super important: `ArrayCollection` is a special object
looks and acts like an array, meaning we can `foreach` over it.

Oh and if you think about it: `OneToMany` and `ManyToOne` are really two views of
the *same* relationship. If a part belongs to one starship, then a starship
has many parts. We've added *one* relationship, but we can see it from two
different perspectives. 

But we're not done yet. Because `make:entity` added new properties, I bet we need
to update our database. Create a migration:

```terminal
symfony console make:migration
```

## Checking Out the Migration

This is one of my *favorite* migrations. It alters `starship_part` to a `starship_id`
column, which is a foreign key over to `starship`. This happened because
Doctrine is a smarty-pants. We added a `starship` property to
`StarshipPart`, but Doctrine knew that the column should be called
`starship_id`. It's even going to help us *set* that as we'll see in the next
chapter. Let's migrate:

```terminal
symfony console doctrine:migrations:migrate
```

## Preparing for the Migration

Try it!

```terminal
symfony console doctrine:migrations:migrate
```

It explodes!

> Column starship_id in table starship_part cannot be null.

Remember the `starship_part` table? It already has
50 rows in it! The migration tries to add a
new `starship_id` column and set it to `null`. But that's not allowed,
thanks to the `nullable: false`
Clear those 50 rows with:

```terminal
symfony console doctrine:query:sql "DELETE FROM starship_part"
```
Run the migration again:

```terminal-silent
symfony console doctrine:migrations:migrate
```

## Next Up: Connecting the Dots

So, how do we go about linking a `StarshipPart` object with its `Starship`?
object? Buckle up, because that's next!
