---
challenges:
    - key: impersonation-path-post-only
      question: |
          We added an `app_impersonate_user` route with `methods: [POST]`, then pointed
          `switch_user`'s `path` option at it. Why does that make impersonation POST-only?
      answers:
          - With `path` set, impersonation only happens on that route, and a GET request doesn't match it
          - "`path` tells Symfony to reject every GET request that has a `_switch_user` parameter"
          - The route's controller checks the request method before switching users
          - "`enable_csrf: true` is what blocks GET requests, and `path` just renames the URL"
      explanation: |
          Once `path` is set, the switch-user listener stops watching every URL and only acts
          on that route. Since the route only matches POST, a GET request never triggers a
          switch, and there's no controller needed at all.
    - key: impersonation-who-is-allowed
      question: |
          Our new `/impersonate` route has no controller and no `access_control` rule. So what
          stops any logged-in user from posting to it and switching to Captain Picard?
      answers:
          - The switch-user listener checks the `role` from our `switch_user` config, `CAN_IMPERSONATE`, before switching
          - Nothing - we need to add an `access_control` rule for `^/impersonate`
          - The CSRF token can only be generated for admins
          - Symfony only allows impersonation from pages under `/admin`
      explanation: |
          The switch-user listener handles the request before any controller or
          `access_control` rule runs, and it checks the configured `role` - our
          `CAN_IMPERSONATE` - itself. The CSRF token guards against forged requests, while
          `CAN_IMPERSONATE` decides who's allowed.
---

# Hardening Impersonation with POST & CSRF

Let's look at our site's impersonation system. Log in as our super admin:
`janeway@starfleet.space`, password `coffeeblack`. Head to `/admin/user` to see a list of
all the users on our site, each with a "switch to" option. We have a voter that makes
sure we can't switch to ourselves - that's why there's no "switch to" on my own row.

But hover over one of these links and look in the bottom left of your browser. We have a
few problems. First, it's a GET request, so anything that follows links, like a browser
prefetching pages, could trigger it. Second, the user's email is right there in the URL,
so it ends up in places like your web server and proxy logs. And third, there's no CSRF
protection. Symfony 8.2 gives us a way to solve all three. Let's implement it!

## A Dedicated Impersonation Route

First, we need a route. In `src/Controller/SecurityController.php`, we have the `logout()`
stub. It's never actually called - it only exists so we have somewhere to put the
`#[Route]` attribute. We *could* add an impersonation route here the same way. But a
cleaner alternative is to configure it in `config/routes.yaml`. Below the existing
routes, add `app_impersonate_user`, with `path: /impersonate` and `methods: [POST]`:

[[[ code('306a98292e') ]]]

We don't need a controller, because the impersonation system takes over completely,
just like logout does. And we don't need any `access_control` for this route either -
again, the impersonation system takes care of that. All we need is a path that
requires the POST method.

## Configuring `switch_user`

Now open `config/packages/security.yaml` and find the `switch_user` config. `target_route`
tells it where to send you after switching - the homepage - and `role` is the custom role
we require to impersonate. Add `path` set to our new route, `app_impersonate_user`. Now
impersonation *only* happens on that route - and since it requires POST, so does
impersonation. Then enable CSRF with `enable_csrf: true`:

[[[ code('cb5671d7a1') ]]]

## The `impersonation_button()` Function

Since this is now a POST request with CSRF, we need a form. And just like the
`logout_button()` Twig function from the last chapter, there's now an
`impersonation_button()` too.

Open `templates/user_admin/index.html.twig` and scroll down to where we render the
impersonation link. We're using the original `impersonation_path()` for the "switch to"
link. Above, add `impersonation_button()`, which works a lot like the logout
button. If you jump into it, you'll see it takes the label, the user identifier, then an
optional target URI: where to send the user after they impersonate. If you don't set
it, it falls back to the `app_homepage` route from our config. And just like the logout
button, you can pass `attr` for the button and `formAttr` for the form.

Set the label to `switch to`. Then add `attr`, and since it's the full set of HTML
attributes, set `class` inside. Copy all the classes from the old link and paste them in
there. Now remove the old link:

[[[ code('2af01eff95') ]]]

## The `impersonation_exit_button()` Function

The other place we need this is the exit link, in `templates/base.html.twig`. Scroll up
a bit... here it is. `is_granted('IS_IMPERSONATOR')` means we're currently
impersonating someone, and inside we have a link built with
`impersonation_exit_path()`. Above, add `impersonation_exit_button()`. This one just
needs a label, then the optional target URI, plus `attr` and `formAttr`. We are
passing a target URI to `impersonation_exit_path()`, so we'll do the same for the button.

Set the label to `Exit Impersonation`. The second argument is the path from below,
which sends us back to the user admin when we exit. Last, pass `attr` with `class`, then
copy the classes from the old link and paste them inside:

[[[ code('89d7e19b97') ]]]

Now delete the old link.

Oh, and I just realized we forgot something on the user admin's
`impersonation_button()`. We passed the label and the attributes, but not the user
identifier! Add `user.userIdentifier` as the second argument.

## Trying It Out

Back on the user admin page, refresh. Hover over one of the switch two's, it's now a button...
Inspect it, and you'll see it's a whole form, with its `action`
pointing to `/impersonate`. There's a field holding the user we're switching to -
because it's a POST, that's what keeps the identifier out of the URL. Then we have the
CSRF token and the submit button. Perfect!

Close that and try impersonating Picard: click "switch to"... perfect! We're on the homepage, and
successfully impersonating them. Now click "Exit Impersonation", and we should land back
on the user admin. And... there we go! That worked. Cool!

Next, let's look at some user checker enhancements in Symfony 8.2!
