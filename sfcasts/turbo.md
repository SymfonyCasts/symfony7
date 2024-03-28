# Turbo: Your Single Page App

When I build a UI, I want it to be beautiful, interactive, and smooth. Personally, 
I choose *not* to use front-end frameworks like React or Vue or Next. But you
*can*... and there's nothing wrong with them: those are great tools. Also,
building an API in Symfony is awesome!

But if you want to build your HTML in Twig - like I love doing - we can absolutely
have a super-rich, responsive, interactive user interface!

One *big* piece of a fancy interface is removing full-page reloads. Right now, when
I click around, watch: it's fast, but these are full-page reloads. Those don't happen
if you use something like React or Vue.

To eliminate those, we're going to use another library from the same people that
made Stimulus called Turbo. Turbo can do a *lot* of things, but its main job is to
eliminate full-page refreshes. Like Stimulus, it's a JavaScript library. And
also like Stimulus, Symfony has a bundle that helps integrate it.

## Installing Turbo

Find your terminal and run:

```terminal
composer require symfony/ux-turbo
```

This time, the recipe made two interesting changes. I'll show you. The first is in
`importmap.php`: it added the `@hotwired/turbo` JavaScript package. 

[[[ code('10866a256b') ]]]

The second change is in `assets/controllers.json`. We didn't talk about this file 
before, but it was added by the StimulusBundle recipe: it's a way to activate 
Stimulus controllers that live inside third-party packages.

[[[ code('c1639fbd7f') ]]]

So the `symfony/ux-turbo` PHP package we just installed has a JavaScript controller
inside called `turbo-core`. And because we have `enabled: true` here, it means that
controller is now registered and available: it's as if it lived in our `assets/controllers/`
directory.

Now we're not going to *use* this controller directly - we're not going to attach
it to an element. But the fact that it's being loaded & registered with Stimulus
is enough to *activate* Turbo on our site.

## Full Page Refreshes Gone

What the heck does that mean? It's like magic: give the page a refresh, and bam!
Full-page reloads vanish! Watch up here: when I click back, you won't see it reload.
Boom! It's super fast and all happening via Ajax.

Here's how it works. When we click this link, Turbo intercepts the click and,
instead of a full page reload, it makes an Ajax call to that page. That Ajax call
returns the full HTML for that page and then Turbo puts that onto *this* page.

That small thing transforms our project into a single page application and makes
a big difference with how fast our site feels.

## AJAX Calls & the Web Debug Toolbar

But there's one more thing. I'll refresh so we can see it. Whenever you make an Ajax
call in a Symfony app - whether it's via Turbo or any other way - the Web Debug
Toolbar *notices* that. Watch right around here when I click. Check that out!
We have a running list of all the Ajax calls made on this page. And if we want to
see the profiler for any of those Ajax requests, we can click the link.

And yeah... there we are. Here's the Ajax request that was made for the homepage.
Though with Turbo, you don't even need to rely on this trick because, as we click
around, this entire bar is replaced by the new Web Debug Toolbar for the page.

Oh, and get this: in Turbo 8, which is out now, your site will feel even *faster*.
That's thanks to a new feature called Instant Click. With this, when you *hover* over
a link, Turbo makes an Ajax call to that page *before* you click. Then, when you
*do* click, it loads instantly... or at least has a head start.

Turbo has a lot of other features, and we use a *bunch* of them in our
[LAST Stack Tutorial](https://symfonycasts.com/screencast/last-stack) where we build
a frontend with popovers, modals, toast notifications, and more.

## Turbo Requires Good JavaScript

But one note about Turbo. Because full page reloads are gone, your JavaScript needs
to be built in a way to handle that. A lot of JavaScript expects full page reloads...
and if HTML is suddenly added to the page *without* a reload, it breaks. The good
news is that if you write your JavaScript in Stimulus, you're good.

Watch. No matter how we get to the homepage, our JavaScript to close the sidebar
just keeps working.

Alright squad, we're on the home stretch! Before we finish, I want to do one
last bonus chapter where we play with Symfony's awesome generation tool:
MakerBundle.
