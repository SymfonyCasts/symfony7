# Many To Many

Coming soon...

Okay team, we now have a `Starship` entity and a `Droid` entity. The
question is, how do we connect them? So if you think about it, each
`Starship` should have many `Droids` and each `Droid` should belong to many
`Starships`. So don't think about the database, just think about the
objects. And if you think about the objects in your classes, you can kind
of see how a `Droid` is going to need a `Starship` property that's going to
hold an array of `Starship` objects. 

So that's what we're going to focus on first. We're going to go back to our
Symfony console, `make:entity`, and we are going to modify the `Starship`
class, `Starship` entity. In this case, we're going to add a `Droids`
property, which is going to be an array of `Droid` objects or collection of
`Droid` objects. 

Let's go back to our relation type, that nice little wizard. So this is
going to be related to a `Droid` entity. If you check out the menu here,
`ManyToMany` is what we want this time. So each `Starship` can relate to
many, can have many `Droid` objects, and each `Droid` can relate to many
`Starship` objects. That's exactly what we want. So we'll type
`ManyToMany`. 

Then it asks us if we want to map the inverse side of the relationship,
which basically means do we want the ability to say `$droid->getShips()`?
That sounds handy. So let's say yes. And then the new field name inside a
`Droid` `ships` is fine. And so we'll enter one more time to exit this. 

You can see updated both the `Starship` and `Droid` entities. Let's go
check out the changes in each of those entities. So in `Starship`, it added
a new `Droids` property, which is a `ManyToMany`. It also initialized
`Droids` to the `ArrayCollection`. And added a `getDroids()` method, an
`addDroid()` method, and a `removeDroid()` method. So you're thinking,
"Hey, this looks a lot like a `OneToMany` relationship." You are absolutely
correct. 

Over in `Droid`, it's very similar. So down here, we have a `Starships`
property, which is a `ManyToMany`. It's initialized in the constructor. We
have the same `getStarships()`, `addStarship()`, and `removeStarship()`.
All right, we're all set. 

Let's generate the migration for this. 

```terminal
symfony console make:migration
```

Perfect, and go check this thing out. This is fascinating. So open up the
new one, and look what it did. It created a new table called
`starship_droid`, which is a `starship_id` foreign key and a `droid_id`
foreign key. So it turns out in the database, this is how you structure a
`ManyToMany` relationship with a join table. 

The really cool thing with Doctrine is that we all need to think about
objects. A `Starship` object has many `Droid` objects, and a `Droid` object
has many `Starship` objects. Doctrine is going to take care of all the
boring details of saving that relationship to the database. 

All right, before I keep going, let's run that migration. 

```terminal
Doctrine migrations:migrate
```

And yes, we have that new join table. All right, the next question is, how
do we actually relate `Droids` to `Starship` objects? We're going to handle
that next and see how this all saves into the database.