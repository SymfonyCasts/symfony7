# Doctrine Inheritance: Classes in the Database

## Mapped Superclass
- With Doctrine ORM, DB tables are mapped to classes
- What about inheritance in the database? Can this work? Yep!
- First type of inheritance: Mapped Superclasses
  - Similar to an "abstract class" in OOP
  - Cannot be instantiated
  - Cannot be queried
  - Used to share common properties and mappings between entities
- Starship Entity
  - Want different "types" of starships
    - not just a type field, but a sub-object with its own properties
    - But we don't want to repeat the common properties of all starships in each type
- New entities:
  - `symfony console make:entity Scout`
    - `sensorRange` (integer)
  - `symfony console make:entity Freighter`
    - `cargoCapacity` (integer)
- In `Starship`
  - change to MappedSuperclass and abstract
- In `Scout`, extend `Starship`, remove id
- In `Freighter`, extend `Starship`, remove id
- `symfony console doctrine:schema:update --dump-sql`
  - Drops the starship table, creates 2 new tables: scout, freighter
- `symfony console foundry:load-fixtures` - error

## Inheritance Foundry Fixtures
- Show failed `symfony console foundry:load-fixtures`
- The problem is that the `StarshipFactory` is trying to create a `Starship`
- `symfony console make:factory` (all)
- Open ScoutFactory - defaults are mostly shared, so let's move logic to StarshipFactory
- In `StarshipFactory`
  - make abstract
  - add `@template T of Starship`
  - `@extends PersistentProxyObjectFactory<T>`
    - This will make phpstan happy and add autocompletion with PhpStorm
  - Remove `class()` method
- In `ScoutFactory` and `FreighterFactory`
  - add `@extends StarshipFactory<Scout>` and `@extends StarshipFactory<Freighter>`
  - adjust defaults to use `array_merge(parent::defaults(), [...])`
- In AppStory
  - replace StarshipFactory with ScoutFactory and FreighterFactory
- `symfony console foundry:load-fixtures` - works!
- Go to the homepage...
  - error - still trying to query the Starship table, but it doesn't exist anymore
- In MainController::homepage(),
  - Replace `StarshipRepository` with `ScoutRepository`
  - works... but we only see scouts, not freighters
- We'd have to inject bot repositories, query both, and merge the results
- Not ideal. Would be cool to still be able to inject the StarshipRepository
  and have it return both scouts and freighters
- We'll solve this next by using a different type of inheritance: Single Table Inheritance

## Single Table Inheritance
- With Single Table Inheritance, all classes in the hierarchy are stored in a single table
- In Starship
  - `MappedSuperclass` -> `Entity(repositoryClass: StarshipRepository::class)`
  - add `ORM\InheritanceType('SINGLE_TABLE')`
  - add `ORM\DiscriminatorColumn('ship_type', 'string')` (look into attribute)
  - `[ORM\DiscriminatorMap([
        'freighter' => Freighter::class,
        'scout' => Scout::class,
     ])]`
- `symfony console doctrine:schema:update --dump-sql`
  - drops the scout and freighter tables, creates a new starship table with all the
    fields from both entities, plus a new `ship_type` field
  - even though our scout and freighter properties are not-nullable, they need to
    nullable in the database, because they won't be filled for the other type of ship
- `symfony console foundry:load-fixtures` - works!
- `symfony console doctrine:query:sql 'select * from starship'`
  - note this command has changed to `dbal:run-sql`
- in `MainController`
  - switch back to `StarshipRepository`
- Go to homepage - 6 ships!
  - Check the profiler and view formatted query
- Some cons:
  - cannot have non-nullable fields in child entities
  - potentially a ton of empty fields in the database
- Next, let's look at the final type of Doctrine inheritance

## Class Table Inheritance
- With Class Table Inheritance, each class in the hierarchy is stored in its own table.
  - Only properties specific to that class are stored in the table for that class
- In `Starship`, change to `JOINED` inheritance type
  - That's it!
- `symfony console doctrine:schema:drop --force`
- `symfony console doctrine:schema:update --dump-sql`
  - creates a new starship table with only the common fields, and creates new scout and
    freighter tables with the specific fields, plus a foreign key to the starship table
- `symfony console foundry:load-fixtures` - still works!
- `symfony console doctrine:query:sql 'select * from starship'`
  - only returns the common fields, not the specific fields for each type of ship
- `symfony console doctrine:query:sql 'select * from scout'`
- check the app and the profiler... joins are happening behind the scenes
- Let's add another Starship type, this time, deeper in the hierarchy
- `symfony console make:entity MiningFreighter`
  - `laserPower` (integer)
- `symfony console make:factory` - all
- in `src/Entity/MiningFreighter.php`
  - extend `Freighter`, remove id
- in MiningFreighterFactory...
  - First, we need to adjust the FreighterFactory
    - remove final, add `@template T of Freighter`, add T to extends
  - Back in MiningFreighterFactory
    - `extends FreighterFactory<MiningFreighter>`
    - adjust defaults to use `array_merge(parent::defaults(), [...])`
- in AppStory, create 2 mining freighters
- `symfony console foundry:load-fixtures` - ERRROR!!!
  - "Entity 'App\Entity\MiningFreighter' has to be part of the discriminator map of 'App\Entity\Starship'..."
- This step is easy to forget...
- In `Starship`, `'mining_freighter' => MiningFreighter::class` to the discriminator map
- load fixtures again - works!
- Check the homepage - all 8 ships!

# Querying Classes
- When listing, show the ship type
- Can't access the discriminator column, here's a trick
- In `Starship`
  - Move discriminator map to `private const TYPE_MAP`
  - add `final public function getType(): string`
    - `return array_flip(self::TYPE_MAP)[static::class]`
      - important to use `static` here!
- In `homepage.html.twig`
  - Before the ship name, add `{{ ship.type }}`
  - Check the app
  - Tip: even if not multilingual, you can use the types as translation keys for full control
  - `{{ ship.type|replace({'_': ' '})|title }}`
- In StarshipRepository, add filterShips() method
  - `return $this->createQueryBuilder('s')
        ->where('s INSTANCE OF '.Scout::class)
        ->getQuery()
        ->execute()`
  - use filterShips in MainController and see results
  - Cannot use class name directly as a parameter
    - `->where('s INSTANCE OF :class')->setParameter('class', Scout::class)`
    - Instead: `->setParameter('class', $this->getEntityManager()->getClassMetadata(Scout::class))`
- Let's try `Freighter::class`
- Notice the mining freighters are included
  - this would also be the case when using the FreighterRepository too
  - You have to specifically exclude
- `->andWhere('s NOT INSTANCE OF :notclass')->setParameter(...)`
- change back to `->findAll()` in `MainController`
