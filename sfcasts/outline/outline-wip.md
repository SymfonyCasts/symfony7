# Doctrine Relations: Outline

## Project Setup

- Tell them to download the course code & set up
- Remind of the project & 1 entity: `Starship`
- 1 entity right now (zzzz boring): we can't track the parts of a starship

## New Entity: StarshipPart

- (record this but move through it quickly)
- so let's add a new entity: `StarshipPart`
- use `symfony console make:entity` to create it
  - broadcast? no
  - add `name`, `price` & `notes` (nullable=yes) properties

- `make:migration` 
- `doctrine:migrations:migrate`
- add TimestampableTrait
- `make:migration` again
- `doctrine:migrations:migrate`
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
  - name it `parts`
  - orphanRemoval => no, talk about this later
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
    run `make:migration`
- Check out the migration file
  - Woha! It added a new column `starship_id` to the `starship_part` table
- Doctrine is smart: we added a `starship` property, but it
    knows that the column should be `starship_id`
- `doctrine:migrations:migrate`
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
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
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
- in `getDefaults()`, add a `starship` key set to `Starship::randomOrCreate()`
  - We want to make `getDefaults()` set all the required fields
  - `randomOrCreate()` is a method that will return a random ship
    that matches our criteria or create a new one
  - On the homepage, we only show waiting or in-progress ships,
    so let's relate each part to one of those
- try the fixtures again
- no error, check the database:

```
symfony console doctrine:query:sql 'SELECT * FROM starship_part'
```

- 100 parts and each is related to a random, waiting/in-progress ship
- Explicitly set a ship in the fixtures
- try the fixtures again & query for the parts
- They're all related to the same ship! Yay!
- and we have the correct 23 ships
- temporarily change to `createOne()`
- 123 ships! What the Ferengi?
- `getDefaults()` is called for each part, so it's creating a new ship
    for each part... even though we won't use that ship
- Change to `::new()`
- Back to 23 ships
- Explain that factories are like "recipes" for creating objects
  and explain why setting a relationship to a factory is a best practice
- Yes, you can pass a factory to a relationship in Foundry
    and it will delay creating the object until/if it's needed
- Remove the ship override so each part is, once again, related
    to a random ship
- And change back to `randomOrCreate()`
  - In that case we could make sure the ship has a specific status
    like "in progress" so we can track how much to charge the customer
  - Change `starship` to `Starship::new(['status' => StarshipStatusEnum::STATUS_IN_PROGRESS])`

## Fetching Parts for a Ship

ref: https://symfonycasts.com/screencast/doctrine-relations/fetching-relations
- On the starship show page, let's show the parts for that ship
- In `show()` action, autowire `StarshipPartRepository` & use
    `findBy()` to get the parts for the ship
    - the interesting part is that we're not passing an id
      for the ship: we're passing the `Starship` object
- dd() the parts to see that it works
  - choose an in progress ship (only those have parts per our fixtures)
- use `$ship->getParts()` and dd() to see that it works
- Refresh: instead of an array of `StarshipPart` objects, we see
    a `PersistentCollection` object that looks empty!
- The `parts` property will never be a true array
  - It'll either be an `ArrayCollection` or a `PersistentCollection`
  - But don't worry: both act like arrays, including being able
    to `foreach` over them
- But why is it empty? Because Doctrine is awesome: it doesn't
    query for the parts until we ask for them
- loop over the parts and *then* `dump()` each part
- refresh and see the parts in the WDT
- 2 queries: one to get the ship and then, a moment later, when 
    we `foreach` over the parts, Doctrine automatically queries
    for the parts for that ship
- Amazing!

## Rendering Parts in the Template

Ref: https://symfonycasts.com/screencast/doctrine-relations/rendering

- Undo the `foreach` and `dump()`. Instead, pass a `parts` variable to the
- template, loop over it and render the parts
- But even this is too much work!
- Update template to loop over `ship.parts`
- It works! 
- This calls the `getParts()` method on the `Starship` object
    and Doctrine queries for the parts, just like before
