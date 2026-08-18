# Impersonating Users with switch_user

Time to look at user impersonation - also called *switching users*. This is
the feature of having one user temporarily impersonate another.

This is really useful for site admins to debug a problem a specific user is having.
You can impersonate them to see exactly what they would see when logged into the
site... without having to know their password.

## Creating a User CRUD

The first thing we're going to do is make a user CRUD, so that admins can choose the
user they want to impersonate from the database.

Over in your terminal, run:

```terminal
symfony console make:crud
```

This is going to be for the `User` class. And we'll call it `UserAdminController`.
Do we want tests? No.

Ok, it generated all of these files. Jump over to your IDE and find the new
`UserAdminController`.

Only thing we'll change here is the route prefix. Change it to `/admin/user`:

[[[ code('9ec23052ee') ]]]

Since these routes are now prefixed with `/admin`, our `access_control` configuration already
restricts them to just admins. So we get that for free here.

Let's see this new CRUD! Over in the app, go to `/admin/user`... and we're redirected to
the login page. We need an admin, so use `janeway@starfleet.space`, password
`coffeeblack`.

This CRUD... doesn't look too hot... so let's pretty-ify it a bit. Open
`templates/user_admin/index.html.twig`. I'm going to select all and paste in
some nicer HTML, you can find this in the script below:

[[[ code('ed5e55b002') ]]]

Back to the browser and refresh. Much better!

You can see I've already added the "switch to" links, but they don't do anything
yet. This will be how an admin switches to another user.

## The `switch_user` Config

To see how the impersonation feature is enabled and configured, jump over to your
terminal and run:

```terminal
symfony console config:dump security firewalls
```

Scroll way up... until you find the `switch_user` section... here we go.

Here are the config options. `provider` is if you want to use a different user
provider for some reason. Remember, the user provider is how the security system
loads users. Most apps, like ours, just have the one provider, so we can leave
this as the default.

`parameter` is the query parameter in the URL that triggers the switch. This will be fine to
leave as the default too.

Switching users *is* a dangerous action - you don't want just
*any* user to be able to impersonate an admin of your site. So... by default, this feature
is locked behind a role: `ROLE_ALLOWED_TO_SWITCH`. Anyone without
that role cannot switch users.

Finally, `target_route` is where you want to send the user after the switch occurs.

## Enabling `switch_user`

Time to configure this. First, go to `config/packages/security.yaml`. Down in `role_hierarchy`, under
`ROLE_ADMIN`, add `ROLE_ALLOWED_TO_SWITCH`:

[[[ code('1277fdb16a') ]]]

Now our admins will have this role.

And then up here, uncomment `switch_user: true` - the `true` just enables the feature with
all the defaults.

I want to override one thing: `target_route`. Send them to `app_homepage`:

[[[ code('6e3a3f9563') ]]]

If you don't set this, then by default, they'll switch on whatever page they're
currently on. And you can imagine: if we switch to Picard on *this* page, he wouldn't
have access - so we'd immediately get a 403. I think it's always better to choose a
page to land them on, that everyone has access to.

## Wiring Up the Switch Button

Now to wire up that button. Go back to the index template and find that "switch to"
link... here it is. Clear out the `href`. There's a nifty Twig function to generate
the URL for us! Output `impersonation_path(user.userIdentifier)`:

[[[ code('4cfab28e2e') ]]]

Click through to find this on our `User` entity. If you remember, `getUserIdentifier()` comes
from the `UserInterface` and our `User`'s implementation returns the email address.

Now go back to the browser and refresh this user admin page. Hover over Picard's "switch to"... and
down in the bottom, the browser shows the URL that was created. Click the button!

## Impersonating Picard

We're back on the homepage... that's expected... but did we switch to Picard?

Check out the web debug toolbar. We're authenticated as `picard@enterprise.space`!
It worked!

There's even a little bit more information here. It shows us the **impersonator** -
that's our original user account. And we have this special "Exit impersonation" link.

That's all fine for development. But in production, there's no web debug toolbar, so
we need our own way to exit impersonation. And we should make it *very* obvious that
you're impersonating someone, in case you forget.

We'll cover that next!
