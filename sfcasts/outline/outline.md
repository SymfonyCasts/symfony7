# Symfony 8 Forms Advanced Outline

## Prerequisites
- Fork TutsHero last step of https://github.com/SymfonyCasts/symfony7/tree/ep6-security
- Upgrade PHP version to `>=8.4.1` in composer.json
- Upgrade Symfony to the latest 8.1 w/ deps: w/ `symfony composer update`
- Upgrade recipes
- Apply missing TutsHero steps from ep6-security at the moment of forking 

## Configure empty_data for a form type class
- Open /admin/starship-part/new form
- Who creates a new `StarshipPart` object when we submit the form?
- Symfony does it for us
- But what if our `StarshipPart` has a constructor w/ requires arguments?
- Let's check it
- Open `src/Entity/StarshipPart`
- You can see that `name` and `price` are required according to our validation rules
- Let's ask for them in the constructor
- Add `public function __construct(string $name, int $price)`
- And set the properties in the constructor
- Now go submit the form again - it will fail!
  > Too few arguments to function App\Entity\StarshipPart::__construct(), 0 passed
- Symfony doesn't know how to create a new `StarshipPart` object because it
  doesn't know what to pass to the constructor
- We can fix this by configuring the `empty_data` option in the form type class
- Open `src/Form/StarshipPartType.php`
- Add `empty_data` option to the form type class
- Set it to `fn (FormInterface $form) => new StarshipPart()`
- And pass `$form->get('name')->getData()`
- And `$form->get('price')->getData()`
- Submit the form again - starship part successfully created!

### Configure empty_data for a form field
- We can also use `empty_data` for a single form field
- Open `StarshipPartRepository::findAllOrderedByPrice()`
- We allow nullable serch term
- Let's make it more strict and require it to be a `string` only
- Change method signature to `findAllOrderedByPrice(string $search)`
- Below, fix the if statement to `if ($search !== '')`
- That's an important fix because if someone will search for `0` it will automatically
  be converted to false with just `if ($search)` check - we don't need that
- Now open `PartController`
- Inside `index()` change to: `$query = '';`
- let's search a part on the website - it works
- But if you submit an empty search form - the error!
  > StarshipPartRepository::findAllOrderedByPrice(): Argument #1 ($search) must be of type string, null given
- Yeah, we should either explicitly typecast null to string in `$query = $searchForm->get('query')->getData();`
- Or we can leverage `empty_data` option again... but now for the form field only
- Open `src/Form/PartSearchType.php`
- Add `'empty_data' => '',` to the `query` field
- Submit the empty search form again - it works now!
- Instead of null, the form uses empty string as the default value for the `query` field

## Data Transfer Object (DTO) instead of entities in form types
- But if you go back to /admin/starship-part/new form
- And submit an empty form - it will fail again
  > StarshipPart::__construct(): Argument #1 ($name) must be of type string, null given
- We could do the same `empty_data` trick for `name` & `price`
- But instead, let's think a bit
- Let's open `src/Entity/StarshipPart`
- We say name and price are required according to our validation rules
- And in the database, we do not allow nullable (`nullable=false`)
- Though PHP code tell us the different story - it says those properties can be null!
- Let's be honest and fix property types in the PHP code
    - Change to: `private string $name;`
    - And to: `private int $price;`
    - Not it perfectly reflects the DB setup
- Fix setters and getters:
    - We already require those properties in the constructor - great!
    - Change to: `public function getName(): string`
    - And to:  `public function getPrice(): int`
- Now PHP code tell us the truth - those properties cannot be null
- But for the forms, we still need to allow invalid states
- For this, create DTO class `src/Dto/StarshipPartDto.php`
    - Make it `final`
    - Copy all the properties from the entity including asserts
    - And change them to `public`
    - Drop ORM annotations
    - And allow `null` for `name` and `price` properties
    - For this, change to: `public ?string $name = null;`
    - And to: `public ?int $price = null;`
- Back to `StarshipPartType` form
    - Set `'data_class' => StarshipPartDto::class,`
    - And comment out `empty_data` there - we don't need it anymore
