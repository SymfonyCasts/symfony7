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
###...
- What if I want the `captian` field called `commander` in the form?
- Well, yes, we can update the label, but the field would remain `captain` in the form data
- Let's try to rename the form field - update it to `commander`
- If you open `/admin/starship/new` - you will see:
  > Can't get a way to read the property "commander" in class "App\Entity\Starship".
- That makes sense, but how can we fix it?
- Rename the property on the entity? Yes, but it would require a migration,
  and I do want internally that field to be called `captain` for clarity
- We can use custom getter/setter for that field
- Add getter `'getter' => function (Starship $starship): ?string {}`
- Inside, return `$starship->getCaptain();`
- Below add `'setter' => function (Starship $starship, ?string $value): void {}`
- Inside, call `$starship->setCaptain($value ?? '');`
- Go refresh the page and submit the form - it works, the value is saved to the `captain` property
- Custom getter/setter give us full control, but we wire the reading and writing by hand - a bit verbose if all we want is to rename a field
- But wait! Let's see another approach
- I will comment out the custom getter/setter
- And instead add `'property_path' => 'captain',`
- Reload the page - it still works, and it's much simpler: the field just maps to the `captain` property
- The only problem you may still notice - the required `*` icon is gone
- We can fix it by adding `'required' => true,` option to the field explicitly
- Reload the page - the `*` is back!
- So, custom setters/getters are great, and they gave you access to the whole object
- This might be useful if you want to render a few fields into one
- E.g. in getter you can join user's first and last names and render them as a single field 
- And in setter you can split the value back into two properties setting them accordingly
- But if all you need to do is to map the field to existent property - `property_path` is the right way to go

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

## Custom reusable form field type based on core one
- Our `credits` styling is nice, but to reuse it we must repeat stuff on every field:
  the `'block_prefix' => 'credits'` option, the label, etc.
- "An amount in credits" is a concept we'll want on more fields (part price, ship cost, etc)
- Let's bundle it into a real, reusable *form field type*
- Create it w/ `symfony console make:form Type\\CreditsType`, then trim it down
- I use `Type\\` prefix to put it into `src/Form/Type/`
- And we don't need `data_class` here - it's a single field type, not a whole form
- Open that `src/Form/Type/CreditsType.php`
- First, remove the whole `buildForm()` - we don't need for a simple type
- Then, make it extend a core type - I will open "Generate" meny (`Cmd + N`) -> "Override methods"
- Override `getParent()`
- Return `IntegerType::class` - our type IS an integer field, we just layer on top of it
- Credits are whole numbers, so `IntegerType` fits perfectly
- Now open `StarshipPartType`
- Change to `->add('price', CreditsType::class)`
- The default block prefix of this type is ALREADY `credits`, so we don't need it
- Run `symfony console debug:form CreditsType` to inspect it
- You'll see the parent type, the resolved options, and the `credits` block prefix
- I will comment out redundant `'block_prefix' => 'credits'` option
- Since we made `IntegerType` parent, we can pass its options
- Reload /admin/starship-part/new - still works, the `credits_widget` block still applies
- One thing to notice: the widget markup STILL lives in the template
- If we used `CreditsType` in another form in another template, we'd get a plain input - let's fix that next

## Custom type w/ theme (a custom widget)
- The `credits_widget` block still sits in `new.html.twig` via `{% form_theme form _self %}`
- That means the markup is NOT bundled with the type - use `CreditsType` elsewhere and it's gone
- A proper custom widget should carry its own markup - let's move it to the theme
- Cut the `{% block credits_widget %}` out of `new.html.twig`
- Also remove the now-unused `{% form_theme form _self %}` line there
- Paste the block into our global theme `templates/_form-theme.html.twig`
- Since `_form-theme.html.twig` is already registered globally in `twig.yaml`,
  the `credits_widget` now applies everywhere automatically
- Reload /admin/starship-part/new - the ₡ addon is still there, but `new.html.twig` is clean now
- That's the whole point: `CreditsType` + its theme block = a self-contained widget,
  usable anywhere with a single line and zero template work
