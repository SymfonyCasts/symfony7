# Many To Many Render

Coming soon...

## Introducing Starships and Droids: The Perfect Duo

Alright, folks! We have a fantastic line-up of starships and their
dedicated droids. Now, the million-dollar question is: how do we make this
duo visible on our site? Let's consider a scenario: we've got a starship
and we want to list all the droids assigned to it. Sounds tough? Not at
all! It's actually a piece of cake, as simple as a `OneToMany`
relationship! 

## The `OneToMany` Symphony

First things first, let's open our template
`/templates/starship/show.html.twig`. No need to be picky about where
exactly, but I'm going to snatch the H4 and the P tag for `arrived at` and
then paste them below. Let's give `arrived at` a makeover and change it to
`droids`. Clear out the `arrived at` down here and let's break that line
up.

```terminal
symfony serve
```

This part is so simple it's almost laughable. We have a starship object, so
for `droid in ship.droids`, we just call it `droid`. This calls the
`getDroids()` method, which returns a collection of `Droid` objects. So, we
can say things like `{{ droid.name }}`.

## No Trailing Comma Drama

Let's jazz it up a bit. We want commas, but not that pesky trailing comma.
Here's a neat trick for you: I'll say `{% if not loop.last %}, {% endif
%}`. I know, I know, you're thinking I could show you a more sophisticated
way to do this. And you're right! But let's stick with the basics for now.

## The Polite Else Tag

Here's where it gets fun. If there aren't any droids to loop over, we can
use an `else` tag and say `No droids on board (clean up your own mess)`.
Sounds a bit snarky? It's the hard truth, my friends! This works because
`droid` is a `Droid` object. We're simply leveraging our `ManyToMany`
relationship. It's as simple as that.

## Droids on the Homepage

Now that we're flexing our coding muscles, let's show off a bit on the
homepage. I want to list all the droid names related to the starship. It's
pretty similar to what we did earlier, but with a little twist. Open up the
template for this at `/templates/main/homepage.html.twig`. Right after
`parts`, let's dive in. I'm going to add another div and say `Droids: {{
ship.droidNames ?: 'none' }}`.

## The Smart Method

Sure, we could use our `loop` thing and `loop.last comma` thing, but why
not show off a bit? We've needed the droid names in two spots, so let's add
a new smart method in the `Starship` class. This could go anywhere, but
let's stick it at the bottom of the class where we have our droid methods
already. Let's create a `public function getDroidNames(): string`. Now,
we'll return the string, the comma-separated string using `return
implode(', ', $this->droids->map(fn(Droid $droid) =>
$droid->getName())->toArray());`.

## Wrapping it Up

Now we've got our `getDroidNames()` method and it's going to be a breeze to
use it on our homepage. We just say `{{ ship.droidNames ?: 'none' }}` and
voila, we're done! You'll see our droids listed and if there are no droids,
it'll just say `none`. 

So, this wasn't some flashy tutorial on `ManyToMany` relationships, but we
did add a neat smart method to our entity. In the next session, let's
explore how we can join across `ManyToMany` relationships. Till then, keep
coding and having fun!