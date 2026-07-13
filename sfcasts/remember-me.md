# Enabling "Remember Me" Feature

Let's take a little tour behind the curtain to understand how our
user remains logged in as they navigate from page to page. As you can see,
when we jump around different pages, we stay logged in, just as we expect.
But, how does that magic happen? The trick lies in PHP sessions that store
the current user's details.

Inspect the page and go to the "Application" tab, then find the cookies for
our site. PHPSESSID is our session cookie. This value is a unique identifier
for our session. It points to some stored data on our server. The browser
passes this cookie back to the server with every request, allowing the server
to retrieve the related data. This data can be anything, like flash messages,
or some kind of saved site configuration. And since we are using the `form_login`
authenticator, it stores the authenticated user in the session.

Notice the "Expiry" value is set to "Session". This means the cookie is deleted
when we close our browser. And no cookie means no way to find our session: we're logged out.

Try it out, delete the cookie (to mimic closing our browser)... and reload the page...
We're logged out!

## Customizing the Cookie

Just a quick side note: you can tweak this cookie to your liking. To see the options,
run the following in your terminal:

```terminal
symfony console config:dump framework session
```

Remember, the session isn't security-specific, that's why it's configured at
the framework level.

Here, you can see that we can change the storage. By default, it uses the
file system, PHP's default. But you have options like
Redis or a database. You can configure a bunch of cookie-related options, like
the cookie name, path, domain, and more. These defaults are good for most cases,
but the options are there if you need to tweak them.

## Remember Me Feature

A popular feature on many sites is 'remember me', which ensures users
remain logged in, even between sessions (closing and opening their
browser). Let's enable that. In your IDE, open `config/packages/security.yaml`.
Under our main firewall, add `remember_me: ~`:

[[[ code('5e1569ab0e') ]]]

The tilde says: enable this feature, and use the default config.

To see the default config, at your terminal, run:

```terminal
symfony console config:dump security firewalls
```

Scroll up to find the remember_me section.

The cookie data consists of the user identifier, an expiration date, and a signature
to make sure it isn't tampered with. It uses our kernel secret by default to generate
the signature.

The `signature_properties` option is interesting. These are the properties of
the user that, when changed, will invalidate the cookie. By default, it just uses
the `password`. Remember earlier when we talked about logging users out of different devices?
As long as the password is included in the signature properties, the feature
we discussed will also logout remembered users!

You can add additional properties here, like email or username. Then, when any
of them change, the cookie will be invalidated (and the user logged out).

This `token_provider` option allows you to customize the storage of the remember me token.
By default, it uses a stateless approach, so nothing is stored on the server. The cookie
contains all the information needed to log the user in. Here, you can customize this
behavior with your own service, or use the built-in `doctrine` provider, which stores the token in a
database table. I think the default is good enough for most cases, but it's nice to know you have options.

Next is all the cookie options. Again, the defaults are good for most cases but the
lifetime is worth mentioning. This is how long the cookie will last, in seconds, before it expires.
This default is 1 year, so you may want to change it to something shorter, like 1 month.

`always_remember_me` allows you to always remember the user, no need for that login checkbox.
This is useful in some applications, but for most cases, we want the user to have the choice.
So we'll keep the default as false.

This `remember_me_parameter` is the parameter name that needs to be passed during login
to activate remember me functionality.

Remember me also needs to be enabled in our `login_form` authenticator. Scroll up to find its default
config... Here it is: `remember_me`, and it defaults to `true`. Perfect, so as long
as we pass the `_remember_me` parameter during login, remember me will be activated.

Let's add this checkbox to our login form. Open `templates/security/login.html.twig` and
scroll down until you find this commented out `div`. This was added by the `maker-bundle`.
Uncomment it.

Here we have a checkbox input, and the name is `_remember_me`. Perfect! One thing I'll
change, add the `checked` attribute to the input to default it to checked:

[[[ code('fb27db2fda') ]]]

Let's give this a whirl. Back in our browser, go to the login page. Nice! Here's
our pre-checked remember me checkbox. Login with `picard@enterprise.space`...
password: `makeitso`.

We're logged in... and nothing really looks different... 

Inspect the page, go the "Application" tab, and find the cookies for this site. Sure
enough, we have a new cookie called `REMEMBERME`. Notice the expiry, it's set to a future
date, a year from now based on our config. What's important, is that this cookie isn't
attached to the session.

Delete the `PHPSESSID` cookie, to simulate closing the browser... and refresh the page...

We're still logged in! Our remember me system is working!

Next, we're going to look at some special, authentication attributes.
