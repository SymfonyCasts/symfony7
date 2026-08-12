# Creating a Custom Voter

Open `src/Controller/StarshipAdminController.php`. We have a pretty standard CRUD
setup for our starships here. And right now, the route prefix for all of these is
`/admin`. If you recall, anything starting with `/admin` is locked down to
`ROLE_ADMIN` by our `access_control`.

The first thing I want to do is make this public... and *then* add a more
fine-grained permission system on top. So remove the `/admin` prefix.

Head over to the browser, make sure we're *not* logged in, and go to `/starship`.
There's our CRUD... but it's completely public. We can edit ships, delete them, and
create them as an anonymous user. Definitely not what we want.

Here's the goal: logged-in users should only be able to edit their *own* ship, if
they have one. Remember, we added a `starship` property to our `User` class. So if
Picard were logged in, he'd only be able to edit and delete this top row, because
that's *his* starship. Viewing can stay public for everyone - anonymous users,
other logged-in users, anybody. It's the operations that actually *change* data in
the database that will require special permissions.

## Hiding Actions with `is_granted()`

Start by locking these actions behind some `is_granted()` calls in our Twig
templates.

Jump over to `templates/starship_admin/` - this is where all the templates for this
CRUD live - and open `index.html.twig` first. At the very top, this anchor tag is
the "Create new" button. For creating, we're going to keep it simple: only admins
can create new ships. So wrap it in `{% if is_granted('ROLE_ADMIN') %}` and
`{% endif %}`.

Perfect. Now only admins can actually create new starships.

Next up is the edit button. But first, refresh the page... "Create new" has
disappeared. Now get rid of that edit button for anonymous users.

Down here, we have a "Show" and an "Edit" button. Wrap the edit button in an
`is_granted()` check. And for this one, we're *not* going to use a role. We're
going to use a generic custom attribute - sometimes called a *permission*. Name it nice
and simple: `edit`.

But we're not done: pass a *second* argument to `is_granted()`. This second
argument is called the **subject**, and our subject is going to be `starship`. Now
close everything up with an `{% endif %}`.

Refresh the page... and that edit button disappears.

There's one other place: the show page. If you click "Show", we want to exclude
these actions if you don't have permission - with those same `is_granted()` checks. So
go to the show *template* and scroll to the very bottom, where we find the edit
link. Wrap this one in `{% if is_granted('edit', starship) %}`, then close it with
`{% endif %}`.

The delete button is going to use a different permission:
`{% if is_granted('delete', starship) %}`.

Go back and refresh this page... and they're gone, because we're anonymous.

## Logging In Changes... Nothing

Ok, log in as Picard: `picard@enterprise.space`, password `makeitso`.

Now visit the starship listing... and we have the *exact* same permissions as an
anonymous user. This is actually our ship right here, but all we can do is show it
- we can't edit or delete it.

To handle those fine-grained permissions, we need a custom voter.

## `make:voter`

And there's a maker for that! Over in your terminal, run:

```terminal
symfony console make:voter
```

Call it `StarshipVoter`.

Now go take a look at what that created: `src/Security/Voter/StarshipVoter.php`.

It added some boilerplate for us, including two permission constants. For the first
one, rename the actual value to just `edit` - matching what we used in our
template.

And this `VIEW` constant? We're not going to have fine-grained permissions on
viewing: anyone can view a starship. So change this constant to `DELETE`... and
change its value also to `delete`.

## `supports()`

So what does a voter actually do? Because this class extends `Voter`, it's
autoconfigured with Symfony. There's nothing you have to do service-wise here -
this is already automatically registered.

Whenever the security system is calculating which voter should be used, it calls
`supports()`. That's where we tell it when to use this voter. The `$attribute` is
what we passed to `is_granted()` as the first argument, and the `$subject` is the
second argument.

In this case, the generated code is almost correct already. We have
`in_array($attribute, [self::EDIT, self::VIEW])` - change `VIEW` to `DELETE` - and
then `$subject instanceof Starship`. That's exactly what we want. I'm just going to
clean this up a little bit by importing `Starship`.

## `voteOnAttribute()`

When `supports()` returns `true`, `voteOnAttribute()` is called.

There's a little bit of boilerplate here too. This method has a
`TokenInterface $token` argument. The only thing you really need to know about the
token right now is that it's what the security system uses to wrap the user. You can grab
the user from it with `getUser()` - and that's what's happening here.

This `instanceof` check ensures `$user` isn't null. Since we only have one user
class, change it to our `User` entity. That will help with autocompletion below.

If we don't have a user, we add a reason for debugging and return `false`, meaning
the `is_granted()` check will fail.

Down here, we're switching on the attribute. So you can have completely different
logic for `EDIT` to figure out whether someone should be able to edit, and
different logic for deleting. And if none of them match, we `return false`, failing
the check.

For us, `EDIT` and `DELETE` are going to have the same logic, so we can collapse all of
this.

But first, up at the top, for this subject: because `supports()` already ensured
this is an instance of `Starship`, we can be assured that this `mixed $subject` is
in fact a `Starship`. So generate a docblock. Delete everything except for the
subject... and type it as `Starship`.

Down below, we have the user, *and* we have the starship (as the subject). Which means we can now
check whether the user is part of the starship:
`return $user->getStarship()?->getId() === $subject->getId()`.

## Trying It Out

That should be it! Back to the browser and refresh - remember, we're logged in as
Picard.

Perfect! This seems to be working. We can edit just *this* ship, because it's *our*
ship. And all these other ones? No edit button. If we hit "Show" for our ship, the actions are
visible. Click "Edit".

We have a little problem... actually not so little... see if you can guess what it is. We'll
fix it in the next chapter.
