# Validation Constraints

In the previous chapter, we installed the Symfony Validator Component and
applied our first validation constraint to our form's `name` field. This setup
helps us catch errors when we submit an empty form that bypasses client-side
HTML5 validation.

However, our validation is currently tucked away inside the form type.
This setup is fine until we create another form for the same `StarshipPart`
entity. If we did that, we'd end up duplicating all constraints in the new
form type.

## Adding Validation to Entity Properties

A better approach is to attach validation directly to the entity itself.
That way, every form automatically benefits. Moreover, it will be useful
if we decide to validate the entity object standalone, outside a form.

Let's start by commenting out the constraint in the form type:

[[[ code('cdb7cd8d88') ]]]

Next, open up the `StarshipPart` entity. Right above the `$name` property,
start adding a `#[NotBlank()]` attribute. PhpStorm gives me a few options,
and I'll go with `Assert\NotBlank`. This creates a common `Assert` alias for our
constraint namespace for convenience. Inside, we can specify a `message:`
Grab that from the form type:

> Every part should have a name!

[[[ code('75d701551c') ]]]

## Adding More Constraints

While we're here, let's add a rule for the `$price` property too. Because
we can't have Starship parts going for free, they're expensive. So, above
`$price`, add `#[Assert\GreaterThan()]` and set `value` to `0`. We can also
customize this `message`, how about:

> StarshipPart cannot be free!

[[[ code('1a3494e727') ]]]
 
Perfect. 

Now, let's try our form again. Hm, we still see the `name` error only.
Where is our new validation error for the price? This is actually expected
behaviour. Most validation constraints are ignored when the value
is `null` to prevent tripping up on optional fields. But `NotBlank` is
different - it rejects `nulls` outright. So, above our `GreaterThan`
constraint, add `#[Assert\NotBlank()]` with the message:

> You forgot to set the price!

[[[ code('c5bb18d5bb') ]]]

You can stack as many constraints as you want on the same field.

Now, let's submit the empty form again. Finally, we're getting errors for both
fields!

And if we set the `price` to 0 and try again, we'll see a slightly different
error message coming from the `GreaterThan` validation constraint. Perfect!

## Benefits of Symfony Form Validation

Here's a cool detail: When a form is invalid, Symfony automatically returns
a `422 Unprocessable Content` HTTP status code when rendering the invalid
form. You can see the status in your browser's network tab or in the
web debug toolbar (WDT). This behavior ensures compatibility with tools that
rely on the HTTP specification, like *Symfony UX Turbo*.

This works because, in our controller, we're passing the `$form` object to the Twig template.
If we had called `->createView()` on it:

```php
return $this->render('admin/starship-part/new.html.twig', [
    'form' => $form->createView(),
]);
```

like was required in older Symfony versions, the status code would default
to `200 OK`, which is not ideal for invalid forms, and would break
integration with things like Turbo.

## Debugging Validation Issues

The web debug toolbar is super handy for validation errors too. You'll see
a form icon with the number of errors your form contains. We have 2 now.
If you click on it to open the profiler Form tab, you'll see which form type
class is responsible for the form. Click on the field name for useful
information you might need during debugging. 

If you switch to the `Validator` tab, you'll see similar data, but presented
slightly different with more context regarding the validation constraints,
like its class name.

This tab is especially handy when you use the Validator Component outside
of forms, which it totally possible! Regardless of your setup, Symfony gives us everything we need
to debug validation issues. 

## Understanding CSRF Protection

Now, let’s talk about another important concept: CSRF protection.
CSRF stands for Cross-Site Request Forgery. It’s a type of attack where a
malicious website tricks your browser into sending a request you didn’t
intend — for example, submitting a form or clicking a button on another site
without your knowledge.

To prevent this, web frameworks use CSRF tokens - random values that prove a request
really came from your app and not from another site. In traditional CSRF protection,
the server generates a token and embeds it in each form as a hidden field, while also
storing the same value in the user’s session. When the form is submitted, the server
verifies that the token from the form matches the one in the session. If it’s missing
or invalid, the request is rejected and a CSRF validation error is shown. This prevents
attackers from forging requests, because they can't know, or include
the correct CSRF token.

That's the classic, *stateful* approach and Symfony does support this. 

But in newer Symfony versions, there’s also *stateless* CSRF protection —
useful for apps that don’t want to rely on sessions. Instead of storing
tokens on the server, Symfony checks things like the request’s Origin and
Referer. If using Stimulus, you can harden this protection with cookies and
header tokens that the browser sends along with the form.

### Installing the CSRF protection

Head to the terminal and install the CSRF component:

```terminal
symfony composer require csrf
```

Oops, there's no Flex alias for this. So instead, install the actual package
`symfony/security-csrf`:

```terminal-silent
symfony composer require symfony/security-csrf
```

As soon as that package is installed, CSRF protection is enabled for
all Symfony forms by default making your forms more secure out of the box. 

### Checking CSRF Protection

You can see this for yourself. Head over to the form page, refresh it, and
open your browser's HTML inspector. Inside our form, you'll find a hidden
input type wired to a `csrf-protection` Stimulus controller. This controller
is added as part of the StimulusBundle's Flex recipe and adds that hardening
mentioned earlier.

The easiest way to see it in action, is by modifying the CSRF field value in
the browser. Replace it with any random string, and submit
the form. You'll get a *global* form validation error.

> The CSRF token is invalid. Please try to resubmit the form.

*Global* means it’s not tied to a specific field, but to the form as a whole.

And just like that, Symfony has blocked an invalid request and returned an error,
preventing any write actions from being executed.

And that's how form validation is done. You add validation constraints, either
to the form, or to the object you're validating as attributes. Then, before
processing the form data, call `$form->isValid()` to check if everything is good to go.

Up next, we'll see how to render form fields in a specific order pushing
important fields up and shift optional down. Stay tuned!
