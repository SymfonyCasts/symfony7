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

Ref: https://symfonycasts.com/screencast/doctrine-relations/foundry-factory-relation
Note: This section is rocky. The original was too long & in the weeds. I've
shortened it, but I think some of the proposed code and results are incorrect.

- We have a few parts and a few ships, but let's create a lot
- This is a perfect job for Foundry
- remove manually created fixtures & replace with Foundry
- create 100 parts
- try the fixtures: it errors: `starship_id` cannot be null
- in `getDefaults()`, add a `starship` key set to `Starship::random()`
- try the fixtures again
- no error, check the database:

```
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
```

- Things are looking good! Each part is related to a different ship
- `getDefaults()` is called for each part,
    so each part is related to a different, random ship
- This includes ships that have any status, including "waiting"
  - But in reality, we only track parts for ships that are "completed" so
      we know how much to charge the customer. We're not Ferengi, but we're
      not a charity either!
  - Change `starship` to `Starship::createOne(['status' => StarshipStatusEnum::STATUS_COMPLETED])`
  - try the fixtures again and check the database
  - now, all parts are related to completed ships
  - but... we have 123 ships! What the Ferengi?
    - 23 from the original fixtures and 100 from the parts:
    - `getDefaults()` is called for each part, so it's creating a new ship
        for each part... 
    - fix this by setting the `starship` key to a factory that sets the status
        to `STATUS_COMPLETED`.
    - Explain that factories are like "recipes" for creating objects
      and explain why setting a relationship to a factory is a best practice

## Fetching Parts for a Ship

ref: https://symfonycasts.com/screencast/doctrine-relations/fetching-relations
- On the starship show page, let's show the parts for that ship
- In `show()` action, autowire `StarshipPartRepository` & use
    `findBy()` to get the parts for the ship
    - the interesting part is that we're not passing an id
      for the ship: we're passing the `Starship` object
- dd() the parts to see that it works
- use `$ship->getParts()` and dd() to see that it works
- Refresh: instead of an array of `StarshipPart` objects, we see
    a `PersistentCollection` object that looks empty!
- Explain this a bit:
- https://symfonycasts.com/screencast/doctrine-relations/fetching-relations#persistentcollection-amp-arraycollection
- loop over the parts and *then* `dump()` each part
- refresh and see the parts in the WDT
- 2 queries: one to get the ship and then, a moment later, when 
    we `foreach` over the parts, Doctrine automatically queries
    for the parts for that ship
- Amazing!

## Rendering Parts in the Template

Ref: https://symfonycasts.com/screencast/doctrine-relations/rendering

- Pass a `parts` variable to the template, loop over it and render
    the parts
- But even this is too much work!
- Update template to loop over `ship.parts`
- It works! 
- This calls the `getParts()` method on the `Starship` object
    and Doctrine queries for the parts, just like before
- Render the number of parts with `{{ ship.parts|length }}`
   - 
- In WDT, still 2 queries (or 3 due to the count?), but the second/third occurs
    in the template

## Owning and Inverse Sides

- Replicate https://symfonycasts.com/screencast/doctrine-relations/owning-vs-inverse
- Important to understand, but need to avoid getting too wordy

## Relation orderBy and fetch

Ref: https://symfonycasts.com/screencast/doctrine-relations/orderby-extra-lazy

- When we call `getParts()`, we're not guaranteed the order
- How can we order the parts, like by `name`?
- Add `orderBy` to the `ORM\OneToMany` attribute in `Starship`
- Refresh the page: the parts are ordered by name!
- Check the query in the profiler: it's ordering by name!
- but, there are a lot of queries: one for the ships and then
    one to find the parts for each ship
  - This is called the "N+1" problem, which we'll tackle later.
    it's a minor performance issue thanks to the lazy loading:
    the fact that Doctrine doesn't load the parts until we ask for them
- The problem here is even crazier, however: we're querying for
    the parts for each ship... *just to count them*!
- Again, this is a minor performance issue... unless you have
    a lot of parts
- To fix this issue, we can use `fetch="EXTRA_LAZY"` in the `ORM\OneToMany`
    attribute in `Starship`
- Refresh the page: we have the same number of queries, but
    the second query is now a `SELECT COUNT(*)` query. Much more efficient!
- Should we always use `fetch="EXTRA_LAZY"`? First, this is a small
    performance optimization that I wouldn't worry about unless a ship can have
    a lot of parts
  - Second, there are som cases where this can cause an extra query.
  - Again, this is a minor performance issue, but that's why it's not the default

## Filtering a Relation

Ref: https://symfonycasts.com/screencast/doctrine-relations/collection-criteria
- `$ship->getParts()` returns all parts, but what if we only want
    the parts that cost more than 100 credits?
