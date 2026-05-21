# Installing the Security Bundle

Hey Friends! Welcome to the Symfony Security course! In this tutorial, we're going to explore
one of the most important parts of any web application: controlling who can access
your site and what they're allowed to do once they're inside. To help explain these
concepts, we'll use our Starshop universe: a massive interstellar repair station
filled with mechanics, captains, engineers, restricted hangars, and highly secure
command decks.

Think of Symfony Security like the automated security system aboard the station.
When a ship arrives, the station first needs to identify who's docking: that's authentication. 
Then, once inside, the station decides which sectors that person can access: that's authorization. 
Along the way, we'll explore firewalls, roles, voters, login systems, password hashing, and more.

To follow along, you'll want to download the course code, which you'll
find at the top of the page. Once you've got that, open the `start`
directory in your favorite IDE. You'll find code that looks like this. Take a
peek at the readme to get started. Follow the steps outlined there,
and once you're done, you can run the Symfony server using:

```terminal
symfony serve -d
```

Open the provided link in your browser and you'll see our Starshop app -
a nifty tool designed for managing and repairing starships. 

## Installing the Security Component

Now, let's get our hands dirty. Our first task is to install the security
component. Over in our terminal, run the following command:

```terminal
symfony composer require security
```

Looks like it's installed the `symfony/clock` component, a dependency of
the security packages, along with the `symfony/security-bundle` and
`symfony/security-http` component. The bundle is responsible for integrating the
security components into Symfony, while the `security-http` component deals
with the HTTP aspects of security.

To see what's changed, run:

```terminal
git status
```

We can see updates to our `composer.json` and `composer.lock` files and the
bundle was added to our `config/bundles.php` file.

The Flex recipe added these `config/packages/security.yaml` and
`config/routes/security.yaml` files. Let's dig into those. 

## Examining the New Files

Back in our code, open `config/routes/security.yaml`. This adds a special route
*loader* for adding logout routes. Currently, it doesn't do anything, but once
we configure logging out in our security settings, a logout route will
be automatically added.

Next up, take a look at `config/packages/security.yaml`. This file is
the heart of the operation. Under the `security` key, we have options to
configure `password_hashers`, which we'll discuss later. Then, we have this
`providers` key — this is where we dictate where our users come from. The
default setting uses an in-memory provider, perfect for smaller sites.
However, we'll be focusing on loading users from the database in this
course.

## Firewalls

Moving on, we have the `firewalls` section. Think of these as force
fields that surrounds your entire app or specific parts of it. Now, the order
here is crucial. The first one matched, wins. By default, we have two firewalls,
a `dev` firewall and a `main` firewall. 

The `dev` firewall is triggered when we access certain dev-specific path patterns,
like the profiler. This `security: false` means that when we hit those paths, security is completely
disabled and the next firewall is ignored.

Since the `main` firewall doesn't have a pattern, that means it covers the entire app
and is like a catch-all. Any additional firewalls below would never be reached.

Now just because the entire app is covered by the `main` firewall, doesn't mean that
all users must be authenticated. This `lazy: true` option means that anonymous users
(users that aren't logged in) are allowed to access the app, and right now, any
page. It's also lazy, so checking the user's authentication and authorization is deferred
until it's actually needed by our code. This can help improve performance and, when
using session authentication, allow http caching.

This `provider` key tells the firewall where to load users from when they're needed. Right
now, it's using the `users_in_memory` provider configured above.

Below is where we'll configure different methods for authentication.

## Authentication vs Authorization

Let's take a moment to clarify the difference between *authentication* and *authorization*.

Authentication answers the question: *who* are you? It's the process of proving your identity,
such as logging in with a form or presenting an API token.

Authorization answers the question: *what are you allowed to do?* It controls what parts of the
application you can access and what actions you're permitted to perform once you're authenticated.
For example, even after logging in successfully, only users with the admin role can access the station's
command deck, or, only the owner of a repair ticket can edit it.

The firewall only handles authentication.

## Access Control

This `access_control` section is one way to handle *authorization*. It allows
you to mark certain paths as requiring specific roles. The commented out examples here show
that when accessing any path starting with `admin`, the user must have the admin role,
and when accessing a path starting with `profile`, the *user* role is required.

So when either of these are accessed, if the user isn't logged in, they'll be forced to authenticate.
Either by being redirected to a login page, or given a 401 Unauthorized response, depending
on how authentication is configured. If they *are* authenticated, but don't have the correct role,
they'll get a 403 Forbidden response.

Again, order here is important. The first rule matched wins.

## Web Debug Toolbar

With security is enabled, go back to our app and refresh the homepage...
Notice the new security icon in the web debug toolbar. This gives us a quick overview of
the security status for the current request. It shows `n/a` because we aren't authenticated so
don't have a username. Hovering over it, we can see that we're using the `main` firewall and
indeed, not authenticated.

Next up, let's create the User entity that will provide the users for our `main` firewall.
