# Starship Entity

We have a database and can connect to it but... it doesn't have any tables!

The Doctrine ORM uses PHP classes to represent tables in the database. Doctrine
calls these classes "entities", but they're really just standard, plain old PHP
classes.

In our StarShop app, we have the concept of Starships. This is a perfect candidate
for our first entity. What does a Starship look like? If we open up
`src/Model/Starship.php`, we can see that a Starship has an `id`, `name`, `class`,
`captain`, `status`, and `arrivedAt`. These are the columns we want in our database
table for a Starship.

We could create this entity by hand, but since we have the MakerBundle installed,
we're going to use the `make:entity` command. Run:

```terminal
symfony console make:entity
```

For the entity name, use `Starship`. We're not using Symfony UX Turbo, so answer
`no` to that question. This created a `Starship` class in the `Entity` directory
and a `StarshipRepository` class in the `Repository` directory. We'll talk about
repositories later.

The command is asking us to add properties to our entity. Jump back to are Starship
*model* to see what properties we need. `id` is a special field that the Maker
will add for us so start with `name`. Field type? Use the default: `string`. Field
length? Use the default: `255`. Can this field be null in the database? No, every
Starship needs a name.

Next is `class`, it'll be the same as `name`. Now `captain`, also the same as `name`.

The next field will be `status`. Doctrine defaults to a `string` for this field, but...
if we look at our `Starship` model, we see that `status` is actually an _enum_. How
can we map this to a column? Back in the terminal, hit `?` to see all the different
types we can add. At the bottom, we see an `enum` type. Use that. `Enum class`? This
is the full class name of our `StarshipStatusEnum`: `App\Model\StarshipStatusEnum`.

Can this field store multiple values? No, a Starship can only have one status at a
time. Can this field be null? No.

The last property is `arrivedAt`. Notice how the Maker defaults to `datetime_immutable`
instead of `string`. This is because we suffixed our property name with `At`. Hit enter
to use this type. Can this field be null? No.

Let's take a look at our newly minted `Starship` entity. In the `src/Entity/` directory,
open `Starship.php`. This class is just a standard PHP class but check out these
PHP attributes:

The `#[ORM\Entity]` attribute on the class tells Doctrine that this a table in our
database. The name of the table can be customized, but we'll use the default which is
the _snake cased_ class name: `starship`.

Now look at the properties, they have `#[ORM\Column]` attributes. This attribute
tells Doctrine that these properties are columns in our table. This attribute allows
us to customize the column, but the defaults are usually good.

Doctrine is smart and guesses the column type from the type hint. For example, `id`
will be an integer type, `name` will be a string type, and `arrivedAt` will be a
timestamp type.

The `id` property has a few extra attributes: `#[ORM\Id]` marks it as the primary key
for the table and `#[ORM\GeneratedValue]` tells the database to auto-generate this value.
It'll be an auto-incrementing integer.

We can remove the `length` arguments from the string columns as this is the default.

Check out our `status` property. This is a special column because it's an enum. You
may be aware that some databases have native support for enums, but because not all
do, Doctrine just uses a string or an integer for the enum. The type is determined
based on the type of enum class we have. `StarshipStatusEnum` is a string-backed enum,
so the type will be string. We can actually remove this `enumType` argument from the
attribute as it'll be guessed from the type hint. Cool!

Down below, the maker generated getters and setters for all our properties. Our
old `Starship` model had two extra methods: `getStatusString()` and
`getStatusImageFilename()`. Copy and paste them to the bottom of our new `Starship`
entity.

Entity done! Let's make sure we didn't mess anything up.

Over in the terminal, run:

```terminal
symfony console doctrine:schema:validate
```

The first part shows our mapping is correct. This means that Doctrine recognizes all
of our attribute metadata as accurate.

The error at the bottom tells us that our database is out of sync.

Just having the entity class doesn't mean it exists as a table in the database. For
that, we need to have Doctrine create the table _schema_ for us. The schema is like a
blueprint for what your table looks like in the database.

There are a few ways to get the table into the database, but we're going to use
migrations. That's next!
