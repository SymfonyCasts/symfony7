# Cascade

Coming soon...

So when you try to load the fixtures now, you're met with this humongous
error. This is  actually a really common error in Doctrine. It says, "an
entity was found through the  relationship `starshipDroids` that was not
configured to cascade persist operations for  entity `StarshipDroid`". What
this is basically saying is, hey, look, you're persisting  this `Starship`,
and this `Starship` has a `StarshipDroid` attached to it on one of the 
relationships, but nobody ever called `persist()` on this `StarshipDroid`.
So Doctrine's  like, okay, you're persisting `Starship`, it's connected to
`StarshipDroid`, but you  never told me to persist this. So what do you
want me to do? 

Now the problem here is that inside of our `Starship` entity, we don't have
access to  the entity manager. So we can't call
`manager->persist(StarshipDroid)`. Fortunately, we  can use something
called `cascade={"persist"}`. So scroll all the way up to find the 
`starshipDroids` property, find the one-to-many. And on here, we're in a
new option here  called cascade. Type it manually because it gives us way
more than we need. Then an  array, and we're gonna say `persist`. 

That's it. So that does what the name sounds like. It says if somebody
persists this  `Starship`, I want to cascade that persist down onto any of
the properties. Now be  careful with this because it does kind of make
things automatic that are not normally  automatic. And that can, in some
cases, make a code a little bit less predictable. But  in this case, it's
exactly what we want. So try those fixtures again. And it works. 

What this means now is that we can once again use `ship->addDroid()`. But
instead of  just creating, relating one droid to one ship, I want to get
back to creating a bunch  of ships and relating them to a bunch of droids.
So to do that, we can get rid of all  of our manual code we added. And then
uncomment out the `droids` property that was  passing into
`starshipFactory`. 

Try the fixtures again. And they work. That's amazing. It works because
behind the  scenes, Foundry calls `addDroid()` on each `Starship` for each
`Droid`. And we just  proved that `addDroid()` once again works. But there
are some limitations.

 What if we want to add a droid to a `Starship` and control the
`assignedAt` property?  So one way to do that is going to `Starship`, look
for `addDroid()`, and add an  argument for that. So add a
`DateTimeImmutable` called `assignedAt`. Make that optional.  That'll make
it much easier to create these objects. Of course, after we create the 
`StarshipDroid`, we'll say, hey, if `assignedAt` is passed in, then let's
set that on  the `StarshipDroid`. 

The only problem here is there's not gonna be a way using Foundry to
control that  `assignedAt` field. We just don't have that much flexibility.
So in that case, you  would need to kind of take control of things manually
here if you wanted to create a  couple of droids with a specific
`assignedAt` property. 

The last thing I want to do is render that `assignedAt` somewhere on our
site. So to  do that, open up `template/starship/show.html.twig`, and right
here I want to render  the `assignedAt`. The tricky thing is when we call
`ship.droids`, this gives us the  `Droid` object, but what we really need
here is the `StarshipDroid` join entity object. 

So no problem, we just need to do a little bit more work for
`StarshipDroid` object.  In `ship.starshipDroids`. So let's loop over the
join entity. This is one case where  we're purposely not using our shortcut
method. And now we just need to say

`starshipDroid.droid.name`. And that's it. And then for the `assignedAt`,
we'll kind of  sneak it in right here. I'll say assigned. This one is
`starshipDroid.assignedAt`.  `StarshipDroid.assignedAt`. I'll pipe that
into our ago filter to make it look extra  fancy. 

So find one of these ships that actually has a droid. Click into it and
perfect.  R2D2 assigned four minutes ago. 

So that's it. We have touched on every corner of Doctrine relationships,
including the  very tricky many-to-many with extra fields. As usual, if you
have any questions, we are  here for you down in the comment section.