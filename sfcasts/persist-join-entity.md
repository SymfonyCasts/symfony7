# Persist Join Entity

Coming soon...

After refactoring our many-to-many to have a join entity called
`StarshipDroid`, let's reload our fixtures and see what happens. And  we
get an `Undefined property: App\Entity\Starship::$droids` error, actually
coming from `Starship` line 205. And it's coming from the  `getDroids()`
method. Now, it's not very obvious, but when we create the droids, when we
relate the droids to the `Starship` in the fixtures,  behind the scenes at
some point, that actually calls `StarshipArrow getDroids` to figure out
which droids it currently has. 

Of course,  we're still referencing the non-existent property 'droids', so
that's going to fail. So the temporary fix here is just to comment out. So 
now at least the fixtures are fixed. Now, to really highlight what changed
here and what the problem is, I'm going to create a couple of  objects by
hand. So let's say `ship` equals `StarshipVector`, not `crate1`, we could
use that, but let's just grab a random one. And to  avoid any confusion,
I'm going to do that underscore `real` thing again, so we have the real
object, not the proxy. 

Let's do the same thing  to grab a droid. So I'll say `droid` equals
`droidFactory`, again, let's grab a random one, and then call `real` on
that. So the key thing  that changed is before we were able to say
`shipArrow addDroidDroid`. No matter what, that just doesn't work anymore. 

The reason is that  because we're referencing the old `droids` property.
Now it's called `StarshipDroids`, and of course that's just a joint entity.
So when  you have this joint entity, you need to be able to, at least for
now, we're going to make it fancier later. You need to be a bit more 
hands-on. So instead of `shipArrow addDroid`, we'll get rid of that.
`StarshipDroid` equals new `StarshipDroid`, then
`StarshipDroidArrowSetDroid`,  not `ship` but `droid`. And then
`StarshipDroidArrowSetStarship`, `ship`. So we're just creating that
entity, and we're setting those  many-to-one relationships manually. 

And then finally down here, since we're creating these by hand, we need to
persist and flush them.  So `manager` arrow `persist StarshipDroid`, and
`manager` arrow `flush`. 

So kind of a lot of work, but also super straightforward and super  boring.
All right, spin over and try the fixtures now. And let's also check the
database, so `doctrine query SQL`, `select star from StarshipDroid`.  So
just like before, we're selecting from that join table, and we see one
entry in here for one `Starship`, and it's one `droid`. So that  looks
perfect. 

Head over to the homepage and refresh. Ooh, big error, it says,
`[Semantical Error] line 0, col 55 near 'droids WHERE': Error: Class 
App\Entity\Starship has no association named droids`. So this looks like a
query problem, and in fact it is. 

So open up `src/repository/StarshipRepository`,  it has something under our
join here. So we're joining at `s.droids`, there no longer is a `droids`
property, so we need to join over on  `StarshipDroids`, so I'm going to
`s.starship. StarshipDroids`, and for clarity, let's actually call that
`StarshipDroid`, that's what it  actually is. I like to keep my thing
singular, so I'm not going to use `StarshipDroids`, I'll use
`StarshipDroid`. 

And very simple, instead of  counting the `droids`, we're now going to
count the `StarshipDroid`. And now on the homepage, we are on to the next
error, `Warning: Undefined  property: App\Entity\Starship::$droids`. This
comes from our `ship.droidNames` in the homepage template. So we know that
when we call  `ship.droidNames`, it's actually calling
`StarshipArrowGetDroidNames` and here we're still referencing the old
`droids` property. 

So next,  let's fix this in a way where we only have to make changes in
this one file and the whole rest of our application just starts working,
magic.