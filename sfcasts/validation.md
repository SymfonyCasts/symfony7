# Client-side vs Server-side Validation

It's time to talk about form validation. Because just like a starship
without shields, a form without validation is impressive to look at...
but is only one asteroid away from total disaster. Therefore, let's get
our *form* ready for any incoming asteroids.

We know that each `StarshipPart` should have at least a `name` and 
a `price`. Simple, right? But what happens when we accidentally (or
on purpose?) submit an empty form? 

## HTML5 Validation

Interestingly, the browser jumps in to save the day by showing a validation
error. This is HTML5 validation at work. It's client-side validation
that's handled completely by our browser. It's fast, user-friendly,
and quite neat. However, it's not something we can fully rely on!

There are a few reasons for this. First, not all browsers fully
support it. Second, users can easily disable it. Also, as you can see
in practice, it shows only one error at a time instead of all the errors
at once, so users will press that button again and again until
all the errors are gone. And... bots can easily bypass it, and we don't
want bots messing with our starship parts database, right?

If HTML5 validation is skipped, our form gets submitted to the server.
Let's first see what happens then. 

### Disabling HTML5 Validation

We can disable HTML5 validation in the Twig template, but let's try
another, more nerdy way. Open our `StarshipPartType`, and for the
button, add the `validate` option set to `false`. 

Now, refresh the page and inspect the button in your browser's HTML inspector.
Aha, it added a special HTML attribute to the button:
`formnovalidate="formNoValidate"`.

And this is how *anyone* can disable HTML5 validation on our website,
or any website. You can try it yourself in Chrome's Inspector
by editing the HTML code for the other button, save, then click it. 

If I hit the submit button with that attribute on an empty form...
A database error:

> An exception occurred while executing a query: Integrity constraint
> violation: NOT NULL constraint field: starship_part.name

*This* error came straight from our server, and that's a problem
because we've just blindly tried to save invalid data to the database. 

## Server-side Validation with Symfony Validator Component

The good news is we can fix this with server-side validation. Symfony has
a dedicated Validator Component for this purpose. And it works harmoniously
with the Form Component. Let's install it first. Head back to the terminal
and run:

```terminal
symfony composer require validator
```

Let's start small. We want to see a validation error if the `name` field is
empty. The component comes with a lot of built-in
validation *constraints*, which we can attach directly to our form fields.

### Adding a Validation Constraint

Open our `StarshipPartType` class.

For the `name` field, pass `null` as the second argument which is the
default value for it, and an empty array as the third. Inside, 
add a `constraints` key, and then add a new `NotBlank` constraint.

Now, go back to the browser and try to submit the empty form again. Hmm...
we still have the same database error, but look down at the web debug toolbar.
There's a new section regarding validation.

Hover over this little icon, and you'll see "Validator calls: 1" and
"Number of violations: 1". Next to the Validator icon, there's a Forms icon
that shows us "Number of forms: 1" and "Number of errors: 1".

If you click on the Validator icon, you'll see our `NotBlank` error with
its default message:

> This value should not be blank.

## Handling Form Errors

So what's happening here? Even if the form *is* invalid, we're still trying
to save the object to the database, which is not what we want, right?
In the admin controller, we should not persist any data
if the form is invalid. Instead, we should just re-render the form with
errors so the user can fix them, and resubmit the form. To do this, add
another condition in the `if` statement after we checked the form was
submitted: `&& $form->isValid()`.

Refresh the page and resubmit the empty form. There you have it! No more
exception, and the error is nicely rendered directly inside the form.

### Customizing Error Messages

But can we customize the default message? Absolutely! Open the `StarshipPartType`,
and set the second argument of `NotBlank` to... how about:

> Every part should have a name!

Back to the browser, submit the empty form again and there's our custom
message. But wait, why isn't it red? 

## Troubleshooting Tailwind CSS Classes

If you open Chrome's HTML Inspector and look at the rendered error,
you'll see that the error text has the `text-red-700` class and the invalid field
has the `border-red-700` class. These classes come from the built-in
Tailwind CSS form theme we applied earlier in this course, and those are
a valid Tailwind CSS classes, why are we missing styles on them? Don't we
have the `SymfonyCasts/tailwind-bundle` installed?

Here's the catch. These CSS classes are added dynamically and Tailwind
didn't pick them up during compilation because they live in a vendor file
which Tailwind ignores by default. 

### Updating Tailwind CSS Configuration

Open `tailwind_2_layout.html.twig` and you'll see the CSS classes defined
there. How can we ask Tailwind to watch this external file as well while it's
compiling our CSS?

Since we're on Tailwind 4, there's no `tailwind.config.js` anymore.
Instead, open `app.css` in the `assets/styles/` directory. After
`@import` and `@plugin`, add the `@source`. I will go copy the long path
to the `tailwind_2_layout.html.twig` template and paste it here.

We need to adjust the path by prefixing it with `./../../` in order to
go start from the project root directory. And that's it.

Note that this requires Tailwind 4 and won't work in earlier versions.
But we should be on the latest here. You can double-check the exact
version in the `config/packages/symfonycasts_tailwind.yaml` config file - here it 
is, `v4.1.11`. 

Ok, time to try it - refresh the browser aaaand... the text is still not red?
Hm, I copy/pasted the path so it should be the correct. Probably due to
the browser's cache? Let's try "Empty Cache and Hard Reload" first - still
nothing changed.

OK, put on your debug hat and go to your terminal. We can manually run:

```terminal
symfony console tailwind:build
```

In order to see if it can build our final CSS successfully. Aha, there is
an error:

> `@source` cannot have a body

Let's check `app.css`. Ah yes, hard to see, but I forgot to add the semicolon at the end. 

At the terminal, run the build command again... and... success! No errors this time.

Go back to the browser... refresh... and there it is! The error message is now
red, and so is the border around the associated field!

And that's it! We've learned how to add real, server-side, form
validation in a Symfony project. In the next chapter, we will talk more
about validation constraints. See you there!