- Celebrate by removing the `parts` variable
- Render the number of parts with `{{ ship.parts|length }}`
- In WDT, still 2 queries, but the second occurs
    in the template
- No extra queries are made when we call `length` on the collection

## Owning and Inverse Sides

- Ref: https://symfonycasts.com/screencast/doctrine-relations/owning-inverse
- We know that every relation can be seen from 2 sides
- `Starship` has many `StarshipPart`s, but `StarshipPart` belongs
    to one `Starship`: seen from the `Starship` side, this is a `OneToMany`
    But seen from the `StarshipPart` side, this is a `ManyToOne`
- One side is known as the "owning" side and the other the "inverse" side.
- This "mostly" doesn't matter, but give me 3 minutes to explain
    so it doesn't bite you later
- Bonus: you can entertain your friends at parties with small
    talk about owning and inverse sides. You're welcome!
- First, which side is the owning side? 
- For `ManyToOne`, it's easy: the `ManyToOne` side is always the
    owning side: it's the side that has the foreign key in the database:
    `starship_part` has a `starship_id` column, so it's the owning side
- Second, why do we care?
- 2 reasons:
    - 1) The `JoinColumn` attribute, which is optional, can only be used on the owning side
          - And that makes sense: the `JoinColumn` attribute controls the foreign key column
    - 2) You can only set the relationship from the owning side
          - Let me show you
          - In `AppFixtures`, create a `Starship` via the factory, cause I'm lazy
             and proud of it
          - Then, create 2 `StarshipPart` objects by hand to keep things simple
          - Persist and flush
          - These are not related yet, but try loading the fixtures
          - Error! The `starship_id` column cannot be null
          - Let's relate a part to a ship, but instead of `$part->setStarship($ship)`,
             which sets the *owning* side, try setting the inverse side `$ship->addPart($part)`
          - If you think about it, these are saying the same thing
          - Let's see if it saves
          - Reload the fixtures
          - Query for the parts: they're related to the ship!
          - But didn't I say that you can only set the relationship from the owning side?
          - Yes! If you *only* set the relationship from the inverse side, Doctrine
            will *not* save the relationship to the database
          - So why did it work? Am I lying to you for the sake of learning?
          - Open the `Starship` entity and find the `addPart()` method
          - It's calling `$part->setStarship($this)`
          - When we set the inverse side, our own code - generated by the `make:entity`
            command - sets the owning side
    - Takeaway:
      - Every relation has an owning side and an inverse side
      - Though the inverse side is optional: `make:entity` asked us if we wanted
           the inverse side, which gives us the convenient `$ship->getParts()` method
      - You can technically only set the relationship from the owning side,
          but in reality, you can set it from either side thanks to our own code
      - So go wow your friends with your new knowledge... then forget about it:
          in practice, it's not important
    - Remove the temporary code... and reload the fixtures

## orphanRemoval: Deleting StarshipPart Objects when removed from a Ship

- If we have a `StarshipPart` object & need to delete it: easy peasy:
    just call `$em->remove($part)`.
- But sometimes we may want to say: "remove this part from the ship"
- Easy again: `$ship->removePart($part)`.
- But should this set the `starship_id` column to `null` or delete
    the `StarshipPart` object?
- The answer depends on the situation. In our app, a `StarshipPart`
    should always belong to a ship: 
- In the fixtures, create a `StarshipPart` object related to the ship and dump it
  - Reload the fixtures
  - It is saved & related to the ship.
    - we see the proxy objects
    - Normally, no problem: but I want to show you something
      and it's easier to see if we remove any magic the proxies add
  - Use `_real()` on both the ship and part
  - fixtures work fine
- `$ship->removePart($starshipPart)`
- error!
> Not null violation: 7 ERROR:  null value in column "starship_id" of relatio
  n "starship_part" violates not-null constraint
- That makes sense: `removePart()` sets the `starship` to `null`,
  but we said this is not allowed.
