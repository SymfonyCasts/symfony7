---
challenges:
    - key: access-control-roles-or
      question: |
          In `security.yaml`, our `access_control` rule for `^/admin` needs the user to be
          an admin *and* fully authenticated. Why doesn't this do the job?

          ```yaml
          access_control:
              - { path: ^/admin, roles: [ROLE_ADMIN, IS_AUTHENTICATED_FULLY] }
          ```
      answers:
          - A list of attributes is an **or** - any fully authenticated user, admin or not, would get in.
          - "`access_control` accepts only one role; a list is a configuration error."
          - "`IS_AUTHENTICATED_FULLY` isn't allowed under `roles:`, only inside an expression."
          - The list *is* an and, but it's evaluated before the user is loaded, so it always fails.
      explanation: |
          Attributes listed on an `access_control` rule are OR'd: access is granted the
          moment one voter says yes, so any logged-in non-admin sails through on
          `IS_AUTHENTICATED_FULLY` alone. That ambiguity is exactly why Symfony 8.2
          deprecates putting several roles on one rule. An `allow_if` expression says what
          you mean: `is_granted('ROLE_ADMIN') and is_granted('IS_AUTHENTICATED_FULLY')`.
    - key: fully-vs-remembered
      question: |
          Janeway closed her browser days ago. She's back today and the remember-me cookie
          logged her in automatically, without her typing anything. She opens
          `/admin/user`, which now requires `IS_AUTHENTICATED_FULLY`. What happens?
      answers:
          - She's sent to the login page to enter her password again - the remember-me cookie isn't enough.
          - She gets a 403 and no way forward, since remembered users are denied outright.
          - "She gets in - `IS_AUTHENTICATED_FULLY` only shuts out anonymous visitors."
          - Her remember-me cookie is deleted and she's logged out completely.
      explanation: |
          `IS_AUTHENTICATED_FULLY` is only granted when the user actually entered their
          credentials. A remember-me cookie grants `IS_AUTHENTICATED_REMEMBERED` instead -
          enough for Symfony to know who she is, not enough for anything sensitive. She
          keeps her identity and gets bounced to the login form to re-authenticate, which
          is the whole point of sudo mode.
---

# Sudo Mode: Requiring Full Authentication

You've totally seen this before. On sites like GitHub, when you go to perform a
sensitive operation, it asks you to confirm your password. Let's build that into our
app.

This is sometimes called "sudo mode", as a reference to the `sudo` command on Linux.

If you remember from the last course, we looked at `AuthenticatedVoter` and its
built-in attributes. `IS_AUTHENTICATED_FULLY` is the one we want. You can require it,
and then if a user is only *remembered*, they'll be forced back to the login page
before they can continue.

Copy that name. You can use this with any `#[IsGranted]`, but I want to blanket our
whole admin section - everything under `/admin` - with this requirement.

## An `access_control` Expression

Go to `config/packages/security.yaml`. Down here in `access_control`, we already have
a rule: for anything starting with `/admin`, we require `ROLE_ADMIN`.

You might think you can just use an array here... and you can... but it's
not what you want: it's an **or**. So this would let both admins OR **any**
fully authenticated user access to the admin section!

Because it's so ambiguous, using an array here is actually being deprecated in Symfony 8.2.
You'll only be able to use a single role.

Instead, use an expression. Delete the role and use
`allow_if: "is_granted('ROLE_ADMIN') and is_granted('IS_AUTHENTICATED_FULLY')"`:

[[[ code('7dce874097') ]]]

An expression gives us a much less ambiguous way to say "the user must be an admin AND they must
be fully authenticated".

Go back to our homepage and refresh. Error! We need the expression language installed...
Copy the composer require command and paste it into the terminal:

```terminal
composer require symfony/expression-language
```

Refresh again... and we're good.

## Watching It Kick In

Now log in... as `janeway@starfleet.space`, password `coffeeblack` - and make
sure "Remember me" is checked.

Now go to `/admin/user`. We see it! But watch what happens if we delete the session
cookie. Inspect the page, find it under the application tab, delete it, close, and refresh.

We're sent back to the login page. And you can see that little message saying we're
already logged in. So we have to log in *again*: `janeway@starfleet.space`,
`coffeeblack`. Sure enough, we're back in.

## Only Asking for the Password

If you noticed, it was kind of annoying that we had to retype our email as well.
Delete the session cookie again, close this, and refresh.

If you've used this on GitHub, it just asks you for your password. Let's make this a
bit more user-friendly.

Find the template: `templates/security/login.html.twig`. At the top, wrap the heading
in an `{% if app.user %}`, with an `{% else %}` and an `{% endif %}`. If they're not
logged in, in the else, move the normal heading inside. If they *are* logged in, copy just
the `h1` and change it to "Confirm your password":

[[[ code('2c8b51b7e6') ]]]

A bit further down, we have this message that tells them they're logged in. Delete
that entirely - we don't want to show it anymore.

Now swap the email input for a *hidden* input, because we already know what the email
is. Same deal: `{% if app.user %}`, `{% else %}`, `{% endif %}`. Move all of this into
the `else`. And inside the `if`, all we need is `<input type="hidden">` with `name`
matching the one down here - `_username` - and a value of
`{{ app.user.userIdentifier }}`, which is the email in our case:

[[[ code('ad0cfafb0e') ]]]

Refresh to see what that looks like. Cool - this looks much better!

We're already remembered, so we can remove the "Remember me" checkbox and just force them to
be remembered again. Down after the password, do the same thing: an `{% if app.user %}`,
an `{% else %}` and an `{% endif %}`. The checkbox goes in the `else`, and inside the
`if`, another hidden input, named `_remember_me` with a value of `1` - that gets
interpreted as checked:

[[[ code('b40ff35e6f') ]]]

The last thing: change the "Sign in" button. Output `app.user ? 'Confirm' : 'Sign in'`:

[[[ code('c7020d9a16') ]]]

Refresh. Nice! Look how professional this looks!. Password, `coffeeblack`, and... we're in.

Make sure our normal login page still works. Hit logout, then log in.

Sure enough, we're back to our normal form, because we're not already logged in.
`janeway@starfleet.space`, password `coffeeblack`. Perfect - we're back in.

That's sudo mode in a nutshell.

Next: let's create a special voter that allows super admins to do... basically anything.
