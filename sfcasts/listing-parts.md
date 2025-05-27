# Listing Parts

New mission: we need a page that lists *all* the parts available.
Our Ferengi sales team will use this for som classic upselling. You know,
the usual: "Hey, you just bought a starship, how about some shiny new
dilithium crystal organizers or cupholder stabilizers?"

Let's use MakerBundle to give us a head start. Find your terminal and run

```terminal
symfony console make:controller
```

Call it... wait for it... `PartController`. Brilliant!
To keep things focused, say no to tests.

Voila! One class and one template. So far, so good. Take a peek at
the new `PartController`. Not much to see: it renders a template.

Change the URL to `/parts`, and rename it to `app_part_index`.
Copy the route name so we can link to it... and open up
`base.html.twig`.

## Linking to the Parts Page

Remember that "about" link that's sitting there doing nothing? Commandeer
that and turn it into a "Parts" link. Set the `href` to 
`{{ path('app_part_index') }}`. 

Head to the homepage, click our newly minted link, and... well, it's not
the prettiest sight, but it works!

Before we celebrate, we should change the title from the
rather uninspiring `Hello PartController`. Open up `templates/part/index.html.twig`
We're already overriding the `title` block, so let's make it
something exciting like `Parts`.

## Adding Some Substance: Looping Over Parts

To loop over the parts, we need to fetch them from the database.
In `PartController`, we're going to need a query for all the parts.

`StarshipPartRepository` argument here to auto-wire that in. Call it
whatever you want, like `$leeroyJenkins` or `$repository`. To get all the parts — it's
simple: `$parts = repository->findAll()`.

## Printing Parts in the Template

Now that we have this `parts` variable in our template, we can loop over it.
To spice things up, I'll paste in this template: it's just a bunch of stuff to make
it look nice. You can get this code from the code block on this page.

Refresh, and... so much better! 

## A Little Trick: Using the Cycle Function

One interesting thing I'm using here is the `cycle()` function. I wanted to
give each gear a random color to make it look more appealing. The `cycle()`
function lets us pass a bunch of strings, and then `loop.index 0` cycles
through them, It's a small touch, but adds a little bit of flair.

Lastly, replace `ASSIGNED TO SHIP NAME` with `{{ part.ship }}` - this
time, I'm not using `ship.part`, but the *other* side of the relationship,
`part.ship.name`. Oops, my bad, it should be `part.starship.name`. And...
got it! 

Next up, we'll be talking about joins. So, join me! Sorry, I couldn't resist.