- What's the solution? In some cases, you may want to allow the part
    to be removed from the ship: to be "orphaned"
  - to allow this, change the `nullable` attribute on the `JoinColumn`
    in the `StarshipPart` entity to `true`, then run `make:migration`
  - alternatively, if a part should always belong to a ship, you can
    and suddenly one is removed from a ship, you may want to delete
    the part
  - to do this, add `orphanRemoval=true` to the `OneToMany` attribute
    in the `Starship` entity
  - reload the fixtures

## Relation orderBy and fetch

Ref: https://symfonycasts.com/screencast/doctrine-relations/orderby-extra-lazy

- When we call `getParts()`, we're not guaranteed the order
- How can we order the parts, like by `name`?
- Add `ORM\OrderBy` to the `parts` property in `Starship`
- Refresh the page: the parts are ordered by name!
- Check the query in the profiler: it's ordering by name!
- In the `homepage.html.twig`, print the part count for each ship
- Go to the homepage & check the queries:
    - 4 queries for the ships: they look odd due to the pagination
    - then 1 query for the parts for each ship
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
- Use it on the starship show page
- Use `filter()` to filter the parts
- It works! But it's not efficient: it's querying for *all* parts
    and then filtering them in PHP
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

## Parts List Page
- make:controller for `PartController`
- no to test
- Check out the new controller
- Update the URL to `/parts` then go there and tweak the route name
- Add a link to the parts list page in the header
- go to the homepage and click the link
- Ugly, but functional!
- query for all parts in the controller & pass to the template
- Paste in the template code to render the parts
- Show the cycle function
- Cool!
- Render the assigned ship name

## JOINing Relations
ref: https://symfonycasts.com/screencast/doctrine-relations/join-n-plus-one
- render assigned ship name in the parts list page
- Watch the queries: 1 query for the parts, then 1 query for each ship
- Add the JOIN: talk about how you JOIN on the `starship` property,
    not the `starship_id` column: Doctrine takes care of the details
- But we have the same number of queries! Why?
- 2 reasons to JOIN:
    - 1) to avoid the N+1 problem
    - 2) to do a WHERE or ORDER BY on the joined table
- we'll talk about the second reason soon
- For the N+1 problem, we need select the data
- Create repo method for parts, ordered by price
- Refresh to see the new order
- Add the JOIN to the query
  - we join on the property
  - Doctrine handles the details!
- Same number of queries: why?
- We're joining, but not selecting the data
- Use `addSelect()`
  - Select the alias of the entire class
- Refresh the page: only 6 queries down to 1! The JOIN is working!

## Search, the Request Object & OR Query Logic

- Replicate: https://symfonycasts.com/screencast/doctrine-relations/search-form
- A bit unrelated to relations, but important topics that we need
    to cover
- Add input to the parts list page to search for parts by name
- Surround the input with a form tag
- Show the `?query=` in the URL
- How do we read that?
- That's info from the request
- Add a `Request` argument to the controller method
- Use `$request->query->get('query')` to read the query string
- Enhance repo method to search by name
  - it's case-sensitive
  - Add LOWER
- add `value=""` to input
- Change query to OR

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
- `make:migration`, migrate
- `make:fatory` for `Droid` and add some fun droids:
    - `R2-D2` (astromech)
    - `C-3PO` (protocol)
    - `BB-8` (astromech)
    - `IG-88` (assassin)
    - `IHOP-123` (pancake chef)
- reload fixtures
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
> symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
- But the `starship_droid` table has 2 rows: the 2 droids are
    related to the ship. Incredible!
- Doctrine entirely handles inserting & deleting rows in this table
- If we saved all of this and then called `$droid->removeShip($ship)`,
    Doctrine would delete the row in the `starship_droid` table
    Magical!
- Owning vs inverse in a `ManyToMany`
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/many-to-many-saving#owning-vs-inverse-on-a-manytomany
    - Mostly you don't need to think about this
    - And so maybe we shorten this section

