# Many-to-Many but with Extra Data

ManyToMany relations are the *one* place in Doctrine where we have a
table in our database - `starship_droid` - but no corresponding
entity in our app.

But there's a catch: we *can't* add extra *columns* to that join table. Like,
what if we wanted to track *when* a droid was assigned to a starship? To do
that our join table would need an `assignedAt` column. But we can't add that!

## When Many-to-Many isn't Enough

The solution is to roll up our sleeves and start handling things more manually.

We'll *stop* using the many-to-many relationship entirely. Instead, we're
going to generate a new entity that represents the join table. First,
undo the many-to-many relationship (but only worry
about the properties, not the methods). In `Starship`, wave goodbye to the
`droids` property:

[[[ code('08ca1145f3') ]]]

And over in `Droid`, do the same for the `starships` many-to-many property:

[[[ code('30fee2522b') ]]]

Clear out the constructor in both.

Find your terminal and run:

```terminal
symfony console doctrine:schema:update --dump-sql
```

This shows you what your migration *would* look like if you generated
it right now. It's what we expect: no more `starship_droid` table.

## Creating a New Join Entity

But don't generate that migration just yet! We *do* want the join table, 
but now we need to create an entity to represent it. Run:

```terminal
symfony console make:entity StarshipDroid
```

`DroidAssignment` might be a more fitting name, but `StarshipDroid`
helps us visualize what we're doing: recreating the same exact database
relationship via two `ManyToOne`s

Add `assignedAt` along with two more properties to create
relationships from this join table to `Starship` and `Droid`. 

These are going to be `ManyToOne` relationships, and they'll connect
`StarshipDroid` to `Starship` and `Droid`.

## The Migration that Does Nothing

*Now*, generate that migration:

```terminal
symfony console make:migration
```

And check it out. It might seem like there are a lot of changes, but look
closely: it's just dropping the foreign key constraints, adding a
primary key, and recreating the foreign key:

[[[ code('ab36b5fa82') ]]]

So, in the end, this migration doesn't change anything *real* in the database. 

Run it with:

```terminal
symfony console doctrine:migrations:migrate
```

And boom!

> Column `assignedAt` cannot contain `null` values. 

Doctrine is throwing a tantrum because of the existing rows in the
`starship_droid` table. We can pacify it with a default value. Update the
migration manually to say `DEFAULT NOW() NOT NULL`:

[[[ code('da05bc25ab') ]]]

## The Finishing Touches

Let's add a final touch to `StarshipDroid`:

[[[ code('c28ba6cf5b') ]]]

This `assignedAt` isn't really something we should have to worry about.
Create a constructor and set it automatically: `$this->assignedAt = new \DateTimeImmutable();`:

[[[ code('740c1d4a35') ]]]

Hold up, because this is huge! We now have the *exact* same relationship
in the database as before. But since we've taken control of the join entity,
we can add new fields to it. Next, we'll see how to assign droids to Starships
with this new entity setup. And eventually, we'll get fancy and hide this
implementation detail entirely!
