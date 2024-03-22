# Creating your own Service

We know that services do work, and we know that Symfony is full of services
that we can use. If you run:

```terminal
php bin/console debug:autowiring
```

We get the dinner menu of services, where you can order any of these by adding
an argument type-hinted with the matching class or interface.

We, of course, *also* do work in *our* code... hopefully. Right now, all that work
is being done inside our controller, like creating the Starship data. Sure, this
is hard-coded right now, but imagine if this were *real* work: like a complex database
query. Putting the logic inside a controller is "ok"... but what if we wanted
to reuse this code somewhere else? What if, on our homepage, we wanted to get a
dynamic count of the Starships by grabbing this same data?

## Creating the Service Class

To do that, we need to move this "work" into its own service that *both* controllers
could then use. In the `src/` directory, create a new `Repository` directory and
a new PHP class inside called `StarshipRepository`.

[[[ code('9be4c8e3f6') ]]]

Just like when we built our `Starship` class, this new class has absolutely nothing
to do with Symfony. It's just a class that *we've* decided to create to organize
*our* work. And so, Symfony doesn't care what it's called, where it lives or what
it looks like. I called it `StarshipRepository` and put it in a `Repository` directory
because that's a common programming name for a class whose "work" is to fetch a
type of data, like Starship data.

## Autowiring the New Service

Ok, before we even do anything in here, let's see if we can use this inside a
controller. And, good news! *Just* by creating this class, it's already available
for autowiring. Add a `StarshipRepository $repository` argument, and,
to make sure it's working, `dd($repository)`.

[[[ code('f5d319e9bc') ]]]

All right, spin over, click back to our endpoint, and... got it. That's so cool!
Symfony saw the `StarshipRepository` type-hint, instantiated that object, then passed
it to us. Delete the `dd()`... and let's move the starship data inside. Copy it...
and create a new public function called, how about, `findAll()`. Inside, `return`,
then paste.

Back over in `StarshipApiController`, delete that... and it's beautifully simple:
`$starships = $repository->findAll()`.

Done! When we try it, it *still* works... and now the code for fetching starships
is nicely organized into its own class and reusable across our app.

## Constructor Autowiring

With that victory under our belt, let's doing something harder. What if, from
inside `StarshipRepository`, we needed access to *another* service to help us do
our work? No problem! We can use autowiring! Let's try to autowire the logger
service again.

The only difference this time is that we're *not* going to add the argument to `findAll()`.
I'll explain why in a minute. Instead, add a new `public function __construct()`
and do the auto-wiring *there*: `private LoggerInterface $logger`.

Down below, to use it, copy the code from our controller, delete that, paste
it here, and update it to `$this->logger`.

Cool! Over in the controller, we can remove that argument because we're not using
it anymore.

Testing time! Refresh! No error - that's a good sign. To see if it logged something,
go to `/_profiler`, click on the top request, Logs, and... there it is!

So let me explain why we added the service argument to the constructor. If we want
to fetch a service - like the logger, a database connection, whatever, *this* is
the correct way to use autowiring: add a `__construct` method inside another service.
The trick we saw earlier - where we add the argument to a normal method - yeah,
that's special and *only* works for *controller* methods. It's an extra convenience
that was added to the system. It's a great feature, but the constructor way... that's
how autowiring really works.

And this "normal" way, it even works in a controller. You could add a `__construct()`
method with an autowirable argument and that would totally work.

The point is: if you *are* in a controller method, sure, add the argument to the
method - it's nice! Just remember that it's a special thing that only works here.
Everywhere else, autowire through the constructor.

## Using the Service on another Page

Let's celebrate our new service by using it on the homepage. Open up
`MainController`. This hardcoded `$starshipCount` is *so* 30 minutes ago. Autowire
`StarshipRepository $starshipRepository`, then say 
`$ships = $starshipRepository->findAll()` and count them with `count()`.

While we're here, instead of this hardcoded `$myShip` array, let's grab
a random `Starship` object. We can do that by saying `$myShip` equals
`$ships[array_rand($ships)]`

Let's try it! Hunt down your browser and head to the homepage. Got it! We see the
randomly changing ship down here, and the correct ship number up here... because
we're multiplying it by 10 in the template.

## Printing Objects in Twig

And something crazy-cool just happened! A minute ago, `myShip` was an associative
array. But we *changed* it to be a Starship *object*. And yet, the code on our page
kept working. We just accidentally saw a superpower of Twig. Head to
`templates/main/homepage.html.twig` and scroll down to the bottom. When you say
`myShip.name`, Twig is really smart. If `myShip` is an associative array, it'll grab
the `name` key. If `myShip` is an object, like it is now, it will grab the `name`
property. But even *more* than that, if you look at `Starship`, the `name` property
is *private*, so we can't access it directly. Twig realizes that. It looks at the
`name` property, sees that it's private, but *also* sees that there's a public
`getName()`. And so, it calls *that*.

All we need to say is `myShip.name`... and Twig handles the details of how to
fetch that, which I love.

Ok, one last tiny tweak. Instead of passing the `starshipCount` into our template,
we can do the count inside Twig. Delete this variable, and instead, pass a `ships`
variable. In the template, there we go, for the count, we can say `ships`,
which is an array, and then use a Twig filter: `|length`.

That feels good. Let's do the same thing down here... and change it to greater than 2.
Try that out. Our site just keeps working!

Next up: let's create more pages and learn how to make routes that are even smarter.
