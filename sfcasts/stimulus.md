# Stimulus: Writing Pro JavaScript

We know how to write HTML in our templates. And we're handling CSS with Tailwind.
What about JavaScript? Well, like with CSS, there's an `app.js` file, and it's already
included on the page. So you can put whatever JavaScript you want right here.

*But* I highly recommend using a small, but mean, JavaScript library called Stimulus.
It is one of my absolute favorite things on the Internet. You take a part of your
existing HTML and *connect* it to a small JavaScript file, called a controller. This
allows you to add behavior: like when you click this button, the `greet` method on
the controller will be called.

And that's really it! Sure, Stimulus has more features, but you already understand
the core of how it works. Despite its simplicity, this will let us
build any JavaScript and user interface functionality we need, in a reliable
and predictable way. So let's get it installed.

## Installing Stimulus

Stimulus is a *JavaScript* library, but Symfony has a bundle that helps integrate it.
Over at your terminal, if you want to see what the recipe does, commit your changes.
I already have. Then run:

```terminal
composer require symfony/stimulus-bundle
```

When this finishes... the recipe *did* make some changes. Let's walk through
the important ones. The first is in `app.js`: our main JavaScript file. Open that
up, there we go.

[[[ code('32152b65bd') ]]]

It added an `import` on top - `./bootstrap.js` - to a new file that lives right
*next* to this. 

[[[ code('4336a80e93') ]]]

The purpose of this file is to start the Stimulus engine. Also, in
`importmap.php`, the recipe added the `@hotwired/stimulus` JavaScript package along
with another file that helps boot up Stimulus inside Symfony.

[[[ code('409fbd94b7') ]]]

Finally, the recipe created an `assets/controllers/` directory. This is where *our*
custom controllers will live. And it included a demo controller to get us started!
Thanks!

[[[ code('217e917721') ]]]

These controller files *do* have an important naming convention. Because this is
called `hello_controller.js`, to connect this with an element on the page, we'll
use `data-controller="hello"`.

## How Stimulus Works

So here's how this works. As soon as Stimulus sees an element on the page with
`data-controller="hello"`, it will instantiate a new instance of this controller
and call the `connect()` method. So, this `hello` controller should automatically
and instantly change the content of the element it's attached to.

And we can already see this. Refresh the page. Stimulus is *now* active on our
site. This means it's watching for elements with `data-controller`. Let's do
something wild: inspect element on the page, find *any* element - like this anchor
tag - and add `data-controller="hello"`. Watch what happens when I click off to
activate this change. Boom! Stimulus saw that element, instantiated our controller
and called the `connect()` method. And you can do this as many times as you want
on the page.

The point is: no matter *how* a `data-controller` element get on your page, Stimulus
sees it. So if we make an Ajax call that returns HTML and put that onto the page...
yeah, Stimulus is going to see that and our JavaScript is going to work. That's the
*key*: when you write JavaScript with Stimulus, your JavaScript will *always* work,
no matter how and when that HTML is added to the page.

## Creating a closeable Stimulus Controller

So let's use Stimulus to power our close button. Over in the `assets/controller/`
directory, duplicate `hello_controller.js` and make a new one called
`closeable_controller.js`.

I'll clear out almost everything and get down to the absolute basics: import
`Controller` from stimulus... then create a class that extends it.

[[[ code('c358612891') ]]]

This doesn't *do* anything, but we can already attach it to an element on the page.
Here's the plan: we're going to attach the controller to the entire `aside` element.
Then, when we click this button, we'll *remove* the `aside`.

That element lives over in `templates/main/_shipStatusAside.html.twig`. To attach
the controller, add `data-controller="closeable"`. Oh, see that autocompletion?
That comes from a Stimulus plugin for PhpStorm.

[[[ code('0f2eb5ecb1') ]]]

If we move over and refresh, nothing will happen yet: the close button doesn't
work. But open your browser's console. Nice! Stimulus adds helpful debugging
messages: that it's starting and then - importantly `closeable initialize`,
`closeable connect`.

This means that it *did* see the `data-controller` element and initialized that
controller.

So back to our goal: when we click this button, we want to call code inside
the closeable controller that will remove the `aside`. *In* `closeable_controller.js`,
add a new method called, how about, `close()`. Inside, say `this.element.remove()`.

[[[ code('33bc901668') ]]]

In Stimulus, `this.element` will always be whatever element your controller is
attached to. So, this `aside` element. But otherwise, this code is standard
JavaScript: every Element has a `remove()` method.

To call the `close()` method, on the button, add `data-action=""` then the name
of our controller - `closeable` - a `#` sign, and the name of the method: `close`.

[[[ code('c1c7a2a870') ]]]

## Animating the Close

That's it! Testing time. Click! Gone! But I want it be fancier! I want it to
animate when closing instead of being instant. Can we do that? Sure! And we don't
need much JavaScript... because modern CSS is amazing.

Over on the `aside` element, add a new CSS class - it could go anywhere - called
`transition-all`.

That's a Tailwind class that activates CSS transitions. This means that if
certain *style* properties change - like the width suddenly being set to 0 - it
will *transition* that change, instead of instantly changing it.

Also add `overflow-hidden` so that, as the width gets smaller, it doesn't create
a weird scroll bar.

If we try this now, it still closes instantly. That's because there's nothing
to *transition*: we're not changing the width... just removing the element.

But watch this. Inspect Element and find the `aside`: here it is. Manually change
the width to 0. Cool! You go tiny, big, tiny, big, tiny! The CSS side of things
*is* working.

Back in our controller, instead of removing the element, we need to change the width
to zero, *wait* for the CSS transition to finish, *then* remove the element. We can
do the first with `this.element.style.width = 0`.

[[[ code('c1c7a2a870') ]]]

The *tricky* part is *waiting* for the CSS transition to finish *before* removing
the element. To help with that, I'm going to paste a method at the bottom of our
controller.

[[[ code('b9a70df082') ]]]

If you're not familiar, the `#` sign makes this a private method in JavaScript:
a small detail. This code looks fancy, but it has a simple job: to
ask the element to tell us when all of its CSS animations are finished.

Thanks to that, up here, we can say `await this.#waitForAnimation()`. And whenever
you use `await`, you need to put `async` on the function around this. I won't go
into details about `async`, but that won't change how our code works.

[[[ code('81b776d135') ]]]

Let's check the result! Refresh. And... I absolutely *love* that.

Next up, everyone wants a single page application, right? A site where there are
zero full page refreshes. But to build one, don't we need to use a JavaScript
framework like React? No! We're going to transform our app into a single page
application in... about 3 minutes with Turbo.
