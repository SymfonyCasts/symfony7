# Pagination

Foundry helped us add 20 ships. That makes our app look more realistic.
But on production, we might have thousands of starships. This page would be
*gigantic* and unusable. It'd probably also take a long time to load, time during
which we are likely to be assimilated!

The solution? *Paginate* the results: show a few at a time - or per *page*.

To do this, we'll use a library called Pagerfanta - what a cool name! It's a generic pagination library
but has great Doctrine integration! Add the two required packages:

```terminal
composer require babdev/pagerfanta-bundle pagerfanta/doctrine-orm-adapter
```

Scroll up to see what this installed. `pagerfanta/doctrine-orm-adapter` is the *glue* between
Pagerfanta and Doctrine.

On our homepage, we're using `findIncomplete()` from `StarshipRepository`. Open
that up and find the method. Change the return type to `Pagerfanta`: an object
with pagination-related superpowers. But you *can* loop over this object like an
array, so leave the docblock as is.

Now, a super important thing to remember when paginating a query is to have a
predictable order. Add `->orderBy('e.arrivedAt', 'DESC')`.

But instead of returning, add this to a variable called `$query`, then remove
`getResult()`: our job changes from *executing* the query to simpy *building*
it. Pagerfanta will handle the actual execution. Return
`new Pagerfanta(new QueryAdapter($query))` and be sure to import these
two classes.

Back in `MainController`, `$ship` is now a `Pagerfanta` object. To use it,
we need to tell it 2 things: how many ships we want on each page - `$ships->setMaxPerPage(5)` -
and which page the user is currently on: use `$ships->setCurrentPage(1)` for now.
Oh and make sure to call `setCurrentPage()` *before* `setMaxPerPage()` or
weird time travel stuff will happen.

Move over... refresh... and look! We're only showing 5 items: the first page.

Back over change to `setCurrentPage(2)` and refresh again.
Still 5 ships, but *different* ships: the second page. Let's peeks at the query
There are multiple! One to count the total number of results and another to fetch
*only* the ones for this page. Pretty darn cool.

Instead of hardcoding the page to 1 or 2 - a temporary and lame solution - let's
read it dynamically from the URL, like with`?page=1` or `?page=2`.

To do that, autowire `Request $request` - the one from `HttpFoundation` -
and change the `setCurrentPage()` argument to `$request->query->getInt('page', 1)`
to read that value and default to 1 if it's missing.

Head back over and refresh. This is page 1 because there is no `page` param. Add `?page=2`
to the URL... we're on page 2!

Ok, what else would be cool? How about showing the total number of ships,
total number of pages, and the current page number?

Back in the controller, Cmd + Click `homepage.html.twig` to open that up.

Put this info below the `<h1>`. I'll change the bottom margin and add
a new `<div>` (with a bit of styling). Inside, write `{{ ships.nbResults }}` ships.
Then: Page `{{ ships.currentPage }}` of `{{ ships.nbPages }}`.

Spin back over and refresh. Perfect! We have 14 total incomplete ships, and we're on page 1 of 3.
Your numbers may vary depending on how many of our 20 ships are randomly set to
an incomplete status.

Ok! What's missing? How about some links to navigate between pages?
Below the list, I'm going to paste in some code. First,
`if ships.haveToPaginate`: no links needed if there is only one page. Then,
`if ships.hasPreviousPage`, lets add a link to the previous page if one exists,
there wouldn't be a previous page if we're on page 1. Inside, generate a URL
to this page: `app_homepage`. But pass a parameter: `page` set to `ships.previousPage`.
Since `page` isn't defined in the route, it'll be added as a `page`
query parameter. That's exactly what we want! Say `Previous`, then repeat for
the `Next` link: if `ships.hasNextPage` and `ships.getNextPage`.

Refresh, scroll down, and sweet! We see a `Next` link! Click it... and now we're on page *2* of 3,
and the URL has `?page=2`. Below, our widget has both `Previous` and `Next` links. Click `Next` again...
page 3 of 3, then `Previous`, back to page 2 of 3. Pagination perfection!

We built these links by hand, which gives us unlimited power to customize. But Pagerfanta
*does* that can generate this for us. If you want to see how, check out the Pagerfanta docs.
The downside is that customizing the HTML is a bit more difficult.

Next, let's add more fields to our `Starship` entity. The best part? Seeing
how easy it is to add that column to the database. Let's do it!
