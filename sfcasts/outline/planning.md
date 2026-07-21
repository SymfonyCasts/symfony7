# Symfony 8 Forms Advanced

Related PR: https://github.com/SymfonyCasts/tutorial-planning/pull/94 

- Form rendering
    - Cover form rendering variables via `form.field.vars`
    - Cover more form field helpers left uncovered: `form_(id|name|value|label|help|choices)` - an iterator for choice fields
    - Allow HTML contents in form labels w/ `label_html` option
    - Translatable help messages w/ `new TranslatableMessage()` that include all the information needed
- Form theming
    - Form theme blocks
    - Form theme block variables
    - The `block_prefix` option for easier form customization
    - Kevin: One thing I wanted to mention is: let's show a rock solid method to find the different block names - maybe from the profiler? This trips me up a lot (
    - Custom form theme based on core one
    - Custom reusable form field type based on core one
    - Custom type /w theme (a custom widget)
- Advanced validation
    - Flexible validation callback constraint
    - Configure form validation groups
    - Custom validation constraint
        - The `make:validator` command
        - The `debug:validator` command
- Form data
//    - Data Transfer Object (DTO) instead of entities in form types  
//    - Configure empty data for a form type class
//    - Сonditionally change/show/hide form fields
//      based on the underlying data (instead of passing those via constructor directly).
//    - Custom form field options
    - Form mapping getter/setter callbacks
    - Form data transformers
    - Model transformer vs View transformer
    - Custom data mapper
    - Inherit form data from parent form w/ `inherit_data` option
- Embed forms
- Access unmapped fields. Show a "confirm" checkbox as an unmapped field,
  or maybe like a GitHub confirmation input for deleting repos?
  E.g. you need to type capitan name to remove the ship
- How to unit-test your forms - a chapter or two

## Extra / Bonus
- Create a custom type extension (behaviour added to all forms types) e.g.
  similar to `help` extension from Symfony core
- Create a custom type guesser

## Possible mini-courses
1. File uploads:
    - How to upload files
    - VichUploaderBundle
    - LiipImagineBundle
    - Symfony UX Cropper.js via `CropperType`
2. Form flow: steps and events
    - FormFlow for Multi-step Forms
    - Dynamic form events
    - Dependent form fields w/ `SymfonyCasts/dynamic-forms`:
3. JS in forms:
    - Embed a collection of forms
        - Allow adding new item via prototype
        - Allow removing items
        - Add some JS to add/remove dynamically w/o submitting the form
        - The `by_reference` option
    - Bind a custom JS to a field type (something like Symfony UX TogglePassword which is deprecated now, but with a completely standalone JS lib)
    - Symfony UX Autocomplete <select>
