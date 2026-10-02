# Redirecting After Login with _target_path

When we're logged out and try to access a protected resource - say `/admin/user` -
we're redirected to the login page. Log in as our admin, `janeway@starfleet.space`
with password `coffeeblack`... and after login, we're redirected to the resource we
were trying to access. That's default functionality.

But check this out. Log out. Say we're on a starship page and we click "Login". This
time log in as `picard@enterprise.space`, password `makeitso`.

Remember: when we clicked login, we were on a starship page. But now, after logging
in, we're back on the homepage. It would be cool if we went back to the page we were
on when we clicked login.

## The `use_referer` Dead End

Jump over to the terminal and look at the config options for our form login:

```terminal
symfony console config:dump security firewalls
```

Scroll up to find `form_login`. Here we go. We have `default_target_path`, and that's
set to our root - the homepage. That's why we end up there after logging in.

But we also have this `use_referer` option. Let's check it out. In
`config/packages/security.yaml`, under `form_login`, set `use_referer` to `true`:

[[[ code('c248b104d3') ]]]

Back over in our browser, logout... Ooops, I have a typo in my config... Not `user_referer`,
it's `use_referer`.

Now refresh... and visit a starship page. Oh, make sure you're logged out first.

Click "Login" from this page and log in as `picard@enterprise.space`, password `makeitso`.

And... we're back on the homepage.

Didn't work... `use_referer` tells Symfony to redirect to the `Referer` HTTP header browsers pass
along when you click links. When we first visit the login page, it's set correctly... but
as soon as we submit the login form, we've lost it... You could probably make it work
with some fancy footwork, but let's keep it simpler.

## Passing `_target_path` in the Link

Back in the IDE, remove `use_referer`.

Look at the config in the terminal again. There's another option: `target_path_parameter`, and it's
set to `_target_path`. We can set this on the session *or* as a query parameter.
Let's do the query parameter.

Open `templates/base.html.twig` and find where we generate the login
link - right here. We're generating a path to the `app_login` route. Add some parameters:
`_target_path` set to `app.request.pathInfo`:

[[[ code('f4bff617c4') ]]]

Our `app_login` doesn't require any route parameters, so any extras are added as query parameters.
Exactly what we want!

Over in the browser, log out first, then click on a starship. Now hover over "Login":
down in the bottom left, you can see `_target_path` is set to the path for this page.
Click login and we can see it up in the URL bar. That tells Symfony to redirect there
afterwards.

`picard@enterprise.space`, `makeitso` - and it does!

## A Nicer Parameter Name

The only thing I don't like is that `_target_path` feels a little internal. Maybe
that's just me. But there's a way to customize it: that `target_path_parameter`
option sets which parameter we want to use.

Back in `security.yaml`, set `target_path_parameter` to `referrer`:

[[[ code('e2db3fe628') ]]]

And in `base.html.twig`, use the same name:

[[[ code('46428f3036') ]]]

Go to a starship page. Now if we click login, sure enough, we're using `referrer` -
and it looks a little nicer, I think. `picard@enterprise.space`, password
`makeitso`, and that works.

## A Canonical Link for Search Engines

One other small thing. Because every single page now has a unique login link, we want
to tell search engines not to crawl every one of them. The best way to do that is a
canonical link.

In `base.html.twig`, in the `head`, add a `metadata` block... Leave it empty here:

[[[ code('a735c732ef') ]]]

Now, inside `templates/security/login.html.twig`, override that block and add a
`link` tag with `rel="canonical"`. The `href` needs to be an *absolute* URL, so
generate it with `url()` and the `app_login` route:

[[[ code('0aa21f2ad1') ]]]

I'm sure search engines are smart enough to not need this, but it is a good practice to be explicit.

Next: let's look at how we can log in with either an email or a new username field
that we'll add to our users.
