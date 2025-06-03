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

Next, we're going to improve our `findAllOrderedByPrice` function to allow for
a search. We can get rid of our `dd($query);` and pass the query right into
our method. 

```php StarshipPartRepository::findAllOrderedByPrice($query) ```

We're going to have to break this into multiple lines and add an `if`
statement. We're also going to change the return to `$qb =
$this->createQueryBuilder('sp');` and get rid of the `getQuery()` and
`getResults()`, because we only want the `QueryBuilder` for now.

Now the magic happens. If we have a search, we're going to add a `andWhere`
clause that checks if the lower case name of our Starship part is like our
search. I know it looks a bit funky, but bear with me, that's how it's
done. 

```php if ($search) {     $qb->andWhere('LOWER(sp.name) LIKE
:search')->setParameter('search', '%'.strtolower($search).'%'); } ```

Finally, we'll return our query results. 

```php return $qb->getQuery()->getResult(); ```

## Preserving the Search Value

One thing you might notice is that we kind of lose our search value after a
search. We don't see 'holodeck' in there anymore, and that's just rude.
Let's fix that. Back in our template, we're going to add a `value="{{
app.request.query.get('query') }}"`. 

## Searching on Multiple Fields

Now, wouldn't it be great to also search on the notes? Let's say I search
for 'controls'. Right now, nothing shows up. So, I want to search on the
notes but also on the name. 

We're going to need to use some `or` logic here. Back in our repository,
we're going to add an `or` to our `andWhere` clause. You might be tempted
to use `orWhere`, but let's not do that. It can get a bit confusing with
the logical parentheses. Trust me, you'll thank me later. Instead, we can
use `andWhere` and put the `or` right inside.

```php $qb->andWhere('LOWER(sp.name) LIKE :search OR LOWER(sp.notes) LIKE
:search') ```

And there we have it! We can now search on the notes, on the name, or both.
The key takeaway here is when you want to use `orWhere`, embed it inside an
`andWhere`, and you'll have full control over where the logical parentheses
go.

Alright, with that exciting detour complete, let's head back on track and
talk about the final relationship type, many to many. Strap in, it's going
to be a wild ride!
