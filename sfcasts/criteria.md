# The Clever Criteria System

We've got this super handy `$ship->getParts()` method: it returns all the parts
for our starship. But the fiscal year is coming to a close, and we need to
plan our budget. *Boring*, but necessary: our Ferengi bosses demand it!
Most parts are cheap, like the nuts and bolts and duct tape that hold
everything together. We're not really worried about those.
Instead, I want to quickly return all of our ship's parts that
cost more than 50,000 credits.

Sure, we could do a fresh query in our controller for all the starship
parts related to the ship where the price is greater than 50,000. But
where's the fun in that? I want to stick with our easy `$ship->getParts()`
shortcut. Is that possible?

## Adding getExpensiveParts() 

Jump into the `Starship` class and look for the `getParts()` method.
Copy that, paste it below, and rename it to `getExpensiveParts()`.
For now, return everything:

[[[ code('ebc7bb43bc') ]]]

Back in our show template, take this for a spin. Change `parts`
to `expensiveParts`:

[[[ code('3e88506435') ]]]

There's no `expensiveParts` property, but this will call the `getExpensiveParts()`
method that we just crafted. 

## Filtering Out the Cheap Stuff:

Time to make our method return only the expensive parts. Remember:
`$this->parts` isn't an array – it's a special Collection object with a
few tricks up its sleeve. One of these is the `filter()` method. This executes
a callback for every part. If we return true, it includes that part in the final
collection. If we return false, it filters it out. So we can just say
`return $part->getPrice() > 50000;`:

[[[ code('17706a360a') ]]]

Done! Except... this is *super* inefficient. We're still
querying for *every* part related to our starship, then filtering
that in PHP. Imagine if we had 50,000 parts, but only 10 of them
cost more than 50,000. What a waste! Could we ask Doctrine to change the query
so it only grabs the parts related to the starship where the price is greater
than 50,000?

## The Power of the Criteria Object

Enter the `Criteria` object. This thing is mighty. Though, I admit,
also a bit cryptic. Clear out our logic and instead use `$criteria` equals
`Criteria::create()->andWhere(Criteria::expr()->gt('price', 50000))`. To use 
this, `return $this->parts->matching($criteria);`:

[[[ code('1ceafd8105') ]]]

Now, if you know me, you know I like to keep my query logic organized in my
repository classes. But now we have some query logic inside our
entity. Is that bad? Not necessarily, but I like to keep things tidy. So
let's move this `Criteria` logic into our repository. 

## Moving Criteria to the Repository

Over to `StarshipPartRepository` we go. Anywhere in here, add a public
*static* function: `createExpensiveCriteria()`:

[[[ code('d5291507b0') ]]]

Why static? Two reasons: one, because we can (we're not using the `this`
variable anywhere inside), and two, because we're going to use this method
from the `Starship` entity and we can't autowire services into entities,
so it must be static. 

Back in `Starship`, use this. Delete the `Criteria` stuff entirely,
and replace it with `StarshipPartRepository::createExpensiveCriteria()`:

[[[ code('9758fb25e5') ]]]

## Combining Criteria with Query Builders 

Everything still works like a charm, so let's go a step further and flex our
developer muscles. Let's create a method that combines `Criteria` with
`QueryBuilder`s. 

Say we want to get a list of all the expensive parts for *any* `Starship`.
Start by copying the `getExpensiveParts()` method from `Starship`. Paste that
in `StarshipPartRepository`. Then return `$this->createQueryBuilder('sp')`.
Add a `$limit` argument, defaulting to 10. To combine this with a `Criteria`,
say `addCriteria(self::createExpensiveCriteria())`. Now that we're in a `QueryBuilder`,
we can do the normal stuff, like `setMaxResults($limit)`. Want to do an `orderBy`
or an `andWhere`? Go for it. And of course, you can finish this with
`getQuery()->getResult()`:

[[[ code('aca546b7bf') ]]]

Combining `Criteria` with Query Builders is a power move. 

Alright, that's enough about that. Next up, we'll create an entirely new
page to list *every* part. We're on our way to needing some JOINs!
