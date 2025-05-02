# Many To One

Coming soon...

Okay, we have `Starship` and `StarshipPart` in the database. The question
now is, how do we connect them? Each `StarshipPart` should belong to a
`Starship`. To create a relation, we can once again use our `make:entity`
command. So at your terminal, run:

```terminal
symfony console make:entity
```

And if you think about it, in the database, eventually we're going to want
a `starship_id` column added to the `starship_part` table. That's not how
we're going to do things in Doctrine, but we are going to still update the
`StarshipPart` entity. So put in `StarshipPart` here. Now, for this new
field, should we call it `starshipId`? No, and this is where Doctrine
really starts to shine. We don't think about ids, instead we think about
objects, like how a `StarshipPart` object is related to a `Starship`
object. So we're editing the `StarshipPart` class so that we can give it a
`starship` property. So put `starship` here. In this case, for the field
type, use a fake one called `relation`. This will kind of start a little
wizard to help us figure out which relation we need. What class are we
relating to? We are relating a `StarshipPart` to a `Starship`. Then it kind
of walks you through the four different types of relationships so you can
figure out which one you need. If you look in this, ours, the one we want
is `ManyToOne`, where each part relates to one starship, and each starship
can have many parts. So let's say `ManyToOne`. Then is the `starship`
property allowed to be null? Say no to this because we want every part to
be related to a starship. This is really interesting. Do you want to add a
new property to `Starship` so that we can say `$starship->getParts()`? This
is optional, but that actually looks pretty convenient to me, a really
quick way to get the parts for a starship, so I'm going to say yes. We can
call this new field anything, but actually let's call it `parts`. It's a
little bit shorter. Then for orphan removal, say no. We'll talk about what
that is later. Finally, hit enter to exit the command. I committed before
recording, so I'm going to `git status`. Interesting, it updated both
entities. Let's check those out. In `StarshipPart`, the first thing is it
added the `starship` property. That's pretty normal. The biggest difference
is that instead of `ORM\Column`, it's `ORM\ManyToOne`. It also added a
`getStarship()` method and a `setStarship()` method. Over in `Starship`, it
added a `parts` property with a `ORM\OneToMany`. Also, if we scroll down
here, it added a `getParts()` method, of course. Instead of a `setParts()`
method, it added an `addPart()` method and a `removePart()` method. These
are just a little bit more convenient than `setParts()`, especially when
you're working with Symfony's form system or serializer system. Finally, up
in the constructor, it says `$this->parts = new ArrayCollection()`. You need
this, but it's a minor detail. This object looks and acts like an array.
We're going to be able to `foreach` over `$this->parts` just like it was an
array. One thing I do want to mention here is that the `OneToMany` and
`ManyToOne` are actually the same one relation just seen from two different
sides. If a part belongs to one starship, then a starship has many parts.
We've really just added one relationship here. It's that you can see it
from two different sides. Since `make:entity` added new properties to
`Starship`, we need— Let's spin over and make that migration:

```terminal
Symfony console make:migration
```

Awesome. Go check that out. This is one of my favorite migrations. Check it
out. It altered `starship_part` and added a `starship_id` column, which is
a foreign key over to `starship`. This is because Doctrine is smart. We
added a `starship` property to `StarshipPart`, but it knows that the column
should be called `starship_id`. It's even going to help us set that as
we'll see in the next chapter. Let's migrate:

```terminal
Symfony console doctrine:migrations:migrate
```

Before we run this migration— Because we have existing— We're actually
going to delete the entire `starship_part` table. Because if we try to add
this new `starship_id` column and it's not allowed to be null, that's going
to fail. 50 rows affected. Perfect. Now let's migrate. That worked
perfectly. We now have a `starship` table, a `starship_part` table, and
they have a foreign key column between them. How do we relate a `Starship`
object to a `StarshipPart` object? Let's look at that next.