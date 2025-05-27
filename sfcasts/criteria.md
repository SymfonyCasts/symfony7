# The Clever Criteria System

We've got this super handy
`$ship->getParts()` method that gives us a way to find *every* part
for our starship. But the fiscal year is coming to a close, and we need to
plan our budget. *Boring*, but necessary: our Ferengi bosses demand it!
Most parts on our ship are cheap, like the nuts and bolts and duct tape that hold
everything together. We're not really worried about those.
Instead I want to quickly return all of our ship's parts that
cost more than 50,000 credits.

Sure, we could do a fresh query in our controller for all the starship
parts related to the ship where the price is greater than 50,000. But
where's the fun in that? I wanto to stick with our easy `$ship->getParts()`
shortcut. Is that possible?

## Adding getExpensiveParts() 

Jump into the `Starship` class and look for the `getParts()` method.
Copy that method, paste it below, and rename it to
`getExpensiveParts()`. For now, return all the parts. 

Back in our show template, let's take this baby for a spin. Change `parts`
to `expensiveParts`. There's no`expensiveParts` property, but this will
call the `getExpensiveParts()` method we just crafted. 

## Filtering Out the Cheap Stuff:

Time to make our method return only the expensive parts. Remember:
`$this->parts` isn't an array – it's a special Collection object with a
few tricks up its sleeve. One of these is the `filter()` method. This executes
a callback for every part. If we return true,
it includes that part in the final collection. If we return false, it
filters it out. So we can just say `return $part->getPrice() > 50000;`.

Done! Except... this is super inefficient. We're still
querying for *every* part related to our starship, then filtering
that in PHP. What a waste! Could we ask Doctrine to change the query itself, so
it only grabs the parts related to the starship where the price is greater than
50,000?

## The Power of the Criteria Object

Enter the `Criteria` object. This thing is as mighty as a Wookiee, although
I'll admit, it's a bit cryptic. Time to clear out our logic and instead use
`$criteria = Criteria::create()->andWhere(Criteria::expr()->gt('price',
50000));`. To use this, we just say `return
$this->parts->matching($criteria);` at the end.

Now, if you know me, you know I like to keep my query logic organized in my
repository classes. But now we have some of our query logic inside our
entity. Is that bad? Not necessarily, but I like to keep things tidy. So
let's move this `Criteria` logic into our repository. 

## Moving Criteria to the Repository

Over to our `StarshipPartRepository` we go. Anywhere in here, add a public
static function called `createExpensiveCriteria()`. Why static? Two
reasons: one, because we can (we're not using the `this` variable anywhere
inside of here), and two, because we're gonna use this method from the
`Starship` entity. We can't auto-wire services into entities, so it has to
be static. 

Back in `Starship`, let's use this. Delete the `Criteria` stuff entirely,
and instead say
`matching(StarshipPartRepository::createExpensiveCriteria())`.

## Combining Criteria with Query Builders 

Everything still works like a charm. Let's go a step further and flex our
developer muscles. Let's create a method to demonstrate how we can combine
`Criteria` with Query Builders. 

Let's say we want to get a list of all the expensive parts in some
controller. Copy the `getExpensiveParts()` from our `Starship`, because
it'll look exactly the same. Paste that in `StarshipPartRepository`. 

To combine this with a `Criteria`, say
`addCriteria(self::createExpensiveCriteria())`. Now that we're in a Query
Builder, we can do the normal stuff here. So `setMaxResults($limit)`. Want
to do an `orderBy` or an `andWhere`? Go for it. And of course, you can
finish this with `getQuery()->getResult()`. 

Combining `Criteria` with Query Builders is a power move. 

Alright, that's enough thrill for one lesson. Next time, we'll dive into
something completely different. Buckle up!
