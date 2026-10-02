# Logging in with a Username or Email

I want to add the concept of a *username*: a unique string for each user. You've seen
this on tons of social sites, because you don't want to share people's emails. If you
have a site with a public user profile, you'll probably want a username for it.

And what I want for our users, is to be able to log in with *either* their email or
their username.

## Adding the `username` Field

First, add a `username` column to our `User` entity. Over in the terminal, use the
Maker bundle:

```terminal
symfony console make:entity
```

This is for `User`, the new property is `username`, it'll be a `string` with a field
length of `255`. Can this field be null in the database? No - every user needs a
username. And that's it.

Oops, I don't want to add an `exit` property... I'll just force close the command.

Go to our user entity in `src/Entity/User.php`. I'm just going to double-check
that `exit` property wasn't added... good, it wasn't.

Up at the top, duplicate the unique constraint,
because we want usernames to be unique as well: suffix the name with
`_USERNAME` and the field to `username`. Then duplicate the
`UniqueEntity` validation constraint for `username` with the message "There is
already an account with this username":

[[[ code('a7f565bb56') ]]]

Down on the property itself, add `#[Assert\NotBlank]`:

[[[ code('c9c455046b') ]]]

Now that our entity is fully updated, create a migration with:

```terminal
symfony console make:migration
```

Jump to the `migrations/` folder and find it. For the description, use
`add user.username column`:

[[[ code('9acb161eb5') ]]]

## Fixtures and Forms

Adjust the Foundry factory. In `src/Factory/UserFactory.php`,
duplicate the `email` default, name it `username`, and use the Faker `->userName()` generator:

[[[ code('f9250fc928') ]]]

Over in `src/Story/AppStory.php`, add usernames to our users. For Picard, make his
username just `picard`. And for Janeway, `janeway`. Easy peasy:

[[[ code('9d4961dfd9') ]]]

We've updated our fixtures, so reload them:

```terminal
symfony console foundry:load-fixtures
```

Now adjust the registration form. In `src/Form/RegistrationFormType.php`, after
`email`, add `username`:

[[[ code('e45e6b8b30') ]]]

Then the login template, `templates/security/login.html.twig`. Change the description
to mention the username, change the label to "Email or Username", and change the
input `type` to `text` so we're not forcing someone to put an email there:

[[[ code('24d9ca4f11') ]]]

## The `UserLoaderInterface` Trick

The trick to loading by either email OR username is pretty simple: we need to make a little adjustment to our
user repository.

In `src/Repository/UserRepository.php`, implement another interface:
`UserLoaderInterface`. This one comes from the Symfony Doctrine bridge, and it has a
single method, so implement it down here: `loadUserByIdentifier()`.

It takes a string `$identifier` - in our case, whatever is in the username input on our
login form - and we return `null` if it's not found, or a `UserInterface`. Because
our `User` entity implements `UserInterface`, we can just use normal Doctrine query
builder stuff. `return $this->createQueryBuilder('u')` to start, then `->where('u.email = :identifier')` and
`->setParameter('identifier', $identifier)`. Then `->getQuery()->getOneOrNullResult()` to finish it off.

This will work exactly like our current system does. But to add the ability to use
*either* email or username, just add `->orWhere('u.username = :identifier')` to the query builder.

[[[ code('db67759d02') ]]]

That's all we have to do here. But now that our user repository implements
`UserLoaderInterface`, there's one more thing. In `config/packages/security.yaml`, up
at the top under `app_user_provider`, we need to remove the `property` key:

[[[ code('e23325902d') ]]]

That makes it fall back to using our repository's `loadUserByIdentifier()` method.

## Trying Both

Go back to the app and refresh our login page. Nice - "Email or Username".

Try logging in as just `picard`, password `makeitso`. We're in!

Down in the web debug toolbar, it's still showing the email, not the username. That's
because our `User` entity implements `UserInterface`, which has `getUserIdentifier()`
- and we're using the email for that. So anywhere a user identifier is shown, it's
going to be the email. If you have public-facing stuff, don't use the user identifier
to publicly show who someone is: use `getUsername()` instead.

To be doubly sure it works both ways, log out, then log in again as
`picard@enterprise.space` with password `makeitso`. There we go - it works both ways!

Next up: let's improve our user impersonation system.
