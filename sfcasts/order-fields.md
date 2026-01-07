# Organizing Form Fields 

Let's take a moment to discuss the organization of fields in our form,
particularly that Notes field sitting right in the middle of everything.
Ideally, you'd want the required fields to come first, with the optional
ones patiently waiting at their turn at the end.

By default, Symfony renders form fields in the order they're defined inside
the form type - no magic, no surprises. So, if you want a different order,
the simplest solution, as you may already guess, is also the least exciting
one: just reorder the fields in the form type.

Let's give it a go. Move the Notes field to the end, right before the submit
button. Refresh the page and voilà! The Notes field is now at the end.
Problem solved!

## The `priority` Form Field Option

But, what if you don't want to physically move the fields around,
especially when the order needs to change dynamically under certain
conditions? That's where the `priority` option comes to the rescue.

Every form field has a `priority` setting — the default is 0. If we add
`priority` to the `Starship` field and set it to, e.g. 10, Symfony will
render that field earlier. A higher number means it's rendered first,
while a lower number means it's rendered later. You can even assign
a negative priority if you want a field to be rendered closer to the end.

This trick is super handy when field order depends on conditions that
can't easily be solved by simple rearranging your code... Ooor when you want
to avoid large Git diffs in your commands.

## Customizing Field Layouts

But what if ordering isn't enough? What if you want a custom layout? Can we
put the short Name and Price fields on the same line, for instance? The
answer is yes! Symfony provides several helper functions for rendering
forms. We already know some of them.

Up till now, we've been relying on `{{ form_widget(form) }}` to render
all the fields automatically. But when you want more control, you can
render fields manually, one by one.

Instead of rendering the entire form at once, let's render the first
field using `form_row()` and pass `form.starship` — we still want that
field rendered first. After that, add a little HTML to create a grid
using Tailwind CSS classes: `grid`, and also `grid-cols-2` and `gap-4`.

Inside that, render `form_row()` again for `form.name` and `form.price`.
Don't forget the remaining fields before the hardcoded button — simply add
`form_rest()` and pass the `form` variable to render any fields we
haven't rendered yet.

Refresh the page, and you'll see that the Name and Price fields are now
sitting happily next to each other.

## Aligning Submit Buttons on the Same Line

But wait, can we do the same for buttons? Absolutely! Back to the template,
render the Notes field with `form_row()` below the grid, pass `form.notes`
inside. Next, right before the hardcoded button, render `form_row()` again
passing `form.createAndAddNew`

Finally, end with `form_end()`, which renders anything that hasn't been
rendered yet. After `form_rest()`, it will just render the form closing tag.
But I will keep `form_rest()` in the template as a reference and add a comment.
You'll need it if you ever want to render all fields, add extra markup,
and only *then* close the form tag.

## Field Display and Error Handling

Refresh the page again! Hmm, the buttons are still not aligned. If you
inspect the HTML, you'll see why: `form_row()` renders a *full container*,
including the actual field, its label, and any errors if any. All that is
wrapped with a `div` tag.

Usually, that's great for fields, but not in our case. We want to get
rid of that extra `div` wrapper and render only the field itself.
The solution? In Symfony, form field is called _widget_. Replace `form_row()`
for the button with `form_widget()`.

Refresh again, and — voilà! — the buttons are finally in line.

## Render Missing Global Form Errors

But be careful — this introduces a tiny problem. If you remember, when we
previously corrupted the CSRF token and sent the form - we saw a validation
error. If we do that again, we won't see that error message anymore.

You can notice some errors in the WDT, open the validator - already
familiar 2 errors about missing Name and Price. But open the Form tab - aha,
along with those 2 errors we also have the 3rd one attached to the form type.

Yes, some errors belong to the form itself, not to a specific field. When
we used `form_widget(form)`, Symfony automatically rendered those global
errors for us. But now that we're rendering fields manually, we need
to remember to render those global errors manually too.

But don't worry, the fix is easy: add `form_errors(form)` at the very top
of our form. Fake the CSRF token again and send the form to make sure
the CSRF error is back, and our field errors are on their places too.

## Making the Form More User-Friendly

To make the form more user-friendly and help users avoid validation errors
in advance, you can add hints directly to the field using a special
`help` option. For instance, let's tell users that free parts are not
allowed by adding a `help` option to the Price field with a meaningful
message — something like:

> We don't allow free parts! Please set a price
 
When you refresh the form, you'll see this helpful message displayed
in a subtle grey text under the field.

## Adding Form Field Attributes in the Template

Remember when we added CSS classes to the Submit button in the form type?
That works, but it's not ideal — designers probably don't want to touch
your PHP code, and they might not even know what a *form type* is, or how
it works in a Symfony application. So, styling decisions don't really
belong there.

Instead, let's move those styles to the template so that designers can
easily change it. I will comment it out in the form type, copy the long
line of CSS classes, and go to the template. For the `createAndAddNew`,
`{}` add a second argument.

Here you can pass the same options you pass to the form field. So, we want
`attr` option that set to another `{}`. Inside, add `class` option and
set it to the empty string, where paste the CSS classes we copied earlier. 

When you refresh the page - you'll see the same styling, but with cleaner
separation of concerns. Just remember that options defined in Twig templates
overwrite everything set in the form type.

## Highlighting Required Form Fields with CSS

Help messages are great, but sometimes you want required fields to stand
out visually. Let's add a tiny CSS trick to show a red asterisk next to
every required field on our form. Open `assets/style/app.css` and at the
end add:

```css
label.required::after {
    content: " *";
    color: #c10007;
}
```

And refresh the form page to celebrate! Now every required field gets
a nice red asterisk — clear, subtle, and user-friendly.

And just like that, you’ve gone from a default Symfony form to a fully
customized, designer-friendly, error-safe form layout. Not bad for one
chapter, right?

Next, let’s speed up our form creation for different CRUD operations
on our entities by leveraging MakerBundle again. Stay tuned!
