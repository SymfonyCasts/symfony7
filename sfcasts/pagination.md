# Pagination

In the last chapter, we added more starships to this list. It's still
manageable, but if we had thousands of them, we would have performance issues. We
need to *paginate* these results so we only show a few at a time - or per *page*.

To do this, we'll use a library called Pagerfanta - what a cool name! It's a generic pagination library
but has great Doctrine integration! Install the two required packages:

```terminal
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter
```

Scroll up to see what was installed. `pagerfanta/doctrine-orm-adapter` is the *glue* between
Pagerfanta and Doctrine.

On our homepage, we're using `findIncomplete()` from our `StarshipRepository` so open
that up and find that method. We're going to change the return type to `Pagerfanta`
which has some extra pagination-related functionality. This object *also* iterates
over our entities, so we can leave the docblock above as is.

Now, a super important thing to always remember when paginating a query is to have a
predictable order. In our query, add `->orderBy('e.arrivedAt', 'DESC')` to order by
the `arrivedAt` field in descending order.

Instead of returning, add this to a variable called `$query`. We need Pagerfanta to
be able to manipulate the query, so remove the `getResult()` call.

Now, `return new Pagerfanta(new QueryAdapter($query))` and be sure to import these
two classes.

In our `MainController`'s `homepage()` method, we need to configure the current page
and the number of items per page. After the `$ships` variable, add `$ships->setMaxPerPage(5)`,
to show 5 items per page. Then, `$ships->setCurrentPage(1)` to show page 1. It's
important to note that `setCurrentPage()` *must* be called after `setMaxPerPage()` or
you'll have funky results.

Back to our app... refresh... and take a look, we're now only showing 5 items - the first page.

Back in the `homepage()` controller change to `setCurrentPage(2)` and refresh the app.
We now see a different set of starships - the second page. Take a quick look at the query
profiler. We see several new queries. These are added by Pagerfanta and Doctrine to create
an optimized pagination query.

Instead of hardcoding the current page number, we'll get it from a `page` query parameter in the URL.
So `?page=1` shows the first page, `?page=2` shows the second, and so on.

In our `homepage()` controller, inject `Request $request` - the one from `HttpFoundation`
and change the `setCurrentPage()` argument to `$request->query->getInt('page', 1)`.
This will default to 1 if this query parameter is not set.

Back in our app and refresh - this is page 1 because no query parameter is set. Add `?page=2`
to the URL... now we're on page 2.

It'd be nice to show the user some information about the current page: the total number of items, total
pages and what page we're currently on. Pagerfanta makes this easy!

In the `homepage()` controller, Cmd + Click `homepage.html.twig` to open that up.

We'll put this information right below this `<h1>` so I'll change its bottom margin and add
a new `<div>` (with a bit of styling). Inside, write `{{ ships.nbResults }}` to show the
*total number of results*, and in brackets: `Page {{ ships.currentPage }}` to show the current
page number and then `of {{ ships.nbPages }}` to show the total number of pages.

Spin back to our app and refresh. Perfect! We have 14 total results, and we're on page 1 of 3.
Because of the randomness of the fixture data, you will see different numbers here.

We need users to be able to navigate between pages, so we'll create a little "previous/next" widget
below the results.

In `homepage.html.twig`, below where we list the starships, I'm going to paste in some code. First,
`if ships.haveToPaginate`: we only want to show this widget if pagination is required.
`if ships.hasPreviousPage`: we only show the previous link if there is a previous page to go to.
Inside, we're generating a url to our `app_homepage` route. Now, we're injecting the `ships.getPreviousPage`
as a `page` route parameter. Since `page` isn't defined in the route, it will be added as a `page`
query parameter which is exactly what we want! Inside the link, the text: `Previous`. The exact same thing
is done for the `Next` link but using `ships.hasNextPage` and `ships.getNextPage`.

Refresh our app, scroll down, and sweet! We see a `Next` link! Click it... and now we're on page *2* of 3.
The URL shows `?page=2` also. Below, our widget has both `Previous` and `Next` links. Click `Next` again...
page 3 of 3. Click `Previous`, back to page 2 of 3 - perfect!

Navigation done!

We built this simple widget manually but Pagerfanta does have Twig integration and templates for more
complex pagination widgets - like listing all page numbers as links, so you can jump to any page quickly.

Next, we'll add more fields to our Starship entity and look at a migration trick to ensure
existing data is kept valid!
