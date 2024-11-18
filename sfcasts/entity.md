# Starship Entity

We have a database and can connect to it but... it doesn't have any tables!

The Doctrine ORM uses PHP classes to represent tables in the database, like if you
need a table for products, you create a `Product` class. Doctrine
calls these classes "entities", but they're really just standard boring PHP
classes. I like boring!

In our StarShop app, we need to track Starships... so we need a `Starship` table...
so we need a `Starship` entity class. What does a Starship look like? In the last
tutorial, we created a `Starship` model class in the `src/Model` directory. Open
it up. We decided that each Starship has an `id`, `name`, `class`,
`captain`, `status`, and `arrivedAt`.

This is *almost* a Doctrine entity: it's just missing some config that helps Doctrine
understand how to map this class to a table in the database. We could easily add
that by hand. But... we have a tool that can do this for us: the MakerBundle!

Run:

```terminal
symfony console make:entity
```

For the name, use `Starship`. We're not using Symfony UX Turbo, so answer
`no` to that question. This already created a `Starship` class in the `Entity/`
directory and a `StarshipRepository` class. We'll talk about that later.

But we're not done! This command is awesome: it interactively asks what properties -
or columns if you want to think that way - our entity needs. Jump back to the Starship
*model* to see what we need. MakerBundle will add `id` automatically, so 
skip to `name`. Field type? Use the default: `string`. Field
length? `255` is fine. Can this field be null in the database? No, every
Starship needs a name.

Next is `class`, it'll be the same as `name`... then `captain` is also a simple `string`.
Next: `status`. Doctrine defaults to a `string`, but...
look at our `Starship` model, `status` is an _enum_. How
can we map this to a column? Back in the terminal, hit `?` to see all the different
types we can add. At the bottom... `enum`! Use that. `Enum class`? Use
the full class name of our enum: `App\Model\StarshipStatusEnum`.

Can this field store multiple values? No, a Starship can only have one status at a
time. Can this field be null? Nope!

Finally, add `arrivedAt`. Cool! Maker defaults to `datetime_immutable`
instead of `string`. This is because we suffixed our property name with `At`. Smart!
Can this field be null? No.

Let's take a look at our newly minted `Starship` entity: in `src/Entity/`.

Notice: this is a standard PHP class with properties... and one special thing: some
PHP attributes:

The `#[ORM\Entity]` attribute on the class tells Doctrine that this *not* just a
boring PHP class, but an entity that should be mapped to a table in our
database. The table name *can* be customized, but we'll use the default which is
the _snake cased_ class name: `starship`.

Check out the properties: each has `#[ORM\Column]`. This tells Doctrine that these
properties are *columns* in our table. For the type,
Doctrine is smart and guesses from the type hint. For example, `id`
will be an integer type, `name` will be a string type, and `arrivedAt` will be a
timestamp type. Nice!

`id` has a few extra attributes that mark it as the primary key and tells the database
to auto-generate this as an auto-incrementing integer.

Oh, and we can remove the `length` argument from the string columns: this is the default.

The `status` property is a `StarshipStatusEnum` type but Doctrine will
store this as a string in the database. Cool! We can actually remove
the `enumType` argument: Doctrine can guess that from the property type too!

Down below, the maker generated getters and setters for all our properties. Our
old `Starship` model had two extra methods: `getStatusString()` and
`getStatusImageFilename()`. Copy those from the model class... and 
at the bottom of the entity class, paste!

Entity done! We can even double-check our work. At your terminal, run:

```terminal
symfony console doctrine:schema:validate
```

This means that Doctrine sees and can read our attributes.
Then... our database is out of sync?

We have an entity class... but we don't actually have the `starship` table in the database.

There are a few ways to get the table into the database, but the best way is
migrations. That's next!
