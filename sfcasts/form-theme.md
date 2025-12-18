# Built-in Symfony Form Themes

Okay… it’s finally time. The long-awaited moment where we… add final styling
to our form. Yes! From here on out, it’s just 17 chapters of us carefully
adjusting padding, margins, and hand-crafting artisanal CSS. You can go grab
a coffee… I’ll still be here massaging border-radius values.

Ha! I’m kidding! I promise I won’t do that to you. Thanks to Symfony,
we have built-in themes for various CSS frameworks, which means we can
bypass most of the CSS work and jump right into the fun part.

## Symfony’s Built-In Form Themes

Give "Symfony form themes" a quick Google search. There you have it!
Symfony provides us with a plethora of themes: classic table layouts,
bootstrap themes (both default and horizontal) for various versions,
foundation themes - it's like a complete fashion catalog. And the icing on
the cake? If you have a site written in Tailwind CSS, you're in luck!
Symfony has a Tailwind CSS form theme too.

## Applying Themes in Symfony

So, how do we instruct Symfony to use a specific form theme? Twig to our
rescue! Symfony provides a special Twig tag for form themes. First, open
PhpStorm and locate the `new.html.twig` template with our form. At the top,
just under the `extends` tag, type:
`{% form_theme form 'tailwind_2_layout.html.twig' %}`.

Remember to pass the specific form variable - `form` in this instance - so
the theme knows which form to apply.

Hit refresh in your browser and voila! Our form looks much more refined
with neat spacing and alignment.

## An `EntityType` Form Type

Now, let's shift gears and delve into form types. Here's a special treat I
want to show you. Open `src/Form/StarshipPartType.php` and you'll see our
ship field is utilizing Symfony's magical `EntityType`.

In the previous chapter, we learned that this type requires a `class` option.
This needs to be set to the fully qualified class name of the entity
you want to load choices from. In our case, since the `ship` property
in the `StarshipPart` entity is an instance of `Starship`, we use
`Starship::class` here. Symfony will load all the `Starship`s we have
and display them in a special select field.

However, at the moment, they're just showing up as cold, soulless database
IDs. Not very user-friendly, right?

This is because the MakerBundle generated the `choice_label` as
`'choice_label' => 'id'` by default. Let's make this more descriptive.
Inside the `Starship` entity, we have a `name` property. Let's use that!
Replace `id` with `name`, hit refresh in your browser and voila!

It looks much better! However, the list is quite long. Scrolling through
it feels like going through an intergalactic phone book. What if we could
order these `Starship`s by name? Sounds good, right? Let's do it!

## Using a Custom Database Query for `EntityType`

Inside the field configuration, we can add a new option called
`query_builder`. Set it to an anonymous function that takes an
`EntityRepository` named as `$repo`. Inside this function, we can use
`return $repo->createQueryBuilder('starship')`. Then call `->orderBy()`
Pass `starship.name` as the 1st argument, and for the 2nd write:
`Order::ASC->value` because I want an ascending order. Great!

This `Order::ASC->value` comes from the `Doctrine\Common\Collections`
namespace — a delightfully nerdy way of writing a simple 'ASC' string 🙃.
Well, this way we’re sure we didn’t make any typos in those, ummmm, 3 letters.

Anyway, since you have full access to the query builder here, you can even
add custom filters, JOINs, or (Kevin) James Bond-level WHERE clauses to filter
it to a specific sky or galaxy.

Refresh the page, and now we have a perfectly alphabetized list of
`Starship`s. There's just one tiny issue - there are duplicated names. It's
possible our `Starship`s come from different galaxies, cultures, or even
comic strips with questionable names. To make things clearer, let's enhance
the label.

## Using a Custom Callback for the Choice Label

We could display the entity ID next to the name, but I have a better idea.
Let's display the captain's name next to the ship name.

We could create a method like `getNameWithCaptain()` in the `Starship`
entity, which would return the name of the `Starship` and the name of its
captain. And that would be a good sultion. But we can do something even cooler!

Let's skip creating a new entity method entirely and return to the form type.
Instead of the `name`, let's set `choice_label` to an anonymous function too.
It will get `Starship $starship` as an argument, and inside:
`return sprintf('%s (by %s)');`, passing `$starship->getName()` and
`$starship->getCapitan()` as placeholders. 

Hit refresh and now we're cooking! It's so much easier to identify each
ship.

## Setting Form Field Attributes in the Form Type

Before we wrap up, let's address the elephant in the room - the submit
button. The "create and stay" button is the last piece I'd like to polish
on our already perfectly styled form. Fortunately, forms have a handy
option for this - the `attr` option lets you add any HTML attributes to the
form, such as CSS classes.

Let's pass an empty array as the third argument of the field config for the
`createAndAddNew` button. Then, in quotes, add `'attr' =>` and set it to
another empty array. Inside add `'class' =>` and finally set CSS classes there.
I will go copy/paste the classes from the first button and just replace "green"
with "blue" there.

Hit refresh and there we have it! Our button looks fantastic and is ready
for action. You can add any HTML attributes you like in this manner. For
instance, you can set `id`, `placeholder`, and `data` attributes for
Stimulus controllers, among other things. There's also a sibling option
called `label_attr` if you need to style the `<label>` tag of the field.

We did quite a bit of fine-tuning and our form now looks sharp and behaves
beautifully. Up next is something truly exciting and super important:
_validation_. Stay tuned!
