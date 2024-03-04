# Twig & Templates

I want to return HTML for this page. We *could* put that HTML right inside the
controller... but that's going to get ugly fast. Fortunately, there's a better
way: by using a templating library called Twig.

## Installing Twig

At your terminal, make sure you've committed your changes, because I want to see what
this new package's recipe adds to our project. I've already done that. Install it
with:

```terminal
composer require twig
```

## Composer "Packs"

You probably recognize that `twig` is an alias... this time to a package called
`symfony/twig-pack`. And the word "pack" is important in Symfony. A pack is...
kind of a fake package that helps install *multiple* packages at once.

Watch: open up `composer.json`. Instead of *one* new package in here called
`symfony/twig-pack`, we have *three* new packages... and `twig-pack` isn't even
one of them! 

[[[ code('525a45cd85') ]]]

The three packages give us everything we need for a full, robust
Twig setup. So when you see the word "pack", it's not a huge deal: just a shortcut
to install multiple packages at once.

## Symfony Bundles

Ok, let's see what the recipe did! Run:

```terminal
git status
```

We see the usual `composer.json`, `composer.lock` and `symfony.lock`. But for the
first time, we also see a modification to `config/bundles.php`. A bundle is a PHP
package that integrates with Symfony... it's basically a Symfony plugin. Whenever
you install a bundle, you need to activate it in this `bundles.php` file. But
honestly, the recipe system will always do that *for* us... so it's a good thing
to notice, but we'll never edit this file by hand.

[[[ code('3e93886604') ]]]

## The Twig Recipe

The second thing the recipe did was create a `config/packages/twig.yaml` file. The
purpose of each file in `config/packages/` is to configure a *bundle*.

[[[ code('0fae61d6d1') ]]]

For example, `twig.yaml` controls the behavior of TwigBundle. This line here
tells Twig:

> Hey! All my template files will end in `.twig`.

There's a lot more that we *could* configure, but we don't need to. And we'll
dive deeper into these config files in the next tutorial.

The final thing the recipe did was add a `templates/` directory, which.... you guessed
it! Is where our template files will live! It even started us with a `base.html.twig`
file that we'll talk about in a few minutes.

## Rendering a Template

So let's render our first template! To do that, make your controller extend a base
class called `AbstractController`. Be sure to hit tab so that it adds the `use`
statement on top. Extending this base class is optional, but it gives us a bunch
of shortcut methods.

[[[ code('57df7a6331') ]]]

For example, copy the string and then, to render a template type
`return $this->render()` and pass a filename to a template. Use:
`main/homepage.html.twig`.

[[[ code('f95ec85e49') ]]]

Your template filename can be whatever you want, but the standard is to have a
directory that matches your controller name and a filename that matches your method
name.

Let's go create that! In `templates/`, add a new directory called `main`. And inside
that, a file called `homepage.html.twig`. I'll paste... then add an `h1` and
put it around everything.

[[[ code('79313fecbf') ]]]

Let's do this! Refresh. Got it!

And by the way, what is our controller returning? It's *still* a `Response` object!
I *know* because we have a `Response` return type... and our code isn't exploding.
`render()` is just a shortcut to render this template, grab that string of HTML
and put it into a `Response` object. So even though we're rendering a template,
it still goes back to the idea that a controller returns a response.

## Passing Data to a Template

What about passing data to the template? Maybe we query the database and pass in
the total number of starships. We don't have a database in our
app yet, so let's fake it by saying `$starshipCount` equals... I don't know... 457.
That seems like a believable fake number.

[[[ code('ab8debff59') ]]]

To pass variables to the template, add a second argument to `render()`: an array.
Pass `numberOfStarships` set to `$starshipCount`. The *key* will become the name of
the variable inside the Twig template. 

[[[ code('1477181f00') ]]]

## Rendering Variables

In the template, I'll add a div, and some text. To print the number, write `{{`,
the variable name, close `}}`.

[[[ code('ae442a20f9') ]]]

Ok! Move over and try it. Got it! And we just saw our first Twig code!

Twig is its own language, but it's super friendly. It has just three different
syntaxes. The first is `{{` and I call this the "say something" syntax. If you're
printing something, you'll use `{{`. Inside the curlies, we're writing Twig, which
is *very* similar to JavaScript.

## Twig Tags & the "do something" Syntax

For example, we could print the string `'numberOfStarships'`... or the
variable `numberOfStarships`... or even `numberOfStarships` times 10.

[[[ code('c208f5aa76') ]]]

The second syntax of the three starts with `{%`. I call this the "do something"
syntax. This doesn't print anything. Instead, it's used for language constructs
like `if` statements, for loops or setting a variable.

To do an if statement say `if numberOfStarships > 400`, then close this with
`{% endif %}`. Inside, I'll add a comment.

[[[ code('4445d1588b') ]]]

Try it out! That works too!

Twig is its own library, but it's maintained by Symfony... so its docs live at
https://twig.symfony.com. Click the "Docs" link then scroll down. See the "tags"?
It turns out that there are a *finite* number of things you can use with the
*do* something syntax: it's these tags. Like, you can't say `{% applesauce`...
it just won't work. You can only use `{%` then one of these tags. The list is
pretty short... and I probably only use 5 of these on a daily basis.

The third and final syntax of Twig isn't even a syntax at all: it's for comments.
`{#` to write a comment.

[[[ code('c856ab7a8f') ]]]

## Rendering an Associative Array

So we're passing a simple number to Twig and printing it. But Twig can handle
whatever complex data you throw at it. For example, in the controller, create a
new `$myShip` variable, set to an associative array. Then pass that into the template
as a new variable: `myShip`.

[[[ code('783a5d57f8') ]]]

In the template, add another `div`... some text and a table to print the data.
In the `<td>`, we can't just print `myShip`... because printing an associative array
doesn't make sense in PHP... and so it doesn't make sense in Twig. You get the famous
error about array to string conversion.

What we want is to print the `name` key on that array. The way we do that looks
exactly like JavaScript: `myShip.name`.

That's it! And... it works. I'll paste in the rest of our template, which prints
the other keys from the array. Looking good.

[[[ code('7bba3c747c') ]]]

## Twig Functions & Filters

Twig does have a few other tricks up its sleeve, but nothing complex. It has
functions... which work like functions in any language. It also has something
called tests, which *are* a bit unique to Twig, but simple enough to understand.
My favorite concept is probably filters, which are basically functions
with a cooler, more hipster syntax.

For example, there's a filter called `upper` to send a string to uppercase. To
use a filter, find the string that you want to turn into uppercase then add a
`|` and `upper`.

[[[ code('58924f0878') ]]]

The value on the left gets passed through the filter, a lot like using a pipe
at the command line. It works beautifully.... and you can go crazy with filters:
piping to `upper`, then `lower` then to `title` case *just* to confuse your teammates.

[[[ code('dcd9fd0237') ]]]

Okay, we pretty much just learned all of Twig in one session except for one thing:
template inheritance. That's next.
