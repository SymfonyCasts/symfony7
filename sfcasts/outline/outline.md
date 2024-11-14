# Doctrine

## Installing Doctrine

- Download code to follow along
- Check the `README.md` for setup instructions
- show app
  - no database, Starship's data is hardcoded
- Doctrine isn't part of Symfony directly
- Like Symfony, it's a collection of libraries for working with databases
- `composer require doctrine`
  - For docker config, enter `p` to allow the flex recipe - future chapter
  - `doctrine` is a Symfony flex alias for the flex `orm-pack`
    - Flex packs are a way to group related packages
  - scroll up to see installed packages
    - `doctrine/dbal`
      - "Database Abstraction Layer"
      - "talks to the database in a generic way"
    - `doctrine/orm`
      - "Object Relational Mapper"
      - "maps PHP objects to database tables"
    - `symfony/doctrine-bridge` & `doctrine/doctrine-bundle` are the glue between Symfony and Doctrine
    - `doctrine/migrations` & `doctrine/doctrine-migrations-bundle` are for managing database migrations - we'll talk about that later
    - Other packages are support packages
  - `git status` - explain files
    - Added some `.env` and added some bundles
    - `compose*`: docker config - next chapter
    - `config/packages/*`: Doctrine config
    - `src/Entity`: where our entities will live (PHP objects that represent database tables)
    - `src/Repository`: where our repository classes will live (per-entity services that fetch entities)
    - `migrations/`: where our database migrations will live
- Doctrine installed, but no database yet - that's next!

## Database Setup & Docker

