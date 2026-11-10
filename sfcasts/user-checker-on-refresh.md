---
challenges:
    - key: disabled-role-hack
      question: |
          Before 8.2, we added a fake `ROLE_DISABLED` in `getRoles()` whenever a user was
          disabled. Nothing ever checked for that role, so why did it log disabled users out?
      answers:
          - On each request, Symfony compares the user in the session to a fresh copy from the database, and different roles count as a changed user
          - Symfony automatically denies access to any user with a role starting with `ROLE_DISABLED`
          - Our user checker ran on every request and saw the new role
          - The `access_control` rules require users to have only the roles listed in `role_hierarchy`
      explanation: |
          When the user is refreshed from the session, Symfony checks whether they've changed,
          and their roles are part of that check. Adding a role made the user look different,
          so they were logged out. With `user_checker_on_refresh`, our user checker gets a say
          on every request instead, and the hack can go.
    - key: user-checker-adds-to-refresh-check
      question: |
          With `user_checker_on_refresh: true` turned on, what happens to Symfony's built-in
          check that compares the user in the session with the fresh copy from the database?
      answers:
          - It still runs, and our user checker adds one more reason to log the user out
          - It's replaced by our user checker, so only `checkPostAuth()` decides
          - It's skipped whenever the user checker passes
          - It only runs when the user checker throws an exception
      explanation: |
          Symfony still compares the session user with the refreshed one and passes that
          verdict along in `CheckRefreshedUserEvent`. The built-in listener runs your user
          checker and, if it throws, marks the user as changed. It only *adds* a way to log
          users out, so nothing you relied on before goes away.
---

# Rechecking Users with user_checker_on_refresh

Our app's users can be disabled. Here's how it works: the `User` entity has a
`disabledAt` timestamp field. When it's `null`, the user is enabled. When it has a value,
they're disabled. I've already disabled our Picard user so we can see what that looks like. Head
to the login page and log in as `picard@enterprise.space`, password `makeitso`... and we
see "This account is disabled."

## The User Checker

So how does this work? In `config/packages/security.yaml`, we set a `user_checker` - a
custom one we built for our app. Jump into `src/Security/UserChecker.php`. After the
password is checked during login, `checkPostAuth()` checks whether the user is enabled
and, if they're not, throws a `DisabledException`. That's what stops them from logging
in.

That's great for login, but the user checker *only* runs during login. So what about a
user who gets disabled while they're already logged in? Let's see what happens now. Log
in as `janeway@starfleet.space`, password `coffeeblack` - she isn't disabled. We're in,
and if we refresh, we're still good.

## Disabling a Logged-In User

Now let's pretend an admin disables her account. We'll do it right from the terminal:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'janeway@starfleet.space'"
```

One row affected. Note that this syntax is for SQLite - if you're using a different
database, you'll need to adjust the query.

Back in the browser, refresh... and we're logged out! So it *does* work, but we're using
a bit of a hack to make it happen. Remember, the user checker isn't called on every
request. In our `User` entity, find `getRoles()`. We're adding a dummy role here:
if the user isn't enabled, we add `ROLE_DISABLED`. It isn't used anywhere else. On every
request, the user stored in the session is compared with a fresh copy loaded from the
database, and the roles are one of the things compared. If they're different, the user
gets logged out. That's how we sneak around it.

## Hello `user_checker_on_refresh`

In Symfony 8.2, you can have your user checker run on every request instead. That means
we can delete this hacky code entirely:



How? Symfony 8.2 dispatches a new `CheckRefreshedUserEvent` right after the user is
refreshed, and a listener can use it to mark the user as changed, which logs them out.
Symfony ships with one, `RefreshedUserCheckerListener`, that runs your user checker and,
if it throws an exception, marks the user as changed. We just need to turn it on.

In `security.yaml`, add a new 8.2 option to our `main` firewall:
`user_checker_on_refresh: true`:



Now the user checker runs every time the user is refreshed - on every request. One thing
to be aware of: if you enable this, keep your user checker *fast*. Doing a bunch of
database lookups in there would slow down every single authenticated request.

## Trying It Out

Let's try it! Reload the fixtures so nobody is disabled:

```terminal
symfony console foundry:load-fixtures
```

Now log in again as `janeway@starfleet.space`, password `coffeeblack`. We're in! Now run
our query again to disable her:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'janeway@starfleet.space'"
```

Refresh and... we're logged out! The user checker was called, and we no longer need that
little hack to make the refreshed user look different.

Next: Symfony 8.2 adds a *real* "sudo mode"!
