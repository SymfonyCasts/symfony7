---
challenges:
    - key: why-role-disabled-logs-out
      question: |
          We add `ROLE_DISABLED` to `getRoles()` when a user is disabled. Nothing in the
          app ever checks for that role - so why does adding it log them out?
      answers:
          - On every request Symfony reloads the user and compares them to the copy in the session - roles are part of that comparison, so any change deauthenticates the token.
          - "`ROLE_DISABLED` is a reserved role that the firewall rejects automatically."
          - Adding a role invalidates the signature on the session cookie.
          - The user checker runs on every request and sees the new role.
      explanation: |
          Nothing grants or denies based on `ROLE_DISABLED` - it just has to *differ*
          from what's stored in the session. Symfony refreshes the user from the provider
          on each request and compares the password hash, the roles and the user
          identifier. Any difference and the token is thrown out. It's the same mechanism
          that logs out your other devices when you change your password.
    - key: user-checker-not-per-request
      question: |
          We disabled Picard in the database while he was browsing, and he carried right
          on. Why didn't the `UserChecker` from the last chapter stop him?
      answers:
          - A user checker only runs during authentication - nothing consults it on later requests.
          - It only runs for form logins, not for requests authenticated from the session.
          - "His session cached the old `disabledAt`, so `isEnabled()` still returned true."
          - "`checkPreAuth()` did run, but exceptions it throws are ignored outside of login."
      explanation: |
          `UserCheckerInterface` is called by the authenticator - `checkPreAuth()` before
          the password is verified, `checkPostAuth()` after - and by `switch_user`. Once
          you're logged in, nothing calls it again. Symfony *does* reload the user from
          the database on every request, so the new `disabledAt` is sitting right there
          in memory; it's just that nobody asks the checker about it.
---

# Forcing Logout on Disabled Users

We've successfully made it so that a disabled user can no longer log in. But what
about when they're logged in and browsing the site and an admin decides to disable
their account? The functionality we want is for them to automatically be logged out.
As soon as you're disabled, you should no longer be able to do anything, regardless
of whether you're already logged in.

Let's look at how we can achieve this.

First, go into `src/Story/AppStory.php` and *don't* make Picard disabled by default:
switch this back to `createOne()`:

[[[ code('782e0dd6dc') ]]]

Now reload our fixtures:

```terminal
symfony console foundry:load-fixtures
```

Picard is no longer disabled. Back on the login page, log in as
`picard@enterprise.space` with password `makeitso`. Excellent - we're logged in as
Picard.

## Disabling a Logged-In User

Now I want to mimic an admin disabling his account. Run some raw SQL to handle that:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

I'm using SQLite, so the function to get the current timestamp is `datetime('now')`.
If you're using MySQL or Postgres, that's `NOW()`, so depending on your setup, you
might need to adjust this a little.

Hit enter... one row affected. Perfect. Picard should be disabled now.

Go back to the app in the browser and refresh... and we're still logged in. So that
user checker is *not* called on every request.

## Changing Something the Firewall Watches

There are a couple of ways to force a logout when a user is disabled. The easiest is
to change something about the user that's detected on each request.

You might remember we did this a little bit in the last course: when you change the
password of a user, that user automatically gets logged out. That's how we could handle a
"log out of other devices" feature - just re-hash the password.

So the easiest way to achieve this is in our `User` entity. We have this `getRoles()`
method, and *that* is checked on every request. Add `if (!$this->isEnabled())` and
inside, `$roles[] = 'ROLE_DISABLED';`:

[[[ code('dafa6eb46b') ]]]

Now, this role doesn't really mean anything. All we're using it for is to change the
return value of `getRoles()`, and that's enough to say "hey, this user has changed, so
we have to log them out".

Go back to our app and refresh... sure enough, we were logged out, because that user
changed.

## Does it Work with Remember Me?

One thing I want to double-check is that this works with remember me. It *should*,
but let's be sure. Back at the console, reload the fixtures again:

```terminal-silent
symfony console foundry:load-fixtures
```

Log in as `picard@enterprise.space` with password `makeitso` - and this time, make
sure "Remember me" is checked. We're logged back in.

Now inspect the page, go to the Application tab, find the `PHPSESSID` cookie and
delete it. That leaves only the remember me cookie, so we should trigger the remember
me system. Close this and refresh the page. Hover over the user in the web debug
toolbar: sure enough, we're using the Remember Me token.

Now do our little database trick to disable the user - the same SQL query:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

One row affected. Go back, refresh... and we're logged out. It works with both
session-based *and* remember me authentication.

Next up: the messages we show when there's an authentication error, and how we can
customize them.
