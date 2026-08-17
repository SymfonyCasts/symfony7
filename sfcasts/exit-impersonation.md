# Exiting Impersonation & IS_IMPERSONATOR

Right now, we're logged in as Janeway and we're impersonating Picard. But in
production, without this web debug toolbar, it would *not* be clear that you're
impersonating someone.

And I've actually been burned by this! It's very easy to impersonate someone, forget
that you're impersonating them, and then do a bunch of things on the site that you
never should have done as that user.

So I like to make it super clear in production that you *are* impersonating someone -
and then also give a clear way to exit that impersonation.

## The `IS_IMPERSONATOR` Attribute

The first thing to figure out is how we can check whether we're impersonating someone.

Remember earlier in the course, when we talked about the built-in security attributes?
Open the `AuthenticatedVoter` class from the `Security` component. We talked about
most of these but kind of glossed over this
`IS_IMPERSONATOR` attribute. *This* is the attribute you check with `is_granted()` to
see if you're impersonating someone. That's how we can know.

## A Big Red Warning

Close that and open `templates/base.html.twig`. Right at the top, just under the
opening `<body>` tag, add `{% if is_granted('IS_IMPERSONATOR') %}`... and close it with
`{% endif %}`.

Inside, I'm going to paste a little bit of code that you can find in the script below.
And... I'll clean this up a little bit.

What this is doing is adding a red border around the whole page, plus a little text in
the top-left that says who we're impersonating.

Jump back to the homepage and refresh... there we go! Now it's super clear. Big
warning. I like this. Any page we go to, it's obvious that we're impersonating
Jean-Luc Picard.

## Adding an Exit Impersonation Link

Next, if we're impersonating someone, I want to swap this logout button with an "exit
impersonation" link.

Still in `base.html.twig`, scroll down to where we're rendering the login and logout
buttons. Above... before we check for `ROLE_USER` - add
`{% if is_granted('IS_IMPERSONATOR') %}`. And for the `if` below, change it to
an `elseif`.

Then, down here, copy the login link HTML and paste it inside our new `if` so it's styled
similar. For the text, write "Exit Impersonation".

And for the `href`, we can render another Twig function: `impersonation_exit_path()`.

By the way, there's `url` versions of these functions. These do the exact same thing
but generate an absolute URL instead of just the path.

So use `impersonation_exit_path()` with no arguments. And that should be it.

Jump back to the browser and refresh. Cool! There's our "Exit impersonation" link.
Hover over it, and down in the bottom left, we can see the special link that's going
to trigger exiting impersonation.

## Redirecting After Exiting

One little nuance. Say we're on a page that's *not* the homepage...like the "Parts" page... and we click "Exit
impersonation". It did successfully exit: we're back as our actual user Janeway, and
that red border is gone. But we're still on the parts page - it didn't redirect us to the
homepage.

If you remember, over in `config/packages/security.yaml`, we configured this
`target_route`. That does *not* get triggered when you're *exiting* impersonation.

But the first argument to `impersonation_exit_path()` is the path you want to send the
user to after exiting. So what I like to do is send them back to where they chose
the impersonated user: `impersonation_exit_path(path('app_user_admin_index'))`. Then
they can switch to someone else if they want.

Try that out. Manually go to `/admin/user`... find Picard and hit "switch to". That
all works. Now go to Parts... and hit "Exit impersonation". Cool! We're right back at
the admin list to switch to someone else - kind of back where we started.

That's it for impersonation. Next up: login throttling!
