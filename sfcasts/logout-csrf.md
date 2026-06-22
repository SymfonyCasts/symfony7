# Protecting Logout with CSRF

We have a minor hiccup with our logout link. You can logout by just
pointing your browser to `/logout`. This means, theoretically, another site
could send this request on your behalf, effectively forcing you out of
your session. To visualize this, copy the link address and paste it into
a new tab. Close this tab, and refresh the original. We're now
logged out.

The first thing we should do is stop allowing `GET` for the `/logout` route.

Requests that *change state* should never use the `GET` method. Browsers, crawlers,
link previewers, and other tools are free to make `GET` requests automatically because
they're expected to be safe.

I remember reading about someone who built a web app to control their smart garage door
opener. They wanted an easy way to open and close it remotely, so they created a `/toggle-garage-door`
endpoint. Hit the endpoint, and the garage door would open if it was closed, or close if it was open.
Unfortunately, because it accepted `GET` requests, a browser prefetch or over-eager link preview could
trigger it automatically. Funny... until you're on vacation, check to make sure the garage door
is closed, and your browser opens it instead.

Logout isn't nearly as dramatic, but the same principle applies. Logging out changes application state,
so it shouldn't happen because something decided to follow a link. Let's fix that by switching our
logout link to use a `POST` request.

## Changing the Logout Link to a POST Request

In our `SecurityController`, find the `Route` attribute for the `logout()` method. Add
`methods: 'POST'`. Does that solve it? Let's see.

Back in our app, login... Now click "Logout"...

Error! A 405 "Method Not Allowed" error. Why? Our logout link is just a normal link, so it's using `GET`.
We've mitigated the *state change* issue, but now our own site can't log us out!

How can we make this a *post* link? The simplest way is to make it a submit button wrapped in a
form.

## Creating a Hidden Form for Logout

Head over to `base.html.twig`. Right after the logout link, add a `<form>` tag. `action="{{ logout_path() }}"`
and `method="post"`. By default, forms are block elements, so add `class="inline"` to make it an inline element.

Inside, add a `<button>`, `type="submit"`. I want this to have the same styles as the nearby links, so add
`class=""` and copy/paste the classes from the link above. For the button text? `Logout`.

That should be it, remove the old logout link above.

Back to the browser and refresh... Now click "Logout"... Sure enough, we're logged out!

## Styling the Logout Button

Login again... and hover over the `Logout` link - now a button. Notice it doesn't have
the same cursor as the other links. By default, buttons don't have the same cursor as links.
To make it completely transparent to the user, in the button's class, add `cursor-pointer`.

Refresh the page... now the Logout button is completely indistinguishable from the other links.

## Implementing CSRF Protection

Now just because our logout route now requires a `POST` request, doesn't mean other sites can't 
trigger it. To completely prevent this, we need to implement CSRF protection for it.

Open `config/packages/security.yaml`. Under the `main` firewall section, find the `logout` key.
Add `enable_csrf: true`.

Let's try it out. Back in the browser, click "Logout"... Hmm, nothing happened...
It didn't log us out... but that's good! Our logout requires a CSRF token, but
we didn't pass one. This is what would happen if another site tried to log us
out - it couldn't.

Of course, we want our own site to be able to log us out...

We can do this with a hidden input field in our logout form. This will look very
similar to the one in our login form. So open `templates/security/login.html.twig`, find
the hidden field, and copy it.

In `base.html.twig`, find the logout form and paste it inside.

The `name` attribute value is important. Let's confirm what it should be by
looking at the default configuration.

At your terminal run:

```terminal
symfony console config:dump security
```

This is the full default configuration for the `security` bundle... and it's huge!
Narrow it down by running the same command again but adding `firewalls` to the end:

```terminal-silent
symfony console config:dump security firewalls
```

This just shows the `firewalls` section. Scroll up until you find the `logout` key...
`csrf_parameter` is what we're looking for... and `_csrf_token` is the default. And, that's
what we have in our logout form.

I want to use stateless CSRF protection, so I'll keep this `data-controller` attribute.
For the value, inside `csrf_token()`, use `logout` for the token ID.

Now we need to enable stateless CSRF protection for this ID. Open
`config/packages/csrf.yaml` and... nice, `logout` is enabled by default.

That should do it! Back in the browser, refresh... and click "Logout"... Nice!
It worked, we're logged out which means our CSRF configuration is working as expected!

Next, let's enable "remember me" functionality for our login form.
