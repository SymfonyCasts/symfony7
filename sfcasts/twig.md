# Inheritance with Twig

You see each of these starship rows here? They show the generic Starship properties. 
I want to make these a bit more interesting by showing the custom Starship properties for our sub-ships in these rows.
Let's call this *row* a *teaser*. 

## Creating a Teaser Partial

The first thing we'll do is extract our teaser HTML into a partial.

In our `templates` directory, create a new `starship`
directory, and then whip up a Twig file named `teaser.html.twig`.

Then, in our homepage template, find the teaser HTML, which is everything inside
this "for ship in ships" section. Grab that whole chunk and cut it. Next, paste it
in our new `teaser.html.twig` file.

Back in `homepage.html.twig`, include it as a partial with:
`{{ include('starship/teaser.html.twig') }}`. For the parameters, pass
`{ship: ship}`.

Do a quick sanity check by refreshing the app to see if everything is
still in order. Perfect. No regressions!

## Adding Properties to the Teaser

Now that we have the teaser in its own partial, let's spice it up with sub-ship
properties. For example, I want to show the cargo capacity for the freighters. We could
do this with an if statement... like `if ship.type == 'freighter'`, but that would get
messy really fast. Instead, let's use Twig template inheritance. Twig Template Inheritance
with Doctrine Inheritance, I love it!

First, in the `templates/starship` directory, create a new `teaser`
directory. This will be the home for all of our inherited teaser templates.
Create the first one, `freighter.html.twig`.

Back in `teaser.html.twig`, we need to create a *block* that can be overridden by our inherited templates.
Below the "arrived at", add `{% block extra %}{% endblock %}`. This is
empty in the generic teaser template, but it's going to allow templates
that extend it, to override it.

Hop over to our new `freighter` template. Oops, I have a typo in the filename. It should be
`.twig` with an `i`.

First, have it extend `starship/teaser.html.twig`. Now, override the `extra` block,
and inside, add `<div>Cargo Capacity: {{ ship.cargoCapacity }}</div>`. We could call `parent()` here
to render the parent block, but since it's empty, I'll skip that for now.

## Dynamic Template Inheritance

Ready to see some magic? Over in our homepage template, we'll dynamically include the
correct teaser template based on the ship type. Inside the `include()`, break up the
string after `teaser` and add some `~`'s to concatenate. Inside those, add `ship.type`.

Back in our app, refresh and... error! "Unable to find template `starship/teaserfreighter.html.twig`".
Oops, I forgot a `/` after `teaser`. I'll fix that and refresh again...

Still an error, but a different one! "Unable to find template `starship/teaser/scout.html.twig`".
Ahh, we only created a teaser for the `freighter`, but we have a `scout` and `mining_freighter` too.

To make this work, we need to create a teaser for each starship type. But
what if I don't want to have that requirement? I'd like to just fallback to the generic
teaser if a specific one doesn't exist. No problem!

## Fallback Teaser

In `homepage.html.twig`, wrap the template name in the `include()` function in an array. When you pass
an array to `include()`, Twig will try to find each template in the array. The first one it finds, it uses.
So, for the second array element, use our generic teaser: `starship/teaser.html.twig`.

Refresh our app... and there we go! Our Freighters now show their cargo capacities! And below, the scouts and 
mining freighters are using the generic teaser.

## Adding a Mining Freighter Template

Let's go another level and add a teaser for the `mining_freighter`.

In `templates/starship/teaser`, create a new Twig file: `mining_freighter.html.twig`. Just like
our `MiningFreighter` entity extends `Freighter`, this template will extend the `freighter.html.twig` teaser.

Add the `extra` block. This time, render the `parent()` block to include the cargo capacity from
the `freighter` teaser. Below, add `<div>Laser Power: {{ ship.laserPower }}</div>`.

Because of our dynamic template inheritance logic, this is all we should need to do. Refresh the app...
scroll down to our mining freighters... and sweet, these show both the cargo capacity and the laser power!

I think this is pretty elegant, what about you?

Next, we'll see how Doctrine Inheritance and routing work together!