- Capstone: let's make the currency symbol configurable via a custom option
- Back in `CreditsType::configureOptions()`, add `'units_symbol' => '₡',` to the defaults
- Custom options don't reach the template automatically - we must expose it on the view
- Go to "Generate" menu (Cmd + N) -> "Override Methods"
- Override `buildView()` this time
- Inside: `$view->vars['units_symbol'] = $options['units_symbol'];`
- Open `credits_widget`, and write `{{ dump() }}` inside
- Aha, `units_symbol` are vars now
- Comment dump out
- In the theme block, replace the hardcoded `credits` w/ `₡` with `{{ units_symbol }}`
- Reload - looks the same
- Run `symfony console debug:form CreditsType` to see new options are available on this type
- Now if you want to override defaults, you can do it!
- Try `'units_symbol' => '₵',` option to the `price` field
- Reload the page - symbol changed
- Done! `CreditsType` now bundles behavior (from `IntegerType`), its own `units_symbol` option,
  and its own markup - a complete, reusable custom widget

## ...
- Status field for the Starship is rendered as a select list by default
- But we can easily change it
- Open `StarshipType`, for `status` field, add `'expanded' => true,`
- Reload the page - now it's rendered as radio buttons instead of a select list
- But if you want total control over rendering - along with `form_*()` Twig helpers there are more low-level `field_*()` Twig helpers 
- That makes form field rendering even more flexible
- Let's try to render the `Starship::status` field manually and see how they can help you
- Open `templates/starship_admin/_form.html.twig`
- Go inside the `if form.status is defined` block
- First, I will start with `<fieldset></fieldset>` wrapper
- Next, `<legend>{{ field_label(form.status) }}</legend>`
- `form_label()` is a high-level helper that renders the label and its wrapper
- `field_label()` is a low-level helper that renders only the label as a text, no wrapper
- Next, we need iterate over the `form.status` field's errors and render them manually
- We can do it with `{% for label, value in field_choices(form.status) %}`
- Below, render `<label></label>`
- Inside, `<input type="radio">`
- Below, render the label text: `{{ label }}`
- Each input should have the name - we can render it as `name="{{ field_name(form.status) }}"`
- This way Symfony forms will know what this field it is when the form is submitted
- Also, we need the value: `value="{{ value }}"`
- Input type radio is a special, it needs `checked` attr on the chosen input
- Add it with `{{ value == field_value(form.status) ? 'checked' }}`
- And it would be useful to have an id attr as well
- Add `id="{{ field_id(form.status) }}"`
- But since we're in the loop all inputs will have the same id that is not valid
- Let's append a loop index with `id="{{ field_id(form.status) }}_{{ loop.index0 }}"`
- And now we can refer to this id in the label: `<label for="{{ field_id(form.status) }}_{{ loop.index0 }}">`
- Done! Reload the page to see our custom-rendered radio buttons
- I would add `class="mr-5">` to the label to add some spacing between the buttons
- Reload - much better

## Flexible validation callback constraint
- Open /admin/starship-part/new and submit empty form - validation errors
- In previous course we've added some validation constraints to the fields
- But those constraints were related to specific fields only
- However, some rules may span MULTIPLE fields at once - and those attributes can't express that
- Example rule: an expensive part (price over 1000) must explain itself in `notes`
- Attributes validate one property at a time, so we need something more flexible
- Meet the `Callback` constraint! It runs a method with access to the WHOLE object
- Open `StarshipPartDto`
- Add a method `public function validate(ExecutionContextInterface $context): void`
- Add the `#[Assert\Callback]` attribute above it
- Inside: `if ($this->price < 1000 || $this->notes)`
- Inside if - just `return`
- Below if, build the violation and attach it to the `notes` field:
  ```php
  $context->buildViolation('Expensive parts must include notes explaining the price')
      ->atPath('notes')
      ->addViolation();
  ```
- The `->atPath('notes')` is the key - it shows the error under the `notes` field, not globally
- Reload, set price to `5000`, leave `notes` empty, submit - the error shows right on `notes`
- Fill in `notes` and submit - it passes. A rule across two fields, done in plain PHP

