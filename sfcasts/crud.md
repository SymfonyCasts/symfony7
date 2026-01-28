# Embracing Entity's CRUD Operations

Let's talk about CRUD! No, not the icky stuff, but the
*create*, *read*, *update*, and *delete* operations that are the backbone
of most admin sections. Instead of spending precious time writing controllers,
forms, and templates manually, Symfony has a trusty helper that takes care
of all the tedious stuff for us. Time to call upon our reliable ally,
the MakerBundle.

Hop over to the terminal and run:

```terminal
symfony console make:crud
```

Once you do, MakerBundle springs into action, asking some good questions.
For the entity, let's go with `Starship`. For the controller name, we
already have a `StarshipController`, but since we're dealing with admin
stuff, let's name it `StarshipAdminController`. As for PHP unit tests,
skip them for now.

Hit enter and, wow! This time it created a ton of files. A controller,
a form type, and a heap of templates for listing, showing, creating,
and editing the `Starship` entity. This is the kind of boilerplate you'd rather not
write by hand every time.

## Fixing the Problem with Enums on the List Page

Now, let's peek inside the newly minted controller. In PhpStorm, I'll navigate
to the `StarshipAdminController` in our `src/Controller/` directory. The first
thing I want to change is the route path. `Maker` chose a sensible one,
but I like consistency across my admin endpoints, so I'll tweak the route
to `/admin/starship`. Perfect!

Open that `/admin/starship` URL in the browser. Ah, an error! It says:

> Object of class `StarshipStatusEnum` could not be converted to a string.

Classic issue. Let's fix this by opening the template responsible for this
endpoint: `starship_admin/index.html.twig`. Currently, it
attempts to render the status of the Starship directly, but it's not
a string.

If you open the `Starship` entity - you will see that the status property is
a `StartshipStatusEnum`. We'll need to explicitly access its value.

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

The web debug toolbar tells us `StarshipAdminController::show()` is the culprit.
Find that method... jump to the `show.html.twig` template, and update the
field to `starship.status.value`.

Now refresh the page. Cool! We can now see the
individual Starship details and, more importantly, edit or delete them.

If I click on the Delete button - it triggers a JavaScript confirmation dialog.
A small but significant detail to prevent accidental deletions — a feature
I really appreciate.

Cancel that and click Edit - we encounter another similar error, but
this time it's thrown from the default Symfony form theme:

> An exception has been thrown during the rendering of the template
> ("Object of class `StarshipStatusEnum` could not be converted to string")
> in `form_div_layout.html.twig.

## Fixing the Form Pages

Find the `edit()` action in the controller and open the related template:
`edit.html.twig`. Now, this file doesn't contain what we need, but
it does `include()` another template: `_form.html.twig`. Follow up by opening it.

This is where the form is rendered. If you open `starship_admin/new.html.twig`
you will see that we're including the same form for both new and edit actions.
The only difference is the `button_label` we pass as an argument to the `include()`.
The purpose of this template is to avoid code duplication.

Back in `_form.html.twig`, we're not rending that status field manually... The entire
form is being rendered with this `form_widget(form)` call.

Luckily, this fix is done in the form type class. MakerBundle created `StarshipType`
for us - open it up in the `src/Form/` directory. The `status` field
here is the culprit, it seems form field type guessing doesn't work for enums.

No worries, we can explicitly specify the type. Pass `EnumType::class` as
the 2nd argument and go refresh the page. Another error:

> The required option `class` is missing for this EnumType.

Symfony's error messages are quite helpful, so you may already have an inkling
of the problem and how to fix it. But let's confirm this. In your terminal, run an
already familiar command:

```terminal
symfony console debug:form EnumType
```

It will show that the `class` option is required for this form type,
and it must point to the concrete Enum class. In our case, that'd be
`StarshipStatusEnum`.

Add an empty array as the 3rd argument, and inside, set the `class` option to
`StarshipStatusEnum::class`.

Refresh the page again... and... great! The form renders correctly, we can edit
the details, and update the entity. Everything works as expected!

On the list page, scroll down, and you'll find a "Create new" link. Clicking
it brings up the same form, but with no prefilled data — perfect for
creating a brand-new Starship. This is one of the best parts of Maker's CRUD
generation - one form that's reused for both create and update operations.

## Improve Styling of the CRUD pages

OK, let's be honest. The generated code now works great, but visually it's
not winning any design awards. I'll quickly spruce up some styling, but don't worry,
you can copy/paste the same code from the code blocks below the video.

I'll paste some Tailwind CSS classes to the form buttons. Also, some HTML
with proper CSS classes to make the pages look better in `edit.html.twig`...
`index.html.twig`... `new.html.twig`... and finally `show.html.twig`.

Once we're done, head back to the browser and refresh the page - much better!
The new styles lend a cleaner layout and more intuitive buttons, including a "Create new"
button at the top for easy access.

The important thing is that we've made just styling tweaks, the core
functionality is still 100% generated by the MakerBundle. It gives you
a very good start, but you can take the control over this if you want too by tweaking
the generated `StarshipAdminController`. A good start could be adding flash messages.
But it's up to you!

## Applying Symfony form theme Globally

The only detail left - the `Starship` form clearly isn't using the Tailwind CSS form template.
We can apply it in `_form.html.twig`, just as we did for the `StarshipPart` form.
But wait! I'd rather not repeat this for every form in my app.

Instead, let's apply that theme globally for all forms in our app. How?
In the terminal, run:

```terminal
symfony console config:dump twig
```

And find the `form_themes` key in the output, somewhere in the beginning.
Here it is! It's set to the default Symfony form theme, but we can override
it in the config.

Open `new.html.twig` for the StarshipPart and comment out
the form theme tag. We won't need this anymore. Next, copy the theme template name and go to
`config/packages/twig.yaml`.

Below the key, add that `form_themes` option, and below, add
`-`, paste: `tailwind_2_layout.html.twig`. That's it! Now, all forms automatically
use the Tailwind CSS theme. And yes, this config is a list so you can
add more themes here. That's useful for applying patches and customization
to the default form theme. But for now, I will keep things simple.

Head back to the browser to make sure the form was applied for both the new and
edit pages, and make sure our `StarshipPart` form still uses it too.
Yep, looks great, no regression.

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

Up next, we'll create a new form and send it via the GET HTTP method. But for now,
enjoy your freshly generated CRUD and go add more starships to your fleet!

