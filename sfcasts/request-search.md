# Request Search

Coming soon...

What we're going to talk about next, I admit, is a bit unrelated to
Doctrine Relationships. But  it's good stuff, I promise. So inside of this
page here, let's add a search bar. Let's open up a  template for this. So,
`index.html.twig`. And right on top here, let's paste in a search input. 
Now, there's nothing special here, it's just `input type="text"`,
`placeholder="search"`, and  then a bunch of classes and an SVG to make it
look cool. And there we go. 

Now to make this actually something we can submit, we need to surround it
with a form tag. So  let's say `form`, and for the action, let's have this
submit right back to the same spot. So let's  copy that route name, we'll
say `{{ path('') }}`, we'll paste in the route name. Also, because  this is
a search form, we want any of our fields that show up in the URL. So we're
going to say  `method="get"`. Let's move this closing form tag here and
move it down right after the SVG. And  then so that Ryan keeps his sanity,
let's indent these to keep everything looking nice. 

And the last thing here is we need to give this input a name so we can find
its value on the  server. So let's say `name="query"`, or we can call it
search if you want. Perfect. So over here,  if we refresh, let's look for
holodeck. And of course, it doesn't work yet, but you can see the 
`?query=holodeck` up on the URL. 

So the question now is how do we read this? So this is part of the URL, and
so it means it's part  of the request. So if you're looking for IP address,
request headers, these are all things that  are part of the request itself.
And when you need information from the request, there's a special  way to
get a request object. That is you can add it as an argument to your
controller. So far, we  know that you can auto-wire services. And while the
request object is not technically a service,  there's a special case in
there that allows it to be auto-wired. 

So grab the one from HD Foundation, and you can call this anything, but
`request` is probably a  pretty good name. So let's say `query =
$request->query->get('query');`. And just to see if this is  working, let's
`dd($query);`. And got it. It's the string `holodeck`. 

Next up, let's enhance our `findAllOrderByPrice()` to allow for a search.
Let's get rid of the  `dd`, and pass in our `query`. And run into that
method, `StarshipPartRepository::findAllOrderByPrice()`,  and we'll give
this a string `search`. We can make it optional by saying `= ''`. 

All right, and this time, we're going to have to break this into multiple
lines, so we can have an  if statement. So change this return to `qb =`, so
`queryBuilder =`, then get rid of the `getQuery()`  and `getResults()`,
because we just want the `queryBuilder` for now. And down here, we're going
to  say, if we have a `search`, `if ($search)`, then it's going to be
similar to that, `qb->andWhere()`,  instead of `s.name`, it's going to be
`sp.name`, because we're using `sp` up here, like `search`.  And for that
parameter, we're going to fill in the `search` parameter, and here is where
we do our  fuzzies, `%$search%`. I know it looks a little funny, but that's
the way you do it. 

And down here in the bottom, this is where we'll say, `return
$qb->getQuery()->getResult();`.  All right, cool, let's head over here and
try this. And it works. So check this out. If I do an  uppercase holodeck,
it doesn't work, and this is actually something special to Postgres.
Postgres  is a case-sensitive database, so `holodeck` with an uppercase H
does not match `holodeck` with  lowercase. So to handle this, we can just
make our query a little fancier. We can say `LOWER(sp.name)  LIKE :search`.
And now, that's a bit more how we expect it to work. 

And while we're here, notice we kind of lose the value. We don't see
`holodeck` in there. So let's  fix that. Let's move over in our template.
So this is pretty easy. So we're going to add a `value="{{
app.request.query.get('query') }}"`. 

The answer is, Twig gives you one global variable called `app`. It has a
bunch of useful things on  it. One of the most useful is `app.request`, and
then we say `.query.get('query')`. So nothing  complicated, I just want to
show you how you can get the request object from inside the template.  And
refresh, there it goes. 

So what I want to do next is actually allow for us to also search on the
notes. So right now, if I  search for controls, you can see nothing shows
up. So I want to search on the notes, but also on  the name. So it's going
to be an or logic. So head into our repository, and we're going to add an 
`or` here. And one way you might expect to do this is saying `orWhere`, but
I never use `orWhere`.  And the reason is, you can't control where the
logical parentheses go. 

So instead, what you can do is you can use `andWhere` and put the `or`
right inside. So `andWhere`  `LOWER(sp.name) LIKE :search`, and right here,
I'm going to say `or LOWER(sp.notes) LIKE :search`,  which we're already
filling in down here with the percents on, with the fuzziness. Perfect. So 
let's refresh now. Perfect. So we can search from controls, search on the
name, or we can search  on the notes, or we can search on the name. 

So the takeaway there is when you do an `orWhere`, embed it inside the
`andWhere`, and then you  can control kind of like where the parentheses go
and where the logical parentheses go. All right,  next up, we're going to
talk about the final relationship type, many to many.