## Configure form validation groups
- Now open /admin/starship/new - some rules should differ between creating and editing
- Example: a brand-new ship may not have arrived yet (`arrivedAt` can be empty)
- But an existing ship we're editing MUST have an arrival date
- Same form, two different rule sets - this is exactly what validation groups are for
- Open `src/Entity/Starship.php` (it has no constraints yet - these are the first)
- For `name`, `class`, `captain`, and `slug` - let's add `#[Assert\NotNull]`
- I will go with the default message for them
- This will work by default now
- Now, on `arrivedAt`, add `#[Assert\NotNull(message: 'An existing ship must have an arrival date', groups: ['edit'])]`
- A constraint in a named group only runs when that group is active
- By default, a form only validates the `Default` group - yep, first letter capitalized
- So `edit` never runs yet
- Open `StarshipType`
- First, delete `createdAt` and `updatedAt` fields - those are set automatically
- Next, allow null for `arrivedAt`, for this set `'required' => false,`
- In `StarshipPartType`, we disabled HTML5 validation for the specific button only
- Here, I will disable HTML5 validation for the entire form completely so that we could check server-side validation
- In `configureOptions()`, add `'attr' => ['novalidate' => true,],` 
- That's a nice trick that skips HHTML5 validation entirely when clicked
- Right now, if you reload /admin/starship/new and submit empty form
- We will see "Every starship needs a name" error
- Back to `StarshipType`, in `configureOptions()`
- Add a `validation_groups` option set to a closure:
  ```php
  'validation_groups' => function (FormInterface $form) {
      /** @var Starship $starship */
      $starship = $form->getData();
      $isEdit = $starship && $starship->getId();

      return $isEdit ? ['Default', 'edit'] : ['Default'];
  },
  ```
- We reuse the same new-vs-edit logic from before, but now to pick validation groups
- Go to /admin/starship/new, leave arrival empty, submit - only `Default` runs, so only empty name complains
- Now edit an existing ship, clear the arrival date, submit - the `edit` group runs now

## Custom validation constraint
- Callbacks are flexible, but they live inside ONE class and can't be reused
- And they can't easily use services - what if a rule needs a repository or a standalone service? or specific config?
- For that, we build our own reusable constraint with its own validator class
- Some ship names are reserved (say "Death Star", "Imperial Star Destroyer", "Executor" - no service for those!) - let's forbid them
- Run `symfony console make:validator`
- Call it `ForbiddenName`
- It generates two files in `src/Validator/`: `ForbiddenName` (the constraint) and `ForbiddenNameValidator`
- Open `ForbiddenName` - this is just PHP attr config: tweak the `$message`
  e.g. `public string $message = 'The name "{{ value }}" is not allowed to be registered on our shop - go away!';`
- Explain `#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]`
- That fits our case perfectly, so no changes needed
- Open `ForbiddenNameValidator` - this is where the logic lives
- Here's the whole point: we can inject services here (impossible in a callback/attribute)
- Add a constructor with the forbidden list injected, e.g. `public function __construct(private array $forbiddenNames = ['Death Star', 'Imperial Star Destroyer', 'Executor'])`
  (in real life this could be a service, a repository, or a bound parameter because `ForbiddenNameValidator` is just a service and can inject other services)
- In `validate()`, guard the empty case - this default if is good, then add ours:
  ```php
  if (!in_array(strtolower($value), array_map('strtolower', $this->forbiddenNames), true)) {
      return;
  }
  ```
- At the very end we already have a perfect `buildViolation()` code - I will just drop the TODO:
  ```php
      $this->context->buildViolation($constraint->message)
          ->setParameter('{{ value }}', $value)
          ->addViolation();
  ```
- Now use it: open `src/Entity/Starship.php`
- Add `#[ForbiddenName]` on the `$name` property and import the constraint
- Finally, verify everything landed with `symfony console debug:validator "App\\Entity\\Starship"`
- You'll see all constraints per property, including our `ForbiddenName` and the group'd `NotNull`
- `debug:validator` is your rock-solid way to see exactly which rules apply to a class
- Time to try it in action!
- Go create a ship named `DEATH STAR` - blocked with our message!
> The name "DEATH STAR" is not allowed to be registered on our shop - go away!
- Any other name works fine

