# Multiple Submit Buttons

Alright, we've got our "Create" button working like a charm, but what if we
want a couple of different workflows: "Create and Close" to save the part
and jump back to the list, and "Create and Add Another" to save the part
but stay on the form page for quick data entry.

No worries, Symfony is fully capable of handling this. Let's dive into
the second method of adding buttons to a form using the Form type,
and use it to drive our business logic.

## Adding a Second Submit Button Inside the Form Type

First things first, let's tweak the name of our current button in the
`new.html.twig` template to "Create and Close". Now, pop open your Form
type class in `src/Form/StarshipPartType.php`. It's time to drop in a
second button below the fields. Let's add another one using
`->add('createAndAddNew', SubmitType::class)`. This handy
`SubmitType::class` tells Symfony to render it as a `<button type="submit">`. 

If you hop over to the browser and refresh, you'll see our two buttons. The
new one doesn't quite look like a button - that's because we've reset
styles. But technically, in the code, it's `<button type="submit">`.
We'll spruce up the styles later.

## Accessing Unmapped Fields in Symfony

For now, in the controller, we're already aware that `form->getData()`
hands us a mapped entity, which in our case is `StarshipPart`. This time,
though, we need to get our hands on an unmapped field - the submit button
we just added. This field doesn't have a matching property on the entity,
which is why we call it "unmapped".

No sweat, we can access any field. Directly below the `addFlash()`, let's
cook up a `$createAndAddNewBtn` variable that equals
`$form->get('createAndAddNew')`. This should match the button name on your
Form type. Let's give it a quick test run first. Down below, I'm going to
write `dd($createAndAddNewBtn)`.

Back to the browser, filling the form notes is optional, so just the name
and price will do. I'm going to hit our "Create and Add New" button now,
and there's our dump. It's a Submit Button with some intriguing fields.
Take a closer look and you'll spot the magical `clicked = true`. Believe it
or not, this little gem is how we can tell which button was actually
clicked, and we can harness this to power different business logic.

## Harnessing Button Clicks to Execute Business Logic

Back to PHPStorm, I'm going to get rid of the `dd()` and give PhpStorm a
little help. To solve the autocomplete, above the button, I'm going to add
`/** @var SubmitButton $createAndAddNewBtn */`. Now, inside your form
submit logic, try typing in `$createAndAddNewBtn->isClicked()`. That's
exactly what we need. Wrap it in an `if` statement, and if the button was
clicked, let's return `$this->redirectToRoute('app_admin_starship_part_new')`.

Now, give the form another whirl. Here's our flash message:

> The part was successfully created

We see it twice because I just resubmitted the form. In the new scenario,
it should be only one message. So we were redirected back to the form page.
You can test out the "Create and Close" button. That should still bring you
back to the part list page.

So, this was a completely valid and super useful way of adding form buttons
inside the Form Type. But if you can avoid cramming buttons in the Form type
and stick to plain HTML buttons, your designer teammates will silently
thank you.

## The Intricacies of Symfony's Form Type: Field Type Guessing

Let's zip back to the Form type for a second. You've probably noticed this
`$builder->add('price', null)`. The second argument to the `add` method
should be the type of the field and for some fields like `price`, `name`,
`notes`, it is `null`. But why `null` instead of a real type like
`IntegerType` in this case, for example?

Well, Symfony has this nifty feature called "field type guessing". When you
set the type to `null`, Symfony will inspect entity properties metadata.
For example, if `price` is an integer property, Symfony will pick `IntegerType`.
If `name` is a string then Symfony will opt for a `TextType`. If we had,
let's say, an `isActive` boolean, then Symfony would select a `CheckboxType`,
and so on.

You can confirm this by peeking at the rendered HTML. Inspect the code for
the `price` field, and you'll see that it's not just `<input type="text">`,
it's `<input type="number">`, which gives us some handy arrows to increase
and decrease the value. This is because the `price` property on the
`StarshipPart` entity is an integer and the Symfony Form Component catches
sight of it. That's why it uses the more optimized field type.

Most of the time, Symfony guesses exactly what you want. But when Symfony
can't guess correctly, or when you want something different, you can always
override the default guessed type by passing the type explicitly.

For example, like this one `IntegerType::class`. If you refresh, nothing will
change, because that's exactly what was set behind the scenes.

## Exploring Symfony's Built-in Form Field Types

But what built-in form field types does Symfony have and where can you find
them? Great question, and I'm glad you asked! Symfony docs will always
be there to help with that. But Symfony also comes with a super nerd-friendly
tool for discovering everything about built-in field types.

In your terminal, run:

```terminal
symfony console debug:form
```

This dumps all available form types, including your own Form Type classes,
which we can see here as a service.

If you want to inspect a specific type, just specify it as an argument to
the command. For example, run:

```terminal
symfony console debug:form TextType
```

And you'll see all options that `TextType` supported, which options are
required and which options come from parent types it extends. Try another one,
for example, run:

```terminal
symfony console debug:form EntityType
```

You'll see that the `class` option is required and MakerBundle already
filled that for us. So smart! Options are set as the third argument
in the array.

And by the way, Symfony doesn't only guess *field types*, it also guesses
*field type options*. For example, if a Doctrine property is `nullable =
true` like for this `notes` field, then Symfony will automatically make it
optional. And the reverse for the other fields, Symfony will automatically
set `required` HTML attribute if field is not nullable.

You can see it in the HTML inspector. For example, we don't see this
`notes` field is required, but if you inspect the `name`, you will see it
has a `required="required"`. And that's why when you hit "Submit" button
with an empty form, you will see these HTML5 validation errors on empty form
submit.

And of course, you can easily override that if needed, too. Options can be
set within an array as the third argument. And if you pass an array and set
`required` to `false`, you will override the default behavior and the
`price` field is not required anymore.

I'm going to revert back this code because we do want the `price` field as
a required field, and we do want it set in the database.

Now, we'll dive much deeper into form field types later in this course.
For now, let's switch gears to something fun and stylish: making our form
look actually nice by applying a built-in Symfony form theme to it.

Catch you in the next chapter!
