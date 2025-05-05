# Fetching Relation

Coming soon...

# Welcome Back to the World of Symfony!

Oh, hello there, fellow Symfony enthusiast! We're going to dive straight
into the action today, and I promise you'll be up to your elbows in code
before you know it. 

## Let's Get Started!

First off, navigate to our homepage and click on any of the starships
showcasing an 'In Progress' status. These are our little lab rats that we
assign parts to in our fixtures. While we're here, you might notice we're
already listing the parts. But don't let that fool you - it's all
hard-coded at the moment. 

Now, we're about to get our hands dirty by querying for the parts that are
linked to a specific starship. 

```terminal
cd src/Controller/StarshipController.php
```

## Say Hello to Auto-wiring

Remember how we usually query for `StarshipPart` by auto-wiring that
repository? Well, we're doing the same dance here. 

```php StarshipPartRepository $partRepository ```

Next, let's declare a variable, `parts`, and make it equal to our
`$partRepository->findBy`. 

This is pretty standard stuff, really. You know the drill - if you want to
query where some property equals some value, you just use `findBy`. When it
comes to relationships, we're keeping it simple, querying for the
`Starship` property. 

And no, we're not doing `Starship ID` or anything of the sort. We're
keeping IDs out of this - they need their beauty sleep. Instead, we're
going to pass the entire `ship` object. You could just pass the `getID` if
you're feeling lazy, but in the spirit of doctrine, relationships, and
thinking about objects, we're going full steam ahead with the `ship`
object. 

## Debugging and Celebrating

Now, let's debug and see what we've got. 

```php dd($parts) ```

Hit refresh, and voila! We've got an array of 10 `StarshipPart` objects,
all related to this `Starship`. That's pretty awesome, right? But hold onto
your seats because we can make it even easier. 

Replace `parts` with `ship->getParts()`. Now, here's the fun part: instead
of an array of `StarshipPart` objects, we get a `PersistentCollection`. And
even though it looks empty, that's just Doctrine playing hard to get. It's
never going to be a true array, but more an `ArrayCollection` or a
`PersistentCollection`. The important thing is it looks and acts like an
array, so we're happy. 

## Doctrine's Little Secret

Why does it seem empty, you ask? Well, that's because Doctrine is a sneaky
little thing. It doesn't actually query for the parts until we need them. 

```php foreach ($ship->getParts() as $part) {     dump($part); } ```

Even though `parts` appears as an empty `PersistentCollection`, once we
loop over it, we magically see the 10 `StarshipPart` objects. 

## Hello, Queries!

We've got two queries at play here. The first one is for the `Starship`,
and the second one is for all the `StarshipPart`s. The first one comes from
Symfony querying for the `Starship` based on the slug. The second query
happens the moment we `foreach` over the `parts`. At that moment, Doctrine
says, "Oh, I need to go actually query for those parts," and does it. 

Isn't that just amazing? Makes me want to throw a party for Doctrine. 

## Tidying Up and Looping Over Parts

Let's go and get rid of the `parts` variable entirely. We can celebrate by
getting rid of the `StarshipPart` imposter - that was way too much work.
Instead, let's assign a `parts` variable and say `ship->getParts()`. 

```php $parts = $ship->getParts() ```

Now that we've got our shiny new `parts` variable, we can loop over that in
our template. Open up `templates/starship/show.html.twig` and replace the
hard-coded part with our loop. 

```twig {% for part in parts %} {{ part.name }} {{ part.price }} {{
part.notes }} {% endfor %} ```

## Wrapping Up

And there you have it! We've managed to display all 10 of our related
parts, all without making a real query because we're using the shortcut
`ship->getParts()`. 

But you know what? Even this is too much work. Let's get rid of the `parts`
variable entirely. 

```twig {% for part in ship.parts %} ```

We're running wild here, I know. But wait, it still works! Now, just for
kicks, let's also display the number of parts we have on this page. 

```twig {{ ship.parts|length }} ```

We still have two queries, but Doctrine is smart. It knows we've already
queried for all the `StarshipPart`s, so when we count them, we don't need
to make another count query.

Well, folks, that wraps it up for now. Stay tuned for our next exciting
episode where we'll delve into the mysterious world of Doctrine's owning
versus inverse side of a relationship!