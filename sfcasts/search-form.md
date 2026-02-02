# Forms Without a Data Class

We're almost at the finish line of our journey with Symfony Forms.
But before we wrap it up, let's dive into something both fun and practical.
If you navigate to the `/parts` page, you'll see a simple search input.
It's a basic HTML form, and that's all good. But here's a thought: can we
recreate this using Symfony Forms? You bet!

## Creating a Form Without an Entity

When we discuss Symfony Forms, we usually link them to an entity or a data
class. However, this isn't set in stone. Symfony Forms can stand alone,
without any object mapping happening behind the scenes. All the form data
is neatly stored in plain PHP arrays. Our search form is a great example of
this.

Sure, we could build this form right in the controller using the form
builder. But to keep things neat and tidy, let's stick with the form type again.
Plus, we'll pick up a few handy tricks on the way.

Head to your terminal and run the following command:

```terminal
symfony console make:form
```

Name the form `PartSearchType`. This time around, when it asks for an
entity or data class, leave it blank. Next up, locate the generated `PartSearchType` in
the `src/Form` directory and open it. You'll see a placeholder field named
`field_name`. Swap that out with `query` to match the name of the legacy
search input.

## Using the Form in the Controller

Now, head over to the `index()` action in `src/Controller/PartController.php`.
At the start, create a form with `$this->createForm()` passing 
`PartSearchType::class` and store it in a variable named `$searchForm`.
Then, pass it to the template.

In `templates/part/index.html.twig`, render the whole form with
`{{ form(searchForm) }}`. For now, let's keep the original so that we
can compare them. We'll tidy this up later.

After refreshing your browser, you'll notice two search inputs. The new kid
on the block has a label, while the old one is label-free. Let's sort that
out first.

## Hiding the Form Field Label

Back in `PartSearchType`, for the `query` field, pass `null` for the type,
and an array for options. Within it, add the `label` option. You can set
it to any string that you want to be the label of the field. Or, just set it
to `false`. This instructs Symfony not to render the label at all.

While we're here, let's polish things up a bit. Add the `attr` option
for attributes, and inside that, add `placeholder` set to `Search...` to match
the legacy form. On the next line, `class`. Grab the CSS classes
from the original form and paste them here.

Jump back to your browser and refresh the page. It should now be a spitting
image of the legacy search field, minus the search icon, which
we'll take care of later.

If you try to submit the form now, you'll notice it uses the POST method,
which is the default behavior. However, our search form uses GET,
which is more fitting for a search feature.

## What's Next?

Next, we'll switch the form method from POST to GET and
learn how to manage it properly in the controller.
