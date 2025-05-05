# Many To One

Coming soon...

## Setting the Stage for Starship Construction

Alright folks, we've successfully built our `Starship` and `StarshipPart`
entities, and they're now sitting pretty in our database. But here's the
puzzle: how do we attach these starship parts to their respective
starships? How do we give every `StarshipPart` its rightful `Starship`
home? Well, that's where our trusty `make:entity` command comes back into
play. So, fire up your terminal, and let's get this party started:

```terminal
symfony console make:entity
```

## Building Relationships: Think Objects, Not IDs

Now, if you're thinking in traditional database terms, you might be
imagining a `starship_id` column appearing in your `starship_part` table.
But hold your space horses! That's not how we roll in the Doctrine
universe. Instead, we're going to focus on updating our `StarshipPart`
entity. 

When it comes to naming our new field, we're not going to call it
`starshipId`. Doctrine encourages us to think in terms of objects, not IDs.
So we're going to give the `StarshipPart` class a `starship` property. 

For the field type, we're going to use a placeholder called relation, which
will kick-start a helpful wizard to guide us through the process. When it
asks which class we're relating to, we'll say we're connecting a
`StarshipPart` to a `Starship`. 

## Choosing the Right Relationship Type

The wizard will then walk you through the four different types of
relationships. It's like a matchmaking service for your entities! In our
case, we're looking for a `ManyToOne` relationship, where each part belongs
to one starship, but each starship can have many parts. 

When asked if the `starship` property can be null, we'll firmly say no. We
want every part to belong to a starship. 

## Adding Convenience with a New Property

Next, the wizard will ask if we want to add a new property to `Starship` to
allow `$starship->getParts()`. This is optional, but in my book, it's also
super convenient. It's like having a shortcut to your toolbox. So let's say
yes to this one. We'll call this field `parts` - short and sweet. For
orphan removal, we'll say no. We'll dive into that concept later. 

Once we've made all our choices, hit enter to exit the command. After
committing before recording, let's check our changes with a `git status`. 

## Checking the Updates

Well, well, well, what do we have here? Looks like both entities got a
little update. In `StarshipPart`, we've got a new `starship` property.
Instead of `ORM\Column`, we're now using `ORM\ManyToOne`. We've also got a
fresh `getStarship()` method and a `setStarship()` method. 

Over on the `Starship` side, we've got a new `parts` property with a
`ORM\OneToMany`. Scrolling down, we'll see a handy `getParts()` method.
Instead of a `setParts()` method, we've been gifted an `addPart()` method
and a `removePart()` method. Trust me, these will come in handy when you're
tinkering with Symfony's form system or serializer system. 

In the constructor, we've got a line that says `$this->parts = new
ArrayCollection()`. It's a detail you need, but it's not a showstopper.
This object behaves just like an array, so you can `foreach` over
`$this->parts` as if it was an array. 

Keep in mind that the `OneToMany` and `ManyToOne` are really two views of
the same relationship. If a part belongs to one starship, then a starship
has many parts. We've added one relationship, but we can see it from two
different perspectives. 

But we're not done yet. Our `make:entity` command added new properties to
`Starship`, which means we need to create a migration:

```terminal
symfony console make:migration
```

## Checking Out the Migration

This is one of my favorite migrations. It's like watching a starship being
built in real-time. It altered `starship_part` and added a `starship_id`
column, which is a foreign key over to `starship`. This happened because
Doctrine is a smarty-pants. We added a `starship` property to
`StarshipPart`, but Doctrine knew that the column should be called
`starship_id`. It's even going to help us set that as we'll see in the next
chapter. Now, let's migrate:

```terminal
symfony console doctrine:migrations:migrate
```

## Preparing for the Migration

Before we plunge into this migration, we're actually going to delete the
entire `starship_part` table. If we try to add the new `starship_id` column
and it's not allowed to be null, that's going to cause a hiccup. After
deleting the table and affecting 50 rows, we can proceed with the
migration. And voila! We now have a `starship` table, a `starship_part`
table, and they're linked by a foreign key column. 

## Next Up: Connecting the Dots

So, how do we go about linking a `Starship` object with a `StarshipPart`
object? Buckle up, because that's what we'll explore next. We're about to
bring this starship to life!