# Setting Relation

Coming soon...

So the question now is, how can I indicate that a specific `StarshipPart`
belongs to a  specific `Starship`? So far, we've been creating all of our
data inside of our `AppFixtures`, which is just a really handy way to work
with objects directly, so we're going to stick  with that. But instead of
using Foundry here, we're going to create some objects by hand,  just to
make everything as clear as possible. 

So, I'm going to type in `Starship`, but I'm not going to use Foundry here.
We're just  going to use the good old-fashioned `new Starship()`. Then, to
save just a little bit of  time, I'm going to paste some code below that to
make sure that all the required properties  on `Starship` are set. Now,
because we're creating these objects by hand and not through  Foundry, we
need to remember to type `manager->persist($starship)`. 

Right below this, let's create a `StarshipPart` with `$part = new
StarshipPart()`. And  same as before, I'm going to go grab a bit of code
here so that we can fill in all the  properties. Paste that and we are good
to go. 

Let's finish this down here with `$manager->persist($part)`. Then finally,
we'll do  `$manager->flush()`. The reason we need to do `persist` and
`flush` here is because  normally Foundry does that in the background for
us, but in this case, since we're doing  everything manually, we're also
going to call `persist` and `flush` manually. 

We have a new `Starship` and a new `StarshipPart`. They're not related yet,
but let's try  to load the fixtures anyway. 

Find your terminal and run:

```terminal
symfony console doctrine:fixtures:load
```

And boom! We get an error: "Starship ID cannot be null on the
`StarshipPart` table". This  is because when we ran `make:entity`, we made
the ship required. You can see that in  `StarshipPart`. Right above the
`Starship` property, there's the `@ManyToOne`, but there's  also a
`@JoinColumn`. This is optional, but it allows us to control the foreign
key column  in the database. And since we have `nullable=false`, it means
that every `StarshipPart`  must have a `Starship`, which is what we want. 

So then, how do we say that this part belongs to this `Starship`? The
answer is  beautifully simple. Anywhere before `flush`, we're going to say
`$part->setStarship($ship)`.  That's it. 

Now, notice that we're not setting some `Starship` ID property. We're not
even passing an  ID, like `Starship->getID()`. This is the magic of
Doctrine relations. Doctrine knows how  to save this relationship. It will
first save the `Starship` object, and then use its ID  to set the
`starship_id` column on the `StarshipPart` table. 

Let's only create `StarshipPart` manually so that everything is really
clear. Now, let's  reload those fixtures. This time, no errors. 

To prove this is working, let's run:

```terminal
symfony console doctrine:query:sql
```

We'll say `SELECT * FROM starship_part`. Yes, check this out. There's our
one part, and  it's related to `starship_id` 75. Let's look that up. We'll
take this query. We'll say  `SELECT * FROM starship WHERE id = 75`.
Perfect. There's our `Starship` that we created  up here. We can see that
those are now related in the database. 

Here's the big takeaway. When you're working with Doctrine relationships,
you're working  with objects. You're setting objects, relating objects to
each other. You're not working  with IDs. Doctrine handles the boring
details of saving those objects and the relationships  in the database. 

The only problem I see right now is we are doing a lot of work here just to
create one  `Starship` and one `StarshipPart` and relate them. Next, let's
use Foundry to create a  bunch of ships and parts and relate them all at
once. This is an area where Foundry really  shines.