# Rendering the Form

In the last chapter, we dove into the Symfony Form component, built our
first Form Type, and created a `Form` object from the type in the controller.

Now, let's move on to the fun part — bringing it all to life by
rendering the form. The Symfony Form component packs a bunch of neat helper
functions that make form rendering a breeze. Open your template and swap
out the dump with: `{{ form(form) }}`:

[[[ code('706721d396') ]]]

Now, head back to your browser and refresh the page. Uhh... well, we
have a form... but it's hard to see the fields. They are all there, just
totally reset by Tailwind. By default, Tailwind CSS strips away all
styling from form elements to give you a clean slate to work with.

## Understanding Form Submission Methods

If you open your inspector, you'll notice something interesting. The form
is sent via POST method. Symfony defaults to POST for forms, although you can
override this. We'll explore how to send a form via GET later on.

There is no `action` attribute. This means the form submits to the same URL,
which is incredibly convenient when you need to use the same form across
different pages that submit to various places. Sure, you could specify an
action explicitly, but most of the time, it's not necessary.

## Styling with Tailwind CSS

Now to at least make our form fields visible. Tailwind CSS has a Forms plugin
that provides basic styling for form elements. Back in PhpStorm, open
`assets/styles/app.css`, and at the top, add `@plugin "@tailwindcss/forms";`:

[[[ code('c534644e2c') ]]]

Don't forget the semicolon at the end.

Refresh your browser, and you'll notice a slight improvement. We've gone from
invisible fields to a 90's looking form. It's a start at least...

Let's add a minor detail to make our form fields blend better with the
background. In the CSS file, add this bit:

```css
input, textarea, select {
    background-color: inherit;
}
```

[[[ code('4551a5ac0c') ]]]

Refresh... and... Hey! That look's a bit better!

## Adding a Submit Button

Our form is missing the most important element - a submit button. How do we
add one? There are two ways to do this. There's a `SubmitType` field that
we can add to our custom form type class. The alternative is to add
the button manually in the Twig template. We'll start with the manual method,
but don't worry, we'll cover adding a button to the form type later.

Back in the Twig template, below the form, add: 
`<button>Create</button>`, `type="submit"`. I'll add some Tailwind CSS classes
to make it look nicer:

[[[ code('7040d226bd') ]]]

You can copy/paste this long list of CSS classes from the script below.

We have a problem here though, this button must be inside the `form` tag;
otherwise, no matter how many times you click it, it just won't
do anything. So, we'll switch from rendering the entire form at once
to rendering it in a way that gives us more control.

Replace the `form()` function with `{{ form_start(form) }}`, add
`{{ form_end(form) }}` below it, and put a special `{{ form_widget(form) }}`
between them to render all the form fields. Move our submit button just
before `form_end()`:

[[[ code('61e8edc5fa') ]]]


Because when Turbo is enabled, page navigation happens via AJAX requests,
and, I know that sounds crazy, but even form submissions become AJAX. That
can make debugging harder when learning how forms work. For now, plain
old full-page loads keep things simple and predictable.

***NOTE
By the way, I’ve temporarily disabled Turbo Drive globally in this project.
You’ll see some commented-out Turbo code in `assets/app.js`:

[[[ code('2458e42a5f') ]]]


Make sure you also have Turbo disabled so your behavior matches
what you see in the videos if you follow this course on a course project
from the previous course.
***

## Testing our Form

Finally, it's time to test our form. Fill it out with some fun data and
hit that "Create" button! Did it work?!

As of now, we're not doing anything with the submitted data internally — no
saving, no validating, no redirecting. You'll see this when you try to
reload the page, and your browser asks if it should resubmit the form.

But don't worry, we'll cover how to process the form and store the new
`StarshipPart` in the database in the next video. Stay tuned!
