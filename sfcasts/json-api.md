# Creating JSON API Endpoints

If you want to build an API, you can *absolutely* do that with Symfony. In fact,
it's a *fantastic* option in part, because of API Platform. That's a framework
for creating APIs built on top of Symfony that both makes building your API fast
*and* creates an API that's more robust than you could imagine.

But, it's also simple enough to return JSON from a controller. Let's see if we
can return some ship data as JSON.

## Creating the new Route & Controller

This will be our *second* page. Well, it's really an "endpoint", but this will
be our second route & controller combo. In `MainController`, we could add another
method here. But for organization, let's create a totally new controller class.
I'll go to New -> PHP Class and call it `StarshipApiController`.

Because I went to New -> PHP Class, it created the class and the namespace for me.
Super nice! Also, going forward, each time I create a controller, I'll immediately
extend `AbstractController`... because those shortcuts are nice and there's no
downside.

Add a `public function getCollection()` because this will return info about a
*collection* of starships. And, like always, you can add the `Response` return
type or skip it. Above this, add the route with `#[Route()]`. Select the one from
`Attribute` and hit tab.

So I just used auto-completion to add the `use` statements for `AbstractController`,
`Route`, *and* `Response`. Make sure you have all of those. For the URL, how about
`/api/starships`.

Inside, I'll paste a `$starships` variable that's set to an array of three
associative arrays of starship data.

## Returning JSON

You can probably imagine how this will look as JSON. How do we *turn* it into
JSON? Well, it can be this simple: `return new Response` with
`json_encode($starships)`.

But we can do better! Instead, return `$this->json($starships)`.

Let's try it! Find your browser and head to `/api/starships`. Dang, that was easy.
If you're wondering why the JSON is styled and looks cool, that's not a Symfony thing.
I have a Chrome extension installed called JSONVue.

## Adding a Model Class

Now in the real world, when we start querying the database, we're going to be working
with *objects*, not associative arrays. We won't add a database in this tutorial, but
we *can* start using objects for our data to make things more realistic. In the
`src/` directory, create a new subdirectory called `Model`.

Ok, important thing: what we're about to do has absolutely *nothing* to do with
Symfony. I'm simply looking at this array and thinking:

> You know what? Instead of passing around this associative array with `name`,
> `class`, `captain`, and `status` keys, I'd rather have a `Starship` class and
> pass around *objects*.

So entirely on my own, independent of Symfony, I've decided to create a `Model`
directory - this could be called anything - and inside a new class called `Starship`.
And *because* this class is just to help *us*, we get to make it look *however*
we want, and it doesn't need to extend any base class.

Create a `public function __construct()` with five properties: a `private int $id`,
then four more properties for each of the four keys that we have in the array:
`private string $name`, `private string $class`, `private string $captain` and
`private string $status`.

Oh, and my editor is highlighting this file because we installed PHP-CS-Fixer
and that found a code style violation. I can click this to fix it or go here and
hit Alt+Enter to do the fix *there*. Super nice!

Anyway, if you're not familiar with this constructor syntax, this creates a
constructor with five arguments *and*, at the same time, creates five properties
that will be set to whatever we pass *to* these arguments.

But, because I decided to make these properties private, if we *did* instantiate
a new `Starship` object... we wouldn't be able to read any of the data! To allow
that, we can create getter methods. But, I'm not going to do this by hand. Instead,
go to the Code -> Generate menu option - or Cmd + N on a Mac - select getters then
generate a getter for *every* property.

Nice! Five shiny new, public getter method.

## Creating the Model Objects

Ok, back in our controller, let's convert these arrays to objects: `new Starship()` -
hit tab, so it adds the `use` statement - then give this an id of, how about, 1...
and transfer the other values for `name`, `class`, `captain`, and finally `status`.

And just like that, we have our first object! I'll highlight the other two
arrays and paste in the two objects to save time.

We now have an array of 3 `Starship` *objects*... which feels nicer. And we're
passing those to `$this->json()`. Is that still going to work? Totally not! We get
an array of three *empty* objects!

That's because, internally, `$this->json()` uses the PHP `json_encode()` function...
and that function can't handle private properties. What we need is something smarter:
something that can recognize that, even though the `name` property is private, we
have a public `getName()` method that can be called to read that property's
value.

## Hello Symfony Serializer

Is there a tool that does that? Well, remember how Symfony is a *huge* set of
components that solve individual problems? One component is called *serializer*,
and its *whole* job is to take objects and serialize them to JSON... or take JSON
and deserialize that *back* into objects. And it can *totally* handle situations
where you have private properties with public getter methods.

So let's get it installed!

```terminal
composer require serializer
```

And once more folks, yes, this is an *alias*... and it's an alias to a *pack*. This
pack installs the `symfony/serializer` package as well as a few others that make
it work in a really robust way.

Now, without doing *anything* else, go back, refresh, and it works? How?

It turns out that the `$this->json()` method is smart. To peek at it, hold
Command on a Mac or Ctrl on other machines and click the method name to jump into
the core Symfony file where this lives.

Ah! The code here won't make total sense yet, but it detects *if* the serializer
system is available.... and if it is, uses *that* to transform the object to JSON.

But, what do I mean by "serializer system" exactly? And what is the `serializer`
key... inside this container thing? Or, what if we needed to transform an object
to JSON somewhere *other* than our controller... where we don't have access to the
`->json()` shortcut? How could we access the serializer system from *there*?

Friends, it's time to learn about *the* most important concept in Symfony: services.
