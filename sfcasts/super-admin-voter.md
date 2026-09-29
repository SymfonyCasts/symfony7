# Creating a Super Admin Voter

Our app has the concept of an admin, and we've used role hierarchy to make sure that
an admin also has `ROLE_CAPTAIN` and `ROLE_ALLOWED_TO_SWITCH`. And in our
`StarshipVoter`, we have this escape hatch down in `voteOnAttribute()`: if they're an
admin, they can just do anything.

This works, and it's great for fine-grained control - where you have a concept of an
admin and you specifically control what they can do.

But a lot of the apps I've worked on have the concept of a *super* admin: someone in
IT, or a website administrator, who can just do anything. A level above admin.

You might think you can handle that with role hierarchy, but there are so many places
and so many voters you'd need to update. What I like to do instead is create a voter
specifically for super admins.

## A New Voter

First, in `src/Story/AppStory.php`, change Janeway from `ROLE_ADMIN` to
`ROLE_SUPER_ADMIN`:



We've updated the fixtures, so over in the terminal, reload them with:

```terminal
symfony console foundry:load-fixtures
```

Now create our custom voter:

```terminal
symfony console make:voter
```

Call it `SuperAdminVoter`.

Jump back to the IDE and find it: `src/Security/Voter/SuperAdminVoter.php`. Clear
everything out - the constants, `supports()`, and `voteOnAttribute()`.
We'll start with a blank slate.

For `supports()`, we're going to support *everything*, so just return `true`:



Any attribute, any subject: it doesn't matter, this voter supports it.

## Voting on `ROLE_SUPER_ADMIN`

Here's the important part: `voteOnAttribute()`. Whether they have permission or not
is what we check here.

There are a couple of ways to do this. Remember in `StarshipVoter`, we used the
access decision manager to decide for the token - we could inject that and do the
exact same thing. We did that because `ROLE_ADMIN` might be part of a role hierarchy,
so it might not be set right on the user.

But I'm going to say that super admins have to have `ROLE_SUPER_ADMIN` set directly
on the user. That makes this real simple:`return in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true);`



`$token->getRoleNames()` is basically a shortcut to getting the roles off of our user and
`true` gives us strict matching.

Just remember when checking roles this way, it only checks the roles that are *directly* on the user,
not the role hierarchy.

## Excluding the Authentication Attributes

This *almost* is enough... but we have a problem... Some attributes aren't permission-based...
Can you think of which ones?

Look at the constants in `AuthenticatedVoter`. These are attributes you can check for, but they
aren't about permission, they're about *how* you're authenticated. We don't want super admins
to be able to bypass these checks! If they do, our *sudo mode* we added in the last chapter
would be completely bypassed!

Back in `SuperAdminVoter`, add a `private const EXCLUSIONS` and, inside, add all of
those constants from `AuthenticatedVoter`. `IS_AUTHENTICATED`... `IS_AUTHENTICATED_FULLY`...
`IS_AUTHENTICATED_REMEMBERED`... `IS_IMPERSONATOR`... `IS_REMEMBERED`... and... `PUBLIC_ACCESS`:



One, two, three, four, five, six - and over here, one, two, three, four, five, six.
Great, got'em all!

Now, instead of returning `true` in `supports()`, return
`!in_array($attribute, self::EXCLUSIONS, true)`:



This voter will now never run when we're checking for one of those attributes.

Your app might differ in certain ways - you might have other roles or permissions
that you don't want the super admin voter to answer for. If that's the case, just add
them to this list.

## Trying it Out

Go to the homepage. I'm logged in... but because we reloaded the fixtures, I should be
logged out when I refresh. Perfect.

Log in as `janeway@starfleet.space` - she's our super admin - with password
`coffeeblack`.

Go to `/admin/user`. Sure enough, we can still access this page even though it
requires an admin: our super admin voter is granting us access.

The other thing to check: go to `/starship`. If you remember, typical users can only
edit their own ship, but admins can edit *any* ship. And yep, we have that same
functionality now that we're a super admin. Edit one of the starships.

## Watching it in the Profiler

Let's see how it's working. Go to the web debug toolbar, click through to the
security profiler and take a peek at the "Access Decision" tab. We can see all the
voters that are being used.

Down in the access decision log, we can see `ROLE_ADMIN` was *granted*. We don't have
that role directly or via role hierarchy. Click "show voter details". The `RoleHierarchyVoter`
denied us, but the `SuperAdminVoter` granted it.

The next one shows us we were granted for editing a starship. "show voter details" shows
us that `StarshipVoter` granted us. This is because of the `ROLE_ADMIN` check in `StarshipVoter`.
`StarshipVoter` checked for `ROLE_ADMIN`, and the `SuperAdminVoter` granted it.

Down here, these `IS_IMPERSONATOR` checks were denied. That's good! We don't have that attribute,
and our super admin voter didn't auto-grant it!

Our super admin voter is working like a charm!

Next: let's improve the user experience of our login flow.