- `.env`
  - Default is postgres, we'll use in this tutorial
  - Show how to switch to sqlite (if docker isn't available)
    - comment out and uncomment `DATABASE_URL`
  - Don't store sensitive data in `.env` - it's committed to git
  - Override in `.env.local` for local dev
- `docker-compose up -d` if postgres not already available
- symfony cli auto-connects to docker container (`symfony var:export --multiline`)
- In app, web debug toolbar, "Server" tab
- run database related commands via `symfony console ...`
- `symfony console doctrine:database:create`
  - show "database already exists" error
  - but we are connecting successfully
  - docker created the database for us

## Starship Entity

- Doctrine ORM calls objects that represent database tables "entities"
  - Not Services
- Re-create `Model/Starship` as an entity
  - `symfony console make:entity`
  - `Starship`
  - `name` - `string`
  - `class` - `string`
  - `captain` - `string`
  - `status` - `enum` for `App\Model\StartshipStatusEnum`
  - `arrivedAt` - `datetime_immutable` (guessed!)
- `src/Entity/Starship.php`
  - `#[ORM\Entity]` configures the database table
  - `#[ORM\Id]` configures the primary key
  - `#[ORM\GeneratedValue]` configures the auto-incrementing id
  - `#[ORM\Column]` configures the table column
    - Type is guessed - even for enums!
    - Name is snake-cased
    - We can remove the current arguments - they are all guessed!
    - Enum
      - not a "real" enum column type in the db
      - stored as `string` or `int` depending on how the enum is backed
  - Copy `getStatusImageFilename()` and `getStatusString()` from `Model/Starship`
- `symfony console doctrine:schema:validate`
  - Mapping is valid!
  - Database is not in sync!

## Migrations

- Migrations "version control" you database
- Create migration
  - `symfony console make:migration`
  - **NOTE**: Database specific SQL - will look different for sqlite
  - Add description: "Add starship table"
  - up/down
- Pre-status
  - `symfony console doctrine:migrations:status`
  - `symfony console doctrine:migrations:list`
- Run migration
  - `symfony console doctrine:migrations:migrate`
- Post-status
  - `symfony console doctrine:migrations:status`
  - `symfony console doctrine:migrations:list`
- Show migration table in database
  - `symfony console doctrine:query:sql 'SELECT * FROM doctrine_migration_versions'`
- Show `starship` table was created
  - `symfony console doctrine:query:sql 'SELECT * FROM starship'`
  - "empty set" - this is good as it means the table exists!

## Persisting and Fixtures

- `symfony console doctrine:query:sql 'SELECT * FROM starship'`
  - no data
- `composer require --dev orm-fixtures`
  - another flex pack
  - scroll up to see installed packages
    - `doctrine/data-fixtures` the actual fixture library
    - `doctrine/doctrine-fixtures-bundle` the Symfony glue
  - `git status`
    - added bundle
    - `src/DataFixtures` directory
- `src/DataFixtures/AppFixtures.php`
  - We want the same entities as in the old `Model\StarshipRepository` service
  - `$ship1 = new Starship();` (the one from `Entity`)
  - We need to use the setters - no constructor
  - Paste from `tutorial/`
  - Note we are not setting the id - it'll be done automatically by the database because of `#[ORM\GeneratedValue]`
- `$manager->persist()` ship 1/2/3
  - `persist()` tells Doctrine to "queue up" the entity to be saved
  - it isn't saved to the database yet
- `$manager->flush()`
  - `flush()` tells Doctrine to "execute" the SQL to save the entities
  - wraps in a "transaction" - like a git commit
  - if an error occurs when inserting the 3rd ship, the first two aren't saved either
  - faster than saving each entity individually as the insert SQL is optimized
- `symfony console doctrine:fixtures:load`
  - purges the database first and then executes `AppFixtures`
- `symfony console doctrine:query:sql 'SELECT * FROM starship'`
  - our three ships!

## Fetching with DQL, the QueryBuilder and find()

- Refactor app to use database
- SQL
  - `symfony console doctrine:query:sql 'SELECT * FROM starship'`
- DQL - "Doctrine Query Language"
  - `symfony console doctrine:query:dql 'SELECT * FROM App\Entity\Starship'`
- `MainController::homepage()`
  - Change argument to `EntityManagerInterface $em`
  - Change `$ships = $em->createQuery('SELECT s FROM App\Entity\Starship s')->getResult();`
    - fetches all ships from the database
  - Homepage Working!
  - Check the SQL in the debug toolbar
    - Formatted query
    - Runnable query
    - Explain query
    - Backtrace
  - Writing DQL by hand is a drag...
    - Use the `QueryBuilder` - _fluent_ interface for building DQL
      ```php
      $ships = $em->createQueryBuilder()
          ->select('s')
          ->from(Starship::class, 's')
          ->getQuery()
          ->getResult();
      ```
    - Import `Starship` from `App\Entity`
    - Homepage still working!
- `StarshipController::show()`
  - Change `StarshipRepository` to `EntityManagerInterface $em`
  - Change `$ship = $em->find(Starship::class, $id);`
    - fetches a `Starship` ship by id
  - Show page working!
  - Check the SQL in the debug toolbar
  - Cleanup `Model` directory
    - Move `StashipStatusEnum` to `Entity` directory
    - Remove `Model` directory

## Entity Repository

- In `homepage()`
  - `dd($em->getRepository(Starship::class));`
  - `getRepository()` returns a `StarshipRepository`
  - This is a service!
  - Inject directly into controller: `StarshipRepository $repository`
  - `$repository->findAll()`
- In `show()`
  - Inject `StarshipRepository $repository`
  - `$starship = $repository->find($id);` (no longer need `Starship` class)
- Custom repository method
  - `StarshipRepository::findIncomplete(): Starship[]`
  - `->createQueryBuilder()` - auto selects the class for us because we're in it's repo
  - `->andWhere('s.status != :status')->setParameter('status', StarshipStatusEnum::COMPLETED)`
  - `->orderBy('s.arrivedAt', 'DESC')` - newest arrivals first
- In `homepage()`, use our new method
  - `$ships = $repository->findIncomplete();`
- Homepage now only shows incomplete ships (one dropped off)
  - Profiler to show query
- One more repository method: `findMyShip(): Starship`
  - You can imagine this would take a user object and return their ship
  - `return $this->findAll()[0];` to fudge this
  - `homepage()`, `$myShip = $repository->findMyShip();`
- Refresh homepage
  - Profiler shows 2 queries now

## Better fixtures with Foundry & Faker

- `composer require --dev foundry` (Flex alias for `zenstruck/foundry`)
- Also installs `fakerphp/faker` - awesome library that generates fake data
- `git status` to see changes
  - installed foundry bundle
  - config/packages/zenstruck_foundry.yaml - foundry config
- `symfony console make:factory`
  - choose `Starship` entity
- `StarshipFactory::defaults()`
  - Add just enough here to make your entity valid
  - Already guessed some default values
    - `faker()->text()` - random text string
    - `faker()->randomElement()` - random element from array
    - `faker()->dateTime()` - random `DateTime` but we need to wrap in `DateTimeImmutable`
  - `arrivedAt` cannot be in future, change to `dateTimeBetween('-1 year', 'now')`
  - `name`, `captain`, `class`:
    - Copy constants from `tutorial`
    - `faker()->randomElement()`
- `AppFixtures`
  - Change existing fixtures to use `StarshipFactory`
  - `array` to set properties
  - If property is missing, uses default value from factory
  - Copy from `tutorial/`
  - Remove `persist()`/`flush()` - foundry does this for us!
  - `symfony console doctrine:fixtures:load`
  - Success!
  - Add random fixtures: `StarshipFactory::createMany(20)`
  - `symfony console doctrine:fixtures:load`
- Look at all these ships! We should paginate!

## Pagination

- `composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter pagerfanta/twig`
- `StarshipRepository::findAllIncomplete()`
  - Change return type to `Paginator<Starship>`
  - Add `$query` variable and remove `->getResult()`
  - Pagerfanta handles creating the query
  - `return new Pagerfanta(new QueryAdapter($query));`
  - Homepage still works but not yet paginated
- `homepage()`
  - `->setMaxPerPage(5)`
  - `->setCurrentPage(1)`
  - Test - 5 ships shown
  - `->setCurrentPage(2)`
  - Test - another 5 ships shown - this is page 2
  - Inject `Request $request`
  - `->setCurrentPage($request->query->getInt('page', 1))`
- Need a pagination widget
- `templates/main/homepage.html.twig`
  - Add `{{ pagerfanta(ships) }}`
    - Not styled
    - !TODO! pagerfanta styling...
  - Add pagination info widget (below the `h1`):
    ```twig
    <div class="mb-4">
        {{ ships.nbResults }} ships (Page {{ ships.currentPage }} of {{ ships.nbPages }})
    </div>
    ```

## Adding Slug and Timestamp Fields

- `symfony console make:entity Starship` - edit existing
  - `slug`, `createdAt`, `updatedAt` - allow `null` for now - we'll change later
- `symfony console make:migration`
  - add description: "Add slug and timestamps to starship"
- `symfony console doctrine:migrations:migrate`
- We want these fields to be non-null and slug to be unique
  - In `Starship`
  - make `slug` unique
  - remove nullable from `slug`, `createdAt`, `updatedAt`
  - `symfony console make:migration`
  - add description: "Make slug and timestamps not nullable"
  - Auto adds an index for `slug`!
  - `symfony console doctrine:migrations:migrate`
  - Error! slug cannot be null
    - We have existing data
    - Manually update our migration with `->addSql()` to fill these fields
    - Before altering:
      - set `slug` to `id` - we know this is unique
      - set `created_at` and `updated_at` to `arrived_at`
    - `symfony console doctrine:migrations:migrate` - all good now!

## Auto Slug and Timestamps with Doctrine Extensions

- We want to:
  - auto-generate the `slug` from the `name`
  - auto-add `createdAt` when a new entity is created
  - auto-update `updatedAt` when an entity is updated
- `composer require stof/doctrine-extensions-bundle`
  - This is a non-official Flex recipe so we need to choose `y` to allow it
  - We need to enable `sluggable` and `timestampable` in `config/packages/stof_doctrine_extensions.yaml`
    ```yaml
    orm:
        default:
            timestampable: true
            sluggable: true
    ```
  - You can have multiple database connections - `default` is the default
- `Starship`
  - `$createdAt`: `#[Timestampable(on="create")]`
  - `$updatedAt`: `#[Timestampable(on="update")]`
  - `$slug`: `#[Slug(fields=["name"])]`
- `symfony console doctrine:fixtures:load`
- `symfony console doctrine:query:sql 'SELECT slug,created_at,updated_at FROM starship'`
  - `slug` is auto-generated, note if we have duplicate names, a -1, -2, etc. is added to keep unique
  - `created_at` and `updated_at` are auto-filled

## Auto-inject Entities into your Controller

- `StarshipController::show()`
  - Replace constructor args with `Starship $ship`
  - Remove `$ship = $repository->find($id);` and not found logic
- Refresh homepage, and click a ship link
  - It works!
  - Try a non-existent ship
    - 404 - Nice!
  - But how does this work?
- A Controller Value Resolver
  - There is a request one - how Request objects are injected
  - Also a "service" one - how services are injected
  - And an "entity" one
    - Looks at the type-hint - Starship
    - and the route parameter - $id
    - "finds" the entity by this id
    - and throws a 404 if not found
- `id` is a bit ugly though, let's use `slug`
- `StarshipController::show()`
  - Change `#[Route]` to `'/starship/{slug}'`
- `homepage.html.twig` and `_shipStatusAside.html.twig`
  - change `path('starship_show', {'id': ship.id})` to `path('starship_show', {'slug': ship.slug})`
- Refresh homepage, click ship link
  - Error!
  - Only works automatically for `id`
- We need to help it along with `#[MapEntity]`
- `StarshipController::show()`, `$ship` argument
  - add `#[MapEntity(mapping: ['slug' => 'slug'])]`
- We're good!

## Updating & Deleting Entities

- `starship:check-in` update `arrivedAt` by slug
- `starship:remove` to delete entities by slug

## Valid, Rich Entities

- Entities *should* be valid at all times - `new Starship()` is invalid
  - constructor to add required fields
  - remove `?` from property/method types
  - exceptions for `slug` and `id`
- Add *rich* method: `Starship::checkIn()`
