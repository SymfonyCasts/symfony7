# Droid Entity

Coming soon...

Okay, we've seen a couple different relationship types at this point. We
know there's a `ManyToOne`. We also know that there is a `OneToMany`. If we
scroll up here in Starship, there is a `OneToMany`. But as we learned
earlier, `ManyToOne` and `OneToMany` are really the same one type of
relationship just seen from two different sides. So, so far there's really
only one relationship type that we've thought about. There's also a
`OneToOne` relationship, but that's really the same as a `ManyToOne`, where
it restricts a Starship part, where it only allows one Starship part, where
it only allows a Starship to be related to one Starship part. So
`ManyToOne`, `OneToMany`, and `OneToOne` are really the same type of
relationship in the database. So really, so far we only have that one
relationship type. 

Now listen, space repair is dangerous work. Due to that darn vacuum that
tends to try to kill humans, this is perfect work for droids. In fact, we
have an army of droids, where each droid is assigned to multiple ships, and
each ship has multiple droids. That's our second and final relationship
type, `ManyToMany`. 

To get things started, let's create a droid entity. Run:

```terminal
symfony console make:entity Droid
```

Say no to broadcasting. Perfect, give this just a couple of properties. How
about `name`? The defaults are all fine. `primaryFunction`, and
`primaryFunction`. And the defaults are also fine. 

And that's it. And then of course, once we made that change, as it
suggests, let's make our migration. So I'll copy that, because I'm lazy.
Perfect, and let's go check that out. So in the migrations directory, let's
open this new one down here. And absolutely no surprise, create table
`droid`, with all the fields on there, with all the fields. So nothing
surprising. 

To run the migration, use:

```terminal
symfony console doctrine:migrations:migrate
```

Perfect, we have a fresh new `droid` table in the database. No relationship
yet to ship, but at least we have that new table. 

And before we set up the relationship, let's get ourselves some nice data
for droids. Let's run Symfony Console again. This time I'm going to
backspace and say, `make:fatory`. So you can make a Foundry factory. Create
one for `droid`. And perfect, let's open that up. `src/factory`, `droid
factory`. And it gives us some nice defaults at the bottom, so we can use
this immediately, but I want to get some defaults that are a little bit
more fun. So I'm going to remove this array down here and paste in a little
bit of code here, just to give us some more interesting droids. 

All right, let's reload the fixtures. Run:

```terminal
symfony console doctrine:fixtures:load
```

Perfect, so now I have a droid table. It's loaded up some cool fixtures,
but the droids are not yet related to starships. So let's do that next with
our final type of relationship, a `ManyToMany`.