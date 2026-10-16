# Resetting a Forgotten Password

As soon as you have authentication that requires a password, you need a way for users
to deal with a *forgotten* password. Let's create the standard "forgot password"
system - and there's a maker for it. Over in the terminal, run:

```terminal
symfony console make:reset-password
```

This maker requires a few packages: the [SymfonyCasts ResetPasswordBundle](https://github.com/SymfonyCasts/reset-password-bundle) and
[Symfony Mailer](https://symfony.com/doc/current/mailer.html), because we email those password reset links. Copy that command,
prefix it with `symfony`, and paste:

```terminal-silent
symfony composer require symfonycasts/reset-password-bundle symfony/mailer
```

Those two are installed. I'll clear the screen and run the maker again:

```terminal-silent
symfony console make:reset-password
```

What route should users be redirected to after their password has been successfully
reset? It defaults to `app_home`, but our homepage route is `app_homepage`. Next, the
*sender* email - that's our app sending the email - so use `support@starfleet.space`,
with the name `Starfleet Support`. Unit tests? No.

This generates a *ton* of files, and the first thing it tells us is to create a
migration:

```terminal
symfony console make:migration
```

Find it under `migrations/` in your IDE and set the description to
`add reset_password_request table`:

[[[ code('1fef97f38c') ]]]

Then reload the fixtures:

```terminal
symfony console foundry:load-fixtures
```

## The Token Entity

Let's look at some of the files that were created. The way this system works: when
you request a reset, it generates a *token*, and that token is stored in the database
under a new entity - `ResetPasswordRequest`.

Each token is connected to a user, so we know who requested the reset. And it uses
`ResetPasswordRequestTrait` from the SymfonyCasts bundle, which adds things like a
secure hash of the token and when it expires - everything the bundle needs.

Along with the entity, we have `ResetPasswordRequestRepository`. It's a standard
service repository, but it implements `ResetPasswordRequestRepositoryInterface` and
uses a trait to add some special methods. The main method from the interface is
boilerplate to create that token for the user.

## The Two Forms

The maker also created a few forms. The first is
`ResetPasswordRequestFormType`: pretty simple, it just asks for your email. This form
isn't bound to any object, so the `NotBlank` constraint goes directly on the `email` field.
You could add an `Email` constraint here too.

The next is `ChangePasswordFormType`. After you request the reset, you get an email
with a link, and if your token is valid, you land on the form to change your password.
Here we have a `PasswordType` wrapped in a `RepeatedType` - so it's repeated twice,
"new password" and "new password again". The constraints are on the form type too:
`NotBlank`, `Length`, plus `PasswordStrength` and `NotCompromisedPassword` - we're
going to talk about those two a bit more later. `RepeatedType` requires that the
two fields match, so we get that out of the box.

## Unpacking the Controller

Next, the new controller: `src/Controller/ResetPasswordController.php`. It has a few
routes.

The first is `request()`, the forgot-password form. It creates the form from our
request form type, handles the request, and if it's submitted and valid, grabs the
email and calls `processSendingPasswordResetEmail()` - a method down below we'll look
at in a minute. Otherwise, it renders the request template.

After the email is sent, you're sent to the `checkEmail()` page, which just tells you
to check your email.

One interesting thing: remember *user enumeration* from our authentication error
messages chapter - someone probing the site to learn which addresses have accounts? The
reset form is the other obvious place to probe. So if no user is found for the email
they entered, we just *pretend* the email was sent. We generate a fake token to send
to the template that looks like a real one.

### The Token in the Session

Next is the `reset()` route - this is where that special link in the email sends you.
And it does something clever. If the token is in the URL, it stores it in the
*session* and redirects to this same route *without* the token in the URL, then reads
it back out of the session.

Why bother? Because a URL is a leaky place to keep a secret. It lands in browser
history, it gets sent along in the `Referer` header to any third-party script on the
page, and it's the thing people paste into chats when they need help. Moving the token
into the session the moment it arrives means the address bar holds nothing sensitive
for the rest of the flow.

If there's no token in the URL *and* none in the session, we
`createNotFoundException()`.

Then we use a helper from the bundle to validate the token and fetch the user. This
makes sure it's a valid token and isn't expired. If there's a
problem, we add an error to the flash and send them back to the request form.

Now the happy path. If the token is valid, create the `ChangePasswordFormType`, handle
the form request, and if it's submitted and valid... a reset token can only be used *once*,
so this removes it from the database. Then grab the validated plain password, hash it
onto the user - the `$user` variable came from validating the token above - flush to
save it, do some cleanup, and redirect to the homepage.

## Logging Them in Automatically

One quick thing: this system doesn't log the user in after they reset their password.
They are redirected to the homepage, but they have to manually login with their new password.
I think it's fine to programmatically log them in - you know it's them, because they clicked a link in an
email sent to their address. It's almost like a login link.

Up in the `reset()` method, tidy up these parameters a bit, because we're adding a new
one: inject `Security` from the Symfony security bundle:

[[[ code('65046bdb5a') ]]]

Then down in the happy path, right before we redirect to the homepage, add
`$this->addFlash('success', 'Your password has been reset, you are now logged in!');` and
`$security->login($user);`:

[[[ code('97aea1b8e4') ]]]

I think this is a better user experience.

## Sending the Email

Down at the bottom is `processSendingPasswordResetEmail()`. We try to find the user
for that email, and if we *don't* find one, we send them to the check-email page
anyway - which shows the same message as if the email had been sent. No enumeration:
the response looks identical either way.

If the user does exist, we create a reset token. Generating it can throw an exception.
For instance, if they've too recently requested a reset.

This commented-out section lets you explain the reason to the user. It's commented out because
showing that feedback can re-open the enumeration we closed earlier: someone submits
the form twice in a row for the same address, and a "you already requested one
recently" on the second try means that account exists. An unknown address shows the
same generic page both times.

Instead, we redirect to the check-email page with that same generic message.

If there's no error, we have a valid token - so now create the email: the `from` with
our sender address and name, the `to` with the user's email, a subject, and an HTML template. We
pass the reset token into the template, send it, do some cleanup, and redirect.

## The Templates

That was a lot to unpack - let's look at the templates in `templates/reset_password/`.

First, `check_email.html.twig`: what they see after submitting the request. Notice
`resetToken` here - we grab the expiry right off the token to tell them how long they
have - plus a link to try again. I'll paste in some nicer styling, you can find this in
the script below:

[[[ code('ab8ba748a1') ]]]

Next is the email template itself. It looks pretty boring... but we'll keep it that way for
now, the important part is that it generates an *absolute* URL to the reset
password route with the token - that's what the user clicks - and it also tells them when it
expires.

Then `request.html.twig`, the form that asks for your email. I'll paste some styling here too:

[[[ code('4aad2e1cd0') ]]]

And the last one is the reset form: where they land after clicking a valid link. I'll make
this one prettier too:

[[[ code('65524e963b') ]]]

One more thing - the login form needs a "forgot password" link. In
`templates/security/login.html.twig`, just below the form, I'll paste a snippet
you can find in the script below:

[[[ code('b8df3de82d') ]]]

If the user isn't already logged in, they see the link.

## Trying the Whole Thing

Over in the app, go to the login page. There it is: "Forgot your password?" Click it,
enter `picard@enterprise.space`, and submit.

Here's our check-your-email page - our link expires in one hour, which is the bundle's
default.

In production you'd get the email, but in development, by default, Symfony's mailer doesn't
actually send anywhere - it *tracks* them in the profiler though! It
won't be on this request, because we were redirected, so it's on the redirect response before
it. Click the little shortcut to that profiler key, get to the previous request's
profile, and... one email! There's our message, from that template we looked at.

Copy the link and paste it into the browser. See how it redirected to the version
*without* the token at the top? That's the session trick working!

Enter a new password - something a bit more secure, like `makeitso1engage?` - repeat... and hit
"Reset password". Beautiful: "Your password has been reset, you are now logged in!"
And we're logged in as `picard@enterprise.space`. That worked perfectly.

## Configuring the Bundle

If you want to learn more about customizing these email templates and more about the Symfony Mailer,
take a look at our dedicated [Mailer course](https://symfonycasts.com/screencast/mailtrap), linked below.

For the bundle's configuration, the only required option is the repository, so it
knows which repository holds that token entity. To see the full config, run:

```terminal
symfony console config:dump symfonycasts_reset_password
```

Along with the required `request_password_repository`, you can set a `lifetime` - how
long tokens stay valid, one hour (`3600` seconds) by default - and `throttle_limit`,
which is how often someone can request a reset. By default, that's one per hour.

Then there's `enable_garbage_collection`. When people request resets and never consume
the tokens, that table just gets bigger. Garbage collection cleans up expired tokens
to keep it tidy. Definitely check this [bundle's documentation](https://github.com/SymfonyCasts/reset-password-bundle#readme).
I'll link to it below.

So a user can register with a password, and request a new one if they've forgotten it.
Next: let's add a way for them to change it while they're already logged in.
