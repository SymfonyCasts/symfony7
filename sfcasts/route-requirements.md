# Fancier Routes: Requirements, Wildcards, and More

With all of the new code organization, let's celebrate by creating another API
endpoint to fetch a single `starship`. We'll start like usual: create
a `public function` called, how about just `get()`. I'll inclue the optional `Response`
return type. Above this add the `#[Route]` with a URL set to `/api/starships/`...
hmm. This time, the last part of the URL needs to be dynamic: it should match
`/api/starships/5` or `/api/starships/25`... and then we'll return the JSON for
the ship with that id. How can we do that? How can we make a route match a
wildcard?

The answer is by adding `{`, a name, the `}`.

The name inside this could be anything. No matter what, this route will now match
`/api/startships/*`. But whatever you name this, you're now *allowed* to have an
argument with a matching *name*: `$id`.

Below, dump this so we can make sure it's working.

## Restricting the Wildcard to be a Number

Ok! Head over to `/api/starships/2` and... it works! And by the way, we don't always
care, but in our app, the ID will be an integer. If try something that is *not* an
integers - like `/wharf` - the route still matches and calls our controller. And,
that's almost always okay. In a real app, if we queried the database
`WHERE ID = 'wharf'`, it wouldn't cause an error: we just wouldn't find any matching
ships! And then we could trigger a 404 page, which I'll show you how to do soon.

But sometimes, we *do* want to restrict these values: we want to say:

> Only match this route if the wildcard is an *integer*.

To do that, inside the curly brace, after the name, add a `<`, `>` and inside,
a regular expression `\d+`.

This means: match a digit of any length. With this setup, if we refresh the `wharf`
URL, we we get a 404 error. Our route simply wasn't matched - *no* route matched -
so our controller was never called. But if we go back to `/2`, that still works.

And as an added benefit, now that we know this will only match digits, we can add
an `int` type to the argument. Thanks to this, instead of the string `2`, we get
the `integer` 2. These are details that aren't super important, but I want you to
know how things are working and what options you have.

## Restricting the Route HTTP Method

One thing that *is* common with APIs is to make routes only match a certain HTTP
*method*, like `GET` or `POST` in an API. For example, if you want to *fetch* all the
starships, you should use a `GET` request... same if you want to fetch a single
ship. Imagine if we kept building our API and created an endpoint that can be used
to create a *new* `Starship`. The standard way to do that is to have use the same
URL: `/api/starships` but with a `POST` request.

Right now, this wouldn't work. *Every* time the user requested `/api/starships` - no
matter if they use a `GET` or `POST` request, it will match this *first* route.

For that reason, it's common with an API to add a `methods` option  to an array,
where with `GET` or `POST`. I'll do the same thing down here: `methods: ['GET']`.

I can't easily test this in a browser, but if we made a POST request to
`/api/starships/2`, it would *not* match our route.

But we *can* see the change in our terminal. Run:

```terminal
php bin/console debug:router
```

Perfect! Most routes match *any* method... but our two API routes will only match
if a `GET` request is made to that URL.

## Prefixing all of the Route URLs

Ok, I have *one* more routing trick to show you... and this is a fun one. Notice
that every route in this controller starts with the same URL: `/api/starships`. It's
fine to have the full URL inside every route. But if you want, we can add a prefix
to every route. Above the class, add `#[Route]` attribute with `/api/starships`.

Now, unlike when we put this above a *method*, this does *not* create a route.
It just says: every single route in this class should be prefixed with this URL.
So for the first route, remove the path entirely. And for the second, we only
need the wildcard part.

Try `debug:router` again - watch these URLs:

```terminal-silent
php bin/console debug:router
```


They don't change!

## Finishing the new API Endpoint

Okay. Let's finish our end point. We need to find the one ship that matches this
ID. Normally we'd query the database: `select ** from ship where id =` this ID.
Our ships are hardcoded right now, but we can still do something that's going to
look pretty much exactly like what it will once we *do* have a database.

We already have a service - `StarshipRepository` - whose whole job is to fetch
starship data. Let's give it a new superpower: the ability to fetch the *single*
`Starship` for an id. Add `public function find()` with an `int $id` argument that
will return a nullable `Starship`: so a `Starship` if we find one for this id, else
`null`.

Right now, the easiest way write this logic is to loop over `$this->findAll()`
as `$starship`... then if `$starship->getId() = $id`, return `$starship`. I'll
change my `uf` to `if`. Much better.

And if we didn't find anything, at the bottom, `return null`.

Thanks to this, our controller is *so* simple. First, autowire the repository
by adding an argument: `StarshipRepository` and just call it `$repository`. The
order of arguments in a controller doesn't matter.

Then `$starship = $repository->find($id)`. Finish at the bottom with
`return $this->json($starship)`.

I think we're ready! Refresh. It's perfect!

## Triggering a 404 Page

Though... if we try an id that does not exist in our fake database - like `/200` -
the word `null` isn't what we want. In this situation, we should return a 404
response.

To trigger that, we're going to follow a really common pattern: query for an object,
then check if that returned anything. If it did *not* trigger a 404. Do that in
Symfony by saying: throw `$this->createNotFoundException()`. I'll pass this a message.

Notice the `throw` keyword: we're throwing a special exception object that triggers
a 404. That's nice because it means that, as soon as it hits this line, nothing
*after* will be executed.

Try it out! Yes! A 404 response - and the message - "Starship not found" - is only
shown to developers in dev mode. In production, a totally different page - or JSON -
will be returned. You can check the docs for details on production error pages.

Next: let's build the HTML version of this page, a page that shows details about
a *single* starship. Then we'll learn how to link between pages via the route name.
