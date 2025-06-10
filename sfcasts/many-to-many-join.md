# Joining Across a Many-to-Many Relationship

Ever wondered which starship in your fleet is crawling with the most
droids? Me too! So let's find out! I want to list every ship in descending
order based on their droid count.

Dive into `src/Controller/MainController.php`. Our query is:

```terminal
$ships = $repository->findIncomplete()
```

Click into that method and give it a new, snazzy name:
`findIncompleteOrderedByDroidCount()`. Copy that, swing back to the
controller, and replace the old method name with the new one. 

We've changed nothing yet, so a quick refresh gives us
the same stuff.

To order the starships by their droid count, we need to 
join across the join table all the way to `droid`, group by
`starship`, and then count the droids. Woof. Actually, it's quite
nice!

In `StarshipRepository`, add a left join. But we're not going to think about
the join table or database. Nope, focus only on the *relationships* in Doctrine.
So we're joining across `s`, which is our starship, and `droids`, the property
that has the ManyToMany relationship to `Droid`. Finally, we alias
those droids as `droid`.

To count the droids, add a `group by s.id`.

To order replace the existing `orderBy` with
`orderBy('COUNT(droid)', 'ASC')`. 

After that, hit refresh and boom! At the top, you'll see `droids none`. But
as you scroll down, the droid count increases. If you're brave enough to
venture a couple of pages ahead, you'll start seeing starships with two,
three, or even four droids!

The key here? There's nothing special about this join. We join across the
property and Doctrine handles the rest.

If you peek at the query on this page, you'll see it's
handling all the details. Search for `starship_droid` to find the query. 
This is ugly, but if you format the query, we see that it selects from `starship`,
taking care of the join over to the
join table, joining again over to `droid`, and allowing us to count and
order by the count *on* that `droid` table. Impressive Doctrine, impressive.

That's *technically* it for ManyToMany! But next we're going to handle
a more advanced, but still common, use case: adding data to the join
table, like a date when the droid joined the starship.