## Rendering ManyToMany Relations
- I'm happy to report that *using* a `ManyToMany` relation is
    exactly the same as using a `OneToMany` relation
- On the starship show page, loop over `ship.droids` and render
    the droid names
- Done!
- `$ship->getDroids()` is a collection of `Droid` objects

## ManyToMany in Foundry
- it's really the same as `OneToMany`
- create 100 droids
- set 'droids' to randomRange(1, 5) in overrides
  - Foundry is smart: it will detect the `addDroid()` method
    and use it, ensuring that both sides of the relation are set
- try the fixtures: no errors
- query for the droids: 100
> symfony console doctrine:query:sql 'SELECT * FROM droid'
- query the join table:
> symfony console doctrine:query:sql 'SELECT * FROM starship_droid'
- looks *almost* correct: but the ships are related to the
    same set of 1-5 droids Why?
- `randomRange()` is called only once, so it's using the same
    1-5 random droids for each ship
- Makes sense: but how can we pass a different value to each ship?
- Pass a closure, instead of an array
 - This closure will be called for each ship
- Much more better!
- btw, `getDefaults()` is called for each object,
  - Hence, the random status for each ship
  - So you can *almost* use `randomRange()` but 
      you're not guaranteed that there will be any droids
  - More importantly, the best practice is to only use 
      `getDefaults()` to set the required fields
- Replicate: https://symfonycasts.com/screencast/doctrine-relations/many-to-many-factory

## Join Across ManyToMany Relations
- On the homepage could we order the ships by the number of droids?
- Rename repo method

- That would require a join across the `starship_droid` table
- add the join:
    - Notice again that we're joining on the property, not the
      column (or in this case, the join table)
    - But the query *does* join to the join table
- Now we can order it, using `droid` as the alias
- Add the order by
- Refresh the page: the ships are ordered by the number of droids!
- So nothing special with joining across a `ManyToMany` relation
    - It's just a join like any other: you join on the property,
      Doctrine takes care of the details

## ManyToMany with Extra Columns

- ManyToMany relations are unique in that Doctrine entirely
    manages the join table: there is no entity for it
- A side effect of this is that you can't add extra columns
    to the join table
- But what if we want to track the date that a droid was
    assigned to a ship?
- In this case, we need to create a new entity: `StarshipDroid`
  and handle things a bit more manually
  - This will act as our join table, but we'll need to manage
    it a bit more manually
- Undo the `ManyToMany` relation from `Starship` and `Droid`
    - just the property & constructor code
> symfony console doctrine:schema:update --dump-sql
- This would drop the `starship_droid` table, undoing the
    relation in the database
  - but hold on the migration for now
- Let's create a join entity `bin/console make:entity` to create an entity for
    the join table: `StarshipDroid`
- `DroidAssignment` is a better name, but I want you to see
    that we're recreating the exact same relation in the database
- Add a `assignedAt` property, then 2 more:
- `droid` with `ManyToOne` to `Droid`
    - Say yes to generating the other side for `droid` as
    - Not required, but will give us max flexibility
- `starship` with `ManyToOne` to `Starship`
    - Say yes to generating the other side for `starship`
    - Not required, but will give us max flexibility
- yes to mapping inverse of `starship` property
- Check out the changes in the entities
  - Beautifully boring: `StarshipDroid` has 2 `ManyToOne` relations
  - `Starship` and `Droid` have a `OneToMany` relation to `StarshipDroid`
- In the `StarshipDroid` entity, add a `__construct()` method
    to set the `dateAssigned` property to `new \DateTimeImmutable()`
- generate the migration and open it up
- It looks like a lot of changes at first, but look closer:
- the `starship_droid` table is already there from the
    `ManyToMany` relation: the migration is just adding an autoincrement
    `id` column and the `date_assigned` column
- This is really the same relation as before, but we've taken
    control of the join table to add an extra column
    - We made a bunch of changes in PHP, but the database, essentially,
      stayed the same
- Run the migration
- It fails!
> column "assigned_at" of relation "starship_droid" contains
> null values
- We need to set a default value for the `assignedAt` column
- rerun the migration

