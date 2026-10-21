---
challenges:
    - key: current-user-non-nullable-redirect
      question: |
          Our controller takes the user as a non-nullable argument:

          ```php
          public function __invoke(
              #[CurrentUser]
              User $user,
              // ...
          ): Response {
          ```

          We never added an `#[IsGranted]` or an `access_control` rule - yet visiting
          `/change-password` while logged out sends us straight to the login page. Why?
      answers:
          - With nobody logged in, Symfony has no `User` to hand a non-nullable argument, so it denies access - and the firewall turns that into a redirect to the login form.
          - "`#[CurrentUser]` implies `IS_AUTHENTICATED_FULLY` and runs an access check of its own before the controller is called."
          - "Symfony passes `null`, and the redirect comes from the firewall noticing that `$user->setPassword()` failed."
          - "Routes whose templates live under `security/` are covered by an `access_control` rule out of the box."
      explanation: |
          The rule is a simple one: if nobody is logged in and the argument isn't
          nullable, Symfony won't call the controller at all - it denies access rather
          than handing us `null`. The firewall catches that and, since we aren't
          authenticated, sends us off to the login form. So writing `User $user` instead
          of `?User $user` quietly became our access control - lovely when that's exactly
          what you wanted! Make it nullable and the behaviour flips: we'd get `null`, and
          the page would happily render.
    - key: why-require-current-option
      question: |
          We tucked the `currentPassword` field behind an option that defaults to off:

          ```php
          if ($options['require_current']) {
              $builder->add('currentPassword', PasswordType::class, [
                  // ...
              ]);
          }
          ```

          Why not simply always add the field?
      answers:
          - The reset-password flow renders this very same form type - and someone resetting a forgotten password has no idea what their current one is.
          - "`PasswordType` fields can't be added unconditionally when the form isn't bound to an object."
          - "The `UserPassword` constraint only works on the first field of a form, so the field has to be added conditionally."
          - "Always adding it would break `RepeatedType`, which needs to be the only field in the form."
      explanation: |
          `ChangePasswordFormType` came to us from the reset password maker, and it's
          pulling double duty now: the reset flow renders it for someone who just clicked
          a link in their email, and our new controller renders it for someone who's
          already logged in. Those two people know very different things! Making the
          field opt-in meant the reset flow kept working without us touching a single
          line of it, while our controller switches the field on with
          `createForm(ChangePasswordFormType::class, options: ['require_current' => true])`.
---

# Adding a Change Password Form & UserPassword

We need a way for logged-in users to change their password if they want to.
Unfortunately there's no maker for this, so we're going to build it by hand - though
we can use a maker to create the controller. Over in the terminal:

```terminal
symfony console make:controller
```

Call it `ChangePassword`. No unit tests.

I'm also going to reset the database, so all our users have their default passwords
again:

```terminal
symfony console foundry:load-fixtures
```

The maker created `templates/change_password/index.html.twig` and a
`ChangePasswordController`. Let's tidy the template up a bit: instead of living in its
own subdirectory, rename it to just `change_password.html.twig` and move it
into the `security/` folder - I feel like it belongs there. Then delete the empty
directory.

## Prepping the Controller

Open `src/Controller/ChangePasswordController.php`. First, change the route path to
`/change-password`. Down below, update the template to the one we just moved:
`security/change_password.html.twig`.

I'm also going to rename the method to `__invoke()`, because it's not really an
"index" - now it's an invokable class. I like doing that when a controller has only
one action and the class name fully explains what it does.

Now for the dependencies. This is a feature for logged-in users, so inject our `User`
entity and add the `#[CurrentUser]` attribute above it so Symfony injects the current
one. The nice thing about this: because the method now *requires* a user, anyone
hitting this route while logged out gets redirected to the login page. Exactly what we
want.

Next we need to save the new password, so add `EntityManagerInterface $em`. We need to
hash it, so `UserPasswordHasherInterface $hasher`. And we need the `Request` from HTTP
foundation:

