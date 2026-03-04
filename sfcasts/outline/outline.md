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
