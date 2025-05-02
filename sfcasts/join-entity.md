# Join Entity

Coming soon...

`ManyToMany` relationships are unique in Doctrine because it's the one
place where you have a table  in your database, in our case
`StarshipDroid`, but there is no corresponding entity in your project. 
Doctrine entirely handles that table itself, which is awesome. A side
effect of this is that we can't  add extra columns to that join table. For
example, what if we wanted to track the date that a droid  was assigned to
a ship? In the database, we'd probably add an `assignedAt` column to that
join table,  but in Doctrine we can't do that. As soon as you have a
`ManyToMany` where you need extra data on  the join table, you basically
need to back up and start handling things a bit more manually. 

What this means is that you'll actually stop using the `ManyToMany`
entirely, and instead we're  going to generate a new entity that represents
that join table. To start, undo the `ManyToMany`  relationship entirely,
but it's going to worry about the properties, not the methods. 

So in `Starship`, remove the `droids` property entirely, and over in
`Droid`, do the same thing, remove  the `Starship's ManyToMany` property,
and clear out the constructor code, which should also be done in 
`Starship`. Find your terminal and run:

```terminal
symfony console doctrine:schema:update --dump-sql
``` This is a
handy little command. It's basically going to show us if we generated a
migration right now,  what would that migration look like? It's actually
really, really cool. It's exactly what you expect.  It drops the foreign
keys, and then it drops the `StarshipDroid` table entirely. 

Before we actually make that migration, let's create the new join entity,
`StarshipDroid`, new join entity.  So let's run 

```terminal
symfony console make:entity StarshipDroid
``` A better name
might be `DroidAssignment`, but this is going to help us see how what we're
really doing  is recreating the same exact database, same exact
relationship in the database. 

So I'm going to broadcast, and let's give us a couple properties. First,
that `assignedAt` we wanted,  `datetime immutable`, not in all the
database, and then let's create two more. We're actually going  to create
some relationship properties from this join table over to `Starship` and
over to `Droid`. 

So we're going to have a `Droid` property, this is going to be a
`ManyToOne`, over to the `Droid` entity.  That should not be null, that
cannot be null, and let's say yes to generating the other side of the 
relationship, because that will give us the maximum flexibility. Let's say
no to orphan removal,  we're going to talk about that soon. We also need a
`Starship` property to join over the other one.  That's also a `ManyToOne`.
Related to `Starship`, is not allowed to be null, and yes to generating the
 other side of the relationship. 

Cool, I think we're good. So what this did is beautifully boring, created a
`StarshipDroid` entity  with relationships to `Droid` and `Starship`, and
of course in `Starship` it created the inverse side  of the relationship
with `StarshipDroids` and set that up. So really nothing that we haven't
seen yet. 

Alright, let's go over here and generate that migration. 

```terminal
symfony console make:migration
``` Awesome, and go check it
out. In the migrations directory, let's open this guy up, and at first it 
looks like there's quite a lot of changes, but if you look really closely
here, all it's doing is  basically kind of like temporarily dropping the
foreign key constraints, adding a primary key to that  Droid table,
recreating the foreign key constraints, and then adding the `assignedAt`. 

So it's effectively still the same things we had before, we have a
`StarshipDroid` table that has relationships  over to `Starship` and over
to `Droid`. We've made a bunch of changes in PHP, but the database
essentially  stayed the same. 

Let's run over and try this migration, 

```terminal
symfony console doctrine:migrations:migrate
``` And it
explodes. Column `assignedAt` of relation `starship_droid` contains null
values. So what's  happening here is we already have a `StarshipDroid`
table in our database from before, and it already  has a bunch of rows in
it. And so when our migration tries to add this `assignedAt`, that is not
null,  that explodes on all the existing rows. 

So the solution here is just to give this a default value. So actually
update the migration manually,  say default now, and then not null,
perfect. Let's try that. So since this entire migration failed,  Doctrine
has marked it as not having run yet. And so we can rerun the migrations,
and you'll see that  it hasn't run yet, and it runs it, and this time it
works. 

Alright, the last little detail before I move on is over in
`StarshipDroid`, this `assignedAt`, it's  not really something that we
should need to worry about. I think it's something that should
automatically  be set whenever one of these is created. So let's create a
constructor. 

So this here `assignedAt` equals `new \DateTimeImmutable()`. That's it, we
now have the exact same  relationship in the database as before. We've
taken control of the join entity, so we can add new  fields to it. 

Next up, let's see how we can actually assign droids to Starships with this
new entity setup. And  eventually we're going to get super fancy and hide
this implementation detail entirely.