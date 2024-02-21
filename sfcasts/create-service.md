# Creating your own Service

Okay, we know that services do work, and we know that Symfony is full of services
that we can use. If you run:

```terminal
php bin/console debug:autowiring
```

This is like a dinner menu of services, where you can order any of them by adding
an argument type-hinted with the correct class or interface.

We, of course, *also* do work in *our* code... hopefully. Right now, all that work
is being done inside of our controller, like creating the Starship data. Sure, this
is hard-coded right now, but imagine if this was *real* work: like a complex database
query. Putting the logic right inside a controller is "ok"... but what if we wanted
to reuse this logic somewhere else? What if, on our homepage we wanted to get a
dynamic count of the Starships by leveraging this same data?

## Creating the Service Class

To do that, we need to move this work into its own service that *both* controllers
could then use. In the `src/` directory, create a new `Repository` directory and
a new PHP class inside called `StarshipRepository`.

Just like when we built our `Starship` class, this new class has absolutely nothing
to do with Symfony. It's just a class that *we've* decided to create to organize
*our* work. And so, Symfony doesn't care what this is called, where it lives or what
it looks like. I called it `StarshipRepository` and put it in a `Repository` directory
because that's a common programming name for a class whose work is to retrieve a
type of data, like Starship data.

## Autowiring the New Service

Ok, before we even do anything in here, let's see if we can use this inside a
controller. Up top, *just* by creating this class, it's already available for us
to autowire in our code. Add a `StarshipRepository $repository` argument, and,
to make sure it's working, `dd($repository)`.

All right, spin over, click back to our endpoint, and... got it. That's so cool!
Symfony saw the `StarshipRepository` type-hint, instantiated that object, then passed
it to us. Delete the `dd`... and let's move the starship data inside. Copy that and
create a new public function called, how about, `findAll()`. Again, we can call
this whatever we want. Here, `return`, then paste.

Sweet! Over in `StarshipApiController`, delete that, and it's beautifully simple:
`$starships = $repository->findAll()`.

Done. When we try it, it *still* works... and now the code for fetching starships
is nicely organized into its own class and reusable across our app.

## Constructor Autowiring

With that victory under our belt, I think we can get more complex. What if, from
inside `StarshipRepository`, we needed access to *another* service to help us do
our work? No problem! We can use auto-wiring! Let's try to autowire the logger
service again.

But this time, we're *not* going to add the argument to the `findAll()`. I'll
explain why in a second. Instead, add a new `public function __construct()` and
do the auto-wiring *there*: `private LoggerInterface $logger`.

Down below, to use it, let's copy the code from our controller, delete that, paste
it here, and update it to `$this->logger`.

Cool! Over in the controller, we can remove that argument because we're not using
it anymore.

Try it now. Refresh! No error - that's a good sign. To see if it logged something,
go to `/_profiler`, click on the top request, Logs, and... there it is!

So let me explain why we added the service argument to the constructor. If you want
to fetch a service like the logger, this is actually the *correct* way to use
autowiring: add a `__construct` method inside another service. The trick we saw
earlier, where we added the argument to a normal method... yeah, that's a special
thing that only works for *controller* methods. It's a little extra convenience that
was added to the system. It's a great feature, but the constructor way,... that's
the normal way autowiring works.

And this "normal" way, it even works in a controller. You could put a `__construct()`
method with an autowireable argument and that would totally work.

The point is: if you *are* in a controller method, sure, add the argument to the
method - it's nice! Just realize that's a special thing that only works here.
Everywhere else, we're going to follow this pattern.

## Using the Service on another Page

Let's celebrate our cool new service by using it on the homepage. Open up
`MainController`. This hardcoded `$starshipCount` is *so* 5 chapters ago. Autowire
`StarshipRepository $starshipRepository`, then say 
`$ships = $starshipRepository->findAll()`, then count those with `count()`.

And, while we're here, instead of having this hard-coded `$myShip` array, let's grab
a random `starship` object. We can do that by saying `$myShip` equals
`$ships[array_rand($ships)]`

Let's try it! Hunt down your browser and head to the homepage. Got it!. We see the
randomly changing ship down here, and the correct ship number up here... because
we're multiplying it by 10 in the template.

## Printing Objects in Twig

And something crazy-cool just happened! A minute ago, `myShip` was an associative
array. We just changed it to be a Starship *object*. And yet, the code on our page
kept working. We just accidentally saw a superpower of Twig. Head to
`templates/main/homepage.html.twig` and scroll down to the bottom. When you say
`myShip.name`, Twig is really smart. If my ship is an associative array, it'll grab
the `name` key. If my ship is an object, like it is now, it will grab the `name`
property. But even *more* than that, if you look in `Starship`, the `name` property
is *private*, so we can't access it directly. Twig realizes that. It looks at the
`name` property, sees that it's private, but *also* sees that there's a `getName()`.
And so, it calls *that*.

All we need to say is `myShip.name`... and Twig handles the details of how to
fetch that, which I love.

Ok, one last tiny tweak. Instead of passing the `starshipCount` into our template,
we can do the count inside Twig. Delete this variable, and instead, pass a `ships`
variable. In the template, there we go, for the count, we can say `ships`,
which is an array, and then use a Twig filter: `|length`.

I love that. Let's do the same thing down here... and change it to greater than 2.
Try that out and... our site just keeps working!

Next up: let's create more pages and learn how to make routes that are even smarter.
