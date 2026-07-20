# Fetching the User in Services/Controllers

We left off by using the `getUser()` helper method from `AbstractController`
to get the currently logged-in user. Then, we're grabbing the user's Starship with
`getStarship()` (using the null-safe operator in case there's no logged-in user).
Look at this warning PhpStorm is giving us on that...

"Potentially polymorphic call..."

It's basically saying that the object returned by `getUser()` *might not* have the
`getStarship()` method.

To see why, jump to the `getUser()` definition. Its return type is `UserInterface`
(or null). This *is* returning our `User` entity, which implements `UserInterface`,
but PhpStorm doesn't know that. It sees that there's an object in our
app that does implement `UserInterface` with a `getStarship()` method, our `User` entity.
It's just warning us that it can't be sure it is, in fact, this object.

Let's make sure it knows it is this object.

## Injecting User into a Service

First, we'll use an alternative way to get the current user. You may need to access the
current user in another service. And that service won't have access to the `AbstractController`
helper methods.

In our `index()` method, inject `Security`, the one from the SecurityBundle, `$security`.

This is a helper service that can access the currently logged-in user.

Below, set the `$myShip` variable to `$security->`. Take a look at the available
methods: `getUser()` fetches the current user. We also have `isGranted()` to
perform authorization checks.

There's a few other helpers for more advanced use cases... And check out these last two:
`login()` and `logout()`. These are for programmatically logging in and out users without
needing to go through the standard flow. We'll take a look at these a bit later.

Choose `getUser()` and then write `?->getStarship()` to fetch the user's starship (if there is one).

This is basically the exact same logic we had before, but using a service. We're no longer relying
on the `AbstractController`.

## Ensuring the User is our User

We still have the same warning though. So let's fix that.

First, add `$myShip = null`, and below, `$user = $security->getUser()`. Now add
`if ($user instanceof User)` and inside, `$myShip = $user->getStarship()`. The `instanceof`
protects us against a `null` user and ensures the object is indeed our `User` entity.

Now we can delete the old code below.

Time to test this out, jump over to the browser and refresh the homepage. Nice, the sidebar
still works. Logout... and the sidebar is gone. Still working as expected.

## User Argument Resolver

Using the `Security` service is required to get the current user in a service... But since
we're in a controller, we can do something simpler, and I think, more elegant. Controllers
have a special feature called argument resolvers. They allow injecting certain objects into
the controller, even if they aren't services. The `Request` object is one of these. It isn't
a service, so you can't inject it into another service, but you can inject it into a controller.
There's a *request* argument resolver that enables this. There's also a *user* argument resolver.

Replace injecting `Security` with `UserInterface $user`. This parameter will now use the *user*
argument resolver! Below, we can remove the `$user =` line.

Go back to the browser and refresh the homepage... Hmm, we're redirected to the login page...

When you inject the user this way, it makes this controller *require* a logged-in user. An
access denied exception is thrown if there isn't one, so we're redirected to the login page. It's basically
the same behavior as if we had added an `IS_AUTHENTICATED` check.

This isn't what we want for our homepage though, it should be accessible to anyone, logged in or not.
How can we allow this while still using the user argument resolver? Make the user argument nullable
by prefixing `UserInterface` with a `?`.

Now go back to the homepage... no more redirect, and no sidebar. Now login as Picard... Email:
`picard@enterprise.space`, password: `makeitso`. Sweet, the sidebar is back and showing our ship!

I love this! Our `index()` method definition is nice and expressive!

## `#[CurrentUser]` Attribute

We can do even better though!

Replace the `UserInterface` type-hint with our actual `User` entity... be sure to keep it nullable.

This won't quite work yet. We have to help the user argument resolver along. Above the parameter,
add the `#[CurrentUser]` attribute. I think this is still expressive, it's saying to inject the *current user*
for this parameter. And we for sure have the correct user object, the method type-hint enforces this.

Below, we can simplify all this logic with just `$myShip = $user?->getStarship()`.

Beautiful!

Back to the browser... and refresh the homepage... we see the proper sidebar because we're logged in. Logout...
and the sidebar is gone.

I don't think this `index()` method could get any more expressive. The `StarshipRepository` and
`Request` objects are required. And now, the current `User` is optional.

Next, we'll look at security voters and permissions!
