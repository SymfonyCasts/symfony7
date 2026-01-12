# Built-in Symfony Form Themes

Okay... it’s finally time. The long-awaited moment where we... add styling
to our form. Yes! From here on out, it’s just 17 chapters of us carefully
adjusting padding, margins, and hand-crafting artisanal CSS. You can go grab
a coffee... I’ll still be here massaging border-radius values.

Ha! I’m kidding! I promise I won’t do that to you. Thanks to Symfony,
we have built-in themes for various CSS frameworks, which means we can
bypass most of the CSS work and jump right into the fun part.

## Symfony’s Built-In Form Themes

Give "Symfony form themes" a quick Google search. There you have it!
Symfony provides us with a plethora of themes: classic table layouts,
bootstrap themes (both default and horizontal) for various versions,
foundation themes - it's like a complete fashion catalog. And the icing on
the cake? If you are using Tailwind CSS, you're in luck!
Symfony has a Tailwind CSS form theme too.

## Applying Themes in Symfony

So, how do we use a specific form theme? Twig to the
rescue! There's a special Twig tag for form themes. First, open
PhpStorm and locate the `new.html.twig` template with our form. At the top,
just under the `extends` tag, type:
`{% form_theme form 'tailwind_2_layout.html.twig' %}`.

Remember to pass the specific form variable - `form` in this instance - so
the theme knows which form to apply.

Hit refresh in your browser... and voila! Our form looks much more refined
with neat spacing and alignment.

## An `EntityType` Form Type

There's something that's still bothering me... Open `src/Form/StarshipPartType.php`...
The `starship` field is an `EntityType`. If you recall from the previous chapter,
this field only requires the `class` option. This is the relationship
that connects our `StarshipPart` to a `Starship`. It displays all the
`Starship`'s in a select element.

But look at this `choice_label` set to `id`. This sets the property of `Starship`
that will be displayed in the dropdown. MakerBundle sets this to `id` by default.

However, at the moment, they're just showing up as cold, soulless database
IDs. Not very user-friendly, right?

This is because the MakerBundle generated the `choice_label` option as
`id` by default. We can do better!
Inside the `Starship` entity, we have a `name` property. Let's use that!
Replace `id` with `name`, hit refresh in your browser and...

Boom! Much better! However, the list is quite long and seems pretty random.
What if we could order these `Starship`s by name? Sounds good, right? Let's do it!

## Using a Custom Database Query for `EntityType`

Inside the field configuration, add a new option called
`query_builder`. Set it to an anonymous function that accepts
`EntityRepository $repo`. Inside this function,
`return $repo->createQueryBuilder('starship')`. Then, `->orderBy()`,
first argument: `starship.name`, second: `ASC` for ascending order.

Totally not necessary, but let's get a little fancy and use an `Order` enum
for the second argument. So instead of `ASC`, write
`Order`, importing the enum from `Doctrine\Common\Collections`, then
`::ASC->value`. Super nerdy... But at least we know we didn't make a typo in
those, ummm... 3 letters...

Since we have access to the full query builder, we can easily add custom filters,
JOINs, or even complex WHERE clauses to filter the results to a specific solar system
or galaxy.

Let's see if this worked!

Back in the browser, refresh the page... Cool! The starships are now sorted
alphabetically by name. There's just one tiny issue - there are duplicated names. It's
possible our `Starship`s come from different galaxies, cultures, or even
comic strips with questionable names. To make things clearer, let's enhance
the label.

## Using a Custom Callback for the Choice Label

We could display the entity ID next to the name, but I have a better idea.
Let's display the captain's name next to the ship name.

We could create a method like `getNameWithCaptain()` in the `Starship`
entity, which would return the name of the `Starship` and the name of its
captain. That would be a good solution... especially if you need this
value elsewhere in your app.

However, let's keep it simple for now. For the `ship`'s `choice_label` option,
instead of `name`, set it to an anonymous function that accepts `Starship $starship`.
Inside, `return sprintf('%s (by %s)')`, passing `$starship->getName()` and
`$starship->getCapitan()` as the placeholder values. 

Hit refresh... and now we're cooking! It's so much easier to identify each
ship.

## Setting Form Field Attributes in the Form Type

Let's address the ugly button in the room! The "Create and add new"
submit button looks gross.

There are a couple ways we can add CSS classes to style it nicely. The easiest
is to use the `attr` option in the form field configuration.

Back in our form type, add an option array to the `createAndAddNew` field with
`'attr' => []`. This is an array of HTML attributes we want to add to the button.
Inside, add `class => ''`, jump back to our `new.html.twig` template, copy the
classes we used for the first button, and paste them here. Replace `bg-green-700`
with `bg-blue-700` to give it a visual distinction.

Back in the browser, hit refresh and there we have it! Our button looks fantastic and is ready
for action. Any HTML attribute can be added in this manner. For
instance, you can set `id`, `placeholder`, or even a `data` attribute to make fields
interactive with Stimulus! There's also a sibling option
called `label_attr` if you need to style the `<label>` tag of the field.

We did quite a bit of fine-tuning and our form now looks sharp and behaves
beautifully. Up next is something truly exciting and super important:
_validation_. Stay tuned!
