# Multiple Submit Buttons

Alright, we've got our "Create" button working like a charm, but what if we
want a couple of different workflows: "Create and Close" to save the part
and jump back to the list, and "Create and Add Another" to save the part
but stay on the form page for quick data entry.

No worries, Symfony is fully capable of handling this. Let's dive into
the *second* method of adding submit buttons to a form - using the `SubmitType`.

## Adding a Second Submit Button Inside the Form Type

First things first, let's tweak the name of our current button in the
`new.html.twig` template to "Create and Close":

[[[ code('b9028fa5b0') ]]]

Now, pop open your form type class in `src/Form/StarshipPartType.php`.
It's time to drop in a second button below the fields. Add this using
`->add('createAndAddNew', SubmitType::class)`:

[[[ code('d04ed3a4ec') ]]]

This handy `SubmitType::class` tells Symfony to render it as a
`<button type="submit">`. 

If you hop over to the browser and refresh, you'll see our two buttons. The
new one doesn't quite look like a button - that's because we've reset
styles. But technically, in the code, it's `<button type="submit">`.
We'll spruce up the styles later.

## Accessing Unmapped Fields in Symfony

For now, in the controller, we're already aware that `$form->getData()`
hands us a mapped entity, which in our case is `StarshipPart`:

[[[ code('f3b6d94b2b') ]]]

This time, though, we need to get our hands on an unmapped field - the submit
button we just added. This field doesn't have a matching property on the
entity, which is why we call it "unmapped".

No sweat, we can access raw form data. Directly below the `addFlash()`,
cook up a `$createAndAddNewBtn` variable that equals
`$form->get('createAndAddNew')`. This should match the button name on your
Form type. Let's give it a quick test run first. Down below,
`dd($createAndAddNewBtn)`:

[[[ code('a38ad41ac2') ]]]

Back to the browser, filling the form notes is optional, so just the name
and price will do. Then hit our "Create and Add New" button...
and... there's our dump. It's a Submit Button with some intriguing fields.
Take a closer look and you'll spot the magical `clicked = true`. Believe it
or not, this little gem is how we can tell which button was actually
clicked, and we can harness this to power different business logic.

## Harnessing Button Clicks to Execute Business Logic

Back to the code, get rid of the `dd()` and give it PhpStorm
little help. To solve the autocomplete, above the button, add a docblock with
`/** @var SubmitButton $createAndAddNewBtn */`. Now, inside the form
submit logic, write `$createAndAddNewBtn->isClicked()`. That's
exactly what we need. Wrap it in an `if` statement, and if the button was
clicked, let's return `$this->redirectToRoute('app_admin_starship_part_new')`:

[[[ code('ff29d468c2') ]]]

Now, back in the browser, refresh and resubmit. Hmm, we see the successful
flash message twice... That's because it was added during the `dd()` request,
and again, when we resubmitted. But it did work - we're back on the blank
form, ready to add another part. Sweet!

## The Intricacies of Symfony's Form Type: Field Type Guessing

Let's zip back to the Form type for a second. We're using `$builder->add()`
to add fields to our form. Sometimes we specify the second argument, but
others, we don't. This second one is the field type, and it's null
by default. So, why don't we need to always specify it?

Well, Symfony has this nifty feature called *field type guessing*.
When the type is `null`, Symfony will inspect the underlying data
class - in our case, the `StarshipPart` entity. It will look for a property
that matches the field name. Based on the found property's type
(guessed from type-hints and metadata), Symfony will automatically select
the most appropriate form field type.

For example, since `price` is an integer, Symfony will pick the `IntegerType`.
Since `name` is a string, Symfony will opt for a `TextType`. If we had,
let's say, an `isActive` boolean, then Symfony would select a `CheckboxType`.
Pretty cool, eh?

Most of the time, Symfony guesses exactly what you want. But when it
can't guess correctly, or when you want something different, you can always
override the default guessed type by passing the type explicitly.

If we change `price` to `IntegerType::class`, when we refresh the form,
nothing will change, because that was the guessed type.

## Exploring Symfony's Built-in Form Field Types

But what built-in form field types does Symfony have and where can we find
them? Great question, and I'm glad you asked! Symfony docs will always
be there to help with that. But Symfony also comes with a super nerd-friendly
tool for discovering everything about built-in field types.

At your terminal, run:

```terminal
symfony console debug:form
```

This dumps all available form types, including your own form type classes,
which we can see here.

If you want to inspect a specific type, just specify it as an argument to
the command. For example, run:

```terminal
symfony console debug:form TextType
```

And you'll see all options that `TextType` supported, the options that are
required, and which options come from parent types it extends. Try another one:

```terminal
symfony console debug:form EntityType
```

We use this in our `StarshipPartType` for the `ship` field. With this one,
you'll see that the `class` option is required. If we look at our form type...
the MakerBundle already filled that for us:

[[[ code('29a3fd5171') ]]]

So smart! Options are set as the third argument of `$builder->add()`.

And by the way, Symfony doesn't only guess *field types*, it also guesses
*field type options*. For example, if a Doctrine entity property is `nullable: true` like
for this `notes` field:

[[[ code('aa80383bc0') ]]]

Then Symfony will automatically make it optional. And the reverse for
the other fields, the `required` HTML attribute will be added if the field
is not nullable, like for `name` and `price`:

[[[ code('dc0fdd0924') ]]]

You can see this with the HTML inspector... If we inspect the notes field, we see it doesn't
have the `required` attribute. If we inspect the name field, it does! Same with price.

That's why when you submit an empty form, we see HTML5 validation errors for
the required fields.

And of course, you can easily override this behaviour in that third
`$builder->add()` argument array. To see this, add `required => false`
to our `price` field's options. Back in the browser, refresh and inspect
the price field - the `required` attribute is gone!

Revert that change - it really is required!

We'll dive deeper into form field types later in this course. For now, let's
switch gears to something fun and stylish: dressing up our form for the ball
by applying a built-in Symfony form theme to it. That's next!
