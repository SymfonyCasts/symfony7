# Droid Entity

Coming soon...

## A Symphony of Relationships

Well, you've had a bit of a taste of relationship types by now. You've been
formally introduced to `ManyToOne` and `OneToMany` — it's like they are
two sides of the same coin. So, in reality, you've been dealing with just
one type of relationship so far. 

And that `OneToOne` relationship you've heard about? Well, it's just a
`ManyToOne` relationship playing dress up, restricting a Starship part so
that it can only have a monogamous relationship with one Starship. So,
`ManyToOne`, `OneToMany`, `OneToOne` — they're all partying together in
the same type of relationship club. But hey, variety is the spice of life,
right? 

## Enter the Droids

Let's bring in some drama. Picture this: space repair. It's a high-stakes,
dangerous job. That pesky vacuum of space is always out to get us humans,
so who better to tackle it than our trusty droids? 

You command an army of droids, each assigned to multiple starships and vice
versa. This is where our second and final relationship type, `ManyToMany`,
takes the spotlight. 

Let's set the stage by creating a droid entity. Pop this command in your
console:

```terminal
symfony console make:entity Droid
```

And just like that, you're in business. Now we need a couple of properties:
`name` and `primaryFunction`. The defaults will do just fine. That's it,
easy peasy. 

But remember, a developer's work is never done. After you've made your
changes, it's migration time — and not the bird kind. Copy the following
command, because we're developers, and developers are efficient (not
lazy!):

```terminal
symfony console doctrine:migrations:migrate
```

Voilà! You've got a shiny new `droid` table in your database. It's not yet
in a relationship with `ship`, but hey, every relationship has to start
somewhere.

## Populating the Universe with Droids

Before we set up the relationship, let's populate our universe with some
cool droids. Run this:

```terminal
symfony console make:factory Droid
```

Open up `src/factory/DroidFactory` and you'll see it's already cooked up
some default data for you. But let's add some spice to these droids.
Replace the existing array with some of your own data and let's make these
droids a bit more interesting. 

Reload the fixtures with:

```terminal
symfony console doctrine:fixtures:load
```

And there you have it! You've got a `droid` table filled to the brim with
fascinating droids. But they're still flying solo, not yet in a
relationship with `starships`. Let's change that with our final type of
relationship, a `ManyToMany`.