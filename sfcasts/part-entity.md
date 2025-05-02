# Part Entity

Coming soon...

"We are already listing ships that we're working on on our home page. This
is thanks to our handy-dandy `Starship` entity that we created  in the last
tutorial. But now we gotta level things up here. We need to start keeping
track of the parts that are used in each Starship.  I think you can see
where this is going. Each part is going to belong to exactly one Starship,
and each Starship will have many parts. 

But before we start thinking about relationships, the first thing we need
is simpler. We need a new entity to keep track of the parts.  So go over to
your terminal and open a new tab, since that one's running our server, and
run 

```terminal
symfony console make:entity
```

We'll call this one `StarshipPart`. My creativity amazes me. All right, say
no to broadcasting. We don't need to worry about that.  And give this just
a couple of fields. How about `name`? String's fine. That's fine. And then
say no to nullable, since every part  should have a `name`. And then we'll
add `price`. This will be an integer, because it'll be in credits. Not
null. And then finally,  we'll have a `notes` field. We'll make this text
so it can be longer, and say yes to nullable. And then hit, and that's it. 

Now, we just created a new entity, so we need to have a migration for it.
So copy `symfony console make:migration` and paste that.  And let's go
check that out. So over here, in migrations, you can see the new migration.
And actually, I cleaned up all of our old  migrations from the previous
project, so this is just our `StarshipPart`. Perfect. And let's migrate
that by saying 

```terminal
symfony console doctrine:migrations:migrate
```

Perfect. And we now have that table in our database. But one thing I'd like
to add to pretty much all of my entities is a `created_at`  and
`updated_at`. You can see I have it inside of `Starship` here with this
`TimestampableEntity`. I'm actually going to copy that.  I'll close this.
Let's go into `StarshipPart`. And we're just going to paste that right on
top. This comes from a library that we  installed in the last tutorial. And
it just has a `created_at` and `updated_at` properties that will
automatically be set. 

And since we did just add two new properties, run `make:migration` again.
And inside of here. Perfect. It's all just the table to add  `created_at`
and `updated_at`. So run 

```terminal
symfony console doctrine:migrations:migrate
```

And those fields are there. Now, in the last tutorial, we inside of our
fixtures, `src/DataFixtures/AppFixtures`, we use a really cool  library
called `Foundry` to help us create a bunch of dummy objects really, really
quickly, we're gonna do the same thing for `StarshipPart`.  And the first
step to doing that is generating a factory for that entity. So 

```terminal
symfony console make:factory
```

That's all we need. It sees our `StarshipPart`, it sees that it doesn't
have a factory yet. So we're going to set zero. And there we go,  created
`src/Factories/StarshipPartFactory`. And it even included some very, very
boring defaults for each of the fields. I think we can  do better than
that. If we're going to go to this project, let's have some fun. 

So at the top of `StarshipPartFactory`, paste in some code with some
example parts. You can grab this code from the code block on this page. 
I'm also going to go down here into defaults and replace this return with
some code that uses the random data that we have up here. 

And finally, let's actually use this factory inside of our fixtures. So
anywhere in here, but I'll go to the bottom, I'm going to say 
`StarshipPartFactory::createMany`. And I don't know, let's create 50 of
these. All right, spin back over to terminal. Let's run 

```terminal
symfony console doctrine:fixtures:load
```

Say yes. And let's check those out over here. So how about 

```terminal
doctrine:query:sql
```

And then we'll say `select * from starship_part`. And check that out with
just a couple lines of code here, we have 50 random and awesome  parts in
our database. Next up, let's create our first relationship. Next up, we'll
create our first relationship to start relating these  parts to their
ships."