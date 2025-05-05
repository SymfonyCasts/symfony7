# Cascade

Coming soon...

## Tackling the Doctrine Beast

Alright, folks. Picture this scenario: you're loading up your fixtures and
suddenly, WHAM! You're hit head-on by the most colossal error message. It's
a classic one, straight out of the Doctrine playbook. The culprit? Our dear
friends `starship droids` and `cascade persist`. 

The error bleats out, "An entity was found through the relationship
`starship droids` that was not configured to `cascade persist` operations
for entity `starship droid`." It sounds like an alien language, right? 

Well, let me translate for you. The error is crying out in despair, saying,
"Hey, you're saving this `starship` and it's got a `starship droid`
hitching a ride. But, you've ghosted me on saving the `starship droid`. I'm
left hanging here! What's the deal?" 

The snag we've hit is that in our `starship` entity, we're cut off from the
entity manager. So, we can't just call up `manager->persist(starship
droid)`. What a pickle, right? 

## Harnessing the Power of `cascade persist`

But fear not, my fellow coders! We've got a secret weapon at our disposal:
`cascade persist`. 

So, summon your inner Jedi, scroll up until you locate the `starship
droids` property, and seek out the `OneToMany`. We're going to add a new
option here called `cascade`. I'll type it in manually - it's like we're
doing our own stunts. Then, we'll create an array and say `persist`.

```terminal
OneToMany(cascade={"persist"})
```

What we're doing here is setting up a sort of domino effect. If anyone
saves this `starship`, we're going to cascade that save down to any
attached properties. 

A word of caution though: use this power wisely. It's a bit like installing
an automatic pilot. It can make your code a bit unpredictable, but in our
case, it's exactly the fix we need. 

Let's give those fixtures another whirl. And voila! It works like a charm. 

## Back to Adding Droids

Now, we're back in business. We can use `ship->addDroid()` once more. But,
let's not rest on our laurels. I want to create a fleet of `ships` and
assign them a bunch of `droids`. 

We're going to turf all the manual code we added and bring back the
`droids` property into the `starship factory`. 

Let's fire up the fixtures again. And guess what? They work. It's like a
magic trick, isn't it? 

Behind the scenes, Foundry is calling `addDroid()` on each `starship` for
each `droid`. And we just proved that `addDroid()` is back in action. 

## Finer Control with `assignedAt`

But, what if you want to add a `droid` to a `starship` and control the
`assignedAt` property? The solution is to add an argument for `assignedAt`
in `starship`, like a `DateTimeImmutable`. 

```terminal
addDroid(Droid $droid, ?\DateTimeImmutable $assignedAt = null)
```

We'll make it optional to keep things flexible. Then, after creating the
`starship droid`, we'll set the `assignedAt` if it's provided. 

It's a smooth move, but there's a slight issue. Foundry won't let us
control the `assignedAt` field. So, if you want to assign some `droids` at
a specific time, you'll need to take the wheel manually. 

## Displaying `assignedAt`

Finally, let's make that `assignedAt` visible on our site. We'll need the
`starship droid` join entity object to do that. A bit more work, sure, but
we're not afraid of that, are we? 

With our `starshipDroid.droid.name` and `starshipDroid.assignedAt` in
place, we'll sneak in our `assignedAt` and pipe it into our `ago` filter
for a touch of flair. 

```terminal
starshipDroid.assignedAt|ago
```

And there you have it! You can now see when our `droids` were assigned. 

That's all she wrote, folks! We've explored the deepest corners of Doctrine
relationships, even the elusive many-to-many with extra fields. As always,
if you have questions, drop them in the comments below. We're all in this
together!