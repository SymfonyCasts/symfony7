# Adding a Search + the Request Object

Let's take a quick, but useful, detour away from Doctrine Relationships. I know,
I know, you're wondering why we would ever want to leave such a thrilling
topic. Well, we're going to spice things up by adding a search bar to our
page. Just trust me on this one, it's going to be good.

First, pop open the `index.html.twig` template. Right at the top, I'm
going to paste in a search input. Nothing too fancy here, just an 
`<input type="text" "placeholder="search"`, and then a smattering of
classes and a swanky SVG to make it look all pretty. 

To let this bad boy submit, we need to wrap it in a `form` tag.
For the action, have it submit right back to this page:
`{{ path('app_part_index') }}`. Also, add a `name="query"` and
method="get" to the form. This way, when we submit the form, it will
append the search query to the URL as a query parameter.

## Getting the Request

Next, head over to `PartController`. How do we read the `name`
query parameter from the URL? Well, that is information from the request,
just like request headers or POST data. Symfony packages all of that
up in a `Request` object. How do we get that? In a controller, it's
super easy. Add a `Request` argument to your controller method.

You probably remember that you can autowire services like this. The `Request`
object isn't *technically* a service, but Symfony is cool enough to let it be
autowired anyway. Grab the one from `Symfony\Component\HttpFoundation\Request`.
You can call it anything, but to stay sane, let's call it `$request`.

To make sure this is working, `dd($query)`. Spin over and try it out.
Look at that! It's the string 'holodeck'. 

## Enhancing the Search

Next, let's improve the `findAllOrderedByPrice` method to allow for
a search. Remove the `dd($query);` and pass it into
the method. 

Break this into multiple lines and add an `if`
statement. Also going to change the return to `$qb = $this->createQueryBuilder('sp');`
and get rid of the `getQuery()` and
`getResuls()`: we only want the `QueryBuilder` for now.

Now for the magic. If we have a search, add an `andWhere()`
clause that checks if the lower case name of our Starship part is like our
search. I know it looks a bit funky, but that's because PostgreSQL is
case-sensitive.

Finally, we'll return the query result. 

## Preserving the Search Value

You might notice is that we kind of lose our search value after a
search. We don't see 'holodeck' in there anymore, and that's just rude.
Let's fix that. Back in our template, add a
`value="{{ app.request.query.get('query') }}"`. 

## Searching on Multiple Fields

Now, wouldn't it be great to also search on the notes? Let's say I search
for 'controls'. Right now, nothing shows up. So, I want to search on the
name *and* the notes.

We need to use some `OR` logic. Back in our repository,
add an `OR` to the `andWhere()` clause. You might be tempted
to use `orWhere()`, but that's a trap! You can't guarantee where
the logical parentheses will be. Trust me, you'll thank me later. Instead,
use `andWhere()` and put the `OR` right inside.

And there we have it! We can now search on the notes, on the name, or both.
The takeaway is when you want to use `orWhere()`, don't: embed the `OR`
inside an `andWhere()`, and you'll have full control over where the logical parentheses
go.

Alright, with that exciting detour complete, let's head back on track and
talk about the final relationship type, many to many.