- Now back to `AdminController::newStarshipPart()`
    - This `$form->getData()` will return our DTO object now
    - We should map it to the entity before persisting
    - We can do it manually like...
    - Or we can leverage Symfony's ObjectMapper to do it for us
- First, install it with `symfony composer require symfony/object-mapper`
    - Now, inject it in `newStarshipPart()` as `ObjectMapperInterface $objectMapper`
    - Inside `if`, I will drop `$part = $form->getData();` w/ the related PHPDoc
    - Instead, add `$part = $objectMapper->map($form->getData(), StarshipPart::class);`
    - We can use it the other direction too, i.e. map from entity to DTO if needed
    - PhpStorm should already know it's a `StarshipPart` object now
    - If your IDE doesn't know about it, you can leave a PHPDoc for `$part`
- Now go refresh the page, fill in the form, and submit it
- Go search the created part - here it is! It worked as before
- But now our PHP code is more correct and reflects the database setup

## Conditionally show/hide form fields based on the underlying data (instead of passing those via constructor directly).
- If you go to /admin/starship/edit
- And we allow choosing a status
- But for new ships the status should be always `waiting`
- While we should allow changing it for existing ships when edit
- However, we should remember that this form is used for both creating and editing ships
- We could duplicate form type: one for new and one for edit
- But it's not a good idea - we will have to maintain two forms types instead of one
- Instead, we can conditionally show/hide the `status` field
- Passing a bool flag via form type constructor is not a good idea either
- Instead, we can leverage the underlying data to determine if the form is for creating or editing
- But first, open `StarshipType`
- Let's dump that `$options` var, add `dd($options);`
- And reload /admin/starship/edit
- Aha, there's `data` that is set to the `Starship` object we are editing!
- Open /admin/starship/new - the same Starship object but an empty one
- We can clearly leverage it to determine if the form is for creating or editing base on the ID
- Comment out the dump
- Below, add `$starship = $options['data'];`
- I will also add `/** @var Starship $starship */`
- But `data` does not guarantee to be a `Starship` object - it can be null sometimes
- Below, write safe `$isEdit = $starship && $starship->getId();`
- Below, add `if ($isEdit)`
- Move the `status` field inside this `if` block
- We will show it only for editing existing ships
- Final tweak, open `Starship`
- Update `private ?StarshipStatusEnum $status = StarshipStatusEnum::WAITING;`
- Go create a new starship - no status field, as expected
- But when you edit the created starship - the status field is there, and you can edit it
- ### ...
- Let's allow writing in `slug` field only on creation to avoid URL changes for existing starships for SEO purposes
- With text fields - we can make them readonly
- Add options with `attr` set to an empty array
- Inside, add `'readonly' => $isEdit,`
- Open the new form - we can write in that field
- If you open existing starship for editing - the field is readonly now
- But tricky users can easily bypass this editing HTML in the browser
- Open Chrome Dev Tools, remove `readonly` attr, and submit the form - the value is updated!
- You should keep it in mind working with readonly fields - they are not secure
- TODO Show how to edit theme to make readonly fields look slightly dimmed
- I will comment `readonly` attr out, though it's not required
- Below `attr`, add also `'disabled' => $isEdit,` option
- Try `readonly` attr first, then show `disabled` option for slug
- Update the page - now the field is disabled
- If you open Dev Tools and remove `disabled="disabled"`, update the field and submit - no changes are done
- This is the proper secured way if you don't want user input, or just do not render the field at all - you can just print the plain value in the template

## Custom form type options
- But what if we want to pass some data from outside of the form type
- What if we still want to allow editing `slug` field for admins?
- We know we can check for admin w/ `$this->isGranted('ROLE_ADMIN')`
- But this info does not exist in `$options` or on the entity
- How to pass this value to the form type? 
- Passing it into form type constructor is not a good idea
- Instead, we can leverage custom form type options
- In `configureOptions()`, `setDefaults()` add `'is_admin' => false,`
- Below add `$resolver->setAllowedTypes('is_admin', 'bool');`
- Yes, Symfony Forms can even validate the type for us, so handy!
- This way we created a new option, check it with `dd($options)`
- Comment the dump line out
- Tweak `slug` field logic to `'disabled' => $isEdit && !$options['is_admin'],`
- Now go to `StarshipAdminController::edit()`
- Add `'is_admin' => $this->isGranted('ROLE_ADMIN'),` as options to `createForm()`
- Reload the page to see the field can be still  edited by admins

