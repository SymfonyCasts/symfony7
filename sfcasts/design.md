# Smart Model Methods & Making the Design Dynamic

Adding the `.value` to the end of the enum to print it works like a charm.
But I want to show another, more elegant, solution.

## Adding Smarter Model Methods

In `Starship`, it'll probably be common for us to want to get the *string* status
of a `Starship`. To make that easier, why not add a shortcut method here called
`getStatusString()`? This will return a `string`, and inside,
return `$this->status->value`.

Thanks to this, over in the template, we can shorten to `ship.statusString`.

Oh, and this is *more* Twig smartness! There is *no* property called `statusString`
on `Starship`! But Twig doesn't care. It sees that there *is* a `getStatusString()`
method and calls that.

Watch: when we refresh, the page still works. I really like this solution, so I'll
copy that... and repeat it up here for the `alt` attribute.

And while we're fixing this, in `show.html.twig`, we print the status there too.
So I'll make that same change... then close this.

## Finishing our Dynamic Template

Ok: let's finish making our homepage template dynamic: it's smooth space sailing
from here. For the ship name, `{{ ship.name }}`, for the captain, `{{ ship.captain }}`.
And down here for the class, `{{ ship.class }}`.

Oh, and let's fill in the link: `{{ path() }}` then the name of the route. We're
linking to the show page for the ship, so the route is `app_starship_show`. And
because this has an `id` wildcard, pass `id` set to `ship.id`.

And now, so much better! It looks nice and we can click these links.

## Dynamic Image Paths

But... the image is still broken. Earlier, when we copied the images into our
`assets/` directory, I included three files for the three statuses. Up here, we
*are* "kind of" pointing to the in progress status... but this isn't the right way
to reference images in the `assets/` directory. Instead, say `{{ asset() }}` and
pass the path relative to the `assets/` directory, called the "logical" path.

If we try that now... we're closer. But the "in progress" part
needs to be *dynamic* based on the status. One thing we could try is Twig
concatenation: to add `ship.status` to the string. That *is* possible, though it's
a bit ugly.

Instead, let's revisit the solution we used a minute ago: making all the data
about our `Starship` easily accessible... *from* the `Starship` class.

Here's what I mean: add a `public function getStatusImageFilename()` that returns
a string. Let's do all the logic for creating the filename right here. I'll
paste in a `match` function.

This says: check `$this->status` and if it's equal to `WAITING`, return this string.
If it's equal to `IN_PROGRESS` return this string and so on.

And exactly like before, because we have a `getStatusImageFilename()` method, we
get to, in Twig, *pretend* like we have a `statusImageFilename` property.

And now, we got it!

## Last Details of Making the Design Dynamic

Final things! Let's fill in some missing links, like this logo should go to the
homepage. Right now... it goes nowhere.

Remember, when we want to link to a page, we need to make sure *that* route has
a name. In `src/Controller/MainController.php`... our homepage does *not* have
a name. Ok, it has an auto-generated name, but we don't want to rely on that. 

Add `name:` set to `app_homepage`. Or you could use `app_main_homepage`.

To link the logo, in `base.html.twig`... here it is... Use `{{ path('app_homepage') }}`.

Copy that and repeat it below for another home link. 

We'll leave these other links for a future tutorial.

Back at the browser, click that logo! All good. The final missing link is over
on the show page. This "back" link should *also* go to the homepage. Open up
`show.html.twig`. And up on top - there it is - I'll paste that same link.

Ok team, the design is done! Congrats! Treat yourself to a tea... or latte... or
donut or a walk amongst nature to celebrate. Because this is huge! Our site
*looks* and feels *real*. I'm *thrilled*.

Now we can focus on the finer details. Like, when we click this link, the sidebar
is *supposed* to collapse. To handle that, I want to introduce you to my favorite
tool for writing JavaScript: Stimulus.
