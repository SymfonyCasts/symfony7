# Authentication Attributes

Symfony Security has some special authentication attributes. These are defined
as constants on the `AuthenticatedVoter` class. Find that in the Symfony
Security component. I'm on PhpStorm, so I'll use double-shift to launch
the find anything feature. Now I'll look for `AuthenticatedVoter`...
here it is!

## Understanding Security Attributes

We briefly touched on *roles* earlier, all our users have a `ROLE_USER`. We
then used the `is_granted()` Twig function to check if the user has that role.
These attributes are similar to roles, in that they are strings, but they're
not the same. Roles are attached to the user object. These authentication attributes
are determined at runtime based on the current login state. They tell us whether the
user is logged in, but also *how* they were logged in.

The best way to understand these attributes is to see them in action.

## Practical Demo of Security Attributes

Open `src/Controller/MainController` and find our homepage method. Add `dump()`, and
inside, we'll have an associative array with the following keys and values:

`ROLE_USER => $this->isGranted('ROLE_USER')`. I know we said these attributes are not roles,
but they're used in the same way, so, we'll check for the `ROLE_USER` role first.

The `isGranted()` method is available to us because our controller class extends from `AbstractController`.
This base class provides us with a lot of useful methods, including security helpers.

Next item, `AuthenticatedVoter::IS_AUTHENTICATED_FULLY => $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)`.
I'm using the constants here but using the raw string values would work just as well.

Then, `AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED => $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)`,
`AuthenticatedVoter::IS_AUTHENTICATED => $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED)`,
`AuthenticatedVoter::IS_REMEMBERED => $this->isGranted(AuthenticatedVoter::IS_REMEMBERED)`, and finally,
`AuthenticatedVoter::PUBLIC_ACCESS => $this->isGranted(AuthenticatedVoter::PUBLIC_ACCESS)`:

[[[ code('acf64538a7') ]]]

Did I get them all? 1, 2, 3, 4, 5, and in `AuthenticatedVoter`... 1, 2, 3, 4, 5, 6. I'm skipping
`IS_IMPERSONATOR` for now, as it's related to impersonating users, and we'll cover that in
detail later.

## Live Testing of Security Attributes

Time to see these attributes in action. Head to our app's homepage... ensure you're logged
out... and refresh.

Cool, here's our dump.

They're all false, except for `PUBLIC_ACCESS`, which is true. 

`PUBLIC_ACCESS` is the odd one out. Unlike `IS_AUTHENTICATED_FULLY` or `IS_REMEMBERED`, it does
not describe the current user. It's mainly used in your security configuration to specifically
mark a route or path as public. As an `isGranted()` check, it’s basically useless because it
will pretty much always return `true`.

Now, go to the login page and login as `picard@enterprise.space` and password: `makeitso`. Keep
the remember me option checked.

Now check the dump. `ROLE_USER` is `true`, no surprise there, all users have this role.

`IS_AUTHENTICATED_FULLY` is `true` only when the user has logged in during the current session,
like we just did. `IS_REMEMBERED` is `false` because we're not just remembered, we're fully authenticated. 
`IS_AUTHENTICATED` is `true` whenever you're authenticated, whether fully or remembered.

Let's do our trick again to delete the session cookie. Inspect the page, open the "Application"
tab, find the `PHPSESSID` cookie, and delete it. Now refresh the page and check the dump.

What's changed? Well, `ROLE_USER` and `IS_AUTHENTICATED` are still true, but `IS_AUTHENTICATED_FULLY` is
now false, and `IS_REMEMBERED` is now true.

You can see how these attributes can be used to see how the user is authenticated. Why is this important?
Why not just have `IS_AUTHENTICATED` and that's it? Well, a remembered user is considered less secure than
a fully authenticated user. Imagine a user who has logged in on a public computer and checked the Remember
Me option. They close the browser and walk away. The next person opens the browser and visits our site.
They'd still be logged in as the previous user!

You've probably seen how some sites require you to re-enter your password when performing sensitive actions,
like changing your email address or password. This is how you can do the same thing in your apps!

You can restrict certain actions to only fully authenticated users by ensuring they have the
`IS_AUTHENTICATED_FULLY` attribute, while allowing remembered users to perform less sensitive actions
by just checking for the `IS_AUTHENTICATED` attribute.

## `IS_AUTHENTICATED_REMEMBERED`

Let's test one last permutation, logout... and login again, but this time, uncheck the Remember Me option...

Check the dump. These are all the same values as when we logged in with Remember Me checked... even
`IS_AUTHENTICATED_REMEMBERED`... What's with that?

`IS_AUTHENTICATED_REMEMBERED` is a bit of a historical naming quirk in Symfony. It doesn't mean:
"is this user authenticated via remember-me". It means: "Is this user authenticated *at least* as much
as a remember-me user?".

`IS_AUTHENTICATED` was added later to mean the same thing, but without the confusing name. You might
see `IS_AUTHENTICATED_REMEMBERED` used in older Symfony code, but you should use `IS_AUTHENTICATED`
going forward.

## Wrapping Up

Phew! That's a wrap for these special attributes! Next, we'll dive deeper into roles and the
role hierarchy system.
