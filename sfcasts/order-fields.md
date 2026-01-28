# Organizing Form Fields

Let's take a moment to discuss the organization of fields in our form,
particularly that Notes field sitting right in the middle of everything.
Ideally, you'd want the required fields to come first, with the optional
ones patiently waiting their turn at the end.

By default, Symfony renders form fields in the order they're defined inside
the form type:

[[[ code('a451a46da7') ]]]

No magic, no surprises. So, if you want a different order, the simplest
solution, as you may have already guessed, is also the least exciting
one: just reorder the fields in the form type.

Let's give it a go. Move the Notes field to the end, right before the submit
button:

[[[ code('a03d3bc11e') ]]]

Refresh the page and voilà! The Notes field is now at the end. Problem solved!

## The `priority` Form Field Option

But, what if you don't want to physically move the fields around,
especially when the order needs to change dynamically under certain
conditions? That's where the `priority` option comes to the rescue.

Every form field has a `priority` setting — the default is 0. If we add
`priority` to the `Starship` field and set it to 10, Symfony will
render that field earlier:

[[[ code('f4ee0009cb') ]]]

A higher number means it's rendered first, while a lower number means it's
rendered later. You can even assign a negative priority if you want a field
to be rendered closer to the end.

This trick is super handy when field order depends on conditions that
can't easily be solved by simply rearranging your code... Ooor when you want
to avoid large Git diffs.

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
field rendered first:

[[[ code('c040887987') ]]]

After that, add a little HTML to create a grid using Tailwind CSS classes:
`grid`, `grid-cols-2` and `gap-4`. Inside, render `form_row()` again for
`form.name` and `form.price`:

[[[ code('8e8ceb18ac') ]]]

Don't forget the remaining fields before the hardcoded button — simply add
`form_rest()` and pass the `form` variable to render any fields we
haven't rendered yet:

[[[ code('79bd10ebe9') ]]]

Refresh the page, and you'll see that the Name and Price fields are now
sitting happily next to each other.

## Aligning Submit Buttons on the Same Line

But wait, can we do the same for the buttons? Absolutely! Back to the template,
render the Notes field with `form_row()` below the grid, passing `form.notes`.
Next, right before the hardcoded button, render `form_row()` again
passing `form.createAndAddNew`:

[[[ code('162be4adaa') ]]]

This `form_end()` function renders any remaining fields and closes the form tag.
To choose where we want to render the remaining fields, we can use `form_rest(form)`.
I'll add a comment to explain this:

> Anything you want to add after all fields are rendered, but before
> the closing `</form>`

[[[ code('0d0c32c0c3') ]]]

## Field Display and Error Handling

Refresh the page again! Hmm, the buttons are still not aligned. If you
inspect the HTML, you'll see why: `form_row()` renders a *full container*,
including the actual field, its label, also errors if there are any. All that is
wrapped with this `div` tag.

Usually, that's great for fields, but not in our case. We want to get
rid of that extra `div` wrapper and render only the field itself, the button
in this case. The solution? In Symfony, form fields, that is the `input`,
`button`, `select` HTML elements, are called _widgets_. So, to render just
the button element, replace `form_row()` for the button with `form_widget()`:

[[[ code('25e0726bd2') ]]]

Refresh again, and — nice! The buttons are finally in line.

You can manually render all the components of a `form_row()` this way.
`form_widget()` renders the field element, `form_label()` renders the
label element, `form_errors()` renders a list of errors (if any), and
`form_help()` renders help text (if any).

## Render Missing Global Form Errors

But be careful — we've introduced a problem. Corrupt the CSRF token again...
and submit the form... We see the errors for name and price, but not
the expected CSRF error message.

In the web debug toolbar, the validator tab shows 2 errors, but the form tab
shows 3. What's up with that? Open the validator panel. Ok, we see our name
and price errors.

Now go to the forms tab. Here we see 3 errors - the 2 field errors, and
the CSRF error attached to the form itself. This one's a form-level validation
error, that's why it doesn't show up in the validator panel.

Back in our template, when we previously used `form_widget(form)`, Symfony
automatically rendered the form-level, or global errors, for us. But now that
we're rendering fields manually, we need to remember to render those global
errors manually too. So, right after the `form_start()`, add `form_errors(form)`:

[[[ code('b2b2380700') ]]]

Ok, let's try this again. Back in the browser, refresh the page... corrupt
the CSRF token... and submit.

Sweet! All the expected errors are back.

## Making the Form More User-Friendly

To make the form more user-friendly and help users avoid validation errors
in advance, you can add hints directly to the field using a special
`help` option. For instance, let's tell users that free parts are not
allowed by adding a `help` option to the Price field with a meaningful
message:

> We don't allow free parts! Please set a price

[[[ code('bb340846ff') ]]]
 
When you refresh the form, you'll see this helpful message displayed
in subtle grey text under the field.

## Adding Form Field Attributes in the Template

Remember when we added CSS classes to the Submit button in the form type?
That works, but it's not ideal — designers probably don't want to touch
your PHP code, and they might not even know what a *form type* is, or how
it works in a Symfony application. So, styling decisions don't really
belong there.

Instead, let's move those styles to the template so that our designers can
easily change it. I'll comment it out in the form type. Copy the long line
of CSS classes, and go to the template. For the `form_widget(form.createAndAddNew)`
call, add a second argument, an options hash.

Here you can pass the same options you pass in the form type. So, we want an
`attr` option set to another hash. Inside, add the `class` option... and
paste the CSS classes we copied earlier:

[[[ code('89cd9db3d0') ]]]

When you refresh the page - you'll see the same styling, but with a cleaner
separation of concerns. Just remember that options defined in Twig templates
overwrite everything set in the form type.

## Highlighting Required Form Fields with CSS

Help messages are great, but sometimes you want required fields to stand
out visually. Let's add a tiny CSS trick to show a red asterisk next to
every required field on our form. Open `assets/styles/app.css` and at the
end, copy/paste the following snippet from the script below:

[[[ code('baccced173') ]]]

Refresh the form... Cool! Now every required field gets a nice red asterisk.
Not just for this form, but for all forms in our app. Neat!

And just like that, you’ve gone from a default Symfony form to a fully
customized, designer-friendly, error-safe form layout. Not bad for one
chapter, right?

Next, let’s speed up our form creation for different CRUD operations
on our entities by leveraging MakerBundle again. Stay tuned!
