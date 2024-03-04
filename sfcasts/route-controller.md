# Routes, Controllers & Responses

Ok, here's the scoop. Wesley Crusher - everyone's favorite ensign from Star Trek -
has retired from Starfleet and is working with *us* to start a new business: Wesley's
Star Shop. Someone's gotta break the Ferengi monopoly on the galaxy's starship repair
business, and he's hired *us* to build the site to do that. We're about to give
the Ferengi a run for their latinum!

## Creating the Controller

And it starts with building our first page. The idea behind every page is always the
same. Step one, give it a cool URL. That's called the route. Step two, write a PHP
function that *generates* the page. That's known as the controller. And that
page could be HTML, JSON, ASCII art, anything.

In Symfony, the controller is always a method inside a PHP class. So, we need to
create our first PHP code! Where does PHP code live in our app? That's right, the
`src/` directory.

Inside this `src/Controller/` directory, create a new file. I would normally select
new "PHP class", but for this first time, create an empty file. We'll do each
part by hand. Call it `MainController.php`, but you can name this whatever you want.

Inside, add the open PHP tag, and then say `class MainController`. Above this, add
a namespace of `App\Controller`.

[[[ code('51ebc199a2') ]]]

## Namespaces & Directories

Okay, a few things about this. First, the fact that I put this class inside
a directory called `Controller` is optional. That's just a convention. You
could rename this to whatever the Klingon word for `Controller` is and everything
would the same... and probably be more interesting!

*However*, there *are* a few rules about PHP classes in general. The first is that
every class *must* have a namespace *and* that namespace needs to match your
directory structure. It's always going to be `App\` then whatever directory you're
inside. Without going into too much detail, that's a rule you'll find in *every*
PHP project.

The second rule is that your class name must match your filename `.php`. If you
mess either of these up, you'll get an error from PHP that it can't find your class.
The Ferengi never make this mistake.

## Creating the Controller Method & Route

Anyway, our goal is to create a *controller*, which is a *method* in a class
that builds the page. Add a new public function and call it `homepage`. But, again,
the name doesn't matter. And... yea! It's not done yet, but *this* is our controller!

[[[ code('77472b5890') ]]]

But remember, a page is the combination of a controller and a *route*, which defines
the page's URL. Where do we put the route? Right *above* the controller method
using a feature of PHP called an *attribute*. Write `#[]` then start typing
`Route` with a capital `R`. Check out that auto-completion!

Either option will work, but use the one from `Attribute` - which is newer - then
hit tab. When I did that, something super important happened: my editor added a
`use` statement at the top of the class. Anytime you use a PHP attribute, you
*must* have a corresponding `use` statement for it in the same file.

These attributes work almost like PHP functions: you can pass a bunch of
arguments. The first one is the path. Set this to `/`.

[[[ code('08f356a54e') ]]]

Thanks to this, when someone goes to the homepage - `/` - Symfony will call this
controller method to build the page!

## Controllers & Responses

What... should our method return? Just the HTML we want, right? Or the JSON
if we're building an API?

*Almost*. The web works on a well-known system. First, a user *requests* a page.
They say:

> Hey, I want to see `/products`... or I want to see `/users.json`.

What we return back to them, yes, contains HTML or JSON. But it's more than that.
We also communicate back a status code - which says whether the response was
okay or had an error - as well as these things called headers, which communicate
a bit more info, like the format of what we're returning.

This whole beautiful package is called the *response*. So yeah, most of the time,
we'll just be thinking about returning HTML or JSON. But what we're *truly* sending
is this bigger, nerdier thing called a *response*.

And so our entire job as web developers - no matter *what* language
we're programming in - is to understand the request from the user, then create and
return the response.

And this brings us back to something I *love* about Symfony. What does our controller
return? A new `Response` object from Symfony! And again, PhpStorm wants to
auto-complete this, suggesting a few different `Response` classes. We want the one
from the Symfony `HttpFoundation` component. That's the Symfony library that contains
everything related to requests & responses.

Hit tab. Once again, when we did that, PhpStorm added a `use` statement at the top
of the file. I'm going to use this trick *constantly*. Anytime you reference a class
name, you *must* have a corresponding `use` statement, else PHP will give you an
error that it can't find the `Response` class.

Inside this, the first argument is the content that we want to return.
Start with a hardcoded string.

[[[ code('1df1b30322') ]]]

Route, check! Controller that returns a Response, check! Let's try this. Back at
the browser, this page was just a demo that shows before we have a *real* homepage.
Now that we do, when we refresh... there it is!

I know it's not much yet, but we just learned the first *fundamental* part of
Symfony: that every page is a route & controller... and that every controller returns
a response.

Oh, and it's optional, but because our controller always returns a `Response`, we
can add a `Response` return type. That doesn't change how our code works, but it
makes it more descriptive to read. And if we ever did something silly and returned
something *other* than a response, PHP would give us a clear reminder.

[[[ code('5d504440d6') ]]]

Next up: to supercharge our development, let's install our first third-party
package and learn about Symfony's amazing recipe system.
