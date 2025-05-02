# Orphan Removal

Coming soon...

I'm going to show you a really interesting and fairly common situation. So
in our fixtures, let's  scroll down a little bit and create a new
`StarshipPart`. 

```php $starshipPart = StarshipPartFactory::createOne([ 'name' => 'Toilet
Paper', 'starship' => $ship, ]); ```

But to make it easy to see, I gave this one a specific name of one of the
most important things  never to forget when you take off on a trip on a
spaceship. Toilet paper. Yes, we all remember the  pandemic. Then I'm also
going to assign this part specifically to the Starship above. Set that to 
`ship` and I actually forgot to assign that variable up here so I'll say 

```php $ship = StarshipFactory::createOne(); ```

And down here we're making sure that this part is related to that specific
`Starship`. And then  below this, I'm going to dump our `StarshipPart`. 

```php dump($starshipPart); ```

Perfect. So nothing fancy at all yet but let's try reloading our fixtures.
No errors and for the  first time, we can actually see that proxy object I
was talking about. When you create an object  through Foundry, it actually
passes you back your new object but it's wrapped in this thing called a 
proxy. Now most of the time, that doesn't matter. We don't care. But in
order to show you as clearly  as possible our situation, up here on the
ship, I'm going to get the real object by saying 

```php ->_real(); ```

We're going to do the same thing down here for `StarshipPart` by calling 

```php ->_real(); ```

All right, try the fixtures again and they work fine. This time without
that proxy and we can see  that our `StarshipPart` is in fact related to
the correct `Starship`, the USS Espresso, which is  what we created right
above here. So so far, everything is looking and feeling good. 

Now what happens if we want to delete a `StarshipPart`? Now that's usually
pretty easy because  we're going to say `manager->remove($starshipPart)`
and of course you'd say `manager->flush()` to  actually save that to the
database. 

What if you wanted to do this slightly differently? What if you wanted to
say, I want to remove this  part from this ship? So what I might do in that
case is say something like

 ```php $ship->removePart($starshipPart); ```

Kind of cool, right? So let's see what happens now when we reload the
fixtures. It explodes with our  favorite error that `starship_id` cannot be
null and that makes sense. When we call `removePart()`,  we actually set
the `starship` to `null` but we said that that's not allowed. So what's the
solution? 

In some cases, you may want to allow the parts to be removed from the ship
to become orphaned, which  means setting the `JoinColumn` to allow this
change, we'd go to `StarshipPart`, change this nullable  to true, generate
a migration and then run that migration. 

Alternatively, if a part should always belong to a ship and suddenly one is
removed from a ship,  we may want to delete that part entirely. To allow
this, go in `Starship` to the `OneToMany` and add  `orphanRemoval=true`. 

All right, now let's spin over, reload the fixtures, and no error, and
check this out. The ID of our  part is now null because it was deleted
entirely from the database. So you can see `orphanRemoval`  basically says,
hey, if any of these parts become orphaned, go ahead and remove it from the
database  entirely. So a super handy thing to have in your back pocket. All
right, next let's talk about  something else.