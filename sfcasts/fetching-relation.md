# Fetching Relation

Coming soon...

On the homepage, click into one of the ships that has the `In Progress`
status, because these  are the ones we assign parts to in our fixtures.
Down here, you can already see that we are  listing the parts, but this is
actually just hard-coded. So for the first time, we need to  query for the
parts that are related to this specific Starship. To do that, head over to
the  source controller, `StarshipController`, and normally, if we want to
query for Starship parts,  we're going to auto-wire that repository. So
let's start that same way here. Say  `StarshipPartRepository`, then I'm
going to call this `PartRepository`. Then below this, let's  say `parts`
equals. This is actually perfect. `PartRepository`, arrow, `findBy()`. So
normally,  if you want to query where some property equals some value, you
can use `findBy()` with this  array here. And for relationship, it's
actually no different. We're going to query for the  Starship property. So
notice, we're not doing `Starship ID` or anything like that. We don't  need
to worry about IDs. So `Starship` property. The other kind of interesting
thing here is  that we're going to pass it the entire `ship` object. You
actually can pass just the `getID()`  here if you want to, but in the
spirit of doctrine and relationships and thinking about  objects, I'm going
to pass the entire `ship` object. And below this, let's `dd($parts)` and 
see what happens. 

All right, spin over, refresh, and got it. 10, an array of 10
`StarshipPart` objects, all  related to this Starship. That is awesome. But
there is an easier way. In the `dd()`, instead  of saying, replace the
`parts` with `ship->getParts()`. That's nice. Here's the interesting 
thing, though. Instead of an array of `StarshipPart` objects, we get some
sort of doctrine  collection. And inside the collection, best we can tell,
it actually looks empty. So two things  here. First, when you're working
with a relationship like this, this is never actually going  to be a true
array. It's either going to be an `ArrayCollection` or what's called a 
`PersistentCollection`. In both cases, we don't really care because this
object looks and acts  like an array. So it's not really a detail that we
need to think about. The bigger mystery is,  why does this seem like it's
empty? And the answer is because doctrine is awesome. 

It doesn't actually query for the parts for this ship until we need them.
So check this out.  I'm back in our controller. Get rid of the `dd()`
instead. I'm going to say `foreach part as  part`. Instead of here, we're
going to `dump()` that. So even though parts look like an empty 
collection, when we loop over it, suddenly we'd see the 10 `StarshipPart`
objects. 

What's really cool here is we can see that there are two queries. I'm going
to say view  formatted queries. The first query is just the one for the
Starship. And the second query is the  one for all the Starship parts for
this Starship. So the first one is coming from right here,  when Symfony
queries for the Starship for us based on the slug. The second query happens
 actually right at this moment here. As soon as we `foreach` over the
parts, at that moment,  doctrine says, oh, I need to go actually query for
those parts, and it does it. That is  amazing. So let's undo the `foreach`.
I'm actually going to get rid of the `parts` variable  entirely. We can
even celebrate by getting rid of the `StarshipPart` imposter. That's all
way  too much work. Instead, down here, as in a `parts` variable, and we'll
say `ship->getParts()`. 

All right, so now that we have a new `parts` variable, we can loop over
that in our template.  So let's open up
`templates/starship/show.html.twig`. And here is our one hard coded part
right  here. So outside of the `li`, start our loop, which is nothing
special for `part in parts`. And  down here, very end, we'll do our end.
And for anything inside of here is just really normal  logic. So `part` is
a `StarshipPart` object. So we can just print things like normal, like 
curly curly. `Part.name`. This is my cool universal credits symbol. So
replace the 25 here with  curly curly. `Part.price`. And finally, down here
for a Hong Kong, that is going to be curly  curly `part.notes`. 

So nothing special here once we're inside the `for` loop. Let's give it a
try. 

Actually, for my own sanity, I'm also going to indent these spans. 

All right, head over and I'm going to click and go back to our page. And
look at that. God,  it's all 10 of our related parts, all without making a
real query because we're using the  shortcut `ship->getParts()`. But even
this is too much work. Head back to your controller. And  get rid of the
`parts` of variable entirely. I know we're getting crazy because in 
`show.html.twig`, we already have a `ship` variable. Because in
`show.html.twig`, we already  have a `ship` variable. So we just loop over
for `part in ship.parts`. We know this is going to  call the `getParts()`
method. So that's going to be the same, really the same code that we had a 
second ago in our controller. Can we try that? It still works. 

All right, as a little bonus, let's also run to the number of parts we have
on this page. So  `show.html.twig`. We're gonna have to run `parts`. A
little parentheses. Let's say curly curly.  That's really cool thing. It's
a `ship.parts`, which is gonna be that collection of parts. And  we just
pipe that into twigs `length` column `length` filter. What I want you to do
is we have  two queries currently, and one refresh, there's the parts nine.
And we still have two queries  because it's smart enough that it knows that
we already queried for all of the `Starship parts`.  So when we count them,
we don't need to like make another account query and just use this the 
information already has. 

All right, next up, we need to talk about something really, really
important concept inside of  doctrine called the owning versus inverse side
of a relationship.