## StarshipDroid Fixtures
- try the fixtures: it errors:
> Undefined property: App\Entity\Starship::$droids
> from `Starship::addDroid()` method
- to temporarily fix, remove `droids` key in fixtures
- Update fixtures to show `addDroid()` method not working
- Update fixtures to use the `StarshipDroid` entity
- fixtures work! And we see it in the database
- homepage is busted:
> [Semantical Error] line 0, col 55 near 'droids WHERE': Error: Class App\Entity\Starship has no association named droids
- that comes from the query in the `StarshipRepository`
    - it's trying to join to the `droids` property, but that
      property no longer exists
- fix join
- try the homepage:
> Warning: Undefined property: App\Entity\Starship::$droids 

## Cleverly Hiding the Join Entity

- refresh show page: it's broken
> Warning: Undefined property: App\Entity\Starship::$droids
- `ship.droids` calls the `getDroids()` method on the `Starship`
    object, and that references the `droids` property
- Hold up: things are getting confusing: I *still* want to be able
    to say `$ship->getDroids()` and have it return a collection of `Droid`
    objects. Most of the time, this `StarshipDroid` entity should be
    hidden
- Can we get `getDroids()` to work again? We can!
- Use `->map()` to change `$this->starshipDroids` to a collection of `Droid`
    objects

++ b/src/Entity/Starship.php
@@ -202,7 +202,7 @@ class Starship
      */
     public function getDroids(): Collection
     {
-        return $this->droids;
+        return $this->starshipDroids->map(fn (StarshipDroid $starshipDroid) => $starshipDroid->getDroid());
     }

- if that works, use it in getDroidNames()

++ b/src/Entity/Starship.php
@@ -223,7 +223,7 @@ class Starship

     public function getDroidNames(): string
     {
-        return implode(', ', $this->droids->map(fn(Droid $droid) => $droid->getName())->toArray());
+        return implode(', ', $this->getDroids()->map(fn(Droid $droid) => $droid->getName())->toArray());
     }

     /**


  - The show page works again! Amaze!
- So fetching the droids for a ship is right back to being easy
- Let's repeat in `Droid::getStarships()`, though we don't use it

b/src/Entity/Droid.php
@@ -66,7 +66,7 @@ class Droid
      */
     public function getStarships(): Collection
     {
-        return $this->starships;
+        return $this->starshipDroids->map(fn (StarshipDroid $starshipDroid) => $starshipDroid->getStarship());
     }

     public function addStarship(Starship $starship): static

------------------------------------------

- So looping over the droids of a ship is the same as before.
    This entire change is kinda hidden from the rest of our code
    That's freakin' awesome!

## Making the adder/remover methods work again

- Needing to create a `StarshipDroid` object every time we want
    to relate a droid to a ship is a bit more work but it's also
    easy to understand. So, honestly, it's fine
- But let's turn things up to 11 and make the `addDroid()` and
    `removeDroid()` methods work again
- Change the fixtures back to use the `addDroid()` method
- Try the fixtures: it errors
> Undefined property: App\Entity\Starship::$droids
- Makes sense!
- Update `addDroid()` to add a `StarshipDroid` object
- Try the fixtures: new error:
>  new entity was found through the relationship 'App\Entity\Starship#starshipDroids' that wa
  s not configured to cascade persist operations for entity
- Explain about persisting

## cascade
- Add `cascade={"persist"}` to the `starshipDroids` property in `Starship`
- Use carefully: it makes things easier, but also automatically
    does things that you might not want
- It works!
- Put back `droids` key in fixtures
- Try the fixtures: it works!
- How? Foundry uses the `addDroid()` method to add the droids
    one-by-one.
- So cool!
- But there are some limitations: what if we want to add a droid
    to a ship and set the `assignedAt` property?
- Add argument to `addDroid()` method
- But no way to set the `assignedAt` property from Foundry

## Printing assignedAt in the Template
- loop over starship droids in the template
