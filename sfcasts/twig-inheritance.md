# Twig Template Inheritance

What about adding a layout to our page - like a header and a footer? Take a peek
at the HTML for the page: it's *just* the HTML from the template. There's nothing
special in Twig where a base layout with a header and a footer is automatically
wrapped around our content. Whatever you have in your template is what you get on
the page.

However, the Twig recipe *did* add a base layout file called `base.html.twig`. 

[[[ code('339b00d890') ]]]

It's really simple now, but *this* is where we'll add our top nav, footer and any other
things that should live on every page. The question is: how can we make *our*
template use this?

## Extending the Base Layout

With a *cool* feature called template inheritance. In `homepage.html.twig`, at the
top, type `{% extends` then the name of the base template: `base.html.twig`. And
notice: this is the *do* something tag. We're not *printing* this template, we're
telling Twig that we want to *extend* it.

[[[ code('432c044dc7') ]]]

If we do nothing else and refresh, we get an error:

> a template that extends another one cannot include content outside Twig blocks.

Hmm. When you extend a template, it tells Twig that you want to render your
template *inside* that base layout. But... Twig has no idea *where* our content
should go. Should it take our homepage HTML and put it down here? Or up here? Or
right there? It doesn't know! So it throws that error.

The way we tell it is via these *blocks*. Blocks are holes into which a child template
can put content. And you may have noticed one block called `body`... which is
*exactly* where we want our content to go. To put it there, surround all the content
with a `{% block body %}`... and at the bottom, `{% endblock %}`.

[[[ code('463b23afe2') ]]]

And now... it's alive! It doesn't look much different, but we *are* inside the base
layout.

This is called template inheritance because it works exactly like PHP class
inheritance. Imagine you have a `Homepage` class that extends a `Base` class. That
`Base` class has a `body()` method, and we override that `body()` method in the
`Homepage` class. It's the same concept in Twig.

## Overriding the Page Title

And these block names - like `javascripts`, `stylesheets` and `body` - aren't special
names... and they're not registered anywhere. Feel free to create new blocks however
and whenever you want. For example, suppose we want to change the `title` of the
page from a child template. In this case, the recipe already gave us a block called
`title` to do that. And *this* block has default content... which is why we already
see `Welcome` on the browser tab. Let's override this in *our* template.

[[[ code('9cd48b3d9f') ]]]

Anywhere outside the `body` block, say `{% block title %}`, type something, then
`{% endblock %}`.

[[[ code('6c92b85954') ]]]

## Replacing vs Appending the Parent Block

And now, got it! New title! And notice that when we override a block, we override
it *completely*. We don't see the word `Welcome` anymore. Occasionally, you *may*
want to *add* to the parent block instead of replacing it. You can do that by saying
`{{ parent() }}`.

This is really neat! The `parent()` function grabs the content from the `title` block
of the parent template. Then we use `{{` to print it. This time we see welcome
and *then* our title.

But since we don't really want that, I'll remove it.

Status check: we're returning HTML and we have a base layout. Yeah, our site is still
horribly ugly, but we'll fix that in a bit.

Next up, let's run one command and instantly gain access to some of the most powerful
debugging tools on the web.