## Access unmapped fields
- We can create starship parts, but can't delete them, let's fix it
- Deleting stuff is dangerous, so let's build a proper confirmation
- But instead of the JS style that was generated by Maker for Starships, I'd like GitHub style
- On GitHub, to delete a repo you must type its name - let's do the same for a part
- First, the form - run `symfony console make:form DeleteStarshipPartType`
- Type `StarshipPart` for the entity
- Open `src/Form/DeleteStarshipPartType.php`
//- Set `'data_class' => StarshipPart::class,` so we get the part in `$options['data']`
- I will drop all the auto-generated fields, we don't need them
- Now add a text field where the user must retype the part's name
- Add `->add('confirmName', TextType::class)`
- And I will also add the button here: `->add('delete', SubmitType::class)`
- Not go to `AdminController`
- We need a separate page where we will render this form 
- Create a new action: `public function deleteStarshipPart()`
- Register this route as `#[Route('/starship-part/{id}/delete', name: 'app_admin_starship_part_delete', methods: ['GET', 'POST'])]`
- Inside, typehint `StarshipPart $starshipPart,`
- And create the form w/ `$form = $this->createForm(DeleteStarshipPartType::class, $starshipPart);` - we will pass the entity as the 2nd argument 
- And pass it to the `admin/starship-part/delete.html.twig`
- Create the template
- Add a red "danger zone" heading like `Delete "{{ part.name }}"?`
- Render the form with `{{ form(form) }}`
- Last, add a delete link on the list - open `templates/part/index.html.twig`
- Render a link to the delete page in `for` loop:
  `<a href="{{ path('app_admin_starship_part_delete', {'id': part.id}) }}" title="Delete">🗑️</a>`
- Go to /parts
- Click the link - it opens an empty page
- Time to render the form
- Back to `delete.html.twig`
- Render `{{ form(form) }}`
- Back in `deleteStarshipPart()`
- Handle it w/ `$form->handleRequest($request);`
- Below, `if ($form->isSubmitted() && $form->isValid()) {`
- Inside if: `remove($starshipPart)` & `flush()`
- I will also add a flash message: `$this->addFlash('success', 'The part was successfully deleted.');`
- And don't forget about `return $this->redirectToRoute('app_part_index');` at the end
- Reload the delete page - an error:
  > Can't get a way to read the property "confirmName" in class "App\Entity\StarshipPart".
- Right - there's no `confirmName` on `StarshipPart`, and we don't WANT one
- This is a throw-away confirmation input, not something to persist
- Fix it with `'mapped' => false,` - now the form won't read/write it to the object
- But how do we validate the typed value matches the real name?
- Grab the part first: at the top of `buildForm()`, add `$part = $options['data'];`
- Add `/** @var StarshipPart $part */` above it
- Attach an `EqualTo` constraint right on the field (field-level constraints, remember?):
  ```php
  ->add('confirmName', TextType::class, [
      'mapped' => false,
      'constraints' => [
          new EqualTo(
              value: $part->getName(),
              message: 'The name does not match this part',
          ),
      ],
  ])
  ```
- Let's also disable the label `'label' => false,`
- And add `'help' => sprintf('Type "%s" to confirm deletion', $part->getName()),`
- Try it: click delete, type the WRONG name, submit - blocked by `EqualTo`
- Type the correct name, submit - the part is gone, no ancient JS `confirm()` popup needed
- Bonus: if you ever need the raw typed value, it's `$form->get('confirmName')->getData()`
  (unmapped fields never touch your object, so this is the only way to read them)

## Create a custom type extension
- Quick question: where did the `constraints` option we just used come from?
- It's NOT part of `FormType` - open its source and search, you won't find it
- Symfony adds it to EVERY field type via a "type extension" - same story for CSRF and `help`
- A type extension lets you add options/behaviour to existing types without a new type
- Let's build our own: a `tooltip` option available on every single field
- Create `src/Form/Extension/TooltipTypeExtension.php`
- Make it extend `AbstractTypeExtension`
- Tell it which types to hook into:
  ```php
  public static function getExtendedTypes(): iterable
  {
      return [FormType::class];
  }
  ```