- We could do a fresh query for all parts that cost more than 100
    credits *and* belong to this ship
- But lame! I still want to use `$ship->getParts()`, it's so easy!
- Add a new method to `Starship` called `getExpensiveParts()`
- We could loop over the parts and filter only for the expensive
    ones, but that's not efficient: we'd be querying for *all* of
    this ship's parts, only to throw most of them away
- Instead, we can use a `Criteria` object to filter the parts
- (use `Criteria` to filter the parts)
- Refresh the page: only the expensive parts are shown
- and the query in the profiler is still efficient: it's only
    querying for the expensive parts. Amazing!
- The `Criteria` object is powerful, though, imo, it's also a bit
    cryptic, I admit
- I like to organize my query logic in a repository method... but
    now we have some query logic in the entity. Is that bad?
    - Not necessarily, but for organization, we can have the best
        of both worlds: by moving the `Criteria` logic to a repository
- In `StarshipPartRepository`, add a `createExpensiveCriteria()`
    but make it static
- Move the `Criteria` logic to this method & return
- Why static? First, because we're not using `$this` in the method,
  so it *can* be static. Second, because we're going to use this
  method from the `Starship` entity... and you can't autowire
  services into entities
- use method in `getExpensiveParts()`
- Also create a `getExpensiveParts(int $limit = 10)` method in the repository
  - But, we're not going to use this method: it's just to show
    that you *can* combine `Criteria` with a query builder

## Most Expensive Parts
- ref: https://symfonycasts.com/screencast/doctrine-relations/popular-answers
- This chapter sets up the JOIN later and shows the
    `include()` function to reuse templates (as this hasn't been shown
     yet in the series)
- https://symfonycasts.com/screencast/doctrine-relations/string-component
    I think is out of place here.
- Maybe a bonus course called "Extra Goodies" that shows a bunch of
    random, but useful, things like this
- Maybe also bonus deploy course (people often ask about this)

## JOINing Relations
ref: https://symfonycasts.com/screencast/doctrine-relations/join-n-plus-one
- talk about N+1 problem
- Show it on the most expensive parts page
- Add the JOIN: talk about how you JOIN on the `starship` property,
    not the `starship_id` column: Doctrine takes care of the details
- But we have the same number of queries! Why?
- 2 reasons to JOIN:
    - 1) to avoid the N+1 problem
    - 2) to do a WHERE or ORDER BY on the joined table
- we'll talk about the second reason soon
- For the N+1 problem, we need select the data
- Use `addSelect()`.
- Refresh the page: only 2 queries! The JOIN is working!

## Search, the Request Object & OR Query Logic

- Replicate: https://symfonycasts.com/screencast/doctrine-relations/search-form
- A bit unrelated to relations, but important topics that we need
    to cover
- ...

## The 4 (2?) Types of Relations
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/relationship-types
- Talk about how `ManyToOne` and `OneToMany` are the same
    relation type, and even `OneToOne`, which I don't use
    often, is the same relation type as `ManyToOne` with
    a restriction that limits to only one related object
- So, there are really only 2 relation types: `ManyToOne` and `ManyToMany`
- Space repair is dangerous work in a vacuum environment: 
    perfect for droids!
- In fact we have an army of droids where each droid is assigned
    to *multiple* ships and each ship has *multiple* droids
- That's a `ManyToMany` relation
- Use `bin/console make:entity` to create a `Droid` entity
- Properties: `name` and `primaryFunction`
- `make:fatory` for `Droid` and add some fun droids:
    - `R2-D2` (astromech)
    - `C-3PO` (protocol)
    - `BB-8` (astromech)
    - `IG-88` (assassin)
    - `IHOP-123` (pancake chef)
- `make:migration` and `doctrine:migrations:migrate`
- Look at the migration file: it created a new table `droid`
    delightfully boring

## ManyToMany: Droids to Ships

- Ok! We have ships and droids! But how do we connect them?
- Each `Starship` should have many `Droid`s and each `Droid`
    should belong to many `Starship`s
- Don't think about the database: think about the objects:
    we want a new droids property on `Starship`
- Use `make:entity` to update `Starship` and add a `droids`
    property
- Choose relation type. Then, yup! This smells like a `ManyToMany`
    relationship. Choose that
- Say yes to mapping other side: optional, but `$droid->getShips()`
    sounds handy.
- Go check out the changes in the entities
- In `Starship`, a new property was added: `droids` with `ORM\ManyToMany`,
    this is a collection of `Droid` objects
- the collection initialized in the constructor was generated
    for us, along with `getDroids()`, `addDroid()` and `removeDroid()` methods
    - If you're thinking "this is just like `OneToMany`", you're right!
- Now check out the `Droid` entity: it has a new property `ships`
    with `ORM\ManyToMany` and the same methods
