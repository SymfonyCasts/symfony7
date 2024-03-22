# Fancier Routes: Requirements, Wildcards, and More

With all the new code organization, let's celebrate by creating another API
endpoint to fetch a *single* `starship`. Start like usual: create
a `public function` called, how about, `get()`. I'll include the optional `Response`
return type. Above this add the `#[Route]` with a URL of `/api/starships/`...
hmm. This time, the last part of the URL needs to be dynamic: it should match
`/api/starships/5` or `/api/starships/25`. How can we do that? How can we make
a route match a wildcard?

The answer is by adding `{`, a name, the `}`.

The name inside this could be anything. No matter what, this route will now match
`/api/starships/*`. But whatever you name this, you're now *allowed* to have an
argument with a *matching* name: `$id`.

Below, dump this to make sure it's working.

[[[ code('b16bd7698e') ]]]

## Restricting the Wildcard to be a Number

Ok! Zoom over to `/api/starships/2` and... it *is* working!

In our app, the ID will be an integer. If I try something that is *not* an
integers - like `/wharf` - the route still matches and calls our controller. And,
that's almost always okay. In a real app, if we queried the database with
`WHERE ID = 'wharf'`, it wouldn't cause an error: it just wouldn't find a matching
ship! And then we could trigger a 404 page, which I'll show you how to do soon.

But sometimes, we *may* want to restrict these values. We may want to say:

> Only match this route if the wildcard is an *integer*.

To do that, inside the curly brace, after the name, add a `<`, `>` and inside,
a regular expression `\d+`.

[[[ code('69b82678c5') ]]]

This means: match a digit of any length. With this setup, if we refresh the `wharf`
URL, we get a 404 error. Our route simply wasn't matched - *no* route matched -
so our controller was never called. But if we go back to `/2`, that still works.

And as an added benefit, now that this only matches digits, we can add
an `int` type to the argument. Now, instead of the string `2`, we get
the `integer` 2. These details aren't super important, but I want you to
know what options you have.

## Restricting the Route HTTP Method

One thing that *is* common with APIs is to make routes only match a certain HTTP
*method*, like `GET` or `POST`. For example, if you want to *fetch* all the
starships, users should make a `GET` request... same if you want to fetch a single
ship. If we kept building our API and created an endpoint that could be used
to create a *new* `Starship`, the standard way to do that would be to use the
*same* URL: `/api/starships` but with a `POST` request.

Right now, this wouldn't work. *Every* time the user requested `/api/starships` - no
matter if they use a `GET` or `POST` request, it would match this *first* route.

For that reason, it's common in an API to add a `methods` option set to an array,
with `GET` or `POST`. I'll do the same thing down here: `methods: ['GET']`.

[[[ code('c62385b5de') ]]]

I can't easily test this in a browser, but if we made a POST request to
`/api/starships/2`, it would *not* match our route.

But we *can* see the change in our terminal. Run:

```terminal
php bin/console debug:router
```

Perfect! Most routes match *any* method... but our two API routes only match
if a `GET` request is made to that URL.

## Prefixing Every Route URL

Ok, I have *one* more routing trick to show you... and this is a fun one! Every
route in this controller starts with the same URL: `/api/starships`. Having
the full URL in each route is fine. But if we want, we can automatically *prefix*
each route's URL. Above the class, add a `#[Route]` attribute with `/api/starships`.

Unlike when we put this above a *method*, this does *not* create a route.
It just says: every route in this class should be prefixed with this URL.
So for the first route, remove the path entirely. And for the second, we only
need the wildcard part.

[[[ code('23887d008f') ]]]

Try `debug:router` again... and watch these URLs:

```terminal-silent
php bin/console debug:router
```

They don't change!

## Finishing the new API Endpoint

Okay. Let's finish our endpoint. We need to find the one ship that matches this
ID. Normally we'd query the database: `select * from ship where id =` this ID.
Our ships are hardcoded right now, but we can still do something that will
look pretty much exactly like what it will, once we *do* have a database.

We already have a service - `StarshipRepository` - whose whole job is to fetch
starship data. Let's give it a new superpower: the ability to fetch a *single*
`Starship` for an id. Add `public function find()` with an `int $id` argument that
will return a nullable `Starship`. So, a `Starship` if we find one for this id,
else `null`.

Right now, the easiest way write this logic is to loop over `$this->findAll()`
as `$starship`... then if `$starship->getId() === $id`, return `$starship`. I'll
change my `uf` to `if`. Much better.

And if we didn't find anything, at the bottom, `return null`.

[[[ code('28e91b0460') ]]]

Thanks to this, our controller is *so* simple. First, autowire the repository
by adding an argument: `StarshipRepository` and just call it `$repository`. By
the way, the order of arguments in a controller doesn't matter.

Then `$starship = $repository->find($id)`. Finish at the bottom with
`return $this->json($starship)`.

[[[ code('89a45b2246') ]]]

I think we're ready! Refresh. It's perfect!

## Triggering a 404 Page

But try an id that does *not* exist in our fake database - like `/200`.
The word `null` is... *not* what we want. In this situation, we should return a
response with a 404 status code.

To do that, we're going to follow a common pattern: query for an object,
then check if it returned anything. If it did *not* return something, trigger 
a 404. Do that with throw `$this->createNotFoundException()`. I'll pass this a
message.

[[[ code('d378dcbf47') ]]]

Notice the `throw` keyword: we're throwing a special exception that triggers
a 404. That's nice because, as soon as it hits this line, nothing
*after* will be executed.

Try it out! Yes! A 404 response! The message - "Starship not found" - is only
shown to developers in dev mode. In production, a totally different page - or JSON -
would be returned. You can check the docs for details on production error pages.

Next: let's build the HTML version of this page, a page that shows details about
a *single* starship. Then we'll learn how to link between pages using the route name.
