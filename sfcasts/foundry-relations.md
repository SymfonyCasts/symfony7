# Foundry Relations

Coming soon...

There are a few parts and a few ships, but to make our data really
realistic and help us  develop our app, I want to create lots of parts and
lots of ships. This is a perfect job  for Foundry. Start by creating our
manual code here that created that part and that  starship and related them
to each other. Let's start from scratch. It doesn't really matter  where
I'm going. I'm going to go down to the bottom. I'm going to say 
`StarshipPartFactory::createMany(100);` and let's go crazy and create 100
parts. 

Stop right here and spin over and try the fixtures. 

```terminal
Symfony Console, Doctrine, Fixtures, Load.
```

We see a familiar error here. It says `starship_id` cannot be null in
Starship Part.  This goes all the way back to our `StarshipPartFactory`.
Down here in `getDefaults()`,  this is the only data that's going to be
passed to our `StarshipPart` when it's created. 

The best practice here is to make `getDefaults()` return a key for every
required property  on this object. Right now, we're obviously missing the
`Starship` property, so let's add  that. Let's say `starship` key, not
`starship_id`, definitely not that, and set this to a  really cool method
called `Starship::randomOrCreate()` and pass this in an array. 

The only ships that we're showing on the homepage are either in progress or
waiting, so  to make sure that these parts actually show up on our
homepage, set the status to  `StarshipStatusEnum::IN_PROGRESS`. 

This is a really cool, really powerful method because it's going to look in
the database  first to see if it can find a `Starship` that matches this
criteria. If it can, it's  going to use that. If it can't, it will create
one with that status. 

All right, try the fixtures now. Awesome, no errors. Let's query
from—let's grab all the  Starship parts. This actually looks perfect. If
you look closely, there are 100 parts and  they're each related to a random
`Starship`, which should be a `Starship` that's in a  in progress status. 

What if we wanted more control over this? What if we wanted to assign all
100 of these  parts to the same one ship? I know that sounds kind of weird,
and it is, but it's going  to help us explain a few important things about
foundry and relationships. Up here, let's  first start by getting a ship
variable, so `ship = StarshipFactory::createOne([])`. 

Down here in `StarshipPartFactory`, we can pass a second argument and say,
hey, we want  `starship` to be set to this specific ship. 

All right, try the fixtures again and query once again for the parts.
Perfect. They're all  related to the same one ship. Also over here, query
for the Starships themselves. Inside  of here, if you look closely, we have
23 ships, which is the correct amount because we  create—let's see here.
That fixture should create 20 in the bottom, and we also have 1, 2,  and 3,
so 23 in total, so everything is looking right. 

Here's where things get kind of interesting. In `StarshipPartFactory`,
instead of saying  `randomOrCreate()`, use `createOne()`. Just trust me on
this. All right, I'll go over and  load the fixtures again, then query for
all the ships. What the fringy? There are now  tons of ships. 

If you look closely, there's 123 ships. Here's the problem, for each part,
`getDefaults()`  is called for each part. For all 100 parts, it's calling
this line right here, and that's  creating and saving a `Starship`, even
though we're never going to use that `Starship`  because we override it a
second later. 

The solution is to change this to `StarshipFactory::new([])`. That actually
creates a new  instance of that factory, not an object in the database.
Let's try that. Reload the  fixtures, then query for the ships. Perfect. We
are back to our 23 ships. 

These factory instances, they act like recipes for creating objects. This
doesn't actually  create an object in the database, it's just a recipe for
one. When you pass a factory  instance like here, Foundry is going to delay
creating this object until and if it's  needed. Only if the `Starship` is
not overridden will it create a new `Starship` and save  it. It's a best
practice when you're setting relationships to set them to a factory 
instance for this exact reason. 

Looking at fixtures, let's remove this override. We don't really need that.
Change the  factory, change back to `randomOrCreate()` because that was
actually working pretty well.  I like that. 

Let's reload the fixtures one last time to make sure we didn't mess
anything up. Looks  good. All right, next. It's time to fetch all of the
parts for our ship. When we do that,  we're going to enjoy some sweet
doctrine magic.