## Cover form rendering variables via `form.vars.value`
- But we completely hide status for create form
- It may be clearer if we still print the status in the form for clarity
- Open `starship_admin/new.html.twig`
- Aha, the form is actually rendered in `templates/starship_admin/_form.html.twig` - open that
- Instead of rendering the whole form let's just render errors first: `{{ form_errors(form) }}`
  You should remember from the Basic Forms it renders global form errors,
  we don't want to miss them.
- Then `{{ form_rest(form) }}`
- Between, we're going to render either status as a text or select field
- We can render a specific field as `{{ form_row(form.status) }}`
- How can we check if the form has status field?
- Let's wrap it in `if form.status is defined` check is enough
- In `else`, we need to print the status as text
- But how to get access to the entity? Should we pass it from the controller to this template?
- Well, there's an easier way. Dump the form object w/ `dump(form)`
- Aha, we have `vars`... open it
- The `form.vars.data` contain the Starship object that we can use
- There's also `form.vars.value` that has a bit different meaning, we need data in this case
- Print the status w/ `Status: {{ form.vars.data.status.value }}`
- Wrap it w/ a `<div class="mb-6 text-gray-800">`
- Refresh the page to see the "waiting" status

## Allow HTML contents in form labels w/ `label_html` option
- Open /admin/starship-part/new
- I want to be clear say that the price should be in credits
- We can easily change field's label to clarify it
- Open `StarshipPartType`
- Set `label` option to `Price (in credits)`
- I will also wrap it in a span with some styles `<span class="text-gray-500 text-sm">`
- Reload the page - HTML is escaped in label for security reasons
- If we're sure there's no potential XSS there - we can enable HTML in label
- Below, add one more option: `'label_html' => true,`
- Refresh again - much better

## Translatable help messages w/ `new TranslatableMessage()` that include all the information needed
- Let's add a help message for `captain` field on the `StarshipType`
- Add options, `'help'` option
- And set its value to `sprintf('The captain will command %s droids on the starship', $starship->getStarshipDroids()->count())`
- If you try to create a new Starship - it will say 0 droids
- If uoi edit existing - it will say xx droids
- But what if I want to say "1 droid" instead of "1 droids"?
- Yeah, we can use if-else to change that part, but Symfony has a special component that can help with this job perfectly
- Translation component is integrated perfectly in Symfony forms
- It can translate labels, help, and other attrs.
- Even though we have a single-locale website, it still can be perfectly used for pluralization
- Install w/ `symfony composer req translations`
- It will add `symfony/translation` to `composer.json` though the package was already installed as indirect dep
- Next, create a translation file `translations/messages+intl-icu.en.yaml`
- There are a few syntaxes, but ICU is the most flexible
- Note: Watch our Translations tutorial to know more!
- Add `form.starship.captain_droids: > {count, plural,`
- Next, `add =0 {No droids on the starship yet}`
  Then `one {The captain will command a single droid on the starship}`
  And `other {The captain will command # droids on the starship}`
- To pass the actual number, add `help_translation_parameters` below
- And set it to `[]`, inside: `'count' => $starship->getStarshipDroids()->count(),`
- Go reload the page - now the message is translated
- But instead of passing all this data separately, Symfony now allow you to pass it as a single object
- Set `help` to `new TranslatableMessage()` and pass the values there
- Reload the page to see everything still works

## Form theme blocks
- Open /admin/starship-part/new and view the page source
- Notice all the wrapping `<div>`s, labels, and classes around each field
- We never wrote any of that markup, but you know from Basics course where it comes from
- It comes from the default Tailwind form theme
- Open `config/packages/twig.yaml`
- See `form_themes` set to `tailwind_2_layout.html.twig`
- This is a built-in theme shipped with Symfony
- Every single piece of a field is rendered by a Twig block
- `form_row` is the outer block - it calls `form_label`, `form_widget`, `form_errors` and `form_help`
- Let's peek at the source
- Open `vendor/symfony/twig-bridge/Resources/views/Form/tailwind_2_layout.html.twig`
- It's just a bunch of `{% block ... %}` - and it extends `form_div_layout.html.twig` which holds the base markup
- Key concept: Symfony picks the block to render a field by its name, walking a hierarchy from the most specific to the most generic (fallback)
- Our goal for this section: add a "credits" addon right inside the `price` field's input

