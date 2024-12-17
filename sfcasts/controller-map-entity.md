# High-Tech Controllers: Auto-inject Entities

When we drill into a ship from our homepage and see its *show* page, this URL
isn't very pretty or memorable. It's just the ID of the ship. Imagine if Jean-Luc Picard
announced he was the captain of the USS 43 instead of the Enterprise. Lame!

Let's change this to use our new `slug` field instead. Like `id`, it's unique, so we can
use it to find a single ship in the database.

First though, I want to show you something super cool. Open
`StarshipController::show()`. We are injecting the `$id` from our route
parameter and the `StarshipRepository` service to find the ship *from* this ID.
Then we have logic to throw a 404 if the ship isn't found.

Replace all the arguments with just `Starship $ship`, then, delete all this
finding and not found logic. This is one slim controller now - I love it. If you're
saying "but Starship isn't a service", you're right, but bear with me.

Back in the app, we're on the Starship show page. Refresh... and... it still works!
Let's try visiting a ship we know doesn't exist: one with ID 999. We get a 404 error.
We still have the same logic as before... How?!

Entities are not services... that's still, and always, true. Look in our
`MainController::homepage()` controller. We are injecting the `Request` object. This
also isn't a service. If you tried to autowire this into a service's constructor, you'd
get an error.

Controllers are special. When Symfony calls a controller method, it first looks at
all the arguments and passes them through something called "controller value resolvers".
There are several, and we've used a bunch already - though we didn't know it. There's
a `RequestValueResolver` to inject the `Request` object and a
`ServiceValueResolver` if an argument is type-hinted with a service.

Symfony's Doctrine integration provides an `EntityValueResolver`. This is how we're
able to inject the `Starship` entity. It works because we've type-hinted `Starship`,
a valid Doctrine entity, and we have an `id` route parameter. Since every entity has
an `id`, the resolver automatically queries for the entity then passes it to us.
If the entity isn't found, it throws a 404. I *love* this!

Back to our mission: to use the Starship `slug` in the URL instead of the ID.
First, update the `#[Route]` attribute to `/starship/{slug}`. Next, we need to update
all the places where we generate the URL for this route. Don't worry, there are
only 2.

Start with `templates/main/homepage.html.twig`. Search for "show" - here we go.
In the `path` function, replace `id: ship.id` with `slug: ship.slug`. Now, open
`templates/main/_shipStatusAside.html.twig`, find "show", and in this `path`
replace `id: myShip.id` with `slug: myShip.slug`.

Jump back to our app and click "Back" to go to the homepage. Hover over a ship link
and look the URL. It's much prettier! Click the link.

Red alert! 

> Cannot autowire argument $ship...".

The problem is that when there is no route wildcard called `id`, it reverts
back to trying to autowire `Starship` like a service. When the route wildcard is
*not* called `id`, we need to help it a bit.

Back in `StarshipController::show()`, move `Starship $ship` to its own line
to give us some room. Above it, add an attribute: `#[MapEntity]` with an array
with a key of `slug` - this is the route parameter name and
a value of also `slug` - this is the property name it should query on.

Back to the app and refresh. It's working again, red alert cancelled!

Try putting random text in for the slug and... 404! Perfect!

Now are ship URLs are pretty, human-readable, and SEO-friendly!

Flying through space is dangerous business. Sometimes starships experience
"rapid unscheduled disassemblies"... or in other words, they explode. We need
a way to remove ships from our database that no longer... err... exist. Next, we'll
see how to delete entities with Doctrine.
