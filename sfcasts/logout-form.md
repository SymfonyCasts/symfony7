# Rendering a Logout Button with logout_button()

Let's look at our logout system. Open `src/Controller/SecurityController.php`. We have
this `logout()` stub where we just define the route - the security system takes it over
completely. But notice we set `methods: 'POST'`, so only a POST request can actually log
you out. That's great: if a GET request could do it, link scanners and the like could
log you out.

Now open `config/packages/security.yaml`. Down under the `main` firewall, we configure
`logout`. We set its `path` to our logout route, and we also set `enable_csrf: true` -
another thing you should always do with logout.

## The Hand-Built Logout Form

Next, open `templates/base.html.twig` and scroll down to find the logout link. Here it
is. Since logout needs a POST, we're building a form by hand. Inside, we render the CSRF
token, generated with `csrf_token('logout')`. `logout` is the default token ID the
security system uses, so it's just one of those things you have to know when you build
the template. Then, of course, we render the logout button.

## Hello `logout_button()`

In Symfony 8.2, all of this can be replaced by a new Twig function: `logout_button()`. If
you dig into the PHP that renders it, you'll see it takes a `label`, which defaults to
`Log out`, then the firewall `key`. You can also pass `attr` - HTML attributes for the
button - and `formAttr` - HTML attributes for the form element itself. And you don't
have to worry about the CSRF token ID, or even the path to your logout route. It knows
all of that automatically by reading your configuration.

Back in the template, inside `logout_button()` set the first argument, the label, to `Logout`, just
like we have below. Our button and form both have classes, so we need to pass those as
attributes. Add `attr`, and since this holds *all* the attributes, it needs a second
level: `{class: '...'}`. Copy and paste the button's classes in there. Then do the same
with `formAttr`, setting `class` to `inline`. Cool! Now remove the old form:



## Testing It Out

Let's try it! Over in the app, log in as `picard@enterprise.space`, password
`makeitso`. Right-click the logout button and choose "Inspect" to see what it renders.
Sure enough, it generates the same HTML we had before, including the proper CSRF token.

To double-check it works, click it... and we're logged out!

Next, let's see how impersonation has been improved in Symfony 8.2.
