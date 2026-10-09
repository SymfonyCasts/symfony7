# Restricting Impersonation with a Voter

In our user admin, admins have the ability to switch to different users using
Symfony's impersonation - or `switch_user` - feature. The way this works is that, in
our config, we used role hierarchy to give every admin `ROLE_ALLOWED_TO_SWITCH`.
That's what Symfony checks: if you have that role, you're allowed to switch to any
user.

I want to add some logic to this. The rules: you can't switch to *yourself*. I'm
logged in as Janeway, and if I click "switch to"... I don't even want to know what
happens. Probably implodes the universe.

And I don't want admins to be able to impersonate other *admins*. In our app, an
admin would be able to impersonate a captain, but not another admin or super admin.
Super admins can impersonate anybody - except themselves.

We can do this with our own custom voter, because when Symfony checks for that role,
it also passes the target user as the *subject* of the `isGranted()` check.

## A Custom `CAN_IMPERSONATE` Attribute

The first thing: remove `ROLE_ALLOWED_TO_SWITCH` from `role_hierarchy`, because we're
going to use a voter to check this - it's not going to be part of `ROLE_ADMIN`
anymore.

The name of this role is configured up above under `switch_user`, and the option is
just called `role`. We're going to use a custom one that *doesn't* include the
`ROLE_` prefix, because we want Symfony's packaged role voter to ignore it completely
and leave this to our custom voter. Call it `CAN_IMPERSONATE`:



Now, in `templates/user_admin/index.html.twig`, wrap the impersonation link in an
`{% if is_granted('CAN_IMPERSONATE', user) %}` - passing the user from the current
loop as the subject. Add `{% endif %}`... and indent the guts:



The other thing: our `SuperAdminVoter` in `src/Security/Voter/` has that `EXCLUSIONS`
list. Add `CAN_IMPERSONATE` to that list, so super admins can't bypass our new voter.



Double-check that this is working. Back on the user admin, refresh. Sure enough, that
link is gone - super admins can't bypass this check anymore.

## Creating the `ImpersonationVoter`

Over in the terminal, create a new voter:

```terminal
symfony console make:voter
```

Call it `ImpersonationVoter`.

Let's go check it out. Remove the constants - we're not going to use them - and
clear out `supports()` and `voteOnAttribute()`.

In `supports()`, `return 'CAN_IMPERSONATE' === $attribute && $subject instanceof User`:



Add some docblocks to `voteOnAttribute()`: clear them all out except
for the subject. Because of that check up above, we know the subject is going to be our
`User` entity.

Now, get the `$currentUser` from the token and make sure it isn't null. If we can't get
the current user, return `false`. Now check whether the subject's user identifier is the same as the
current user's identifier - that's so we can't impersonate ourselves:



Next, copy that `in_array()` check from our super admin voter: if the current user has
`ROLE_SUPER_ADMIN`, return `true`. Super admins are allowed to impersonate anybody else -
that's the bypass:



Remember, when we created the super admin voter, we decided to check that role right
off the user itself, *not* through the role hierarchy.

## `isGrantedForUser()`

But now we *do* want to check the role hierarchy, to see if the target user is an
admin. For that, create a constructor... And inject the `Security` service as `$security`.

Back down in `voteOnAttribute()`, `return !$this->security->isGrantedForUser()`:



Normally you've used just `isGranted()`. `isGrantedForUser()` lets you check an
*arbitrary* user instead of the currently logged-in one - really useful in things like
CLI commands where there is no current user. It works exactly like `isGranted()`,
except the first argument is the user you're checking, the second is the attribute, and the third
is the optional subject.

Pass `$subject` as the user and `ROLE_ADMIN` as the attribute.

Back to the page and refresh. Cool! We can't switch to ourselves - that rule works -
but we *can* switch to a captain.

## More Users to Play With

Let's add some extra users to better see the feature. Go to
`src/Story/AppStory.php` and, right below where we create our users, add
`UserFactory::createMany(5)` with their roles set to `ROLE_ADMIN`. Duplicate
this line to add 5 more but for `ROLE_CAPTAIN`:



That creates five admins and five captains on top of our existing users. Load them:

```terminal
symfony console foundry:load-fixtures
```

Back in the app, refresh - we'll be logged out, because the user changed. Log in with
Janeway again, password `coffeeblack`.

Before we go further, I want to fix something that's really driving me crazy: these
ugly action buttons. Back over in `user_admin/index.html.twig`, fix the container with `flex-start`, and
because that button has two words in it, add `whitespace-nowrap`:



Refresh. There we go - much better.

## Trying the Rules

Because I'm a super admin, I can't switch to myself, but I *can* switch to anyone
else, even admins - exactly the rule we decided.

Now log in as one of our new admins to see whether they're blocked from switching to
other admins. Copy one of their emails, log out, and log in. If you look back at
`AppStory` and `UserFactory`, we set the password to `engage` for every user unless we
override it. So type that in: `engage`.

I'm logged in as this admin now. Go back to the listing at `/admin/user` and check this
out: we can switch to the captains, but we *can't* switch to the super admin or to any
of the other admins.

Nice! Feature complete!

Next: let's set up a password reset system.
