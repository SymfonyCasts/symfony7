# Creating a Form Type Class

## Introduction: The Power of Forms

Forms are everywhere. Login boxes, search fields, admin tooling, checkout
steps — if your app lets humans type things, congrats, you’re in Form Land.
And even though front-ends today might be buzzing with Ajax, Turbo, and
fancy JavaScript libraries, the core job is still the same old classic: grab
some data, check it, and do something meaningful with it. And that’s exactly
where Symfony's Form component steps in wearing a cape. It wrangles request data,
applies validation, protects you from CSRF villains, and generally keeps you
from hand-crafting HTML inputs like it's 1995.

So let's get our hands dirty and create our first form!

## Getting Started with the Course Code

To follow along, download the course code from this page and extract it.
Inside, you'll find a `start/` directory containing the course project.
Open its `README.md` file and you'll find instructions to get the website up
and running. I've already done this part, so I'll just launch the built-in
Symfony web server in my terminal with:

```terminal
symfony serve -d
```

Then, open the site by holding `Cmd` and clicking at the URL in the output.
Welcome to Starshop, your one-stop shop for all your essential Starship
parts — hyperdrive couplers, warp coils, left-handed quantum stabilizers,
you know, the usual stuff. Let's head over to Parts page.

Business is booming in our little corner of the Galaxy, so now we need
to create new Starship parts in the admin area. I've already added
a link to "Create a new part". The only issue is that the actual form
is still a work in progress. Let's fix that!

## Installing the Symfony Form Component

Before we can build forms, we need the Symfony Form component itself.
Install it using:

```terminal
symfony composer require form
```

Running `git status` will show us that the installation brought some new config
files. Perfect, now we're ready to start building. We could build the form
manually, field by field, line by line, but that's a surefire way to drain
our life force.

## Creating a Form Type Class with Maker

Instead, let's let Maker bundle help us. Run:

```terminal
symfony console make:form
```

Here's a quick tip: if you haven't installed the Form component yet, Maker
will tell you exactly what command you need to run. Maker is like that
friendly coworker who gently reminds you that you forgot to run
`composer install`... again.

You can name the form whatever you like, but it's common practice to end
form classes with "Type". For instance, since we're creating Starship
parts, I'll name this one `StarshipPartType`. Next, maker will ask if we
want to map the form to an entity. We certainly do in this case, so choose the
`StarshipPart` entity. That's the entity we'll be creating with this form.
Once that's done, maker will create a new file in the `src/Form/` directory.
Now, open it up in PhpStorm.

The class extends `AbstractType`, which comes from the Form component we
just installed. Maker has also conveniently added a form field for every
property on the entity. We don't really want users editing timestamps
though (unless we're looking for time travel paradoxes), so let's get rid
of those fields. Below, in the `configureOptions()`, you'll spot the
`data_class` option set to the `StarshipPart::class`. That binds this
form to that entity. Quite handy!

## Creating the Form Object with `createForm()`

Now it's time to create a form object using our new form type. The controller
behind this page is `AdminController` and the action is `newStarshipPart`.
Let's open it up. Inside, we'll create a form object with
`$form = $this->createForm...`. 

Symfony kindly provides two useful methods, `createForm()` and
`createFormBuilder()`. The latter lets you build a form right inside
the controller, perfect for quick prototypes. However, best practice is to
create a dedicated form type class, like the one we just crafted.

So, we'll stick with `$this->createForm()`. Pass our `StarshipPartType::class`,
and voila, we've got a form! If we `dd($form)` below and refresh the page,
you'll see it's a Form object — a fully-fledged PHP object with all the
logic. Let's pass it to the template with `'form' => $form` and remove the
`dd($form)` statement. Now we can render it in the template. 

## `Form` Object in PHP vs `FormView` one in Twig

But there's a twist! If you first dump it in Twig with `{{ dump(form) }}` and
refresh the page again, you'll see that it's a `FormView` object instead.
That's Symfony quietly doing you a favor converting the internal Form object
into a simpler *view model* that can be rendered in Twig. They might
look similar, but remember, they're technically different objects.

Older versions of Symfony required developers to explicitly pass
the `FormView` object to the template for rendering. That's why you might
see `$form->createView()` calls in legacy projects.

But with the latest version of Symfony, this is handled automatically,
so you don't need to worry about it. In fact, to make it work properly with
Turbo, you should now always pass the internal form object. 

Okay, up next, we'll render and submit the form for real. Are you ready to
dive in?
