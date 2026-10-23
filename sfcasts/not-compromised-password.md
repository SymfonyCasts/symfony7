# Requiring Strong, Uncompromised Passwords

We now have three places where we ask someone to create a new password: our change password
flow, the registration flow, and the password reset flow.
The good news? Reset password and change password already share
`ChangePasswordFormType`. The bad news? Registration has its own
`RegistrationFormType`... with its own, *different* set of validation constraints.

In our `ChangePasswordFormType`, we have four password field
constraints: `NotBlank`, `Length`, plus two more interesting ones -
`PasswordStrength` and `NotCompromisedPassword`. Now look at
`RegistrationFormType`: just `NotBlank` and `Length`, and that `Length`
happily accepts a 6 character password.

So instead of copying the constraints from one form into the other - and then keeping
the two lists in sync forever - create a custom *compound* constraint that holds all of
them.

## Creating the Compound Constraint

In `src/`, add a new PHP class called `Password` in the `App\Validator\Constraints`
namespace. Make it `final` and extend `Compound` - the one the Symfony validator component.
Then implement the single method: `getConstraints()`.

Inside, we return an array of the constraints this one should expand into. We want the
full set, so hop back to `ChangePasswordFormType`, copy all four... back in
`Password::getConstraints()`... return... and paste:

[[[ code('269f086e44') ]]]

Now, in `ChangePasswordFormType`, replace that entire array with just `new Password()`:

[[[ code('9cf866f5f0') ]]]

And do the same over in `RegistrationFormType`:

[[[ code('3ba1d69ef4') ]]]

Perfect. That's a *great* way to cut down on duplication.

## Watching All Four Fail at Once

Let's try this out. Head to the app and click the register link. I'm not going to worry
about the other fields - let's focus on the password. Type `password` and register.

Look at the password field: *three* separate validation
errors. Our password must be at least 12 characters, our password strength is too low,
and the password has been leaked in a data breach. Let's go through these one at a
time.

The first one is the easy one: it's our `Length` constraint which requires a minimum
of 12 characters. So let's fix that - try `passwordpassword` instead.

Now we're down to two errors. The first one - "The password strength is too low" - comes
from `PasswordStrength`. That one runs a bit of math to estimate the *entropy* of the
password - how many distinct characters, from how big a pool - to work out how complex
it really is. And if we jump into the class, we can see what's configurable: a
`minScore`. By default, it uses `STRENGTH_MEDIUM`, so it isn't demanding *very* strong -
and that's the knob to turn if you want to require more or less. Which is why `password`
was a problem... and why `passwordpassword` still is. Long, but far too simple.

Ok, back to the form with something more complex *and* longer than 12 characters. How
about `correcthorsebatterystaple`? That sounds pretty complex... but we still get an
error:

> This password has been leaked in a data breach, it must not be used.

## How `NotCompromisedPassword` Works

That's coming from the `NotCompromisedPassword` constraint, and it does something
genuinely neat: it checks the password against
[Have I Been Pwned](https://haveibeenpwned.com/Passwords), a service that tracks
passwords found in real data breaches. If yours shows up in there, you can't use it.

And don't worry - it is *not* sending raw passwords over the wire. It sends a small
piece of a hash of the password, gets back a list of possible matches, and compares
them locally. The password itself never leaves your server.

So let's find something that gets past all four. Try `makeitso1engage?` and register.
Cool - no errors on the password field at all! And because it *is* a password field, it
clears itself out, so we have to fill it in on every submit.

That's a tidy way to consolidate the constraints for *any* field into a single
constraint you can reuse in multiple places.

Next: our login form is throttled, but our registration form is wide open. Let's rate
limit it.
