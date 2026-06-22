# app.user and Login/Logout Links

Earlier, when we created our login form, we quickly talked about this `app.user`
Twig variable. Let's explore it further. `app` is a global Twig variable - meaning
it's available in all our templates. It contains a lot of useful context on
the current request, including the current user (if available).

In `base.html.twig`, just before the body block, dump it with `{{ dump(app.user) }}`.

Back in our browser, refresh the homepage. It dumped `null`. Makes sense, as we're
not logged in yet. Go to `/login` and login with our good buddy `picard@enterprise.space`,
password: `makeitso`. We're back on the homepage, and the dump now contains an instance
of our `User` object. Specifically, Jean-Luc's user object. This is a super quick
way to access any information about the currently logged-in user. But also, an easy
way to check if a user is logged in. If `app.user` is null, the user is not logged in.
If it's not null, the user is logged in.

## Adding Login and Logout Links

Let's utilize the `app.user` variable to add login and logout links at
the top of our page. Back in `base.html.twig`, first, remove the `dump()`.

Find the "Contact" link, we'll add our new links after it. Start by creating an if block
using `{% if app.user %}`. Then, add an `{% else %}` statement, followed by an `{% endif %}`
to close the block. Copy the "Contact" link above to ensure the styling is consistent. Paste
inside the `if`. Here, `app.user` is *truthful*, meaning the user is logged in, so we want
to show the logout link. Change the link text to "Logout" and it's href to `{{ path('app_logout') }}`.
Remember, this `app_logout` route is from our `SecurityController`'s `logout()` method.

Next, copy the "Logout" link and paste it inside the `else`. Change the `path` to `app_login` and the link
text to "Login".

That should be it, let's give it a whirl!

Refresh the homepage... We're still logged in, and sure enough, we see the logout link. Click it...
we're logged out and see the login link instead. Click it... and log back in...

Sweet! Our user experience is much better!

## Generating the Logout Link

There's another way to generate the logout link. Back in `base.html.twig`, replace `path('app_logout')`
with `logout_path()`. Notice there's a `logout_url()` function as well. The difference is that `logout_path()`
generates an *absolute path*, like `/logout`, while `logout_url()` generates an *absolute URL*, like
`https://starshop.dev/logout`. In most cases, `logout_path()` is good enough.

Back to the browser... refresh... hover over the "Logout" link... and in the bottom left, the link
is still `/logout`.

You might be wondering why this function is needed. Why would I use `logout_path()` instead of
`path('app_logout')`? For most apps, there's no difference. In advanced apps with multiple firewalls,
using `logout_path()` automatically detects the current firewall and generates the correct logout link. 

Click into the function to find its definition in `LogoutUrlExtension`. `getLogoutPath()` accepts a `$key`
parameter, which is the firewall key defined in `config/packages/security.yaml` under the firewalls section.
Passing a key allows generating logout links for different firewalls.

## `ROLE_USER` and `is_granted()`

There's another common method to check if a user is logged in. Our users have the concept of *roles*. In the
web debug toolbar, if we hover over the "User" tab, we see our current user has one role: `ROLE_USER`. We've
configured *every* authenticated user to have this role.

Back in our `if` statement in `base.html.twig`, replace `app.user` with `is_granted('ROLE_USER')`. This function
checks if the current user has the specified role. Only authenticated users will have this role.

If we refresh the homepage, everything still works as expected. We can logout... login... and the links
change as expected.

We'll dig deeper into roles and `is_granted()` in a future chapter.

We still have one small security issue left with our logout link. Let's fix that next!
