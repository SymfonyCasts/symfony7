# Many To Many Join

Coming soon...

## Title: Leading the Fleet: Ordering Starships by Droid Count

Alright folks, ever wondered which starship in your fleet has the most
droids? Well, let's find out! We're going to list our ships in descending
order based on their droid count. The ones with the most droids get the top
spot. Sounds cool, right? Buckle up, let's do this!

First things first, we need to dive into our `Source Controller`,
specifically the `Main Controller`. Currently, our query is a little
something like this: 

```terminal
$ships = $repository->findIncomplete()
```

Click into that bad boy real quick. We're going to give it a new, snazzy
name: `findIncompleteOrderedByDroidCount()`. Copy that, swing back to the
controller, and replace the old method name with our shiny new one. 

Oh, look at that! We've changed nothing yet, so a quick refresh gives us
the same results. It's always a nice feeling, isn't it? It's like we're
sneaking around in our own code, not breaking anything. But now, things are
about to get a little more exciting. 

## Title: Joining Forces with Droids

We need to order our starships by their droid count. To do this, we're
going to join across the join table all the way to `droid`, group by
`starship`, and then count the droids. Sounds like a party, right? 

Kick things off with the join. In `Starship Repository`, add a left join.
The coolest part? We're not going to think about the join table or
database. Nope, we're just focusing on the relationships in Docker. So
we're joining across `s`, which is our starship, and `droids`, which we'll
alias as `droid`. 

To count the droids, we'll need to add a `group by s.id`. It's like herding
our starships together before counting their droid passengers. 

Next up, let's use this `orderBy` up here, because we don't want to add
another one. Replace the existing `orderBy` with `orderBy('COUNT(droid)',
'ASC')`. 

## Title: The Droid Count Reveal

After that, hit refresh and boom! At the top, you'll see `droids none`. But
as you scroll down, the droid count increases. If you're brave enough to
venture a couple of pages ahead, you'll start seeing starships with two,
three, or even four droids. It's like a surprise party with each page! 

The key here? There's nothing special about this join. We join across the
property, Doctrine handles the rest. It's like having a personal assistant
taking care of all the grunt work for us. 

If you take a quick peek at the query on this page, you'll see it's
handling all the details. It's like a backstage pass to the magic of
Doctrine. Search for `starship_droid` and you'll find the query. Granted,
it's a bit like reading ancient runes, but if you format the query, you'll
see it's selecting from `starship`, taking care of the join over to the
join table, joining again over to `droid`, and allowing us to count and
order by the count on that `droid` table. It's a pretty slick way of
handling all those joins behind the scenes. 

And that's it, folks! Nothing mystical or magical here, just another day in
the life of a Doctrine join. Now go on, enjoy your ordered list of
starships and their droid counts. May the Force be with you!