- `FormType::class` means ALL fields, because every built-in type ultimately extends it
  (if we wanted only text inputs, we'd return `TextType::class` instead)
- Now declare the option in `configureOptions()`:
  ```php
  $resolver->setDefault('tooltip', null);
  $resolver->setAllowedTypes('tooltip', ['null', 'string']);
  ```
- Options don't reach the template by themselves - expose it on the view, override `buildView()`:
  `$view->vars['tooltip'] = $options['tooltip'];`
- That's it - thanks to autoconfigure, the extension registers itself (tag `form.type_extension`)
- Verify it's live: run `symfony console debug:form FormType`
- Search the options list - our `tooltip` is now there, on the base type
- Now let's actually render it - open our global theme `templates/_form-theme.html.twig`
- Override the `form_label` block to append an info icon when a tooltip is set:
  ```twig
  {% block form_label_content %}
      {{ parent() }}
      {% if tooltip is defined and tooltip %}
          <span class="ml-1 cursor-help text-gray-400" title="{{ tooltip }}">ℹ️</span>
      {% endif %}
  {% endblock %}
  ```
- `parent()` works here - `form_label_content` exists in the Tailwind theme we `use`
- Time to try it - open `StarshipPartType`
- On the `name` field, add `'tooltip' => 'Give your part a short, creative name',`
- On the `price` field, add `'tooltip' => 'Prices are in galactic credits',`
- Reload /admin/starship-part/new - hover the ℹ️ next to the label, the tooltip shows!
- The magic: this option now works on ANY field of ANY form, zero changes to the field types
- Don't believe me? Try `'tooltip' => ...` on a field in `StarshipType`

## Embed forms
- Open /admin/starship/{id}/edit - we can edit the ship, but its parts live on a totally separate page
- Wouldn't it be nice to tweak a ship's parts right here, and save everything at once?
- A `Starship` has a `parts` collection (a `OneToMany`), so let's embed a sub-form for each part
- First, we need a form for a SINGLE part - run `symfony console make:form EmbeddedStarshipPartType`
- Type `StarshipPart` for the entity
- Yep, this time we need `StarshipPart` as you will see soon
- Open `src/Form/EmbeddedStarshipPartType.php`
- Trim it down to the fields we want to edit inline: `name`, `price`, `notes`
- Remove the `starship` field - the parent ship IS the starship, picking it here makes no sense
- Remove any generated submit button too - the parent form owns the submit
- Keep `'data_class' => StarshipPart::class,` - note this maps the ENTITY this time, not the DTO
- Now embed it - open `StarshipType`
- Add a collection field:
  ```php
  ->add('parts', CollectionType::class, [
      'entry_type' => EmbeddedStarshipPartType::class,
      'label' => false,
  ])
  ```
- `entry_type` is the form rendered for EACH item in the collection
- Reload /admin/starship/{id}/edit
- Every existing part now renders as its own little sub-form, right inside the ship form
- Peek at the HTML - the field names are indexed: `starship[parts][0][price]`, `starship[parts][1][price]`...
- That's how Symfony keeps each embedded entry separate
- Try it: change a part's price, submit the ship form - the part is updated!
- All embedded forms are validated and saved together, in one request
- And notice: ZERO JavaScript - this is pure server-side form embedding
- Why no cascade needed? Existing parts are managed Doctrine entities, so editing them just flushes
- About dynamic add/remove - that's a whole topic on its own - we cover it in a dedicated tutorial
- For now, the takeaway: you can embed and edit an entire collection of forms with no JS at all
- If you try to set price to 0 - it allows us!
- It was not validated because of the embed form
- To fix, we need to add `#[Assert\Valid]` on `$parts` prop
- Try again - a validation error now!

## Form data transformers
- Let's give ships some tags, like `flagship`, `medical`, `stealth`
- Open `src/Entity/Starship.php`
- Run `symfony console make:entity`
- Update `Starship` entity
- Add `tags` property
- Choose `json` type
- Nullable - no
- Go check the new prop on entity - open `Starship`
- Make a migration and run it: `symfony console make:migration`
- Then `symfony console doctrine:migrations:migrate`
- Now add the `tags` field - open `StarshipType`
- Add `->add('tags')` - it renders as a text input
- I don't want this field be required, so I will add `'required' => false,`
- Go to /admin/starship/new
- Fill in the forms, add a tag and submit - an error:
  > Expected argument of type "array", "string" given at property path "tags".
- The model data is an ARRAY, but a text `<input>` needs a STRING - they don't match
- Sidenote: yes, we COULD add a string-ish setter to the entity and `explode()` there
- But that leaks a form-only format into our domain model, breaks our honest `array` type,
  and can't turn bad input into a clean form error - a transformer fixes all three (more on errors soon)
- A data transformer converts the value between the object and the input, both ways
- Create `src/Form/DataTransformer/TagsToStringTransformer.php`
- Make it implement `DataTransformerInterface`
- `transform()` - runs when RENDERING (array -> string):
  ```php
  return implode(', ', $value);
  ```
- Tweak return type to `string`
- `reverseTransform()` - runs on SUBMIT (string -> array):
  ```php
  return array_filter(array_map('trim', explode(',', $value)));
  ```
- Tweak return type to `array`
- Also, `explode()` expects value to be a string, so let's make sure we don't have null at that spot
- For this, before that line, add `if (null === $value || '' === trim($value))`
- And `return []`
- Now attach it - back in `StarshipType::buildForm()`
- `$builder->get('tags')->addViewTransformer(new TagsToStringTransformer());`
- (why `addViewTransformer` and not `addModelTransformer`? that's the next chapter)
- Reload - the tags show as a comma-separated string, editable
- Type `flagship, medical`, submit - check the DB, it's stored as a JSON array!
- If there's a transformation error - you can use a special `TransformationFailedException`
- Let's allow only known tags
- In `reverseTransform()`, change logic to: 
```php
        $tags = [];
        foreach (array_filter(array_map('trim', explode(',', $value))) as $input) {
            $canonical = strtolower($input);
            if (!in_array($canonical, self::KNOWN_TAGS, true)) {
                throw new TransformationFailedException(sprintf('"%s" is not a known tag. Known tags: %s.', $input, implode(', ', self::KNOWN_TAGS)));
            }
            $tags[] = $canonical;
        }
```
- Now transformer is responsible for transforming tags into known tags - this justifies the use of TransformationFailedException
- Refresh the page and type `flagship, medical, unknown`
- It gives us an error now:
  > This value is not valid.
- We can even customize it, open `StarshipType` and add `[]` for options for `tags` field 
- Customize the default error message with the `'invalid_message'`
- I will set it to `sprintf('An unknown tag is used. Known tags: %s.', implode(', ', TagsToStringTransformer::KNOWN_TAGS))`
- Try to submit the form again with an unknown tag - the error message is now customized!
- This is the transformer's superpower: a failed conversion becomes a clean FORM error, not a 500

## Model transformer vs View transformer
- We just called `addViewTransformer()` - but there's also `addModelTransformer()`. What's the difference?
- Every form field has THREE representations of its value, let's SEE them
- Reload /admin/starship/{id}/edit and open the profiler for the request
- Click the "Forms" panel, then click the `tags` field
- Look at the "Default Data" section - three rows: Model data, Normalized (norm) data, View data
- Right now: Model = `array`, Norm = `array`, View = `"flagship, medical"` string
- Our VIEW transformer sits between Norm and View - it only changed how the value is DISPLAYED
- Norm stays the "real" array - that's the whole idea of a view transformer
- Little experiment: change `addViewTransformer` to `addModelTransformer`, reload the profiler
- Now: Model = `array`, Norm = `"flagship, medical"` string, View = `"flagship, medical"`
- One word changed, and the Norm layer flipped from array to string - see it live!
- A MODEL transformer sits between Model and Norm - it changes the value's TYPE earlier in the chain
- Here's the mental model, from your object to the input:
  ```
  Model data  <—(model transformer)—>  Norm data  <—(view transformer)—>  View data
  ```
- Rule of thumb:
  - Changing the DISPLAY format (array/date/number -> string) -> VIEW transformer (norm keeps the real value)
  - Adapting your STORED type to the field's canonical (norm) type -> MODEL transformer
    (e.g. `DateType`'s `input` option: `DateTimeImmutable`/timestamp <-> the canonical `DateTime` norm;
    our `arrivedAt` above is exactly this)
- Heads up: `EntityType`'s entity <-> id is actually a VIEW transformer (norm stays the entity),
  the id string only appears at the view layer - so don't use it as the "model transformer" example
- For our tags, either technically works, but a view transformer is correct: the array is the real value, only the string is presentation
- Switch it back to `addViewTransformer`
- Proof it's everywhere: click the `arrivedAt` field in the profiler
- Model = `DateTimeImmutable`, Norm = `DateTime`, View = `"2026-07-30"` string
- Core's DateType uses a built-in VIEW transformer - exactly the same idea as our tags, just shipped with Symfony
- And to be clear: this is NOT `buildView()` - transformers PRODUCE the view data both ways,
  `buildView()` only EXPOSES it to Twig at render time

## Unit-testing forms: start with the isolated pieces
- What about testing forms?
- Before testing a whole form type, notice how much form LOGIC we pushed into tiny standalone classes
- Those are the easiest and most valuable things to test - no framework bootstrapping needed
- This is a hidden payoff of transformers/validators: they're trivially unit-testable
- Make sure the test tools are installed: `symfony composer require --dev symfony/test-pack`
- First, test the transformer
- Run `symfony console make:test`
- Choose `TestCase` (plain PHPUnit test, no Symfony dependencies)
- For the file name: `Form\DataTransformer\TagsToStringTransformerTest`
- It will create `tests/Form/DataTransformer/TagsToStringTransformerTest.php` - open it
- It extends plain `PHPUnit\Framework\TestCase` - this class has zero dependencies on Symfony, ideal for our case
- Create `testTransform()`
- Inside: `$transformer = new TagsToStringTransformer();`
- Check for `$this->assertEquals($transformer->transform(['flagship', 'cargo']), 'flagship, cargo');`
- Test the empty cases:
  - `$this->assertEquals($transformer->transform([]), '');`
  - `$this->assertEquals($transformer->transform(null), '');`
- Run it with `symfony php bin/phpunit --filter=testTransform`
- Whoops, failed:
  > TypeError: implode(): If argument #1 ($separator) is of type string, argument #2 ($array) must be of type array, null given
- Yeah, fair, let's fix our transformer: `if (null === $value) { return ''; }`
- Run again - now tests pass!
- Create `testReverseTransform()`.
- Inside, `$transformer = new TagsToStringTransformer();`
- Check good path: `$this->assertEquals($transformer->reverseTransform('flagship, cargo'), ['flagship', 'cargo']);`
- Check trimming: `$this->assertEquals($transformer->reverseTransform('flagship , , stealth ,'), ['flagship', 'stealth']);`
- Also `$this->assertEquals($transformer->reverseTransform(''), []);`
- And `$this->assertEquals($transformer->reverseTransform(null), []);`
- Finally, let's `$this->expectException(TransformationFailedException::class);`
- And `$this->expectExceptionMessageIsOrContains('"unknown" is not a known tag');`
- Finish with `$transformer->reverseTransform('flagship, unknown');` that should throw
- OK, run the whole suite: `symfony php bin/phpunit`
- Green! A pure, fast test with no DB, no container, no HTTP
### Test custom validator
- Next, the custom validator
- Run `symfony console make:test`
- Choose `TestCase` again
- For the file name: `Validator\ForbiddenNameValidatorTest`
- It will create `tests/Validator/ForbiddenNameValidatorTest.php` - open it
- Actually, Symfony ships a special base class for this
- Instead, extend `ConstraintValidatorTestCase`
- Implement `createValidator()` to return `new ForbiddenNameValidator(['bad', 'forbidden'])`
- Next, create `testAllowedName()`
- Inside, test successful path: `$this->validate('foo', new ForbiddenName());`
- Below `$this->assertNoViolation();`
- Now create `testForbiddenName()`
- Inside, test failure: `$this->validate('bad', new ForbiddenName());`
- Then `$this->buildViolation('The name "{{ value }}" is not allowed to be registered on our shop - go away!')`
- Chain with `->setParameter('{{ value }}', 'bad')`
- And finish w/ `->assertRaised();`
- Run the suite again: `symfony php bin/phpunit` - green!
- Two small classes, fully covered, and we haven't even touched the form yet

## Unit-testing a form type with TypeTestCase
- Now the form type itself - Symfony has a dedicated base class: `Symfony\Component\Form\Test\TypeTestCase`
- Let's create one more test: `symfony console make:test`
- Choose `TestCase` again
- For the file name: `Form\StarshipTypeTest`
- It will create `tests/Form/StarshipTypeTest.php`
- Open it and extend `TypeTestCase` instead
- The core pattern: build an object, submit an array, assert the object got populated
- First, rename method to `testSubmitValidTags()`
- Inside, add:
  ```php
  $starship = new Starship();
  $form = $this->factory->create(StarshipType::class, $starship);

  $form->submit(['name' => 'Nostromo', 'tags' => 'flagship, cargo'], false); // false = don't clear missing fields

  $this->assertTrue($form->isSynchronized());
  $this->assertSame('Nostromo', $starship->getName());
  $this->assertSame(['flagship', 'cargo'], $starship->getTags());
  ```
- Note the `false` 2nd arg to `submit()` - a partial submit, so we skip the noise of every other field
- Run the test: `symfony php bin/phpunit --filter StarshipTypeTest`
- An error:
  > UndefinedOptionsException: The option "widget" does not exist
- Yep, that's because type guessers works only in a real container, not in `TypeTestCase`
- And our DateTimeType field becomes just simple TextType in tests, which does not have that `widget` option
- Best practice is to declare types explicitly in form types
- Open the type and change `->add('arrivedAt', DateTimeType::class, [...])`
- Run again: `symfony php bin/phpunit --filter StarshipTypeTest`
- Now it says:OK, but there were issues!
  > OK, but there were issues!
  > Tests: 1, Assertions: 3, PHPUnit Notices: 1.
- Hm, rerun the command with `--display-phpunit-notices`
  > No expectations were configured for the mock object for Symfony\Component\EventDispatcher\EventDispatcherInterface. Consider refactoring your test code to use a test stub instead. The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.
- Yeah, that's from the internals of `TypeTestCase` because of the recent changes in PHPUnit behavior
- The easiest - add that attribute to the test class: `#[AllowMockObjectsWithoutExpectations]`
- Rerun again `symfony php bin/phpunit --filter StarshipTypeTest` - green!
- Don't test the validation: it is applied by a listener that is not active in the test case
  and it relies on validation configuration. Instead, unit test your custom constraints directly
  as we did above
- It has low value to test other simple fields like `class, captain, slug, arrivedAt`
  they are just simple fields with no custom logic, so we can skip this noise,
  because it's more like we're testing Symfony Form component than our own code
- Now test the transformer's FAILURE at the invalid tags
- Create `testSubmitInvalidTags()`
- Inside, `$starship = new Starship();`
- `$form = $this->factory->create(StarshipType::class, $starship);`
- Submit an unknown tag: `$form->submit(['name' => 'Nostromo', 'tags' => 'flagship, foobar',]);`
- But now Assert `$this->assertFalse($form->isSynchronized());` - the `TransformationFailedException` desyncs the form
- Run the tests again - oh, it's failed! But why? The transformer is throwing, so the form should be desynced
- Ah, yes... the problem is that `isSynchronized()` works per-field, and
  no transformation was to the form but only to the `tags`
- Fix both calls to `$form->get('tags')->isSynchronized()`
- Run tests again - green!
- Now test conditional `status` field
- Create a new method `testIsEditMode()`
- Create the form again: `$form = $this->factory->create(StarshipType::class, $starship);`
- Next, `$this->assertFalse($form->has('status'))`
- And `$this->assertFalse($form->get('slug')->isDisabled());`
- Below, let's check for the existent entity
- But for this we need to add a `setId()` method to the entity, just for testing purposes:
    ```php
    /**
     * @internal use in tests only
     */
    public function setId(int $int)
    {
        $this->id = $int;
    }
    ```
- Back in test, set the id w/ `$starship->setId(1);`
- Create the form again: `$form = $this->factory->create(StarshipType::class, $starship);`
- Assert `$this->assertTrue($form->has('status'))`
- And `$this->assertTrue($form->get('slug')->isDisabled());`
- Another approach would be to use reflection in the test to set the private `id` prop
- Finally, create another form passing `'is_admin' => true,`
- And now `$this->assertFalse($form->get('slug')->isDisabled());`
- Important notes:
  - CSRF is OFF here by default (no session), so submit without a token - that's expected
  - Validation does NOT run in `TypeTestCase` - it's about DATA BINDING, not constraints,
    so `price > 0`, `Assert\Valid`, `EqualTo`... none of those fire here - that needs a functional test



# TODO I'm not sure we should show it too, too much coding on this topic, probably just mention it and link to the dedicated testing courses?
## Functional test: where validation actually runs
- To test validation end-to-end, we go through the real app with booted kernel
- For this, create another test w/ `symfony console make:test`
- Choose `WebTestCase`
- Let's try to check another form: the StarshipPart delete confirmation
- Name the file `Controller\StarshipPartDeleteTest`
- It will create `tests/Controller/StarshipPartDeleteTest.php` extending `WebTestCase`
- Load a part - we have Foundry that could help with it!
- Then request its delete page
- Submit the form with the WRONG name -> assert the part still exists and an error is shown
- Submit with the CORRECT name -> assert a redirect and the part is gone from the DB
- This is the layer where the validator is fully wired - exactly what `TypeTestCase` can't cover
- Takeaway: unit-test the pieces and the type (fast, isolated), functional-test the validation (real, end-to-end)
- Want to go deep in tests - look at our dedicated testing courses! (link to it)
