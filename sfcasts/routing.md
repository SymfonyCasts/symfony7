# Routing with Doctrine Inheritance

Let's create a dedicated show page for our starships and check out how
routing works with Doctrine inheritance entities. We first need a new controller,
so hope over to the terminal and run:

```terminal
symfony console make:controller Starship
```

No need for any tests. Alright, jump over to your IDE, and find our shiny new
`StarshipController`. Start by renaming the method to `show()`. Next, in the
`#[Route]` attribute, change the path to `/starship/{id}`, and name it `app_starship_show`:

Finally, we're going to use the magic of the Doctrine entity value resolver to
inject the `Starship` for the passed id. Add `Starship $ship` as an argument to the `show()` method:

[[[ code('906b6bdc7d') ]]]

For now, just `dd($ship)` to see it in action:

[[[ code('5e8eb64262') ]]]

## Linking to the Show Page

Now, head over to our generic teaser template. Find the placeholder link (the one with
a `#` as the href). Replace that with `{{ path('app_starship_show') }}` and
`{id: ship.id}` as the parameters:

[[[ code('a1b27e9c05') ]]]

Refresh our homepage, and click the first freighter. Woo, this dump shows us this
`Freighter` instance. Go back to the homepage, and click the last mining freighter.
Now the dump shows us a `MiningFreighter` instance. Even though we type-hinted
`Starship`, Doctrine knows to instantiate the correct entity class.

If this all works exactly as you expected, you're right, there really isn't anything special
about routing and Doctrine inheritance. 

## Understanding Type Hinting

But take note: if you head back to `StarshipController::show()`, and change the
type hint from `Starship` to `Scout`:

[[[ code('f72078cc6b') ]]]

You'll end up with a 404 error when
you refresh the page. Why? Because there is no scout with this ID.
So, to make sure any starship can be found on this route, your type hint
should be `Starship`, the topmost entity you want to find for this route:

[[[ code('eacb44e612') ]]]

## Getting the Show Template Ready

Let's get this controller's template ready. The maker bundle created this
`index.html.twig` file in our `templates/starship` directory. Rename it to `show.html.twig`.
Open it up, and replace the title block with `{{ ship.name }}`:

[[[ code('7798d05370') ]]]

Clear out all the boilerplate in the body block.

For the sake of simplicity, copy the contents of our teaser template and paste in
the show template's body block.

[[[ code('f68b83527f') ]]]

In a real world app, the show template would likely
be quite different, offering more details and a different layout.

Back in `StarshipController::show()`, remove the `dd()`, change the template to
`starship/show.html.twig`, and pass `ship` as a parameter.

[[[ code('6b97be3df6') ]]]

Now, head back to our app
and refresh the page. Of course, this looks like the teaser but this
is indeed our separate show page.

## Implementing Twig Template Inheritance

Just like we did with the teasers, we can use Twig template
inheritance with our show template. This needs a different approach though, since
we're rendering the template within a controller, not with the `include()` function in
another Twig template.

First, copy the `teaser` directory to `show` inside the `templates/starship` directory.
These will be the custom, entity-specific show templates. Open `show/freighter.html.twig`,
and change the `extends` to `starship/show.html.twig`:

[[[ code('402a6b7151') ]]]

In `show/mining_freighter.html.twig`,
change the `extends` to `starship/show/freighter.html.twig`:

[[[ code('412a80debb') ]]]

We're ready to use these templates, but we need to figure out which one to render.

At first, you might think you could do the same thing with `render()` and
just change it to an array. But `render()` is strictly type hinted to a
string for the view. So... we have to get our hands a little dirty.

In `StarshipController::show()`, let's make some breathing room and
inject the Twig environment service with `Environment`, the one
from the `Twig` namespace, `$twig`:

[[[ code('a51b7c5aea') ]]]

Next, create a `$template` variable. For this, we'll use *string interpolation* to
build the template name: `starship/show/{$ship->getType()}.html.twig`.

[[[ code('2b0d1c5ffb') ]]]

This is the template name for the specific starship type.

If this template doesn't exist, we want to fallback to the generic show template.

So, `if (!$twig->getLoader()->exists($template))`, and inside, set `$template` to `starship/show.html.twig`:

[[[ code('6cb2981650') ]]]

Finally, replace the string in the `render()` method with our new `$template` variable:

[[[ code('359f54e099') ]]]

This is very similar to what Twig's `include()` function does for us when you pass an array.

Jump over to our app and refresh... Cool, since this is a mining freighter, we now see the
cargo capacity and laser power. Go to the homepage and click the first normal freighter.
Nice, we just see the cargo capacity.

You can hover over the Twig icon in the web debug toolbar to see the template used. Sure enough,
`starship/show/freighter.html.twig`.

Go to the homepage again, and click a scout. Cool, no extra details, and the toolbar shows the
generic `starship/show.html.twig` template is being rendered.

Our home-grown dynamic template system is working!

Up next, we'll explore how associations work with Doctrine
Inheritance, and what considerations you need to keep in mind when using
them.