## Find form theme block names with the Profiler
- To customize the `price` widget, we first need to know which block renders it
- Guessing block names is painful - let's do it the rock-solid way instead
- Reload /admin/starship-part/new and open the profiler for that request (click the request in the WDT)
- Click the "Forms" panel on the left
- Expand the form tree and click the `price` field
- On the right, find the "View Vars" section
- Look at `block_prefixes` - it's an ordered array from generic to specific
  e.g. something like `['form', 'number', '_starship_part_price']`
- Here's the rule: ANY entry in `block_prefixes` + a suffix (`_row`, `_widget`, `_label`, `_help`, `_errors`) is a valid block name you can override
- The last entry is the most specific one - it wins over the earlier ones
- No more guessing: the profiler tells you the exact names
- Bonus: you can also print them right in the template
- In the `templates/admin/starship-part/new.html.twig`, add `{{ dump(form.price.vars) }}`
- And search for `block_prefixes` 

## Override a form theme block and its block variables
- Now let's actually override a block
- Two ways to register a theme: a separate theme file, or inline in the current template
- Let's do it inline - open `templates/admin/starship-part/new.html.twig`
- At the top, add `{% form_theme form _self %}` - this tells the form to look for blocks in THIS template
- Let's override `_starship_part_price_widget` to prepend a currency icon to the *price* field
- Add `{% block _starship_part_price_widget %}` ... `{% endblock %}`
- Inside, add `<div class="flex rounded-lg border border-gray-300 overflow-hidden">`
- And inside of that: `<span class="px-3 flex items-center bg-gray-100 text-gray-500">₡</span>`
- Then `{{ parent() }}` to keep the original help markup
- Reload the page - an error:
  > Block "_starship_part_price_widget" should not call parent()
- Yes, this block is special, so we need to call the "parent" block via `{{ block('form_widget') }}`
- Reload again - the help message for price field has currency icon now

## The `block_prefix` option for easier form customization
- It works, but better: give the field its own stable block prefix
- We don't want to accidentaly change form type or field names and break our custome styles
- Open `StarshipPartType`
- On the `price` field, add `'block_prefix' => 'credits',`
- Check the profiler again - `block_prefixes` now includes `credits`
- Back in `new.html.twig`, override `{% block credits_widget %}` instead
- Reload - it still works
- Bonus: because the prefix is a name we chose, we can reuse `credits_widget` on ANY field
  on this form just by setting the same `'block_prefix' => 'credits'`

## Custom form theme based on core one
- This way we can customize not only widgets but any block like row, label, errors, even `help` message block
- E.g. we can add an ℹ️ icon in front of the help message
- But if we do it the way we show above - it will apply only to the current form
- Instead, I would like to apply globally for all forms
- The proper approach would be to create our custom theme which will "extend" that code default Tailwind theme
- Create `templates/_form-theme.html.twig`
- Inside add `{% use 'tailwind_2_layout.html.twig' %}`
- Now in `config/packages/twig.yaml` use this theme instead of `tailwind_2_layout.html.twig`
- If you reload the page - nothing is changed
- Out custom form theme just uses the Tailwind one
- But now we can customize what we need in it
- In our custom theme file, create `{% block form_help %}`
- Inside add `<div class="flex items-start gap-2">`
- Then inside that `<span class="mt-1">ℹ️</span>` and call `{{ parent() }}`
- If you reload now - every field has this icon
- We should only show it when the help message actually set
- If you track `form_help` widget down to `form_div_layout.html.twig`
- You will see it uses `if help` check
- Let's wrap our custom layout in this simple check
- Reload again - now the icon is only shown on fields where we set `help` message
- If you open /admin/starship/new - there it is!
- Done! Now this works globally for all forms
