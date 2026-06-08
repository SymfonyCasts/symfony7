# Creating the User Class

The first thing we need to actually authenticate someone is a special *user* class. This is a class
that must implement `UserInterface` provided by Symfony. Symfony has a few out of the box implementations
for special cases, but for most applications, you'll want to create your own user class.

## `make:user`

The simplest way to do this is by using the maker-bundle, which provides a little wizard to help.
Head over to the terminal and run:

```terminal
symfony console make:user
```

What should we name this class? The default, `User`, makes a lot of sense.

Do we want to store this user in the database? Yes, we do! This will make our user class
a Doctrine entity.

Next, we need a unique property to identify our users. This will be what Symfony, and in our
case, Doctrine uses to load users from the database. `email` is a common choice, as these should
always be unique to the user. In certain applications, where user details might be public, you might
consider using a unique username instead. Like what GitHub, Slack, and a lot of other social platforms do.

Now we're asked if our `User` needs a password... huh? Is a password really optional?

Kind of. With things like SSO or LDAP, Symfony doesn't actually need
to store or check a password at all. Instead, an external system handles the login, and once it's happy, it
just tells your app, "yep, this user is authenticated." At that point, Symfony trusts the result, and you can
skip storing a password entirely.

But for our purposes, `yes`, we do want to store and verify passwords ourselves.

Ok, if we look at what was changed: a `User` entity and `UserRepository` were created, and our
`security.yaml` configuration was updated.

## Understanding the User Entity

Let's take a look at the `User` entity. In your IDE, open `src/Entity/User.php`.

First thing to notice is the class implements two interfaces. Dig into the first one,
`UserInterface`. This is the core interface every security user must implement. It has
just two methods: `getRoles()`, which returns an array of roles - we'll talk about these
later. The second is `getUserIdentifier()`. This returns a string that uniquely identifies the user.

Dig into the second interface, `PasswordAuthenticatedUserInterface`. This
lets Symfony know our authentication system requires a password and has a single method: `getPassword()`.
If we had said no to the password question earlier, this interface wouldn't be implemented.

Back in `User`, we see the standard Doctrine entity class attribute: `ORM\Entity`. We also have this
`ORM\UniqueConstraint` attribute. This creates a unique index on the `email` column in the database. This
ensures no two users can have the same email. It also improves performance when looking up users by email,
which is important since we'll be doing that *a lot*. Basically every request where a user is logged in.

Scrolling down we have our standard `id` property and the `email` property. The `roles` property is an array (a
list of strings) that's stored as JSON in the database.

Then we have our `password` property and getters and setters for these.

Here's a few interesting things the `make:user` command added.

The `getUserIdentifier()` implementation returns the email property, which, if you remember, is what
we chose as our unique identifier.

`getRoles()` has a bit of extra logic. It grabs the roles that are saved in the database, but also *always* adds
`ROLE_USER` to the list. It's a common convention for every user to have at least this role.

At the very bottom is a magic `__serialize()` method. Whenever a user is serialized for storage in the session, this
method is called. It returns the user properties and values as an array. This funky password stuff replaces the
real password with a hash of the password. This is a best practice to prevent sensitive data, the password, from
being exposed.

## Customizing the User Entity

I want our users to have a name, so let's customize our `User` entity. Remember, our `User` is a Doctrine entity,
so we can use the maker-bundle to add properties to it.

Over in your terminal, run:

```terminal
symfony console make:entity
```

Select our existing `User` entity to modify it. Add a `name` property as a `string`
and with the default `255` field length. Make sure it can't be null in the
database - we want every user to have a name.

Double-check the entity was updated... Here's the getter and setter for `name`... and
up here is the property. Cool!

## Understanding `security.yaml` Updates

The `make:user` command also updated our security configuration. Open `config/packages/security.yaml` to
see the changes.

First, it replaced the in-memory user provider with `app_user_provider` which is set as an `entity` provider
for our `App\Entity\User` class. The `property` to look up users by is set to our choice: `email`.

Down in our `main` firewall, it swapped the old `users_in_memory` provider to our new `app_user_provider`.
This means that when Symfony needs to load a user in our `main` firewall, it'll fetch them from our
`UserRepository`, basically, calling `findOneByEmail()`.

Pretty slick!

## User Migration

Before we forget, at our terminal, let's make a migration with:

```terminal
symfony console make:migration
```

Open the new migration in the `migrations` directory... In the `up()` method, we see the SQL to create the `user` table.
Set the description to: `Add User entity`:

[[[ code('c317523d93') ]]]

Now, run the migration with:

```terminal
symfony console doctrine:migrations:migrate
```

Alright, that worked!

## `UserFactory`

So how do we get users into our app? We'll explore a registration form
later, but for now, let's use a Foundry factory for local development.

Create the factory with:

```terminal
symfony console make:factory
```

Select our `User` entity.

Open `src/Factory/UserFactory.php`. I think we can improve these defaults a bit!

For `email`, use `self::faker()->unique()->email()`:

[[[ code('512c2454b7') ]]]

Calling `unique()` before `email()` ensures that every email generated is unique.

For `name`, use `self::faker()->name()` to generate a realish-looking random name:

[[[ code('bebb8e663f') ]]]

For `password`, instead of a random Faker string, set it to a known value, how about `engage`:

[[[ code('d2270de05a') ]]]

This way, if we create a slew of users, we already know the password for them all!

Finally, we need to create a user in our fixtures. Open `src/Story/AppStory.php`. At the top of the `build()` method, add
`UserFactory::createOne()`. Inside an array, add `'email' => 'picard@enterprise.space'`, `'name' => 'Jean-Luc Picard'`,
and `'password' =>`... How about `makeitso`?

[[[ code('6b8037eb8a') ]]]

`earlgrayhot` would have been too obvious!

Now load the fixtures at your terminal with:

```terminal
symfony console foundry:load-fixtures
```

To double-check our user was added correctly, run:

```terminal
symfony console dbal:run-sql 'select * from user'
```

Nice, here he is! But hmm... we have a serious security issue here... don't worry, we'll fix it soon... but
can you guess what it is?

Next, we'll create a login form so our users can actually log in!
