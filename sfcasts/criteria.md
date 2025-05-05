# Criteria

Coming soon...

## Finding the Pricey Parts: An Adventure in Methods

Alright, my fellow code-wranglers, let's imagine we're whizzing through
space in our trusty starship. We've got this super handy
`$ship->getParts()` method that gives us the low-down on every piece of kit
on our vessel. But what if we're not interested in the cheap nuts and
bolts? What if we want to know about the high-end stuff, the parts that
cost more than 50,000 credits?

Sure, we could do a fresh query in our controller for all the starship
parts related to the ship where the price is greater than 50,000. But
where's the fun in that? Let's stick with our trusty `$ship->getParts()`
methods. They're as easy as pie, and I do love me some pie. 

## The Birth of getExpensiveParts() 

Jump into the `Starship` class and look for the `getParts` method. I'm
going to copy that method, paste it right below, and rename it to
`getExpensiveParts()`. For now, let's have it return all the parts. 

Back on our show template, let's take this baby for a spin. Change 'parts'
to 'expensive parts' and then call `$ship->getExpensiveParts()`. Even
though there's no `expensiveParts` property, it's going to call the
`getExpensiveParts()` method we just crafted. 

## Filtering Out the Cheap Stuff

Now let's make our new method return only the expensive parts. Remember,
`$this->parts` isn't an array – it's a special collection object with a
few tricks up its sleeve. One of these is the `filter()` method. This nifty
little function calls a callback for every single part. If we return true,
it includes that part in the final collection. If we return false, it
filters it out. So we can just say `return $part->getPrice() > 50000;`.

However, this isn't the most efficient way to do things. We're still
querying for every single part that relates to our starship, then filtering
that in PHP. That's like trying to find a diamond in a coal mine. What we
really want to do is change the query itself, so Doctrine grabs only the
parts related to the starship where the price is greater than 50,000. 

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