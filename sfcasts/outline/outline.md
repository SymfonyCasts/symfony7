# Doctrine Relations: Outline

## Project Setup

- Remind of the project & 1 entity: `Starship`
- Tell them to download the course code & set up
- 1 entity right now (zzzz boring): we can't track the parts of a starship

## New Entity: StarshipPart

- (record this but move through it quickly)
- so let's add a new entity: `StarshipPart`
- use `bin/console make:entity` to create it
  - add `name`, `price` & `notes` properties
- `make:migration` and `doctrine:migrations:migrate`
- `make:factory` for `StarshipPart`
- add some fun parts probably by having the user paste in
    some pre-made code
  - ideas:
      - `warp core` (looks cool AND zoom)
      - `shield generator`  (in case you run into any Borg)
      - `captain's chair` (just slightly more comfortable than the others)
      - `fuzzy dice` (obviously)
      - `photon torpedoes` (for when the fuzzy dice don't work)
      - `holodeck` (parental controls? No way!)
      - `Tactical Whoopee Cushion Array` (can't beat them? Embarrass them!)
      - `Temporal Seat Warmers`(warm your seat before you sit down)
      - `Food Replicator` (Earl Grey, hot)
      - `Self-Destruct Button Cover` (for when you have a cat)
      - `Redshirt Dispenser` (Instantly replenishes expendable crew members.)
- reload the fixtures

## ManyToOne: StarshipPart to Starship
- Ok! We have ships and parts! But how do we connect them?
- Each `StarshipPart` should belong to a `Starship`
- to create a relation, we can use `bin/console make:entity` again
- should we call the new property `starshipId`?
- No! This is where Doctrine shines: we don't think about ids
  - instead, we think about objects, like a `Starship` object
  is related to a `StarshipPart` object
- edit the `StarshipPart` entity: give it a `starship` property
- this will be a relation and the command walks us through the 
    relationship types
  - it seems like a `ManyToOne` relationship
  - that makes sense: many parts can belong to one ship
  - nullable? No! Every part should belong to a ship
  - map the other side? This is super interesting. This is
  optional, but will allow us to say `$starship->getParts()`
  to get all the parts for a ship
  - That sounds cool! Let's do it!
- I committed before recording, so run `git status` to see
    the changes
- *both* entities were updated
- In `StarshipPart`, a new property was added: `starship`
- But instead of `ORM\Column`, we have `ORM\ManyToOne`
- It also added a getter and setter for `starship`
- In `Starship`, it added a `parts` property with `ORM\OneToMany`
- It also added a `getParts()` method
- But instead of a `setParts()` method, it added `addPart()`
    and `removePart()` methods: these are just more convenient,
    especially when working with forms or the serializer
- Up in the constructor, it initialized the `parts` property
    to a new `ArrayCollection`
- You need this, but it's a minor detail: it looks and acts like an array:
    you can even `foreach` over it
- mention how `ManyToOne` and `OneToMany` are actually the same *one* relation
    type: just seen from different sides
    - If `StarshipPart` belongs to one `Starship`, then `Starship` has many `StarshipPart`s
- Since `make:entity` added new properties `Starship`, we need to
    run `make:migration` and `doctrine:migrations:migrate`
- Check out the migration file
  - Woha! It added a new column `starship_id` to the `starship_part` table
- Doctrine is smart: we added a `starship` property, but it
    knows that the column should be `starship_id`
- So how do we relate a part to a ship?

## Relating Parts to a Ship
- Ref: https://symfonycasts.com/screencast/doctrine-relations/saving-relations
- Create a `Starship` and `StarshipPart` in `AppFixtures`
- They're not related yet, but try loading the fixtures
- Error!
> `starship_id` cannot be null on the `starship_part` table
- we made the ship required in `make:entity`: you can see that
    in the `nullable=false` on `JoinColumn` above the `starship`
    property
- How *do* we say that this part belongs to this ship?
- Easy! `$part->setStarship($ship)`
- Notice that we're not setting the `starship_id` property
    or even passing an id: we're setting the `Starship` object
- This is the magic of Doctrine: it knows how to save this
    relationship: it will first save the `Starship` object
    and then use its id to set the `starship_id` column on the
    `starship_part` table
- Let's prove it! Reload the fixtures

```
symfony console doctrine:query:sql 'SELECT * FROM answer'
```

- The `starship_id` column is set to 12345. Amazing!
- Let's look up that ship in the database

```
symfony console doctrine:query:sql 'SELECT * FROM starship WHERE id = 12345'
```

- Big takeaway: when you're working with relations, you're
    working with objects, not ids
- Doctrine handles the boring details of saving the relationship

## Creating and Relating many ships and parts via Foundry

... TODO by Ryan
