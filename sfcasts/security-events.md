# Security Events: Tracking the Last Login

The Symfony security system dispatches a bunch of events that you can listen and react
to. Here are the most common ones:

The `InteractiveLoginEvent` is called after a user successfully authenticates
*interactively*, like submitting a login form.

The `LoginFailureEvent` is called after a failed authentication attempt. You can use this to
log failed attempts or customize the failure response.

The `LogoutEvent` is called before a user is logged out. You can use this to perform cleanup
or customize the logout response.

The `SwitchUserEvent` is called after switching to or exiting an impersonated user.

There are more events than just these, but they're for more advanced scenarios.

What I want to do in our app is track the *last login* timestamp for each user. This
is a pretty common feature.

## Adding the `lastLogin` Field

The first thing we need is a field on our `User` entity to store that timestamp. Over
in your terminal, run:

```terminal
symfony console make:entity
```

This will be for our `User`, so we're modifying it. Call the field `lastLogin`. For the
field type, hit `?` to see all the options... and use `datetime_immutable`. Can this
field be null? Yep - if someone has never logged in, it'll be null. And that's it.

By the way, you should always use `datetime_immutable` instead of `datetime` for your timestamp
fields. The immutable version is safer and less error-prone: if you modify a mutable date in
place, Doctrine won't see it as a change and your update will never be saved.

Now generate the migration:

```terminal
symfony console make:migration
```

Jump back to your editor and find that migration. Here it is. Set the description to
`add last login to user`.

Back in the terminal, run it:

```terminal
symfony console doctrine:migrations:migrate
```

Perfect!

## Showing It in the User Admin

When an admin is on the user listing page, I want to show this as a column. Open
`templates/user_admin/index.html.twig`... and find the "Name" table header. Duplicate
it, and call this one "Last Login".

Then, down below, where we're outputting the user's name, duplicate that line too.
Inside, output `{{ user.lastLogin ? user.lastLogin|date('Y-m-d H:i:s') : 'Never' }}`.
This first checks if `user.lastLogin` has a value. If it does, it formats it with the `date`
filter. If it doesn't, it prints "never".

Jump back to that listing in your browser and refresh. Ok, that kind of blew up our actions column -
but let's not worry about that right now. We can see the "Last Login" column, and it says
"never" for each user.

## `make:listener`

Now we need an event listener... and there's a maker for that. Over in your terminal, run:

```terminal
symfony console make:listener
```

Call it `LastLoginListener`. This gives us a big list of events to choose from... and
the one we want is `security.interactive_login`.

This is the event that's dispatched after a user *actively* logs in. Like submitting our
login form. It is *not* dispatched when someone is authenticated another way, such as from their
remember me cookie. And for a "last login" timestamp, that's exactly what we want:
being remembered across sessions isn't really *logging in*.

Go find the new class in `src/EventListener/LastLoginListener.php`.

One thing we actually *don't* need here is the `event` argument on the
`#[AsEventListener]` attribute: Symfony can determine the event from the type-hint on the
method.

Before writing any logic, double-check that this is in fact registered. Over in your
terminal, run:

```terminal
symfony console debug:event
```

This lists all of our listeners and the event they listen to. The last one here is
`security.interactive_login`... and sure enough, there's our `LastLoginListener`. We're good to go.

## Setting the Timestamp

Now wire this up. First, add a constructor... and inject
`private EntityManagerInterface $em`.

Down here, to see what we can grab off the event, jump into `InteractiveLoginEvent`.
We can get the `Request`... and we can get the authentication token - remember, that's an object that wraps the
user. So grab the user from it:
`$user = $event->getAuthenticationToken()->getUser();`

This *might* be null. It probably never will be - that wouldn't make a lot of sense
for a login event - but it's technically possible. So add `if (!$user instanceof User)`, pulling
in our `User` entity, and just `return`. Do nothing.

And if we *do* have a user, set the timestamp:
`$user->setLastLogin(new \DateTimeImmutable('now'));`

The last thing we need is to persist that change to the database:
`$this->em->flush();`

## Trying It Out

Try it out! We're logged in as Janeway right now, so first log out... then log back in
as `janeway@starfleet.space`, password `coffeeblack`.

Now jump to that `/admin/user` listing again... and there we go! Our last login was
tracked and persisted. Pretty cool feature.

Next up: creating a user registration system!
