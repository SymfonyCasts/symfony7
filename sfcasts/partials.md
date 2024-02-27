# Twig Partials, Loops & PHP Enums

We just gave our site a design makeover... which means we updated our templates
to include HTML elements with a bunch of Tailwind classes. The result? A site that's
easy on the eyes.

For some parts of the templates, things are still dynamic: we have Twig code
to print out the captain and class. But in other parts, everything
is hard-coded. And... this is pretty typical: a frontend developer might
code up the site in HTML & Tailwind... but leave it for *you* to make it dynamic
and bring it to life.

## Organizing into a Template Partial

At the top of `homepage.html.twig`, this long `<aside>` element is the
sidebar. It's fine that this code lives in `homepage.html.twig`... but it
*does* take up a lot of space! And what if we want to reuse this sidebar on another
page?

One great feature of Twig is the ability to take "chunks" of HTML and isolate them
into their *own* templates so you can reuse them. These are called template
*partials*... since they hold code for just *part* of the page.

Copy this code, and in the `main/` directory - though this could go anywhere - add
a new file called `_shipStatusAside.html.twig`. Paste inside.

Back in `homepage.html.twig`, delete that, then include it with `{{` - so the say
something syntax - `include()` and the name of the template:
`main/_shipStatusAside.html.twig`.

Try it out! And... no change! The `include()` statement is simple:

> Render this template and give it the same variables that I have

If you're wondering why I prefixed the template with an underscore... no reason!
It's just a convention that helps me know that this template holds only a *part*
of the page.

## Looping over the Ships in Twig

In the homepage template, we can focus on the ship list below, which is this
area. Right now, there's just one ship... and it's hard-coded. Our *intention* is to
list every ship that we're currently repairing. And we *do* already have
a `ships` variable that we're using at the bottom: it's an array of `Starship`
objects.

So for the first time in Twig, we need to loop over an array! To do that,
I'll remove this comment, and say `{%` - so the *do* something tag - then
`for ship in ships`. `ships` is the array variable we already have
and `ship` is the *new* variable name in the loop that represents a single
`Starship` object. At the bottom, add `{% endfor %}`.

And already... when we try it, we get *three* hard-coded ships! That's an
improvement!

Next: it's time for a plot twist that'll lead us to creating a PHP enum.
