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
