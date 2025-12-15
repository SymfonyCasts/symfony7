# Rendering the Form with Twig Helper Functions

In the last chapter, we dived into the Symfony Form component, built our
first Form type, and created a Form object from the type in the controller.

Now, let's move on to the fun part — bringing it all to life by
rendering the form. The Symfony Form component packs a bunch of neat helper
functions that make form rendering a breeze. Open your template and swap
out the dump with this line of code: `{{ form(form) }}`

Now, head back to your browser and refresh the page. Voila! We have a form.
Sure, it might not win any beauty pageants, but as backend developers,
we're not too worried about appearances just yet. Let's focus on
functionality first, and we'll take care of the styling later.

## Understanding Form Submission Methods

If you open your inspector, you'll notice something interesting. The form
is sent via POST method. Symfony defaults to POST for forms, although you can
override this. We'll explore how to send a form via GET later on.

The action attribute is empty. This means the form submits to the same URL,
which is incredibly convenient when you need to use the same form across
different pages that submit to various places. Sure, you could specify an
action explicitly, but most of the time, it's not necessary.

## Styling with Tailwind CSS

Since our project uses Tailwind CSS, let's activate the Tailwind Forms
plugin for some reasonable styling defaults. Back in PhpStorm, open
`assets/styles/app.css`, and in the beginning add
`@plugin "@tailwindcss/forms";`

Don't forget the semicolon at the end. This plugin provides a neat minimal
reset for form controls, making them easier to style with Tailwind classes.
That's designer's favorite feature! 

Refresh your browser, and you'll notice a significant improvement. Our
form has gone from a 90s throwback to a modern developer tool.

Now, let's add a minor detail to make our form fields blend better with the
background. In the CSS file, add this bit:

```css
input, textarea, select {
    background-color: inherit;
}
```

## Adding a Submit Button

Buuuut we've got a tiny issue. Our beautiful reset form is missing the most
important element - a submit button. How do we add one? The best practice
is to add your submit button manually in Twig, not inside the form type.

Let's create a simple button. Back in the Twig template, below the form, add: 
`<button type="submit"></button>`. I will add some Tailwind CSS classes
to make it look prettier like a real stylish button. You can copy/paste
that long list of CSS classes from the scripts below the video.

Remember, this button must be inside the `form` tag; otherwise, no matter how
many times you click or how hard you will press your touchpad, it just won't
do anything. So, we'll switch from rendering the entire form at once
to rendering it piece by piece.

Replace the form tag with `{{ form_start(form) }}`, add `{{ form_end(form) }}`
below it, and put a special `{{ form_widget(form) }}` between them to
render all the form fields.

## A Quick Note About disabled Turbo

By the way, I’ve temporarily disabled Turbo Drive globally in this project.
You’ll see some commented-out Turbo code in `assets/app.js`.

Make sure you also have Turbo disabled so your behavior matches what you see
in the videos. Why? Because with Turbo enabled, page navigation happens
via AJAX requests. And I know, that sounds crazy, but even form submissions
become AJAX! That can make debugging harder when learning how forms work.
For now, plain old full-page loads keep things simple and predictable.

## Testing our Form

Finally, it's time to test our form. Fill out the form and hit that
"Create" button! Did it work?!

As of now, we're not doing anything with the submitted data internally — no
saving, no validating, no redirecting. You'll see this when you try to
reload the page, and your browser asks if it should resubmit the form.

But don't worry, we'll cover how to process the form and store the new
StarshipPart in the database in the next video. Stay tuned!
