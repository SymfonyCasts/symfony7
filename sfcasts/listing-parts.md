# Listing Parts

Coming soon...

## Lights, Camera, Action: Creating a New Page

Alright my coding comrades, time to roll up our sleeves and dive in. Our
mission? Creating a brand new Parts List page. Now, we're going to be a
little sneaky about this — we're going to use `MakerBundle` to give us a
little head start. It's like having a cheat code in a video game, but don't
worry, we've got the skills to back it up. So, find a console and run
`Trident Terminal`, then run `Symfony Console`. And we'll let `Make
Controller` do its thing.

```terminal
Symfony Console Make Controller
```

Our new controller is going to be the big shot, let's call it
`PartController`. And just to keep things focused, we'll say no to tests
for this tutorial. We're all about the action today!

## A Quick Peek at Our New Class

Voila! One class and one template. So far, so good. Let's go take a peek at
our new `PartController`. Right now, there's not much to see, just a simple
template rendering. But we're about to jazz this up. 

Let's change the URL to be `/parts`, and rename it to `App Part Index`.
This is going to be our go-to page for all things parts related. Copy that
route name because we're going to add a shiny new link to this in our
header.

## Sprucing up Our Header

Alright, brace yourself, it's time to transform `base.html.twig`. Remember
that about link that's just sitting there doing nothing? Let's commandeer
that and turn it into our fabulous new parts link. And of course, the href
is going to be `{{ path('app_part_index') }}`.

```terminal
base.html.twig {{ path('app_part_index') }}
```

Head to the homepage, click our newly minted link, and... well, it's not
the prettiest sight, but it works! 

## The Power of a Good Title

Let's not celebrate just yet though, we need to change the title from the
rather uninspiring `Hello PartController`. So, let's open up templates,
`PartIndex.html.twig`. We're already overriding the title block, so let's
make it something much sexier — let's call it 'parts'. Ah, much better.

```terminal
PartIndex.html.twig
```

## Adding Some Substance: Looping Over Parts

Now, we're going to need to loop over all the parts and print them out. So,
instead of `PartController`, we're going to need a query for all the parts.

 For this, we'll need the `Starship Part Repository`. Let's add a
`StarshipPartRepository` argument here and auto-wire that in. Let's call it
repository for short. And to get all the parts — it's super simple:
`parts = repository->findAll()`. 

```terminal
StarshipPartRepository parts = repository->findAll()
```

## Making it Look Nice

Now that we have this parts variable in our template, we can loop over it
and start printing out information. To spice things up, I'm going to paste
in this template, it's just a bunch of stuff to make it look nice. 

Refresh, and voila, much better! 

## A Little Trick: Using the Cycle Function

One interesting thing I'm using here is the `cycle` function. I wanted to
give each gear a random color to make it look more appealing. The `cycle`
function lets you pass a bunch of strings, and then `loop.index 0` cycles
through them, giving us a nice effect of having different gear icons for
each of the parts. It's a small touch, but adds a little bit of flair.

## Wrapping Up

Lastly, let's replace `assign to ship name` with `{{ part.ship }}`. This
time, I'm not using `ship.part`, but the other side of the relationship,
`part.ship.name`. Oops, my bad, it should be `part.starship.name`. And...
got it! 

Alright, that's it for now. Next up, we'll be talking about joins. So, stay
tuned!