- We're all set! Generate the migration... and open it up
- Woha! It created a new table `starship_droid` with 2 columns:
    `starship_id` and `droid_id`
- Again, in PHP, we think of objects: a `Starship` object has
    many `Droid` objects and a `Droid` object has many `Starship` objects
- Doctrine takes care of the boring details of saving this relationship
- Run the migration

## Adding Droids to Ships in the ManyToMany Relation
- In `AppFixtures`, create a `Starship` with `createOne()` and 3
    droids manually (persist and flush)
- The question now is: how do we assign this droid to this ship...
    cause the crew is hungry for pancakes!
- `$ship->addDroid($droid)` (do it for just 2 of the droids)
- Reload the fixtures
- Check the database: the `droid` table has 3 rows: no surprise
- But the `starship_droid` table has 2 rows: the 2 droids are
    related to the ship. Incredible!
- Doctrine entirely handles inserting & deleting rows in this table
- If we saved all of this and then called `$droid->removeShip($ship)`,
    Doctrine would delete the row in the `starship_droid` table
    Magical!
- Owning vs inverse in a `ManyToMany`
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/many-to-many-saving#owning-vs-inverse-on-a-manytomany
    - Mostly you don't need to think about this)
    - And so maybe we shorten this section

## Foundry Proxy Objects
- https://symfonycasts.com/screencast/doctrine-relations/many-to-many-saving#foundry-proxy-objects
- Still relevant?

## ManyToMany in Foundry
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/many-to-many-factory

## Rendering ManyToMany Relations
- I'm happy to report that *using* a `ManyToMany` relation is
    exactly the same as using a `OneToMany` relation
- On the starship show page, loop over `ship.droids` and render
    the droid names
- Done!
- `$ship->getDroids()` is a collection of `Droid` objects

## Join Across ManyToMany Relations
- ref: https://symfonycasts.com/screencast/doctrine-relations/many-to-many-joins#joining-in-a-query-with-a-manytomany
- We have a lot of queries on the starship show page
- It's our ol' buddy the N+1 problem: that friend that keeps
    hanging around well after the party is over
- Is this a real performance issue? Maybe, maybe not
- But we know how to fix it: JOINs!
- Add the JOIN to the query in the repository
  - What's interesting is that we don't think about the database 
    or the `starship_droid` table: we JOIN on the `droids` property
    and it's all handled for us
  - Include the `addSelect()` to select the data
- Refresh the page: only 2 queries! The JOIN is working!
- And check out the query in the profiler: it's a JOIN query
  across the `starship_droid` table

## ManyToMany with Extra Columns

- ManyToMany relations are unique in that Doctrine entirely
    manages the join table: there is no entity for it
- A side effect of this is that you can't add extra columns
    to the join table
- But what if we want to track the date that a droid was
    assigned to a ship?
- In this case, we need to create a new entity: `DroidAssignment`
  and handle things a bit more manually
- Undo the `ManyToMany` relation
- This time, use `bin/console make:entity` to create an entity for
    the join table: `DroidAssignment`
- Add a `dateAssigned` property, then 2 more:
- `droid` with `ManyToOne` to `Droid`
- `ship` with `ManyToOne` to `Starship`
- Say yes to generating the other side for `droid` as
- `$droid->getDroidAssignments()` might be useful
- Say no to generating the other side for `ship`... mostly so
    we can experience how the inverse side is optional
- Check out the changes in the entities
  - Beautifully boring: `DroidAssignment` has 2 `ManyToOne` relations
  - Note: no changes to `Starship` as we decided not to map the
    other side of the relation
- In the `DroidAssignment` entity, add a `__construct()` method
    to set the `dateAssigned` property to `new \DateTimeImmutable()`
- generate the migration and open it up
- It looks like a lot of changes at first, but look closer:
- the `droid_assignment` table is already there from the
    `ManyToMany` relation: the migration is just adding an autoincrement
    `id` column and the `date_assigned` column
- This is really the same relation as before, but we've taken
    control of the join table to add an extra column
    - We made a bunch of changes in PHP, but the database, essentially,
      stayed the same
- Run the migration
- It fails!

## Handling a Failed Migration
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/broken-migration

## DroidAssignment Fixtures
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/question-tag-fixtures

## Doing Crazy Things with Foundry & Relations
- replicate: https://symfonycasts.com/screencast/doctrine-relations/foundry-phd

## JOINing across multiple relations
- replicate: https://symfonycasts.com/screencast/doctrine-relations/foundry-phd

## Pagination
- ref: https://symfonycasts.com/screencast/doctrine-relations/pagination
- Include this? It's unrelated to relations
- Or do we cover this in the "extras" course?
- Or make a small pagination course and mention it here?
