# User Doctrine Relationship

On our app's homepage, we have the concept of "MyShip". It's intended to be
the current user's starship. However, at the moment, it's just a placeholder.
Open `src/Controller/MainController` and in the `index()` method, we're setting
`$myShip = $repository->findMyShip()`:

[[[ code('e5255fa649') ]]]

If we jump into this method, we can see the code is just grabbing the first ship in the database:

[[[ code('6932122c3d') ]]]

Wouldn't it be great if this was actually tied to the user?

First, back in `MainController`, set `$myShip` to `null`:

[[[ code('2e1a2bb61c') ]]]

This simulates if there is no logged-in user, or, the current user doesn't have a starship.

Bounce back to the homepage and refresh... Perfect, the "My Ship" section
just disappears when there's no ship.

## Starship Relationship

Remember, our `User` is a Doctrine entity. This means we can add normal Doctrine
relationships to it. I want the `User` to be able to have a `Starship`.

We can use the entity Maker to create this relationship! Over at your terminal,
run:

```terminal
symfony console make:entity User
```

`User` already exists, so the maker is just asking to add fields to it.

For the new property, call it `starship`. Field type? Enter `?` to see all the options.
Scroll up to "Relationships"... Ooo, let's use the wizard. Enter `relation`.

What class should this entity be related to? `Starship`.

What type of relationship is this? The first one: "Each User has one Starship" and
"Each Starship can have may Users". `ManyToOne`. Choose that.

Do we want `User.starship` to be nullable? We do, because a user doesn't have to
be assigned to a starship.

Do we want the `Starship` to be able to access all it's users? Yep!

Field name for the inverse side? `users` is good.

Done.

## Adding a Migration

Since we've updated an entity, we need to add a migration, so run:

```terminal
symfony console make:migration
```

Great, let's find that migration in our code. Here it is. Set the description to
`Add starship to user`. Down here is the SQL for updating the user table in the
database.

Back in the terminal, run the migration with:

```terminal
symfony console doctrine:migrations:migrate
```

## Updating our Fixtures

I want Jean-Luc to have the Enterprise as his starship. So, let's update our fixtures.
I'll close this migration and open `src/Story/AppStory`. Find where we're creating Picard.
We can create his starship right inside this `createOne()`.

Add `'starship' => StarshipFactory::new()` with an array inside. If you're wondering how
the `new()` method is different from `createOne()`, good eye! `createOne()` will create the
object and persist it to the database immediately. `new()` creates the factory but the
object won't be created until the parent factory's object, in this case, `UserFactory`,
is created. It's best practice to use `new()` when creating factories inline like this.

Ok, inside: `'name' => 'USS Enterprise (NCC-1701-D)'`, `'class' => 'Galaxy'`,
`'captain' => 'Jean-Luc Picard'`. The `Starship` captain field is just a string
but this could totally be another relationship to the `User`! Finally,
`'status' => StarshipStatusEnum::IN_PROGRESS`:

[[[ code('9c485d4e8e') ]]]

Great! Back in the terminal, reload the fixtures with:

```terminal
symfony console foundry:load-fixtures
```

## Fetching the Starship from the User

Now to grab "my ship" from the currently logged-in user! Back in `MainController::index()`,
set `$myShip` to `$this->getUser()?->getStarship()`:

[[[ code('721abba05a') ]]]

The `getUser()` method is another helper provided by the `AbstractController`.
It returns the currently logged-in user or `null` if there isn't one. `?->` is
the null-safe operator, so if `getUser()` returns `null`, it won't try to call
`getStarship()`. It'll just return `null`.

Now refresh the homepage. No error, so login as Picard. Email: `picard@enterprise.space`, password:
`makeitso`. Sweet! The sidebar is back, and we can see our ship, the Enterprise!

Next, we'll look at some additional ways to access the currently logged-in user in our services and
controllers.
