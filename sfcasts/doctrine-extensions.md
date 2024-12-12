# Auto Slug and Timestamps with Doctrine Extensions

In the last chapter, we added three new fields to our `Starship` entity: `slug`,
`updatedAt`, and `createdAt` but we can no longer load our fixtures. This is
because our `StarshipFactory` doesn't set these new fields. We could add them
but... These fields require some logic: `slug` needs to be generated from the `name`. `updatedAt`:
set to the current time when the entity changes and `createdAt`: set to the current
time when the entity is created.

There is a package that can handle this logic: `DoctrineExtensions`! In your terminal,
install the following bundle:

```terminal
composer require stof/doctrine-extensions-bundle
```

This bundle has a recipe... but it isn't considered official. It's a third-party,
or *contrib* recipe. As an extra measure of security, Symfony will ask you to
confirm running the recipe. I know this recipe is safe, so I'll hit `yes` to allow
it.

Scroll up to see what we have. The most important packages are `gedmo/doctrine-extensions`,
which contains the actual extensions, and `stof/doctrine-extensions-bundle`, that
integrates them with Symfony. The others are just dependencies we don't have to
worry about them.

Run:

```terminal
git status
```

To see what the recipe added. It configured the bundle and added a new config file.
For this bundle, we need to edit this config to enable the extensions we want.
In your IDE, open `config/packages/stof_doctrine_extensions.yaml`. Below `default_local`
add a new key: `orm:`. Inside, add `default:` and inside that, enable the following
extensions: `timestampable: true` and `sluggable: true`. This is telling the bundle
to enable these two extensions for the *default ORM entity manager*. An advanced Doctrine
setup could have multiple entity managers, but our standard setup has just one,
called `default`.

These extensions are now enabled, but we need to configure our `Starship` entity properties
to use them. Open `src/Entity/Starship.php`.

Above the `$slug` property, add a new attribute: `#[Slug]`, importing the class from
`Gedmo\Mapping\Annotation`. Inside, add a `fields:` argument set it to an array containing
a single element: `name`. This tells the extension to generate the slug from our `$name`
property when the entity is first persisted.

Above `$updatedAt`, add the `#[Timestampable]` attribute. Inside, add `on: 'update'`, which
means, automatically set this field to the current time *on entity update*. Same for
`$createdAt`, but with `on: 'create'` which means, set to the current time
*on entity creation*.

Now let's reload our fixtures! In your terminal, run:

```terminal
symfony console doctrine:fixtures:load
```

And... it *worked*! Run our SQL query to see the values:

```terminal
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

Yeah! Our `slug` is generated from the `name`, and `updatedAt` and `createdAt` are set
to the same value, the entity creation timestamp. Note: entity creation is considered
an *update*, that's why `updatedAt` and `createdAt` have the same value.

Scroll down a bit. Notice these slugs that are suffixed with `-1`? What's that about?
This is because our `slug` field is unique but our `name` is not. We have some starships,
like `Lunar Marauder` here, that have the *same name*. The slug extension is smart enough
to detect this, and automatically add a numeric suffix (`-1`, `-2`, etc.) to ensure the
slug is unique.

Now that we have a unique, human-readable slug for our starships, let's use it instead
of this ugly `id` in our URLs. We'll also use something called *Controller
Value Resolvers* to make our controllers high-tech! That's next! 
