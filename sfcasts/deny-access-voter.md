# Denying Access with a Voter

Ok, did you figure out our problem? It's actually a pretty big security issue. Check
this out.

We're logged in as Picard and we're editing the Enterprise - that's our ship, so
that's fine. But watch this: change the ID in the URL to `2`... and now we can edit
someone else's ship. Not good!

Also, if we go to `/starship/new`, we can create a brand-new starship, no
problem. Only admins should be able to do that!

So in our Twig templates, we used those `is_granted()` checks - but in this case,
they're just *hiding* the links. They're not actually preventing us from following
them. To do that, we need to deny access based on those same permissions right
inside `StarshipAdminController`.

## Denying Access in the Controller

Start with the `index()` method. We don't need any permissions here: anyone can
access the index.

But for `new()`, our rule was that you need to be an admin. So add
`#[IsGranted('ROLE_ADMIN')]`:

[[[ code('766adfc27a') ]]]

Keep going down to `show()`. Show is fine - anyone can view a starship, no problem.

But then `edit()`. And remember, for the `edit` permission, we need the starship as
the *subject*. So use that same `#[IsGranted]` attribute. The permission was
`edit`... and then pass a second argument: the subject. Just pass the string
`starship`:

[[[ code('b9dc859bbe') ]]]

As long as that references an object that's injected into your controller, it will
be used as the subject. So this will use the `Starship` that we injected into this
controller. Exactly what we want!

Then there's one more place: `delete()`. Same thing - `#[IsGranted]`, `delete`, and
the subject: `starship`:

[[[ code('5c61e58267') ]]]

Back to the browser and try going to the new page again... Access denied. Perfect.
We can still edit our own ship, but if we change the ID to `2`... we now get an
access denied exception. Security holes fixed!

## Admins Can't Edit Anything

One last thing I want to do: if you're logged in as an admin, you should be able to
do *everything* - edit any ship, delete any ship, and of course, create ships.

See what that looks like right now. Log out of Picard and log in as Janeway:
`janeway@starfleet.space`, password `coffeeblack`.

Now go to the starship admin. We see the "Create new" button - that's good - and we
can click it and get to the page. Perfect. But we don't see the edit actions
for *any* of the starships.

## Checking Roles Inside a Voter

The best way to handle this is to add a check for the role inside our voter. So go
to `StarshipVoter`.

At the top here - do you remember how we can check for roles or attributes inside a
service? Add a constructor.

Now, you might think to use the `Security` helper service that we talked about earlier... but
you actually shouldn't do that here. We'll see why in a second.

Instead, inject `private AccessDecisionManagerInterface $accessDecisionManager`:

[[[ code('d111220041') ]]]

Down below, before any of our `voteOnAttribute()` logic happens, check if the user
has `ROLE_ADMIN`. Write `if ($this->accessDecisionManager->decide())` - and the first
argument is the `$token`.

For the second argument, use `ROLE_ADMIN` wrapped in an array.

Inside the `if` statement, `return true`:

[[[ code('80d453a22d') ]]]

## Why Not the `Security` Service?

This is essentially the same logic as the `Security` helper's `isGranted()` method...

So why can't we just use it?

When using the `Security` service, it uses the *current* token. But inside a voter, we
*may* have a different token. This can happen with some edge cases so it's best to
use `AccessDecisionManagerInterface` when *inside* a voter.

Ok, back to our starship listing. Refresh - and remember, we're logged in as
Janeway... we now see the edit and the delete actions, and we can use them.

## Optimizing Voter Performance

One last little thing I want to show you. In a complex app, you can imagine
having hundreds of voters, and `supports()` is called on all of them for every single
permission check. That... can get expensive, but there are ways to improve performance.

Back in `StarshipVoter`, override the `supportsType()` method.

This `$subjectType` gives us the PHP type of the subject - in our case, the class name.
So write `return is_a($subjectType, Starship::class, true)`:

[[[ code('0a5e86f3b3') ]]]

We need that `true` because you have to pass it when you're checking a class as a *string*
with `is_a()`.

That's going to gain us a little bit of performance.

Now override `supportsAttribute()`. It takes the `$attribute` being checked as an argument.
Copy the logic for this from `supports()` above...

And `return in_array($attribute, [self::EDIT, self::DELETE])`:

[[[ code('05ee6745c4') ]]]

As you can see, we basically split the logic of `supports()` into two methods... and we still
need `supports()`. The extra work and duplication only really pays off if you have a ton of voters
and make a lot of permission checks.

Ok, up next, user impersonation!
