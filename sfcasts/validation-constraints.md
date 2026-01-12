# Validation Constraints

In the previous chapter, we installed the Symfony Validator Component and
applied our first validation constraint to the `name` field. This setup
helps us catch errors when we submit an empty form bypassing client-side
HTML5 validation.

However, our validation is currently tucked away inside the form type.
This setup is fine until we create another form for the same `StarshipPart`
entity. If we did that, we'd end up duplicating all constraints in the new
form type.

## Adding Validation to Entity Properties

A better approach is to attach validation directly to the entity itself.
That way, every form automatically benefits. Moreover, it will be useful
if we decide to validate the entity object itself without using forms.

Let's start by commenting out the constraint in the form type. Next, open
up the `StarshipPart` entity. Right above the `$name` property, start typing
`#[NotBlank()]`. PhpStorm gives me a few options, and I'll go with
`Assert\NotBlank`. This command creates a common `Assert` alias for our
constraint namespace for convenience. Inside, we can specify a `message:`
I'll go borrow the exact message from the form type:

> Every part should have a name!

## Adding More Constraints

While we're here, let's add a rule for the `$price` property too. Because
you know, we can't have Starship parts going for free, they are quite
expensive. So above the `$price`, add `#[Assert\GreaterThan()` where set
`value` to 0. We can also customize the message here, how about:

> StarshipPart cannot be free!
 
Perfect. 

Now, let's try our form again. Hm, oddly, we still see the `name` error only.
Where is our new validation error for the price? This is actually expected
behaviour. Most validation constraints are ignored when the value
is `null` to prevent tripping up on optional fields. But `NotBlank` is
different - it rejects `nulls` outright. So, just add another constraint
above: `#[Assert\NotBlank()]` with a message saying:

> You forgot to set the price!

Remember, you can stack as many constraints as you want on the same field.

Now, let's submit the empty form again. Finally, we're getting 2 errors.
And if we set the `price` to 0 and try again, we'll see a slightly different
error message coming from another validation constraint. Perfect!

## Benefits of Symfony Form Validation

Here's a cool detail: When a form is invalid, Symfony automatically returns
a `422 Unprocessable Content` HTTP status code when rendering the invalid
form. You can see the status in your browser's network tab or in your WDT.
This behavior ensures compatibility with tools that rely on the HTTP
specification, like *Symfony UX Turbo*.

Symfony handles this for us automatically because we send the whole `Form`
object to the Twig template rather than manually calling `createView()` on
the Form object before passing the form to the template. This is the
recommended way to go, so the integration with Symfony UX works well.

Plus, the WDT becomes super handy for errors too. You'll see a form icon
with the number of errors your form contains. We have 2 now. If you click
on it to open the WDT Form tab, you can discover which form type class is
responsible for this form. Click on the field name for useful information
you might need during the debugging. 

## Debugging Validation Issues

There's also a Validator tab that provides more details about the errors
you're encountering. If you switch to the `Validator` tab, you'll see
similar data but presented slightly different with more context regarding
validation constraint, e.g. their names that makes it easier to pinpoint
the exact error and the specific fields it relates to.

This tab is especially handy when you use the Validator Component outside
of forms. Regardless of your setup, Symfony gives us everything we need
to debug validation issues. 

## Understanding CSRF Protection

Now, let’s talk about another important concept: CSRF protection.
CSRF stands for Cross-Site Request Forgery. It’s a type of attack where a
malicious website tricks your browser into sending a request you didn’t
intend — for example, submitting a form or clicking a button on another site
without your knowledge.

To prevent this, web frameworks use CSRF tokens — random values that
prove the request really came from your app and not some other site. In
traditional CSRF protection, the server generates a token and puts it into
each form via a hidden field. When the form is submitted, the server checks
that the token is valid. If it’s missing or wrong, the request is rejected
and the CSRF validation error is shown. This stops attackers from forging
requests.

Symfony supports this built-in. You usually install the CSRF package and it
will automatically add tokens to forms and check them for you. That’s the
classic (stateful) approach.

But in newer Symfony versions, there’s also stateless CSRF protection —
useful for apps that don’t want to rely on sessions. Instead of storing
tokens on the server, Symfony checks things like the request’s Origin or
Referer, and (if JavaScript/Stimulus is used) optionally uses a cookie +
header token that the browser sends along with the form. This works well
with modern front-end tools like Stimulus controllers in Symfony UX.

### Installing the CSRF protection

Head to the terminal and install it:

```terminal
symfony composer require csrf
```

Oops, turns out there's no such package. Instead, we should use
`symfony/security-csrf`:

```terminal-silent
symfony composer require symfony/security-csrf
```

As soon as that package is installed, CSRF protection is enabled for
all Symfony forms by default making your forms more secure out of the box. 

### Checking CSRF Protection

You can see this for yourself. Head over to the form page, refresh it, and
open your browser's HTML inspector. Inside our form, you'll find a hidden
input type wired to a `csrf-protection` Stimulus controller.

The easiest way to see it in action, try modifying the CSRF field value in
the browser. Replace it with any random string, e.g. "fake" and submit
the form. You'll get an error:

> The CSRF token is invalid. Please try to resubmit the form.

Just like that, Symfony has blocked an invalid request and returned an error,
preventing any write actions from being executed. 

And that's how form validation should be done. You add validation constraints
to the properties that should be required or should follow specific rules and 
your form will stick to those rules where we will call `$form->isValid()` on it.

Up next, we will see how to render form fields in a specific order pushing
important fields up and shift optional down. Stay tuned!
