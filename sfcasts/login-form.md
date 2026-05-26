# Creating a Login Form

We have our user provider configured and a user in our database. Now we need
a way to log them in! There are several different ways to do this, but we'll
focus on *session-based* authentication with an HTML login form.

This is a pretty common method and Symfony has a built-in authenticator for it.
We just need to generate a bit of code to integrate it into our app.

## `make:security:form-login`

Luckily, the maker-bundle provides a wizard to help!

At your terminal, run:

```terminal
symfony console make:security:form-login
```

We're first prompted to create a controller class for our login/logout routes.
`SecurityController` is a good name, let's go with that!

Do we want a `/logout` URL? Yep! We want our users to be able to log out.

Generate PHPUnit tests? Nah, not right now.

Ok, this generated two new files in our project: the `SecurityController` and a
`login.html.twig` template. It also updated our `security.yaml` file.

Let's explore these.

## Examining the `SecurityController`

First, open `src/Controller/SecurityController.php`. This has a `login()` method
that handles the `/login` route. We're injecting this `AuthenticationUtils` service,
which is a nifty authentication helper.

Inside, we're setting an `error` variable to the last authentication error from those utils.
If there was an error during the login process, this will contain the specific error message,
like "Invalid credentials" or "Account disabled". If there were no errors, this will be null.

Next, we're setting the `lastUsername` variable, also from those utils. Whenever a user tries to log in,
Symfony keeps track of the username they used in their last login attempt. As we'll soon see, we use
this to pre-fill the username field in the login form, this saves the user to re-full that field if they
made a typo in their password.

Finally, we're rendering the new `login.html.twig` template, passing it the `last_username` and `error` variables.

It's important to note that this method doesn't actually handle the login logic itself. This is only called when
the user visits the `/login` page as a normal GET request (like clicking a link). When the user submits the login form,
the security system takes over and processes the login request. If the login is successful, the user is authenticated
and logged in. If the login fails, the user is redirected back here with the appropriate error message.

Below, we have this `logout()` method and route. All it does is throw an exception. What the? Does this mean
everytime a user logs out, they get a 500 error?! Nope! This route is basically just a placeholder so we can
customize the `Route` path and name. The Symfony security system intercepts this route before it ever reaches
the method. If something is misconfigured with your logout settings and this method is hit, then this exception
will be thrown, indicating that you have a bug.

## Checking the `security.yaml` File

Let's check the security configuration changes in `config/packages/security.yaml`. Under our `main` firewall,
we have this new `form_login` config. This tells Symfony to use the built-in form login authenticator. There
are a bunch of options we can configure here, but the defaults are pretty good and standard. The `login_path` is set
to our `app_login` route we saw in the `SecurityController`. This is where users are sent when they need to log in -
like when they try to access a protected page. The `check_path` is set to `app_login` as well. This is where the
login form submits to. It isn't a problem that it's the same route, because you submit credentials using the `POST` method.
This triggers the security system to process the login attempt instead of rendering the login form. Finally, we're
enabling Cross-Site Request Forgery (CSRF) protection for our login form.

Below, we're enabling and configuring the logout functionality by setting `logout_path` to the
`app_logout` route we configured in the `SecurityController`.

## The `login.html.twig` Template

On to the other new file, open `templates/security/login.html.twig`. It extends our base layout and
sets the title block.

In the body block, were rendering the form. Notice the `method="post"` attribute. This is important in order
to trigger the `check_path` on submit. Remember, we're passing the `last_username` and `error` variables to this
template from our `SecurityController`.

Inside, we're first checking if there's an error, and if so, rendering it. This is using the translation system so
if your app is localized, the standard error messages will be automatically translated into the user's locale.

Next, we're checking if a user is already logged in. `app.user` returns the currently logged-in user, or `null` if there
isn't one. If they are logged in, display their `userIdentifier` (their email in our case), and a logout link.

Below is an email type input field for the username, which is pre-filled with the `last_username` variable. Notice
the `name="_username"` attribute. This is important because the form login authenticator looks for this specific
field name when processing the login attempt.

The same goes for the password field below. It needs to be named `_password` for the authenticator to recognize it.
These names can be configured in the `form_login` options in `security.yaml`.

Next, we have a hidden input field for the CSRF token. The value of this token is generated with the `csrf_token()`
function. Again the name attribute, `_csrf_token`, is important for the authenticator to recognize it and can be configured
under `form_login` in `security.yaml` also.

There's some commented out code related to "remember me" functionality which we'll cover later.

Finally, here's the submit button.

## Login Form Styling

Let's see what this thing looks like! Over in our app, visit `/login`... This looks ok... but let's jazz it up!

In the `tutorial` directory, open `login.html.twig` and copy everything. Go back to our
template and replace everything with the copied code. This code is also in the script below.

Refresh the login page... Nice, much better!

## Testing the Login Form

Let's first try some wrong credentials. Email: `invalid@invalid.com`. Password: `invalid`. Hit enter to submit and...

Ok, this "Change your Password" popup is from my web browser. It does not like this password. I'll just hit OK to
close it - we're just in development here. Uhh, no, never save it!

We have the expected "Invalid credentials" error message and check it out, our email field is pre-filled with the
username we just tried. Even if we refresh the page, it remembers our last username. Handy!

Now let's test the CSRF protection. We need to get our hands dirty in the developer tools for this. Right click
somewhere in the form and choose "Inspect". In the DOM, find the hidden csrf_token input field. Change the value to
something invalid, like `invalid`.

Fill in the password with anything... Let's just close the developer tools... and hit "Sign in". I'll close the
crappy password popup again... And nice, we see the "Invalid CSRF token" error.

This error message is pretty technical, I don't think the average user would understand it. Here's a homework
assignment for you: how can you customize this error message to be more user-friendly? Like: "Sorry, something
went wrong. Please try again."? Pop your answers in the comments below!

## Stateless CSRF Tokens

You might have noticed something about the csrf token value before we changed it to `invalid`. Find
it in the dev tools again and take a look. It's just "csrf-token"... shouldn't this be something
random and unique? Yep, if we were using traditional session-based CSRF tokens. Newer versions of
Symfony allow you to use the more modern *stateless* (that's session-less) CSRF tokens.

Check out our `login.html.twig` template and find the hidden input field for the token. Notice the value is
generated with `csrf_token('authenticate')`. `authenticate` is a token id, used to distinguish the
*intention* of this token. If we open `config/packages/csrf.yaml` and look at this `stateless_token_ids` config,
we see that `authenticate` is listed here. This means that any csrf token generated with the `authenticate`
id will use the stateless system.

Further, back in our login template, the hidden token field has a `data-controller="csrf-protection"` attribute.
This is a Stimulus controller that the Flex recipe for the Stimulus bundle provides. You can find it in
`assets/controllers/csrf_protection_controller.js`. This isn't required for the stateless CSRF token system
to work, but it *hardens* the system. Check out our [YouTube short](https://www.youtube.com/shorts/URNBEATIzSQ)
on the topic to learn more.

Ok, back to our login form! Let's try logging in with a valid email and password.

Over in the terminal, check our database to refresh our memory on the user:

```terminal
symfony console dbal:run-sql 'select * from user'
```

Oh yeah, it's our buddy Jean-Luc Picard. Back on the form, hide the dev tools, refresh the page... and enter his
email: `picard@enterprise.space`. Password: `makeitso`. Hit "Sign in"... Yeah yeah, this is a bad password too...

Hmm, we're getting the "Invalid credentials" error. I know I didn't make a typo... Remember that security
vulnerability I mentioned in the last chapter...? Have you figured out what it is?

We'll fix it next!
