# Many To Many Join

Coming soon...

Over on the home page, could we order these ships by their droid count? So
like the  ships that have the most droids would appear on top? Sure. First,
open up  `/src/Controller/MainController.php`. The query for this right now
is  `$ships = $repository->findIncomplete()`. Click into that real quick.
Rename this to be  `findIncompleteOrderedByDroidCount()`. Copy that, back
on the controller, use that new  method name. 

Awesome, we haven't changed anything yet, so when we refresh we see the
exact same results  as before. That's good. It's nice to not break
anything. Now let's think about this, in  order to order these by the
droids, we're going to need to join across that join table all  the way
over to `droid`, group by `starship`, and then count the droids. 

Let's start with the join. So in `StarshipRepository`, add a left join. The
really cool  part about this is that, again, we're not going to think about
the join table or the  database at all. We're just going to think about the
relationships in Docker. So we're going  to join across `s`, which is going
to be our `starship`, dot `droids`, and I'll alias that  entire class or
table, however you want to think about it, as `droid`. 

Now in order to count these, we are going to need to group by, so I'm going
to add a group  by, `groupBy('s.id')`. Group them by `starship`, and
that'll allow us to then count the  droids here in a second. Let's do that.
Let's use this `orderBy()` up here, because we  don't want to add another
one, or we can only have one. Replace the `orderBy()` now that we  have
currently with `orderBy('COUNT(droid)', 'ASC')`. 

Refresh the page, and good, it looks like `droids` none at the beginning.
As we kind of go  further down, we see more and more droids. If we go a
couple pages ahead, you'll start to  see things that have two to three or
four droids assigned to the `starship`. The point here  is there's nothing
special about this join at all. We just join across the property, like  any
other join in Doctrine, and Doctrine takes care of the details. 

Now, if we look at the query on this page, it is taking care of all those
details for us. If  you search on the query page for `starship_droid`,
you'll find the query down here. It's a  little hard to look at, but if you
format a query, what we see here is it's selecting from  `starship`. It's
taking care of that join over to the join table for us, joining again over 
to `droid`, and then allowing us to eventually count order by the count on
that `droid`  table. Pretty darn cool way of taking care of all those joins
for us behind the scenes.  That's it. The takeaway is that there's nothing
special going on here. It's just like any  other join inside of Doctrine.