# Many To Many Foundry

Coming soon...

Over in `AppFixtures`, we've seen how to manually assign a droid to a
starship. Now,  let's see if we can create a whole bunch of droids and a
whole bunch of starships and  assign them all at once. So step one here is
to remove our manual starship

and droid code. Most importantly, remove all these droid and starship
assignments.  Alright, we're back to really basic and boring code here. 

Now, head to the bottom where we're creating our starships and parts. The
first thing  to do is to create a bunch of droids. So do
`DroidFactory::createMany(100)`. And right  below this we're creating
starships. But let's not create 100 of those just yet.  We're going to
control the droids that are assigned to it. So, say `'droids' => 
DroidFactory::randomRange(1, 5)`. This will assign anywhere between 1 to 5
random  droids to each starship. 

This isn't quite right yet, but I want to point out one really cool thing
that is  going to work. So we have a `droids` property here, but in
`starship` we do not have  a `setDroids()` method. Normally that wouldn't
work, it would throw an error. But  `Faker` is smart enough to see that we
have an `addDroid()` method. And it's going  to call that instead one by
one for each of those droids. 

Alright, let's spin over and run `symfony console doctrine:fixtures:load`.
No errors,  perfect. We'll do `symfony console doctrine:query:sql 'SELECT *
FROM droid'`. First  thing let's do is to query. From `droid` list, we
should have 100 droids. And let's  see here, looks like we do. Yep, 213 to
312. 

But now let's query from the `starship_droid` table, the join table. Now
this looks  random, it looks like there's a random droid assigned to each
of the different  starships. But if you look closely here, these random
droids are actually the same 3.  They're repeating 312, 215, and 279. Why? 

If you look back at your `AppFixtures`, the problem is that this line here,
this  `randomRange()`, is only called one time. So it finds a random 1 to 5
droids and  assigns those same 1 to 5 droids to every single one of these
100 starships. So that's  not what we wanted. 

The way to fix this is to pass a closure. So do `fn() => ['droids' => 
DroidFactory::randomRange(1, 5)]`. Perfect, that's it. So now we'll call
our callback  for all 100 of those starships. Which means this will be
called 100 times and will  return the random range for each of those 100
ships. 

Alright, let's try that. So run  ```terminal
symfony console
doctrine:fixtures:load
``` And  ```terminal
symfony console
doctrine:query:sql 'SELECT * FROM starship_droid'
``` And now that is
looking like a true random set of droids assigned to starships. 

Now the other way we could have fixed this is we could have moved this
`droids` key  here into `StarshipFactory` down on the `getDefaults()`
method. I'm going to do this  for two reasons. One is that if there are no
droids for some reason, this is going to  throw an error. So it makes sense
here because we're creating droids right above it.  But in
`StarshipFactory`, we don't really know where this is going to be called
from. 

The second reason is that I like to use defaults only for required
properties. The  properties needed to make this thing save to the database.
And since droids are not  actually required, I like to keep them out of my
defaults and actually set them  wherever I'm actually using my
`StarshipFactory`. 

Alright, next up, let's talk about many-to-many joins.