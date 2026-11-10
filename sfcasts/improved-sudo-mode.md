---
challenges:
    - key: fully-vs-recently
      question: |
          Janeway logged in with her password three hours ago, and she's still logged in.
          Which of `IS_AUTHENTICATED_FULLY` and `IS_AUTHENTICATED_RECENTLY` will she pass
          right now?
      answers:
          - Only `IS_AUTHENTICATED_FULLY`
          - Only `IS_AUTHENTICATED_RECENTLY`
          - Both, because she logged in with her password
          - Neither, because her login is more than two hours old
      explanation: |
          `IS_AUTHENTICATED_FULLY` cares about *how* she logged in: with her password, in this
          session. `IS_AUTHENTICATED_RECENTLY` cares about *when*: within the last two hours
          by default. Her three-hour-old password login still counts as "fully", but not as
          "recently", which is what makes it a real sudo mode.
    - key: remembered-user-very-recently
      question: |
          Picard checked "remember me" last week. Today he opened his browser, and the
          remember-me cookie logged him back in a minute ago. Which of
          `IS_AUTHENTICATED_FULLY` and `IS_AUTHENTICATED_VERY_RECENTLY` will he pass right now?
      answers:
          - Neither, because he hasn't entered his password
          - Only `IS_AUTHENTICATED_VERY_RECENTLY`, because he was logged in a minute ago
          - Only `IS_AUTHENTICATED_FULLY`, because he's logged in
          - Both, because the cookie counts as a fresh login
      explanation: |
          A remember-me cookie isn't proof that Picard still knows his password, so it never
          counts as `IS_AUTHENTICATED_FULLY`. And both recency attributes imply
          `IS_AUTHENTICATED_FULLY`, so even a login a minute ago won't pass
          `IS_AUTHENTICATED_VERY_RECENTLY`. He'll need to enter his password first.
---

# Improved Sudo Mode with IS_AUTHENTICATED_RECENTLY

Open `config/packages/security.yaml` and scroll down to `access_control`. We have a rule
here: anyone accessing a path that starts with `/admin` must have `ROLE_ADMIN` *and* be
`IS_AUTHENTICATED_FULLY`. That second part means you logged in with your password during
*this* session, so it excludes remembered users. If you're only remembered, you get sent
to the login page and have to confirm your password.

I've been calling this "sudo mode" - like on macOS or Linux, where you re-enter your
password to get elevated privileges. But that's a bit of a misnomer. Real sudo mode is
about *how long ago* you last entered your password, not whether you're remembered. Ours
just had the feel of it.

## Real Sudo Mode

In Symfony 8.2, we get a *real* sudo mode, through two new attributes:
`IS_AUTHENTICATED_RECENTLY` and `IS_AUTHENTICATED_VERY_RECENTLY`. The first checks that
you entered your password in the last two hours. The second checks the last five minutes.
And both imply `IS_AUTHENTICATED_FULLY`, so a remembered user can *never* have either of
them. They always go through the login flow.

Let's play with these in our app. But first, we need to take care of a few things.

## Excluding the New Attributes in `SuperAdminVoter`

Open `src/Security/Voter/SuperAdminVoter.php`. This lets anyone with `ROLE_SUPER_ADMIN`
bypass most `is_granted()` checks in our app. But we exclude some attributes that aren't
really about permissions, like `IS_AUTHENTICATED` and `IS_AUTHENTICATED_FULLY` - we don't
want our super admin blindly bypassing those. Add the two new attributes to the
exclusions: `AuthenticatedVoter::IS_AUTHENTICATED_RECENTLY` and
`AuthenticatedVoter::IS_AUTHENTICATED_VERY_RECENTLY`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "5-sudo-mode", "file": "src/Security/Voter/SuperAdminVoter.php", "lines": "10-12,19-20,22,33"}) ]]]

## Fixing a Sneaky Bug in `supports()`

We also have a nasty bug in `supports()`. Back in `security.yaml`, look at our
`allow_if`. Anywhere you can do an `is_granted()` check, you can also pass an
expression - that's what we're doing here. It gets turned into an `Expression` object,
and that object is passed to `supports()` as the attribute. But our `$attribute` argument is typed as
`string`, and an `Expression` can be converted to a string... so PHP quietly casts it!
The whole expression string lands here, it's not in our exclusions, so we say "sure,
you can bypass this". The result? Super admins are never actually checked by this rule:
they bypass it completely.

To fix it, change `string $attribute` to `mixed $attribute`. Now the `Expression` object
is passed as-is, without being cast. Then add an `is_string($attribute)` check in front
of the `in_array()`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "5-sudo-mode", "file": "src/Security/Voter/SuperAdminVoter.php", "lines": "10-11,24-27,33"}) ]]]

That squashes the bug.

## Only Remembering Users Who Asked

Next up is our login template: `templates/security/login.html.twig`. If you're already
authenticated when you land here, we hide the username field - we already know who you
are. And down at the "remember me" checkbox, if you're authenticated, we always force
remember me on.

That was fine before, because `IS_AUTHENTICATED_FULLY` was the only thing that sent an
authenticated user here, which meant they had to be remembered - keeping them remembered
didn't hurt anything. But now these two new attributes can send you here too, and in
that case we shouldn't assume you want to be remembered. Change `if app.user` to
`if is_granted('IS_REMEMBERED')`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "5-sudo-mode", "file": "templates/security/login.html.twig", "lines": "9-12,23,62-64,69,74,81-84"}) ]]]

Now we only force remember me if you're *already* remembered.

## Enabling Sudo Mode

Time to turn it on! Back in `security.yaml`, in the `access_control` `allow_if`, change
`IS_AUTHENTICATED_FULLY` to `IS_AUTHENTICATED_VERY_RECENTLY`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "5-sudo-mode", "file": "config/packages/security.yaml", "lines": "1,52-53"}) ]]]

Keep in mind: with this one, users have to re-enter their password every five minutes -
every two hours with `IS_AUTHENTICATED_RECENTLY`. If your admins work in the admin area
all day, they'll get annoyed typing their password every five minutes! Save these
attributes for truly dangerous actions. It's a balance between usability and security.
Honestly, for this route I'd probably stick with `IS_AUTHENTICATED_FULLY`: remembered
users confirm their password once, then they're good for the rest of the day, until they
close their browser.

## Trying It Out

Let's test it! Head to the login page and log in as `janeway@starfleet.space`, password
`coffeeblack`. Then go to an admin page, like `/admin/user`. We're in.

To see the re-authentication kick in, we have to wait five minutes, since we used
`IS_AUTHENTICATED_VERY_RECENTLY`. While we wait, let me show you how to customize these
values. At your terminal, run:

```terminal
symfony console config:dump security
```

Scroll way up to the top... here we go. `recent_authentication_lifetime` is for
`IS_AUTHENTICATED_RECENTLY` and defaults to `7200` seconds - two hours. And
`very_recent_authentication_lifetime` defaults to `300` - our five minutes. That's how
you tune them.

I'll time travel a bit here...

Ok, it's been about five minutes. Refresh and... sure enough, we're sent back to the login
form to confirm our password. And because we're *not* just remembered, we get the option
to be remembered. Enter `coffeeblack`, confirm... and we're back in! We have another five
minutes in the admin before we need to re-authenticate.

Next, let's look at some rate limiter improvements in Symfony 8.2!