[[[ code('879d585119') ]]]

## Reusing the Form

Now, create the form. But which form?

What's cool is that the reset password maker already created the form we need. Look
at `src/Form/ChangePasswordFormType.php` - from the last chapter. That's all this
requires: the new password, twice. We can reuse it - I love that.

So: `$form = $this->createForm(ChangePasswordFormType::class)`, then
`$form->handleRequest($request)`. If the form is submitted and valid, grab the
password off the form with `$form->get('plainPassword')->getData()`. Take the user we
injected above, `setPassword()`, and hash it with `$hasher->hashPassword($user, $password)`. Then
`$em->flush()`, add a success flash - "Your password has been changed!" - and redirect
to `app_homepage`. For the `render` below, pass the form to the template:

[[[ code('45ff661c58') ]]]

Let's check this out. Go to the app and manually visit `/change-password`. We're
logged out right now, so this should redirect us to the login page... and it does!
Perfect. Log in as `picard@enterprise.space`, password `makeitso`.

Oops - you know what we forgot? We have to update the template. Over in
`templates/security/change_password.html.twig`, clear out the body. For now, render
the form with `{{ form(form) }}`:

[[[ code('308cb27c0c') ]]]

Refresh. Not styled yet - we'll get to that in a minute - but we have our new password
and repeat password fields, just like the reset password page.

## Requiring the Current Password

One thing you should do when a user changes their password is require them to enter
their *existing* one.

There are a couple of ways. In the controller we could add an
`#[IsGranted('IS_AUTHENTICATED_FULLY')]` so we know they're in that sudo mode. But the
more common way is to add a current password field to the form.

We can do that - but we have to make it *optional*, because we don't want that field
when a user is *resetting* their password. They don't know their current password,
that's the whole point.

Over in `ChangePasswordFormType`, down in `configureOptions()`, create a new option
called `require_current` defaulting to `false`. Whenever you add an option, you should
also say what type it is: `$resolver->setAllowedTypes('require_current', 'bool')`, so
it can only be a boolean:

[[[ code('e1369db882') ]]]

Now up at the top of `buildForm()` - I want this to be the first field - add
`if ($options['require_current'])` and inside, add a `currentPassword` field as a
`PasswordType`.

For the options, start with the constraints. First, a `NotBlank` with the message
"Please enter your current password". The second is one you might not have seen:
`UserPassword`. This validation constraint simply requires that the field equal the
user's *current* password. The hashing and verification is all handled for us - pretty cool!

Then set `mapped` to `false`, because it isn't mapped to any object, and set the label
to "Current password":

[[[ code('77fb62e866') ]]]

To make sure it's truly optional, go back to the app and refresh. It's not there - and
that's good! It means the field has to be *enabled*, which means we don't need to
change anything about our reset password flow.

To enable it, head back to `ChangePasswordController` and pass an options array where
we create the form: `require_current` set to `true`:

[[[ code('c4fe4b776b') ]]]

Refresh... perfect, there it is.

## Styling and Testing

Let's paste in some styling to make this look nicer. In the change password template,
paste in this code - you'll find it in the script below:

[[[ code('844a233610') ]]]

Refresh. Much better.

Now let's fail this form on purpose. For the current password, enter `engage` - but
it's really `makeitso`. And for the new password, use `1234` in the first field, and
`4321` in the other, so they don't match. Hit "Change password" and... there we
go: "The value should be the user's current password". We could probably make that
message a bit nicer, to be honest.

Okay, so what *is* the current password? `makeitso`. And for our new password,
`makeitso1engage?` twice. Change password... perfect! "Your password has been
changed!"

Maybe we should double-check. Log out, then log in as `picard@enterprise.space` with the new
password `makeitso1engage?`. Awesome - that all worked.

Next: let's look at those special password-specific constraints in our change password
form, and reduce some duplication in our password validation.
