# Creating a Registration Form

To close this course off, we'll add a way to get new users into the app. And a pretty common
way of doing that is a registration form.

Building that by hand would be boring... but luckily, there's a maker for that!

## `make:registration-form`

Jump over to the terminal and run:

```terminal
symfony console make:registration-form
```

"Do you want to add a `#[UniqueEntity]` validation attribute?" Yes. We can't have two
users with the same email.

"Do you want to send an email to verify the user's email address after registration?"
Choose no for now: there's a bit more complexity to that.

"Do you want to automatically authenticate the user after registration?" Yes! That
will programmatically log them in, so we can see how that works.

"Do you want to generate PHPUnit tests?" No.

Ok, this *updated* one class - our `User` entity - created two new classes and a
template. Let's look though these.

## The `UniqueEntity` Constraint

Start with the `User` entity: `src/Entity/User.php`. All this did was add a
`#[UniqueEntity]` validation constraint on `email` up here.

So during the validation process, before the user is saved, this checks whether
another user already has that email. If one does, we get this message: "There is
already an account with this email." Pretty useful.

We already have this as a Doctrine constraint, but this is a *validation* constraint.
The difference is the Doctrine constraint is enforced at the database level, so a failure
would give a 500 error to the user. The validation constraint gives a nice validation
error message instead.

## `RegistrationFormType`

Next up is the form. Find it in `src/Form/RegistrationFormType.php`.

This is a pretty standard form, but one thing that might look a little different is we have
some unmapped fields. `agreeTerms` and `plainPassword`.

Notice that both have `'mapped' => false`. Because they're unmapped, we don't have an
object to put our validation constraints *on*... so the form lets you add them right
to the field itself. That's why these constraints live here.

The one thing I want to add is our `name` field: `->add('name')`:

[[[ code('a62bdeece7') ]]]

This is a mapped field, so it will be set on the user entity.

## `RegistrationController`

Now find `src/Controller/RegistrationController.php` and let's break this down.

In this `register()` method, the first thing we do is create a new `User`... and then
create our `RegistrationFormType` form for that user. Then we let the form handle the
request, and check if the form is submitted *and* valid.

If it is, we grab `plainPassword` from the form data to get its raw value. All of the
*mapped* fields - in our case, the email and name - are already set onto the user.

We don't need to do anything with `agreeTerms`. It's unmapped and just has an `IsTrue`
constraint. So if the form is valid, we know the user agreed to the terms.

So what's happening here is that we're using the password hasher to hash the plain
password. If you haven't seen this done manually before: you inject
`UserPasswordHasherInterface`, then call `hashPassword()`. Give it the user - which is
how it determines *which* password hasher to use - and the raw password.
It returns the hashed password, so we set that on the user.

Now we have a fully valid user object.

We persist and flush it to the database.

Below is where you'd do anything else you want, like adding a flash message.

Finally, we're returning `$security->login()` - that's the programmatic login system.
It's passing our authenticator and firewall names... but those can be
detected from the request, so we don't even need them. Keep it simple.

And the reason we *return* it, is that `login()` returns a `Response`: the standard
response you'd get after logging in. You don't *have* to return it, though. You can call
`$security->login()` without returning it, then return your own response - maybe a
redirect somewhere .

And of course, if the form isn't a valid submission, we render `register.html.twig` and
pass the form to it.

## Rendering the Form

That template is our last new file, so jump into it.

The maker generated a form that renders each field individually. The one thing I want
to do is render our `name` field as well. Duplicate the email one... and change it to
`name`:

[[[ code('ce3165cfa8') ]]]

Now, if you look at the homepage, nothing looks different yet. What I want is a
"Register" button beside the login button - and of course, only if you're *not*
already logged in.

Open `templates/base.html.twig` and scroll down until you find that big `if` statement
for our links. We have the impersonation link, the logout button, and this login one.
Copy the login link, paste a copy right above it, call it "Register", and set the path
to `app_register`:

[[[ code('11b4e3aef6') ]]]

Jump back to the browser and refresh the homepage. Cool - there's "Register".
Click it and... this looks a little nasty.

In `register.html.twig`, I'll paste a nicer version... You can find this in the script below:

[[[ code('f95a551b1f') ]]]

## Turning Off HTML5 Validation

Now I want to test this form's validation. If we hit "Register", we get... *HTML5*
validation from the browser.

When I'm testing validation manually, I like to disable HTML5 validation so I can
actually see the Symfony validators working. Over in the template, on `form_start`,
pass a `novalidate` attribute set to `true`...

[[[ code('1276ce042e') ]]]

Refresh... and error. Twig didn't like that. I forgot a comma... Nope... still not right...

Oh sheesh, `novalidate: true` needs to go inside the `attr` array.

There we go!

Ok, validation is disabled. Hit "Register" and... we have some validation errors!
These two both come from our form type. But we should be seeing errors for email and
name too. They're both required fields, and they need to be configured in our `User` entity.

## Adding Validation Constraints

Open `src/Entity/User.php` and start with the `$email` property. Add `#[Assert\NotBlank]`:

[[[ code('a34ba95cb6') ]]]

It added a doubled-up `Assert` here, so I'll take that out. This imported an
`Assert` alias, so we don't have to import every individual constraint.

We don't need any arguments. You could pass a `message` to make it a bit friendlier,
but leave the default for now.

The other one is `name`, so add the same thing there: `#[Assert\NotBlank]`:

[[[ code('0921b3a2e9') ]]]

Back to the form, refresh, and hit "Register"... we see a validation error on each
field. Perfect!

Next, the email field should actually contain an *email*. Enter something that isn't
one - like `picard` - and hit "Register".

The other fields gave us validation errors, but this one didn't. We need an email
constraint. So jump back to the `User` entity, up to `email` property, and add
`#[Assert\Email]`:

[[[ code('bad35806d3') ]]]

Submit again... great: "This value is not a valid email address."

There's one other constraint to check: that `#[UniqueEntity]` on email. We already
have a user with `picard@enterprise.space` as their email in the database, so enter
that and register...

"There is already an account with this email." That worked perfectly.

## Registering a Real User

Now register an actual new user: email `natasha@enterprise.space`, name "Natasha Yar", and
the password can be anything. I'll use `stayawayfromarmus`. Check "I agree to the terms"...
and register!

We're back on the homepage, we see the logout button, and if we look down in the web
debug toolbar... sure enough, we're authenticated as `natasha@enterprise.space`. Our
registration and programmatic login is working correctly.

## Does Programmatic Login Count as Interactive?

Let's make sure that programmatic login is considered an interactive login, and triggers
our last login listener.

Hit logout, then log in as `janeway@starfleet.space`, password `coffeeblack`. Then go
to `/admin/user`... and down here, our new user has their last login set correctly! So
a programmatic login *is* considered an interactive login.

## One Last Thing: User Enumeration

I want to end this course with a discussion. Some people wouldn't call this a security
issue - maybe more of a *hardening* issue - but check this out.

Log out and go back to the register page. Say we're trying to find out whether someone
we know uses this site... someone like Picard. We're not actually trying to register:
we just want to know if this email has an account here.

Enter `picard@enterprise.space`, hit "Register", and - as we saw earlier - the error message tells us very
clearly that there's already an account.

This is called *user enumeration*.

I'd love to have a discussion in the comments below about this. Is this a security issue? How could we
have a registration system that doesn't allow this?

Ok, that's it for this security basics course. Thanks for joining me!

'Til next time, happy coding!
