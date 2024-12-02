# Cosmic Queries: the Repository Class

Time to talk Entity Repositories - a place where we "dock" custom queries
for an entity.

In the homepage controller, we wrote a query with the query builder to find all
ships. This is fine, but if we needed to use the same query elsewhere, we'd need
to duplicate it. And if we wanted to change it, we'd need to do it in multiple
places. Gross!

## Fetching The Repository Service

Entity Repositories to the rescue! Wait, didn't `make:entity` create one of these?
It did! To grab the repository object for an entity, try
`dd($em->getRepository(Starship::class))`:

[[[ code('0ce501d299') ]]]

Back to the app and refresh. Cool! We have an `App\Repository\StarshipRepository`
object. Go check out this class: `src/Repository/StarshipRepository.php`.

First, if you're curious how Doctrine knows this class is the repository for the
`Starship` entity, jump into `src/Entity/Starship.php`. Ah, the `#[ORM\Entity]`
attribute has `repositoryClass: StarshipRepository::class`:

[[[ code('ba5e58d0a8') ]]]

Each entity - like `Starship` - has its own repository class. It's empty to start,
but we'll soon fill it with custom queries. Also, it's a service! That means we
can autowire it.

In the homepage controller, remove this `dd()`. Let's simplify: replace `EntityManagerInterface`
with `StarshipRepository $repository`:

[[[ code('96d1698010') ]]]

This query we wrote earlier, to fetch all ships, is *so* common that every repository
comes with a shortcut for it: `findAll()`:

[[[ code('8d6f95e1d7') ]]]

Much nicer! Back in the app, refresh. It still works!

Let's also use this in `StarshipController::show()`. Replace
`EntityManagerInterface` with `StarshipRepository $repository`:

[[[ code('a0f47ba21e') ]]]

Every repository also comes pre-built with a `find()` method! And because this is
the `Starship` repository, we don't need to pass the entity class - just the `$id`:

[[[ code('30c5fcc0cb') ]]]

Jump back to the app, refresh, and click a starship. Still works, perfect!

## Custom Queries in the Repository

Back in the homepage controller, instead of finding *all* ships, what if we need
to only find ships whose status is *not* `completed`: so just `waiting` or `in progress`.
We need a custom query! But this time, instead of writing it in the controller,
let's organize it in the repository.

Add a new `public function findIncomplete()` method that returns an `array`. Include a
docblock so our IDE knows this will be an array of `Starship` objects:

[[[ code('b47753f4d5') ]]]

Inside, `return $this->createQueryBuilder('e')`.
This is just an *alias* for the entity - we'll need it in a sec.
What's cool about creating a query builder in a repository, is that you don't need
to specify the `select()` or `from()` like in the controller. It's done
automatically. All we need to do is add
`->where('e.status != :status')`. `e.status` is the *property* name on the `Starship`
entity and `:status` is a *placeholder* for a value. Pass it a value with
`->setParameter(':status', StarshipStatusEnum::COMPLETED)`.

This silly-looking `:status` and the immediate `setParameter(':status', ...)` is
important. Never include the actual value in the query for two reasons.
First, Doctrine can optimize the query performance slightly when using placeholders.
Second, and more importantly, placeholders prevent SQL injection attacks! If
you thought The Borg was bad, you'll *really* hate SQL injection attacks!
To finish the query, add `->getQuery()` and `->getResult()`:

[[[ code('eb3ad8cae4') ]]]

Back in the homepage controller, replace `findAll()` with `findIncomplete()`:

[[[ code('f55bfc0540') ]]]

Spin back over. We *should* see this completed ship disappear.
We do! The query is working! Check out the profiler:
we see the query *and* the parameter we used.

## Another Custom Query, Another Repository Method

Back in the controller, I don't like this `$myShip` logic. And it's not because
we're faking the idea of "my ship" by just grabbing the first one. It's because,
whatever the logic is, this should be in the repository so we can find "my ship"
wherever we need it.

In `StarshipRepository`, add a new `public function findMyShip()` method
that returns a `Starship` object. We can imagine that this method would take a user
or something to find their ship, but for now, just return `$this->findAll()[0]`
to get the first ship in the table:

[[[ code('011d4e7947') ]]]

Back in the controller, replace this with `$repository->findMyShip()`:

[[[ code('70c539c985') ]]]

That just *reads* better! Spin over to the app and refresh. Still works!
Look at the profiler: two
queries! The first finds all the incomplete ships and the second is the `findAll()`
from `findMyShip()`. Perfect!

Next, let's improve our fixtures and make them 100 times more fun with a library called
Foundry. This will let us create a whole fleet of Starships as if we had a
replicator. Let's do it!
