# Disabling Users with a Custom UserChecker

Let's add the concept of *disabled* users. These are users that have some kind of
flag on them marking them as disabled. When they're disabled, they're not able to log
in. And if they *are* logged in, they'll be logged out immediately.

You can think of this as a soft delete. Deleting the user from our database would
effectively do the same thing, but disabling them allows you to re-enable them later
for whatever reason.

## Adding the `disabledAt` Flag

The first thing we need is a flag on our `User` entity. Add it with the Maker bundle:

```terminal
symfony console make:entity
```

Choose `User`. Now, for the property name... we *could* add an `enabled` boolean
flag, but what I like to do with these types of flags is make them timestamps. If
they have a date time, that means true. If it's null, that means false.

Use `disabledAt`.

This gives you a little bit of an audit. With a true/false, you can't see *when* they
were disabled. But with this, `disabledAt` is the exact time that they were disabled,
so you get more visibility into when the status changed.

Make it a `datetime_immutable`. Can this field be `null`? Yes! Because remember, a
`null` value means that they're enabled. And... that's it.

Now quickly make the migration:

```terminal
symfony console make:migration
```

Jump back to our IDE and find the migration. For the description, put
`add user.disabledAt column`:

[[[ code('2e404a663f') ]]]

Over in `src/Entity/User.php`, sure enough, there's our `disabledAt` property, and
it's `null` by default - exactly what we want. When users are created, we want them
to be enabled. Down here we have the setter and getter. I'm going to add one helper
method to make it easier to ask whether they're enabled or disabled,
`public function isEnabled(): bool` and inside, `return null === $this->disabledAt;`:

[[[ code('235cffb08d') ]]]

## A `disabled()` State for Foundry

Foundry gives you the ability to add *states* to your factory, and it's pretty simple
to do. I want to add a `disabled` state to our user factory so we can create users
who are disabled when they're created.

Go to `src/Factory/UserFactory.php` and add `public function disabled(): self`.
Inside: `return $this->with(['disabledAt' => new \DateTimeImmutable()]);`:

[[[ code('6df4503981') ]]]

Now make Picard a disabled user by default. Go to `src/Story/AppStory.php`. For
Jean-Luc Picard, `UserFactory::createOne()` is directly creating the user. To add a
state, change `createOne` to `new()`, call `disabled()` on it, then call `create()`:

[[[ code('6c0c8589dc') ]]]

We could have kept the `createOne()` and set the `disabledAt` property in the array,
but using states is reusable and, I think, more readable.

Back to the console, and reload the fixtures:

```terminal
symfony console foundry:load-fixtures
```

Picard should be flagged as disabled. But that flag doesn't *do* anything yet - have to
check for it...

## Creating the `UserChecker`

And that's exactly what a `UserChecker` service is for!

Create a new class in our `src/` directory. Call it
`UserChecker`, and the namespace will be `App\Security`, so it lives in a `Security`
sub-namespace.

Make it `final` and add `implements UserCheckerInterface`. This interface has two
methods you need to implement. There's `checkPreAuth()`, which runs when the correct user has
been fetched, but *before* it checks that the password is valid. And
there's `checkPostAuth()`, which is called *after* the credentials have been verified to
be correct.

We're not going to use `checkPostAuth()`, so clear that out. Inside `checkPreAuth()`,
first `if (!$user instanceof User)`, then `return`. Below that,
`if (!$user->isEnabled())`, and inside `throw new DisabledException();`:

[[[ code('23b07073ee') ]]]

## Account Status Exceptions

`DisabledException` is an *account status* exception. Dive into it and you can see
that it extends `AccountStatusException` - this is the type of exception
you throw in a user checker, in either the pre-auth or the post-auth method.

You can implement your own account status exception, but there are a whole bunch of
out-of-the-box ones. `CredentialsExpiredException`, for instance, could be thrown if
you have a system that expires credentials after 30 or 60 days. There's also
`AccountExpiredException` and `LockedException`. The one we're using is
`DisabledException`.

There's also `CustomUserMessageAccountStatusException` - a mouthful - which lets you
throw a generic status exception. I would choose one of the specific ones or build
your own, so it's more clear what the actual account status is.

## Enabling the User Checker

This service exists, but it doesn't actually do anything yet: it's not auto-configured
in any way. We need to enable it in our security config. Open
`config/packages/security.yaml` and, under our `main` firewall, add
`user_checker: App\Security\UserChecker`:

[[[ code('846c7c6c58') ]]]

That's the service ID. Remember, when Symfony autowires classes, it makes the class
name the service ID.

And that should be it! Try to log in as Picard. Go to the login page, enter
`picard@enterprise.space` and `makeitso`.

We get the generic "Invalid credentials." In this case, I *did* use the correct password.
But because Picard is disabled, he's not able to log in: we threw that account status exception.
We'll talk about customizing the error messages a bit later.

So right now, disabled users can't log in. But what happens if they *are* logged in
and they're disabled during their session? Next: let's make sure a disabled user is
logged out the moment they're made disabled.
