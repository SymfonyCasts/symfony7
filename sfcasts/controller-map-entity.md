# High-Tech Controllers: Auto-inject Entities

When we drill into a ship from our homepage and see it's *show* page, this URL
isn't very pretty or memorable. It's just the ID of the ship. Let's change this
to use our new *slug* field instead. Remember, like the ID, it's unique, so we can
use it to find a single ship in the database.

First though, I want to show you something super cool. Open our
`StarshipController::show()` controller. We are injecting the `$id` from our route
parameter and also the `StarshipRepository` service to find the ship from this ID.
Then we have some logic to throw a 404 if the ship isn't found.

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
There are several, and we've used a bunch already - although we didn't know it. There
is a `RequestValueResolver` to inject the `Request` object. Even services are injected
via a `ServiceValueResolver`.

Symfony's Doctrine integration provides an `EntityValueResolver`. This is how we're
able to inject the `Starship` entity. It works because we've type-hinted `Starship`,
a valid Doctrine entity, and we have an `id` route parameter. Since every entity has
an `id`, the resolver automatically finds, and injects, the entity from the database.
If the entity isn't found, it throws a 404. I *love* this!

As I said earlier, I want to use the Starship `slug` in the URL instead of the ID.
First, update our `#[Route]` attribute to `/starship/{slug}`. Now, we need to update
all the places where we generate the URL for this route.

Start with `templates/main/homepage.html.twig`. We'll search for "show" - here we go.
In this `path` function, replace `id: ship.id` with `slug: ship.slug`. Now, open
`templates/main/_shipStatusAside.html.twig`, find "show", and in this `path` function,
replace `id: myShip.id` with `slug: myShip.slug`.

Jump back to our app and click "Back" to go to the homepage. Hover over a ship link
and notice in the bottom left of your browser: the URL now uses the slug. Now, click
the link.

Red alert! We have an error. "Cannot autowire argument $ship...". The problem here
is Symfony is no longer detecting this as an entity value so, it's trying to inject
it as a service... which it's not. The `EntityValueResolver` only works out of the
box when the route parameter is `id`. We need to help it a bit.

Back in `StarshipController::show()`, move `Starship $ship` to its own line
to give us some room. Above it, add an attribute: `#[MapEntity]`. Inside, add
`mapping:` then an array with a key of `slug`, this is the route parameter name.
Then a value of also `slug`, this is the `Starship` property name we want the
`EntityValueResolver` to query on.

Back to the app and refresh. It's working again, red alert cancelled!

Try putting some random text in for the slug and... 404! Perfect!

Now are ship URLs are pretty, human-readable, and SEO-friendly!

Flying through space is dangerous business. Sometimes starships experience
"rapid unscheduled disassemblies"... or in other words, they explode. We need
a way to remove ships from our database that no longer... err... exist. Next, we'll
see how to delete entities with Doctrine.
