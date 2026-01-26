# Embracing Entity's CRUD Operations

Hey there, fellow devs! Today, I want to talk about one of my favorite
tools to boost productivity — CRUD. No, not the icky stuff, but the
*create*, *read*, *update*, and *delete* operations that are the backbone
of most admin sections. Instead of spending precious time writing controllers,
forms, and templates manually, Symfony has a trusty helper that takes care
of all the tedious stuff for us. Time to call upon our reliable ally,
MakerBundle again.

Let's hop over to the terminal and run:

```terminal
symfony console make:crud
```

Once you do, MakerBundle springs into action, asking for some good questions.
For the entity, let's go with `Starship`. For the controller name, we
already have a `StarshipController`, but since we're dealing with admin
stuff, let's name it `StarshipAdminController`. As for PHP unit tests, I'll
skip them for now and — you guessed it — it's homework time for you.

Hit enter and, wow! This time it created a ton of files. A controller,
a form type, and a heap of templates for listing, showing, creating,
and editing entities. This is the kind of boilerplate you'd rather not
write by hand every time.

## Fixing the Problem with Enums on the List Page

Now, let's peek inside the newly minted controller. In PhpStorm, I'll navigate
to the `StarshipAdminController` in our `src/Controller/` directory. The first
thing I want to change is the route path. `Maker` chose a sensible one,
but I like consistency across my admin endpoints, so I'll tweak the route
to `/admin/starship`. Perfect!

Open that `/admin/starship` URL in the browser. Ah, the error! It says:

> Object of class `StarshipStatusEnum` could not be converted to a string.

Classic issue. Let's fix this by opening the template responsible for this
endpoint: it should be `starship_admin/index.html.twig`. Currently, it
attempts to render the status of the Starship directly, but as it's not
a string.

If you open `Starship` entity - you will see that the status property is
an object of `StartshipStatusEnum`. Enums aren't strings, they are objects,
and we need to explicitly access their values.

Even though MakerBundle did a lot of heavy lifting for us, it seems it
doesn't fully grasp PHP enums yet. But fear not, we've got this. All we need
to do is replace `starship.status` with `starship.status.value` in the template.

After refreshing the page we have a lovely list of all the Starships in our
database, complete with some handy actions we can perform on them, like show
and edit.

## Fixing Enums on the Show page

Click on the Show link - it reveals the same error. But now that we're
seasoned problem solvers, let's find the controller and action responsible
for it, and apply the same fix.

WDT tell us that the responsible action for this page is `show()`. Find it in
the `StarshipAdminController`, and update the field to `starship.status.value`.

Now refresh the page - everything is back to normal. We can now see the
individual Starship details and, more importantly, edit or delete them.

If I click on the Delete button - it triggers a JavaScript confirmation dialog.
A small but significant detail to prevent accidental deletions — a feature
I really appreciate.

I will cancel click Edit - we encounter another but similar error, however
this time it's thrown from the default Symfony form theme:

> An exception has been thrown during the rendering of the template
> ("Object of class `StarshipStatusEnum` could not be converted to string")
> in `form_div_layout.html.twig.


## Fixing the Form Pages

Find the `edit()` action in the controller and open the related template:
`edit.html.twig` file. Now, this file doesn't contain what we need, but
it does include another template: `_form.html.twig`. Follow up by opening it.

This is where the form is rendered. If you open `starship_admin/new.html.twig`
you will see that we're using the same form for both new and edit actions,
and the only difference is the label on the submit button, which we pass as
an argument to this `_form.html.twig`.

But we don't render that status field manually. The problem this time lies
in the form type, not the Twig template. MakerBundle created `StarshipType`
for us - open it up in the `src/Form/` directory. The status field
here is the culprit, seems enum form field type guessing doesn't work too
well for Enums yet.

No worries, we can explicitly specify the type. Pass `EnumType::class` as
the 2nd argument and go refresh the page. Another error:

> The required option `class` is missing for this EnumType.

Symfony's error messages are quite helpful, so you may already have an inkling
of the problem and how to fix it. But let's confirm this. In your terminal, run
already familiar command:

```terminal
symfony console debug:form EnumType
```

It will show that the `class` option is required for this form type,
and it must point to the concrete Enum class. In our case, that'd be
`StarshipStatusEnum`.

Add an empty array as the 3rd argument, and inside set `class` option to
`StarshipStatusEnum::class`.

Refresh the page again. Voila! The form renders correctly, and we can edit
the details and update the entity. Everything works as expected. There are
no flash messages, MakerBundle didn't add those, but you can add it if
you want - you have complete control on that.

On the list page, scroll down, and you'll find a "Create new" link. Clicking
it brings up the same form, but with no prefilled data — perfect for
creating a brand-new Starship, yeah? This is one of the best parts of CRUD
generation - one form that's reused for both create and update operations.

## Improve Styling of the CRUD pages

OK, let's be honest. The generated code now works great, but visually it's
not the winning design award. I'll quickly spruce up some styling to it and
speed it up for you a little big, but don't worry, you can copy/paste the
same code from the code blocks below the video.

I will paste some Tailwind CSS classes to the form buttons. Also, some HTML
with proper CSS classes to make the pages look better in `edit.html.twig`,
`index.html.twig`, `new.html.twig`, and finally `show.html.twig`.

Once we're done, head back to the browser and refresh the page - the new styles
lend a cleaner layout and more intuitive buttons, including a "Create new"
button at the top for easy access. It looks much better now!

The important thing is that we've made just styling tweaks, the core
functionality is still 100% generated by the MakerBundle. It gives you
a very good start, but you can take the control over if you want. You can
even add flash messages that we're missing here. It's up to you!

## Applying Symfony form theme Globally

The only detail left - our Starship form looks different from the StarshipPart
form. We can apply the Tailwind CSS default form theme in the `_form.html.twig`,
just as we did for the StarshipPart form. But wait! I'd rather not repeat this
for every form in my app.

Instead, let's apply that theme globally for all forms in your app. How?
In the terminal, run:

```terminal
symfony console config:dump twig
```

And find the `form_themes` key in the output, somewhere in the beginning.
Here it is! It's set to the default Symfony form theme, but we can override
it in the config.

I will open the `new.html.twig` for the StarshipPart and comment out
the form theme tag. Next, copy the theme template name and go to
`config/packages/twig.yaml` file.

Below the key, add that `form_themes` option, and below, add
`- tailwind_2_layout.html.twig`. That's it! Now, all forms automatically
use the Tailwind CSS theme. And yes, you're right, it's a list so you can
add more themes here that's useful for applying some patches and customization
to the default form theme. But for now, I will keep things simple.

Head back to the browser to make sure the form was applied for both new and
edit pages, and let's make sure our StarshipPart form still uses it too
even after we commented out that form applying in the template - yes, look
great, no regression.

And the best part is that even though we set that theme globally, you can still
override it by applying another theme directly in the template targeting
a specific form, like we did in the beginning.

## Wrapping Up

In a flash, we've got a rich controller with CRUD operations and a solid
foundation we can customize to our heart's content. MakerBundle handles
the boring stuff, allowing us to focus on awesome things.

***SEEALSO
If you want an even more powerful admin generator for your Symfony application
with already implemented CRUD operations and other cool features, take a look at
[EasyAdminBundle course](https://symfonycasts.com/screencast/easyadminbundle).
***

Up next, we'll create a new form and send it via the GET method. But for now,
enjoy your freshly generated CRUD and go add more starships to your fleet!

