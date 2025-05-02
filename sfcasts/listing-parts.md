# Listing Parts

Coming soon...

Let's create a new page, a Parts List page. Now, we're going to kind of
cheat to do this, we're going to use Maker Bundle. Find a console and run
`Trident Terminal`, run `Symfony Console`. And we'll run `Make Controller`.
Small Maker just to give us a little head start whenever we have a new
controller. Let's call it `PartController`. And we'll say no to tests just
because we're not focusing on tests in this tutorial. 

Perfect, one class and one template. So very simple. Let's go check out
that new class. `Source Controller`, `PartController`. And really nothing
interesting yet at all, just rendering a template. Well that's up to the
URL to be `/parts`. And the name to be  `App Part Index`. This is the index
page for all of our parts. 

Let's copy that route name because we're going to add a link to this in our
header. So open up `base.html.twig`. And for this about link, which goes
nowhere, we're going to take that over  and say parts. And the href of
course is going to be `curly curly path`. And then we'll paste, `App Part
Index`. 

Alright, save that, head to the homepage. Click our fancy new link and it
looks terrible, but it does work. Alright before we go anywhere, we can at
least change the title to be not  `Hello Part Controller`. That's not
really that useful. So open up `templates`,  `Part Index.html.twig`. We're
already overriding the title block, so let's just change this to parts. 

That's at least a little bit better. Now we know we're going to loop over
all the parts and print them on here. So instead of `Part Controller`,
we're definitely going to need a query  for all the parts. So in order to
do that, we're going to need the `Starship Part Repository`.  So add an
argument here called `Starship Part Repository`. We'll auto-wire that in.
And this  time I'm just going to call it repository. 

Now to get all the parts, this is super simple. Yep, parts equals
`repository, arrow find all`.  Down here we don't need to pass the
controller name in. Instead let's pass a parts variable.  It's `at two
parts`. Nice. 

Now we know now that we have this parts variable in our template, we can
loop over that and  start printing out information. To make our life a
little bit more interesting, I'm going to  actually... ...paste in this
template, but there's nothing fancy happening here. We're still  overriding
the title block, we're still overriding the body block, but now we're
looping over  the part and parts. This stuff here is just a bunch of stuff
just to make it look nice. 

That's why I gave you some freestyling. Alright, so let's see how it looks.
Refresh, and much  better. Now probably the only interesting thing inside
of here is a little tweak trick I'm using.  It's this cycle function. So I
wanted to give every gear kind of randomly a different color  here just so
it would look nice. And so the cycle function, you can pass it a bunch of
strings,  and then this `loop.index 0`, and as this goes 0, 1, 2, 3, 4,
it'll choose this different one  here, 0, 1, 2, 3, 4, and we get this kind
of nice effect of having different gear icons for  each of the parts. So
nothing too important, but that is kind of cool.

Alright, the last thing we're going to do here is we see assign to ship
name. We can do better than that. Let's say `curly curly part dot ship`. So
this time I'm not saying `ship dot part`,  I'm using the other side of the
relationship, `part dot ship dot name`. 

Oh, my bad. Actually, this is why I wasn't getting it. I'll replace it with
 `part dot starship dot name`. And got it. Alright, next, let's start
talking about joins.