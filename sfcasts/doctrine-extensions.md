# Auto Slug and Timestamps with Doctrine Extensions

We added three new fields to `Starship`: `slug`,
`updatedAt`, and `createdAt`. But now our fixtures don't load! The reason is simple:
all 3 fields are required in the database, but `StarshipFactory` doesn't set them.
We could add these, but we shouldn't have to. In a perfect world `slug` would be
automatically generated from the `name`, `updatedAt`:
set to the current time when the entity changes and `createdAt`: set to the current
time when the entity is created.

## DoctrineExtensions

And there's a package that can do this `DoctrineExtensions`! In your terminal,
run:

```terminal
composer require stof/doctrine-extensions-bundle
```

This bundle has a recipe... but it isn't considered official. It's a third-party,
or *contrib* recipe. These are typically fine, just know that contrib recipes are
added by the community and not policed by Symfony's core team.

Scroll up to see what we have. The most important packages are `gedmo/doctrine-extensions`,
which contains the real logic, and `stof/doctrine-extensions-bundle`, that
integrates that with Symfony. No need to worry about the other stuff.

Run:

```terminal
git status
```

to see what the recipe added. It configured the bundle and added a new config file.
Cool! For this bundle, we *do* need to edit this config to enable the extensions
where each extension is like a superpower for your entities.

## Enabling Extensions

Open `config/packages/stof_doctrine_extensions.yaml`. Below `default_local`
add a new key: `orm:`, then `default:` and inside that, enable 2 superpowers, I mean extensions:
`timestampable: true` and `sluggable: true`:

[[[ code('b6789ad642') ]]]

These are now activated in general, but we need to a bit more config to bring them
to life for the `Starship` entity. Open that up again.

## Using Extensions

Above the `$slug` property, add a new attribute: `#[Slug]`, importing the class from
`Gedmo\Mapping\Annotation`. Inside, add `fields:` set it to an array containing
`name`:

[[[ code('739d2afd4f') ]]]

This tells the extension to generate the slug from the `$name` property when the entity
is first persisted.

Above `$updatedAt`, add `#[Timestampable]` with `on: 'update'` so it knows to
set this field to the current time *on entity update*:

[[[ code('d3aaa7e760') ]]]

Same for `$createdAt`, but with `on: 'create'`:

[[[ code('55bf8b6edb') ]]]

## Reloading the Fixtures

Let's try it! At your terminal, run:

```terminal
symfony console doctrine:fixtures:load
```

And... it *worked*! Run our SQL query to see the values:

```terminal-silent
symfony console doctrine:query:sql 'SELECT name, slug, updated_at, created_at FROM starship'
```

Yeah! Our `slug` is generated from the `name`, and `updatedAt` and `createdAt` are set
to the timestamp of when the entity was created. Doctrine considers the
initial save also as an update: that's why `updatedAt` and `createdAt` have the same value.

## Slugs are Kept Unique

Scroll down a bit. Notice these slugs are suffixed with `-1`? What's that about?
This is because our `slug` field is unique but our `name` is not. We have some starships,
like `Lunar Marauder` here, that have the *same name*. The slug extension is smart enough
to detect this, and automatically add a numeric suffix (`-1`, `-2`, etc.) to keep them
unique. Smart!

Now that we have a unique, human-readable slug for our starships, let's use *it* instead
of this ugly `id` in our URLs. We'll also use something called *Controller
Value Resolvers* to make our controllers high-tech! That's next! 
