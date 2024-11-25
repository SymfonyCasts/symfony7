# Starship Entity Repository

Time to talk Entity Repositories - a place where we can "dock" custom queries
for an entity.

In our homepage controller, we wrote a query with the query builder to find all
ships. This is fine, but if we needed to use the same query elsewhere, we'd need
to duplicate it. And if we needed to change it, we'd need to change it in multiple
places. Gross!

Entity Repositories to the rescue! Didn't `make:entity` create one of these? How
do we access it?

In our homepage controller, write `dd($em->getRepository(Starship::class))`, switch
over to our app and refresh. `App\Repository\StarshipRepository`. Ok, this is
one way to access it but there's a better way! Open `src/Repository/StarshipRepository.php`.

First, if you're curious how Doctrine knows this class is the repository for our
`Starship` entity, jump into `src/Entity/Starship.php`. Ah, the `#[ORM\Entity]`
attribute has a `repositoryClass: StarshipRepository::class` argument.

Back to `StarshipRepository`! This is a special type of entity repository - a
`ServiceEntityRepository`. That means it's both an entity repository *and* a service,
so it can be autowired! This is one of those cool Symfony/Doctrine integrations.

Let's use it!

In our homepage controller, remove this `dd()`. Instead of injecting `EntityManagerInterface`,
inject `StarshipRepository $repository`. This query we wrote earlier, to fetch all ships,
is so common that there's a repository shortcut for it: `findAll()`. Replace the query
with `$repository->findAll()`. Back in our app, refresh the homepage to make sure it still
works. Great!

Use this repository in our `StarshipController::show()` controller too. Replace 
`EntityManagerInterface` with `StarshipRepository $repository`. Our repository
has a `find()` method also but because it's the *Starship* repository, we don't
need to pass the entity class - just the `$id`.

Jump back to the app, refresh, and click a starship. Still works, perfect!

Back in our homepage controller, instead of finding *all* ships, I want to find
only the ships whose status is *not* `completed`. Just `waiting` or `in progress`.
This is a perfect query for our `StarshipRepository`. Open that up and add a new
method `public function findIncomplete()` with a return type of `array`. Add a
docblock above to specify that this will return an array of `Starship` objects.

Inside, write `return $this->createQueryBuilder()`. For the first argument, use `e`.
This is just an *alias* for our entity - we'll need it in a sec.
What's cool about creating a query builder in a repository, is that you don't need
to specify the `select()` or `from()` like we did in our controller - it's done
automatically! We need a `where` clause to filter out the completed ships.
Write `->where('e.status != :status')`. `e.status` is the *property* name on our
entity. This `:status` is a *placeholder* for a value that we'll set next with
`->setParameter(':status', StarshipStatusEnum::COMPLETED)`. It's important to always
use this *placeholder*/*parameter* system for a couple of reasons. First, it
improves performance by allowing Doctrine to cache the query. Second, and more
importantly, it helps prevent SQL injection attacks when using user input in a query.
Finally, call `->getQuery()` and `->getResult()`.

Now, in our homepage controller, replace `findAll()` with `findIncomplete()`.

Back in our app, once we refresh, we should see this *completed* ship disappear.
Refresh... and... gone! Perfect, our query is working! Check out the profiler,
we see the query *and* the parameter we used.

Back in our homepage controller, I don't like this `$myShip` logic. We're just grabbing
a random ship from the above `$ships` list. Sounds like a perfect job for another
repository method! In `StarshipRepository`, add a new method: `public function findMyShip()`
that returns a `Starship` object. We can imagine that this method would take a user
or something to find their ship, but for now, just return `$this->findAll()[0]`
to get the first ship in the table.

Now, back in the controller, replace this with `$repository->findMyShip()`. Jump to
the app and refresh. This is now considered *my ship*. Look at the profiler: two
queries! The first one finds all the incomplete ships and the second is our `findAll()`
from `findMyShip()`. Perfect!

Next, we'll improve our fixtures with another library that will allow us to inject
a whole pile of Starships with minimal effort!
