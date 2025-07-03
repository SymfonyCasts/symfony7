# Adding a Search + the Request Object

Time for a quick, but useful, detour away from Doctrine Relations. I know
Doctrine relations rock, but so will this! I want to add a search bar to our
page. Trust me on this one, it's going to be good.

Pop open the `index.html.twig` template. Right at the top, I'll paste in a search
input:

[[[ code('f67471f9e7') ]]]

Nothing fancy here: just an `<input type="text" "placeholder="search"`, and then
a smattering of classes and a swanky SVG to make it look all pretty.

To let this bad boy submit, wrap it in a `form` tag. For the action, have it submit
right back to this page: `{{ path('app_part_index') }}`. Also, add a `name="query"` and
`method="get"` to the form:

[[[ code('6b6dc8f1c8') ]]]

This way, when we submit the form, it will append the search query to the URL
as a query parameter.

## Getting the Request

Next, head over to `PartController`. How do we read the `name`
query parameter from the URL? Well, that is information from the request,
just like request headers or POST data. Symfony packages all of that
up in a `Request` object. How do we get it? In a controller, it's
super easy. Add a `Request` argument to your controller method.

You probably remember that you can autowire services like this. The `Request`
object isn't *technically* a service, but Symfony is cool enough to let it be
autowired anyway. Grab the one from `Symfony\Component\HttpFoundation\Request`.
You can call it anything, but to stay sane, let's call it `$request`:

[[[ code('4c6cea75bd') ]]]

Set `$query = $request->query->get('query')`: the first `query` refers to the
query parameters, and the second `query` is the name of the input field. To make
sure this is working, `dd($query)`:

[[[ code('a346c70d5d') ]]]

Spin over and try it out. Look at that! It's the string "holodeck". 

## Enhancing the Search

Next, let's improve the `findAllOrderedByPrice()` method to allow for a search.
Remove the `dd($query);` and pass it into the method:

[[[ code('0fef7f4784') ]]]

Break this onto multiple lines and add an `if` statement. I'm also going to change
the return to `$qb = $this->createQueryBuilder('sp')` and get rid of the `getQuery()`
and `getResult()`: we only want the `QueryBuilder` for now.

Now for the magic. If we have a search, add an `andWhere()` that checks if
the lower case name of our Starship part is like our search. I know it looks
a bit funky, but that's because PostgreSQL is case-sensitive.

Finally, return the query result:

[[[ code('9834ce31ff') ]]]

## Preserving the Search Value

You might notice that we lose our search value after a search. We don't see
"holodeck" in there anymore, and that's just rude. To fix that, back in the template,
add a `value="{{ app.request.query.get('query') }}"`. Yup, that handy `Request`
object is available in any template as `app.request`:

[[[ code('01432cfda0') ]]]

## Searching on Multiple Fields

Wouldn't it be great to also search on the parts' notes? Search
for 'controls'. Right now, nothing. We really want to search on the
name *and* the notes.

We need some `OR` logic. Back in the repository, add an `OR` to the `andWhere()`
clause:

[[[ code('ebe7605bcc') ]]]

You might be tempted to use `orWhere()`, but that's a trap! You can't guarantee
where the logical parentheses will be. Trust me, you'll thank me later. Instead,
use `andWhere()` and put the `OR` right inside.

And there we have it! We can now search on the notes, on the name, or both.
The takeaway is when you want to use `orWhere()`, don't: embed the `OR`
inside an `andWhere()`, and you'll have full control over where the logical
parentheses go.

Alright, with that exciting detour complete, let's get back on track and
talk about the final relationship type: *many to many*.
