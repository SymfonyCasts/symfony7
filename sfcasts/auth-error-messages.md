# Customizing Authentication Error Messages

Let's talk about the different security errors you can get on the login page.

Try to log in as `picard@enterprise.space` with the password `enterprise` - a wrong
one. We get "Invalid credentials." That makes sense.

Now try to log in with an email that *doesn't* exist: `picard@voyager.space` with
password `makeitso`. We also get "Invalid credentials."

Now disable Picard and see what the message is. Run our special command to disable
him:

```terminal
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

We have to wrap this whole thing in double quotes because we use single quotes inside. One row
affected.

Try to log in as Picard with the *right* email - `picard@enterprise.space` - and the
right password, `makeitso`. And... we also get "Invalid credentials."

## Exposing the Real Errors

So any time there's any issue at all, we see "Invalid credentials." That's the
default on purpose: it prevents the *user enumeration* problem, where someone comes
to your login page and checks thousands of emails to see if they exist on the system
or not.

You can disable this. Go to `config/packages/security.yaml` and, at the very top,
add `expose_security_errors: all`:



Go back and try the correct password, `makeitso`. "Account is disabled."
We see the real error now.

Reload the fixtures so Picard is enabled again:

```terminal
symfony console foundry:load-fixtures
```

Now use a wrong password: `enterprise`. "Invalid credentials."
That makes sense - the credentials *were* invalid.

But try an email that doesn't exist: `picard@voyager.space`, password `makeitso`.
"Username could not be found." A different error!

This is what I was saying about user enumeration. Someone could blast this and figure
out who has an account on this site. You might not think that's a big deal, but it
potentially could be. Imagine a site where simply *having* an account is private - a
job board, or a support community for a medical condition. Confirming an address is
registered, hands that fact to anyone who asks. And even on a boring site, a list of
usernames that definitely exist makes a password-guessing attack much cheaper.

## The Middle Ground: account_status

There's an in-between. `expose_security_errors` is actually an enum: look up
`ExposeSecurityLevel`. We can see `none` - that's the default - `all`, which is
what we switched it to, and `account_status`.

Let's try that one:



Jump back and try `picard@voyager.space` with password `makeitso`. "Invalid
credentials." Perfect - it no longer exposes that we have an invalid user.

Now a wrong password: `picard@enterprise.space` with `enterprise`. We still see
"Invalid credentials." That makes sense.

Run our SQL command again to disable Picard:

```terminal-silent
symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"
```

Log in as `picard@enterprise.space` with the correct password, `makeitso`. "Account
is disabled." But use a *wrong* password... and we still see "Account is disabled."

This is maybe not as big a deal as exposing that a username doesn't exist - you
probably won't have tens of thousands of disabled accounts for someone to farm. But
it's still a small security hole that allows some user enumeration.

## Moving the Check to checkPostAuth()

The reason for this is how we wired up our user checker. Find it in
`src/Security/UserChecker.php`.

Look up into the interface: `checkPreAuth()` checks the user *before* authentication,
and `checkPostAuth()` checks the user *after* authentication. So if we move our check
to post-auth, it won't run until authentication occurs - meaning until the password
is checked. This will only show the message to people who have the correct password,
and that's what we want.

Cut this from `checkPreAuth()` and paste it into `checkPostAuth()`:



Now try `picard@enterprise.space` with the correct password, `makeitso`. We see
"Account is disabled." But with the wrong password: "Invalid credentials." Exactly
what we want.

I think that's the sweet spot. Maybe you *don't* want a disabled user to see that
their account is disabled - that's possible, and we showed that earlier. But in most
cases, it's nice to show them so they can figure out why and contact support.

## Customizing the Message

Now let's look at these error messages and how we can customize them. Look at
`templates/security/login.html.twig`: the `messageKey` is being grabbed off the
exception, and it's being translated on the `security` domain. So it's translated!
Even though your site isn't multilingual, you can still use this feature to
customize a single language.

Let's quickly see how this works, copy "Invalid credentials.", go
to the `vendor/` directory, do a find-in-files and paste it in.

First up is `BadCredentialsException`. This is the actual exception being thrown, and
you can see `messageKey` is what's being translated. All the authentication
exceptions use a `messageKey` like this.

We also see it in the `security` XLIFF files - the translations for the different
languages. Look at the English version: it's just being translated one-for-one.

So we can customize it. Go into `translations/` - I do have Symfony's translator
installed - and create a new file called `security.en.yaml`. Paste "Invalid
credentials." in quotes and change the message to "Your email or password is incorrect.":



Jump back to the login page and try again: `picard@enterprise.space` with the wrong
password. There - our new message!

You can do this for all the different exception messages. Try it out for the disabled message!

Next up: sudo mode - confirming a user's password before they perform sensitive
operations.
