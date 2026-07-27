# User Roles

Let's dive deeper into user roles and how they can be used to control access
to different parts of your application. We have this link to our `/parts` page,
which lists a bunch of Starship parts. Let's restrict this page to starship
captains only.

First thing's first: we need to find this controller. In your IDE, navigate to
`src/Controller/PartController` and jump to the `index()` method.

Here, if they're not a captain, we want to throw an exception, specifically
an access denied exception.

## Securing the Controller

Inside, at the beginning, add `if (!$this->isGranted('ROLE_CAPTAIN'))`.
Creating a role is as simple as that. You don't need to register it anywhere, you can just
start using it! The only requirement is, to be recognized as a role, it must start with `ROLE_`.

Inside the `if`, write `throw $this->createAccessDeniedException('Only captains allowed!')`:

[[[ code('ad9767f3f7') ]]]

This method, just like `isGranted()`, is another helper that comes from `AbstractController`.
It throws the correct exception, which will be caught by Symfony's security system and handled
appropriately.

## Checking Our Work

Hope over to the app in your browser and refresh the `/parts` page. Huh. We're redirected to
the `/login` page. This is a feature of our `form_login` authenticator. When an access denied
exception is thrown, and the user isn't logged in, you'll be redirected to the login page.
Pretty cool!

Ok, so login as `picard@enterprise.space`, password `makeitso`. Hmm, we're still on the
`/login` page... We're logged in because I see our logout message, but I expected to
be redirected to the page that threw the access denied exception... Another cool
feature of the `form_login` authenticator.

Open the profiler panel and click "Search Profiles". This shows us all the recent requests.

This `POST` to `/login` was us submitting the login form. We were then sent to `/parts`, which
gave a 403 error. But then, we're back on `/login`... I expected to be redirected to `/parts`,
but not then redirected back to `/login`...

The problem here is Turbo. It's capturing the form submission, and sees the redirect is an error,
so it just stays on the current page. Not the greatest experience...

## Tweaking the Logout Form

While Turbo works well with normal Symfony forms, our login form isn't handled the same way. I
want the user to see the access denied error so they understand the problem.

To fix this, we can just disable Turbo for our login form. First, reset by logging out.

Now open `templates/security/login.html.twig`... find the `<form>` element... and add the attribute
`data-turbo="false"`:

[[[ code('e52f1817a1') ]]]

## Testing Our Login Again

Let's give this another try. Click "Parts" and login again as `picard@enterprise.space`...
`makeitso`. And... nice! We're on the parts page, and we see the 403 access denied error!

This is totally expected! While Jean-Luc is a captain, his user account doesn't yet have the
`ROLE_CAPTAIN` we're checking for!

## `denyAccessUnlessGranted()`

Before we give his account this role, let's look at some alternate ways we could check for this role.
In `PartController::index()`, we can replace this whole `if` block with
`$this->denyAccessUnlessGranted('ROLE_CAPTAIN')`:

[[[ code('7e45e88255') ]]]

This is another helper that does the same thing as our `if` statement. We could
add our custom error message here but let's see what the default looks like.

Back in the browser, refresh... Still a 403 error but now our message is:

> Access Denied. The user doesn't have `ROLE_CAPTAIN`.

I actually like this message better as it explains the problem more clearly. Remember,
these custom messages never show to the end-user. They're only for us developers
to help us understand the problem.

## `#[IsGranted]` Method Attribute

Since there's no logic attached to throwing the access denied exception, we want the entire method protected,
we can remove this entirely and use a method attribute. Above the method, add `#[IsGranted('ROLE_CAPTAIN')]`:

[[[ code('0f60ceeb12') ]]]

Now refresh the page... same error.

## `#[IsGranted]` Class Attribute

Our `PartController` only has one action method, but if we had multiple methods that all require the same role, we can
move the attribute to the *class level*:

[[[ code('cc4405f412') ]]]

Try that... and refresh the page again... same error. I love keeping my controller code clean and simple!

## Security Profiler Panel

To get a deeper look at what's happening behind the scenes. Open the Security Profiler panel, and click the
"Access Decision" tab. The "Access decision log" section shows us all the checks that were made for this
request. We can see that there was a check for `ROLE_CAPTAIN` and it was *denied*.

## Adding the Role

Time to officially make Jean-Luc a captain! Open `src/Story/AppStory` and find where we're creating his user.
Add `'roles' => `, and in an array, `ROLE_CAPTAIN`:

[[[ code('d28a9583c1') ]]]

At your terminal, reload the fixtures with:

```terminal
symfony console foundry:load-fixtures
```

Reload the page... we're logged out because we reloaded the fixtures. Log back in...

Nice, we're in! Jump into the Security profiler panel again. First of all, check the `Roles` property.
We have `ROLE_CAPTAIN` and `ROLE_USER`. Remember, we have logic in `User::getRoles()` that always adds
`ROLE_USER`. `ROLE_CAPTAIN` is dynamic, as it's stored in the database.

Check the "Access Decision" log again. `ROLE_CAPTAIN` was now granted!

So was `ROLE_USER`... What checked that? Remember in our `base.html.twig` template, we're using
`is_granted('ROLE_USER')` to determine if we should show the login or logout link:

[[[ code('fcd4f2b349') ]]]

This log shows these checks also!

Next, we'll explore how roles can be inherited from other roles.
