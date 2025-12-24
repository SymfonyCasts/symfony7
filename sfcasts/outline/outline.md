# Symfony 7 Forms outline

## Prerequisites
- Upgrade to Symfony 7.3 to have `$form->getClickedButton()->getName()` instead of `isClicked()`
  see: https://symfony.com/doc/current/form/multiple_buttons.html
- Disable Turbo on the whole website, we will enable it late

# 1. Intro
- Are forms still useful in 2025 JavaScript world? Aren't forms ancient?
- It depends on what you're building
- Traditional forms-reach interfaces vs modern JS frontend vs Symfony Live Components
- Power of Symfony Form component: handling request, validating data,
  CSRF protection, etc
- Let's become masters of form tags, inputs & textareas
- Download the course code from this page and unzip it
- Go to the `start/` dir and open the README.md for the instructions
  on how to get the website set up
- I've already done all the steps, so I will just run the web server with `symfony serve`
- Open the website at https://localhost:8000
- Welcome to our Starshop website!
- Go to `/parts`
- Things are going very well in our Starshop,
  now we want to create new parts for sharships 
- I already added a new link: "Create a new part"
  that leads to `/admin/starship-part/new`
- The actual form is a TODO
## Installing Symfony Form Component 
- Let's add a form here, but first install Symfony Form component with:
  `symfony composer require form` command
## Build a form type class with Maker
- We can build the form manually, but Maker bundle can help with it!
- Create our first form with:
  `symfony console make:form` command
- Btw, if you run this command without that package installed - Maker will hint you
  what you need to install
- You can name it as you want, but practice would be to add the "Type" suffix to it
- Set form class to `StarshipPartType`
- Symfony Form component can map form data to an entity. In this
  specific example that's something we want, so I will specify
  `StarshipPart` entity
- Done!
- It created a new file in `src/Form/` dir
- Open the file
- It extends `AbstractType` came from that package we installed earlier
- Maker added each entity property as a field to our form, sweet! Less boring typing
- You can see it even added `createdAt` and `updatedAt` fields
  from the `TimestampableEntity`
- I will remove them - those are set automatically
- The related class entity is written as `data_class` in
  `configureOptions()` we specified in the 2nd question.
  It binds form data to that specific entity class automatically
## Creating Form object from type with `createForm()`
- Now, let's create the actual form object in the controller
- Open `AdminController::newStarshipPart()`
- Inside, call `$form = $this->createForm...`
- Yeah, we have `createForm()` & `createFormBuilder()`,
  you can build a form right in the controller using the
  form builder and skipping creating form type class,
  but best practice is to create a separate class for this
  like we just did
- So I will choose `createForm()`
- Only one required argument - type
- Type our `StarshipPartType::class` there
## `Form` object in PHP vs `FormView` in Twig
- That's it, form created
- If you `dd($form)` it below - that's a Form object
- Next, let's pass it to the template
- For this, pass it to the template `['form' => $form,]`
- However, if we `dump(form)` in the template
- Huh, we have a `FormView`
- Well, behind the scenes, Symfony transformed that `Form` object
  into `FormView` that is ready to be rendered in the template
- Not a big deal for us, but you should mind those are 2 different
  objects, not the same one


# 2. Rendering the form with Twig helper functions
- In previous chapter we installed Symfony Form component
- And created a form type from which we created a form
- Time to render the form in the template now
- And Form component provided us with some Twig helper functions for this
- Write `{{ form(form) }}` instead of the form dump
- Head to the browser
- Refresh the page
- Here's our form!
- I mean, oh my, that looks terrible, but we will work on styles later
- If you open it in the inspector - you will see it's sent via POST method
- By default Symfony send forms via POST, but we can control it manually if needed
- We will see later how to send the form via GET method
- Also, form action is empty - that means the form will be sent
  to the same URL
- You can specify the action URL explicitly, but in most cases it's more convenient
  to leave it empty, especially if you use the same form that
  on different pages should be sent to different URLs
## Tailwind forms plugin
- For now, since we're using Tailwind CSS, let's enable Tailwind forms plugin
- Open `app.css`
- Add `@plugin "@tailwindcss/forms"` there
- This plugin provides a basic reset for form styles that makes
  form elements easy to override with Tailwind classes
- Refresh the page again
- Now it looks much better
## Adding a Submit Button to the Form
- But for now, we're missing something important in our form!
- How can we add a submit button to this form?
- There's a few ways. The best practice is to create a submit
  button manually in the template
- Add `<button type="submit">Create</button>`
- I also add some CSS classes for it:
  `text-white bg-green-700 hover:bg-green-800 rounded-lg px-5 py-2.5 me-2 mb-2 cursor-pointer`
- But we need this button to be inside the form, not out of it
- For this, we can render the form part by part
- Change it to `{{ form_start(form) }}` - this will render
  only the opening form tag 
- Next, `{{ form_widget(form) }}` that will render all the
  fields in the form
- Then put our button
- Finish below with `{{ form_end(form) }}` to render the
  closing form tag properly
- Refresh the page to see the button
- Btw, I have temporarily turned off Turbo Drive globally for this website,
  you can see Turbo-related code commented out in `app.js`
- Make sure you have Turbo also disabled to match the videos in this course
- With Turbo enabled, when you navigate the website - it will send AJAX requests
  to load the new page giving you SPA feel. Even our forms will be submitted
  via AJAX requests which may complicate debugging
- So disabling Turbo will make work with forms more straightforward 
- OK, now fill in the form and press that "Create" button
- Did it work? Well, it did, but we don't do anything with those data yet.


# 3. Processing the submitted form in the controller
- So we can send the form now
- Let's find out how to handle it and save the data
- Back to the controller
- Below the form object, call `$form->handleRequest();`
- We will need the current Request to pass to it
- We know the drill, inject `Request $request` to the method
- And pass it to the `handleRequest()`
- It will fetch from data from the request and apply to the form
- After this our form will contain data from the request
- Then add `if ($form->isSubmitted())` to check if the form was actually sent
- If so, then inside we will fetch data with `$form->getData()`
- Since we set `data_class` to `StarshipPart` in the form type - form will give us
  an instance of that class instead of just array
- So we can call `$part = $form->getData();`
- Below, let's `dd($part);`
- Refresh the browser  
- Yes, that's our StarshipPart object
- I will add `/** @var StarshipPart $part */` above the var to help my PhpStorm
  with auto-completion
- Notice that this object does not have ID set... yet
## Save the form data in the database
- To save the entity, we know the Doctrine chores
- First, inject `EntityManagerInterface $entityManager`
- Then call `$entityManager->persist($part)`
- And call `$entityManager->flush()`
- Now fill in the form
- I will set name field to how about "Hyperdrive coupler"
- Set a price for it
- Add a little note: "Be careful with high revs!"
- And submit form
- Did it work? Open the `/parts` page
- Search for the just created part - here it is!
## Showing a flash message on a successful form processing
- To celebrate (make it clearer) about a new part was just created - let's add
  a _flash message_
- Flash message is a temporary message that should be shown only one time to the
  user
- It stores in the session and as soon as it's shown - it's removed from the session
- It's ideal thing when you just want to say to the user that something was
  successful saved
- Behind the scene, in the `base.html.twig`, you can see we have code
  responsible for rendering flash messages
- We can use such types as success, error, etc. and they will be highlighted
  with a proper color - sweet!
- After we saved the entity, let's add a success message:
  `$this->addFlash('success', sprintf('The part "%s" was created.', $part->getName()));`
- Where first argument should be a type of the message so that we know
  how to handle it, e.g. highlight with a proper color
- The 2nd arg is the actual message we want to say to the user
- And finally, to avoid accidentally submit the same form again,
  let's finish it with a redirect - that's usually best practice after
  you submit forms via POST request, otherwise users will be able
  to resend the same data when they reload the page - we don't want any
  starship part duplicates to be saved!
- We can redirect to the same page, or better redirect to:
  `return $this->redirectToRoute('app_part_index');`
- Let's see how it works
- Update the page, and create a new part
- We're redirected and see the flash message
- Sweet!
- But what if we want to do slightly different business logic on form submit?
- Sometimes I want to create several parts in a row? Even though I can do
  it with a few extra clicks - can we add the second submit button that
  instead of save and close will save and remain on the same form page?
- It would be just perfect for my batch creation when I have a good mood
- Let's figure our how to do it in the next chapter!

# 4. Handling multiple submit buttons
- What if we want to have 2 buttons - one normal that creates and closes
  the page and another tha creates and stay on the same from page?
- Before, I told you that there are several ways of adding buttons
  to the form, let's see the 2nd way 
- I will rename the current button into `Create and close`
- Now open the form type
- Add new field called `createAndAddNew`
- Set its type to a special `SubmitType::class`
- Symfony will render it as a submit button for us
- Open the controller
- So far, we know that we can get form data with `getData()` but in our
  case the `getData()` method gives us not all the data on the form
  because it maps from data to the entity and returns it instead
## Accessing unmapped form fields directly
- To get access to the unmapped field we can call the `get()`
  instead and specify the field name directly
- Get our button and set it on a var: `$createAndAddNewBtn = $form->get('createAndAddNew')`
- Below, dump it with `dd($createAndAddNewBtn)`
- Now head back to the browser
- Fill in the form and click on "Create and close"
- Here's our dump of `SubmitType` that has some fields 
- One of it is `clicked` which is set to `false`
- That's exactly what we need
- Now return back and click on the "Create and add new"
- Now `clicked` is set to `true`
- OK, back to the controller
- Let's see if we have `isClicked()` method on that btn  
- Call `if ($createAndAddNewBtn->...)`
- Hm, no autocomplete, but from the dump we saw it should have
  `clicked` property
- I will try to add above the btn: `/** @var SubmitButton $createAndAddNewBtn */`
- Try now - aha, now we have the autocomplete for this method
- Call it in the if: `$createAndAddNewBtn->isClicked()`
- Inside, add: `return $this->redirectToRoute('app_admin_starship_part_new')`
- Try to refresh the page again
- The success flash message, but we're still on the same page
  and ready to create a new part!
- This is a legit way of adding buttons to the form and in our
  case it's especially useful, but if you can avoid it and
  add buttons in plan HTML - you will simplify life to your
  designer friends
## Built-in form field types
- Head back to the form type
- The 2nd arg is the form field type, but in most fields we have null!
- Well, Symfony Form has so-called "field type guessing"
- If you set field type to null - Symfony will try to guess
  the best type for the field among known built-in types
- How? Great question! It looks at the related entity class
- For example, that `$price` property is an integer on `StarshipPart` entity
- So Symfony automatically applied `IntegerType` for it
- Behind the scene it will be rendered as `<input type="number"`
  instead of more common `<input type="text"` - see it in the HTML inspector
- We can specify it manually, or just let the Symfony do its best to guess for us
  that we want in most cases
- But in case Symfony can't guess the proper type and you need a different one,
  you can always override it with this 2nd arg
## The `debug:form` command
- But what built-in types Symfony has? You can always refer to the docs to know
- But there's a nerd-er way - you can leverage a special command
- Open the terminal
- And run `symfony console debug:form` command
- First of all, it lists all the built-in form types
- It also listed all our form types as services - we only have 1 for now
- And if you specify a form type as the argument to this command, e.g.:
- `symfony console debug:form TextType`
- It will show all available options, along with required ones
- Let's check for `debug:form EntityType` - the one that is used for Starship field
- It shows that `class` option is required there
- And we see it's set to the Starship class there
- Many options are inherited from parent and repeated in all types
- But some are special only to specific types
## Form field type options guessing
- Btw, Symfony does those field type *options* guessing as well
- Options can be specified within the array as the 3rd argument 
- Symfony also looks at the entity property's Doctrine mapping and if
  the property is not `nullable` - it will make the related field "required"
- That's why in the inspector you may notice some fields have
  `required="required"` attr
- But you can manually override default guessing
  and set some fields required or optional with `requred => true/false`
- OK, we will talk more about form field types later in this course
- In the next video, let's see how Symfony can make our form look
  prettier with build-in form themes and how we can apply them to our forms

# 5. Built-in Symfony form themes
- OK, long-waiting... and it's time to add better styling to this form
- Now tens of chapters of boring routine of CSS styling is waiting for us
- You can fast-forward to the end of this tutorial if you don't want work
  on these chores.
- Ok ok, I'm just kidding!
- Symfony comes with built-in themes for popular CSS frameworks
- Google for Symfony form themes
- Here it is! Some div/table layouts
- You use Bootstrap? Symfony provides you both default and horizontal
  layouts out of the box for different Bootstrap version!
- Love Foundation? This theme is also available
- Our site is written on Tailwind CSS - and Symfony has a theme for it too!
- Now, how to tell Symfony to apply a specific form theme to this form?
- There's a Twig tag for this!
- Copy the template name first
- Head back to PhpStorm
- In the `new.html.twig` template, in the very beginning, write: 
  `{% form_theme form 'tailwind_2_layout.html.twig' %}`
- We need to specify the form that will apply that theme
- Head back to the browser and refresh the page to see some changes
- Nice, the form looks much better with aligned fields
## An `EntityType` Form Type
- Now let's talk more about form types 
- In specific, let's focus on the Starship field
- It's set to a special `EntityType` - that one is interesting!
- In the previous chapter we saw it has a required option
- It requires `class` option is set to the proper entity FQCN
- Because the related property is referencing to the `Starship::class` entity,
- it will list starships in the select form field
- But right now it's displayed as cold soulless ID
- That code was generated by Maker, but we can easily change it
- If you look closer, there's `choice_label` key there set to `id`
- Let's change it to `name` - the field we have on Starship entity
- Refresh the page - much descriptive now!
- But the list is long, it's harder to search there
- Would it be cool to order that by name? Yes please!
## Using custom DB query for `EntityType`
- Add `query_builder` key to the field config
- Set it to anon function
- Add first arg as `function (EntityRepository $repo) {}`
- Inside the function: `return $repo->createQueryBuilder('starship')`
- Add `->orderBy('starship.name', ...);`
- I would like to order it by ascending order
- So I set it to `Order::Ascending->value` - make sure
  it's from `Doctrine\Common\Collections` namespace - which
  is a super nerdy way to write a simple `ASC` string 🙃
- Btw, since we have access to the whole query build here,
  you can even filter the result list with some `WHERE` clauses 
- OK, refresh to see the ordered list!
## Using custom callback for choice label
- Though seems we have some duplicated names there
- Well, our ships are from different galaxies, so that
  is totally possible
- In order to distinguish starships easier in that list
  you can add the entity ID to its name
- Or better, let's add the capitan instead the ID
- You probably may guess how we can do it
- Yep, just create a separate getter method, e.g. `getNameWithCapitan()`
- And set `choice_label` to `nameWithCapitan` value
- Remember, `get` prefix is added automatically
- But we can do it even cooler without create an extra method on the entity
- Set `choice_label` to `function (Starship $starship) {}`
- And inside `return sprintf('%s (by %s)', $starship->getName(), $starship->getCapitan());`
## Setting form field attributes in form type
- Btw, our new button does not have styles
- There's a special `attr` option that allows
  you to set HTML attrs on the field
- E.g. we can add some custom classes
- Pass an empty array as the 3rd arg
- Now add `attr` key
- Inside, add an HTML attribute we want - `class` in our case
- I will add same Tailwind CSS classes we have for the 1st button,
  but change green to blue in them:
  `text-white bg-blue-700 hover:bg-blue-800 rounded-lg px-5 py-2.5 me-2 mb-2 cursor-pointer`
- Go refresh the page to the styles
- We can set any HTML attributes this way, like `id` or even
  `data` attributes to attach a Stimulus controller, etc.
- Btw, there's also another option called `label_attr` to set attributes
  on the `<label` tag in case you need to style that too.
- Phew, we did a good job with advanced fine-tuning our form fields.
- Next, let's talk about something really cool and important - validation!
- Stay tuned!


# 6. Form Validation
## HTML5 validation
- Each part should have a name and price set
- But if we try to send an empty form - we will see some HTML5
  validation errors
- HTML5 is a client-side validation that happens in the browser
- If client's browser does not support it or it's disabled
  by client - the form will be sent to our server
- Let's temporary disable HTML5 validation for the submit button
- Open the form type, and for the submit button add `validate => false` 
- If you refresh the page and debug in code inspector
- You will notice it added `formnovalidate="formnovalidate"`
- Save and hit the button
- An error:
  > An exception occurred while executing a query: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'name' cannot be null
- This time it comes from our server
- To solve this problem we need a server-side validation
## Real server side validation
- Symfony has a different *Validator component* that helps with it
- And Form component has a great integration with it
- Let's install it first
- Run: `symfony composer require validator`
- I want to see a validation error if name is empty
- Validator component has a lot of built-in validation constraints
- We can add validation constraints right in form types
- For our purpose `NotBlank` will fit perfectly
- Open `StarshipPartType`
- Add `constraints` key to the `name` field`
- And add `new NotBlank()`
- Try to send an empty form again
- A DB error about "Column 'name' cannot be null"
- But if you look closer to the WDT - it has some errors now!
- There's our error with the default error!
- If form is invalid - we don't want to try to save the object to the DB
- Instead, we just want the form to be rendered with errors
- For this, change to: `if ($form->isSubmitted() && $form->isValid())`
- Update to see the errors rendered with the form
- Can we customize it? sure!
- Set the 2nd argument to `Every part should have a name!`
- Resend the empty form again to see the updated message 
- But wait, why it's not red will you ask me?
- Open the Chrome inspector and debug the code
- The error message has `text-red-700` CSS class
- Moreover, the failed field now also has `border-red-700`
- Those classes were added thanks to the built Tailwind theme we used,
  the error and the field are highlighted with red color
- We have `SymfonyCasts/tailwind-bundle` installed under the hood
- But why we don't see those red colors, didn't Tailwind have to compile it for us automatically?
- Well, yes... but those CSS classes was added dynamically,
  Tailwind didn't find them parsing our project files ignoring vendor/ - exactly from
  where our base theme comes from
- Open the `tailwind_2_layout.html.twig` to see those classes there
- Could we ask Tailwind look for CSS classes there? Yes!
- Since we're on the Tailwind v4 - no more `tailwind.config.js` needed
- Instead, in the app.css, add:
  `@source "./../../vendor/symfony/twig-bridge/Resources/views/Form/tailwind_2_layout.html.twig";`
- Make sure you're on 4.1 that will not work in previous versions
- You can see your specific version in `config/packages/symfonycasts_tailwind.yaml`
- Or in `var/cache/tailwind/`
- Send the empty form again - here it is, so red field. And also some more
  minor style changes
## Validation constrains in entities
- But if we will create a new form type linking it to the 
  same `StarshipPart` entity - we will need to duplicate all
  those validation constraints
- To avoid it, better to apply the validation constraints
  to the specific entity and all form types will automatically
  reuse them
- I will comment out this constraint in the form type
- Copy the custom message
- And readd this constraint in the entity
- I will choose `Assert\NotBlank` for convenience - this will
  create `Assert` namespace, see the namespaces above;
- And paste the error message to it as:
  `#[Assert\NotBlank(message: 'Every part should have a name!')]`
- I will also don't want to allow free parts
- For this, I will add `GreaterThan()` for the price prop
- Set value to 0
- And set message to `Starship part cannot be free!`
- Resubmit the form again
- Hm, we see only error related to the name
- Yeah, most of the validation constraints are ignored
  if the field is `null`
- But that `NotBlank` is special that make sure we have
  no null fields
- Add also `#[Assert\NotBlank(message: 'You forgot to set the price!')]`
- You can add as many constraints as you want to the same field
- If we send empty form now - both errors shown
- Try to set 0 as the price - another error!
## Form validation errors in WDT
- Btw, when we have a validation error - Symfony will automatically
  return `422 Unprocessable Content` server status code
- We can also debug the errors in the WDT
- We have now 2 icons: Validator and Forms
- Click on the form icon
- We see the invalid form is `StarshipPartType`
- The name and price fields have errors - click on them to see
- If you click on Validator tab
- It shows almost the same information but in a different view
- But we can also see constraint names
- Yeah, you can use Symfony Validator separate from Forms,
  and then this tab will be more useful for you
- So from WDT we have all the context about the failed validation
- And we know the exact place where to look at, awesome!
## The CSRF protection
- SEE: https://github.com/symfony/symfony-docs/pull/20964/files
- There's one more concept called CSRF protection
- It makes your forms more secure
- First, it checks for Origin/Referer HTTP headers of the Request
- Next, the request is validated using a cookie and a CSRF token
- TODO probably need a better explanation
- Install it with `symfony composer symfony/security-csrf`
- As soon as you installed that package - Symfony will add CSRF
  (Cross-Site Request Forgery) protection to all our Symfony forms by default
- You can see it yourself
- Open Inspector - you will find out a hidden input filed
  tied to a Stimulus controller
- How to see it works? 
- Delete PHPSESSID - but this will not help, this new CSRF token is stateless
- But if you change field value to something else and send the form...
- Here's our error:
  > The CSRF token is invalid. Please try to resubmit the form.


#  7. Sorting form field
- Open the /admin/starship-part/new
- Can we push that Notes field to the end?
- I want required fields come first and optional - last
- By default, form fields are rendered in the same order
  they are listed in the form type
- So if we want to change the order in which they are rendered - you
  can just reorder them in the form field
- Let's try it, I will push up the Starship field to place it before
  the Notes
- Refresh the page - now Notes is the last
- Instead of moving fields physically you can use priority option
- If we add it to the Starship field and set to e.g. 10 when 0 is by default
- Or set it to negative 10 if you want it to be the last
- Symfony will render the field with higher priority first
- If you refresh again - now Starship field is the first
- That might be useful if you want to dynamically order some fields
  base on some conditions when you can't simply move field to the correct spot
## Manually render form fields with `form_row` Twig helper function
- But if you need more than just order and you want a special
  position in your template - then you can render each field manually in the template
- Symfony forms provide you with more Twig function helpers
- Can we put Name and Price fields on the same line?
- Easy!
- Go to the template
- Instead of `form_widget()` we can use `form_row()`
- And pass the field you want to render
- Let's start with the first one `form_row(form.starship)`
- Below add little HTML:
  `<div class="grid grid-cols-2 gap-4">`
- Inside, render `form.name` & `form.price`
- After the columns, we can just simply add a special `form_rest(form)`
  to render all the remaining fields
- Go refresh the page - it worked!
- Can we make buttons go in 1 line too? Of course!
- But first of all, I will render the Notes first
- Let's try the same trick with `form_row(form.createAndAddNew)`
- I will bring our hardcoded btn next to it
- We can finish with `form_end()` that will render everything that
  was not rendered by this time
- This is enough, though, if you want to render all the fields first
  in order to add some extra tags before closing `</form>` tag - you can
  leverage with another Twig function
- I will keep `form_rest()` for the reference and add a comment below
- Technically, it's redundant in this case because `form_end()` would also
  do the same, but in case you want to render all the fields first,
  then add something else, and only then the closing `</form>` tag,
  then we would need this `form_rest()`
- Refresh the page - ah, buttons are still not aligned in the same row
- If you inspect the form - you will see that form row
  gives us a container with form field and label
- It also renders errors if any
- And you know what? We can render all those things manually,
  there are special Twig helpers for that!
- Instead of rendering the whole row container for the button,
  let's just render its widget with `{{ form_widget(form.createAndAddNew) }}`
- Refresh the page - now buttons are inlined too!
- You can see the button is rendered without that div container anymore
- Buuuut, before we go further, we have a tiny problem currently
- If you remember, when I corrupt the CSRF token value - we got
  a validation error about CSRF
- When I do it again and send a valid form - no errors, but we don't see
  the successful flash message
- What's happening?
- Open the profiler, refresh - aha, an error about CSRF!
- The problem is that some errors are attached to the form itself,
  not to a specific field
- When we did `form_widget(form)` for the whole form - it renders form errors
- Now when we start rendering fields manually - we should
  also manually render errors for the entire form
- Add `form_errors(form)` in the beginning
- Try to send the form again
- Now we see the CSRF error
- Form field errors in turn are still rendered well
- You can resend the empty form to see it
- So don't forget to render `form_errors(form)` when you start
  rendering form fields manually
- I will turn on the JS feature back
## The `help` form field option
- To help customers avoid hitting validation errors we can
  hint them what to write in the field
- For example, we can say that we do not support free parts
- Add `help` option to the `price` field
- And set to it a helpful message
- How about `We don't allow free parts! Set up a price`
- Refresh the page - this message is shown below the field slightly grayed text
## Setting form field attributes in the template
- Do you remember how we add CSS classes for submit btn in the form type?
- But if we did any styles in the form type - that  would complicate life
- for designers, as they may not know what exactly PHP class they need to edit
- Moreover, they most probably don't know PHP syntax at all
- Instead of writing CSS classes in the form type, I will comment it there
- Open the template
- Add an array with `attr` key,
- Then add `class` key
- And set it to `text-white bg-blue-700 hover:bg-blue-800 rounded-lg px-5 py-2.5 me-2 mb-2 cursor-pointer`
- Refresh the page to see the btn is still rendered with styles
- Btw, setting field options in the template has a bigger priority and override
  options set in the form type
## Adding a CSS trick to highlight all required fields
- Help message is awesome for some description, but if
  you have many required fields and they are clear about
  what to write in them - we can do a little CSS trick
- Open `assets/style/app.css`
- Below, add `label.required::after {}`
- Inside `content: " *";`
- And `color: #c10007;`

# 8.
### CRUD operations
- Run `symfony console make:crud` command
- For entity, choose `Starship`
- For controller, we already have `StarshipController`
- Let's name it as `StarshipAdminController`
- PHPUnit tests? Let's keep it as your homework - say "no"
- Oh wow, it created a lot of files: controller, form type,
  and several templates
- Open the controller
- I will change the route to `#[Route('/admin/starship')]`
  to be consistent
- Open the `/admin/starship`
- Ah, an error:
  > `StarshipStatusEnum could not be converted to string`
- Open `starship_admin/index.html.twig`
- Change status to `starship.status.value`
- Maker did you a lot of good work but seems doesn't handle Enums well yet
- Update the page to see it renders the list
- Click on Show link
- The same error
- Do the same fix in `starship_admin/show.html.twig`
- So it renders the list and allow us to watch the specific Starship details
- But more important it also gives us some forms so that we could
  create, update and delete records
- We can delete it from this show page
- Press that button to delete ID 106
- Oh, it asks a JS prompt to confirm the delete action
- I will cancel and return back to the list page
- Now let's press Edit link
- The already known error
- Open the related template: `starship_admin/edit.html.twig`
- It in turn includes `_form.html.twig`
- As you can see we're using the same form for new and edit actions
- The only button name is changed tat we pass as an arg
- But we don't render the `status` field there manually
- Symfony Form component tries to render that
- So, the proper fix should be done in form type
- Open `StarshipType`
- The default type guessing didn't work here
- Set `status` type to `EnumType::class`
- Another error:
  > The required option "class" is missing.
- Set that to `StarshipStatusEnum::class`
- Refresh again...
- Here's out form with prefilled data
- We can edit the form and save
- If you scroll the list till the end - you will find the "Create new" link
- This all generated code definitely needs some style
- I will quickly paste some HTML code that contains some Tailwind CSS classes
  and some rearranged buttons - you can copy/paste it from the code blocks below

## Applying form theme globally for all forms in Twig config
- For the form theme, we should use add it to the form template as we did in `new.html.twig`
- It will work, but wait! I don't want to do this for every new form.
  We can configure this globally in the app
- Open the terminal and execute: `cl config:dump twig`
- There's a `form_themes` key set to default theme
- Back to the PhpStorm
- I will comment out this form_theme setting
- Copy the template name
- Open `config/packages/twig.yaml`
- Add `form_themes:`
- And set it to `- tailwind_2_layout.html.twig`
- Refresh the page to see the theme applied globally for all our forms
## Symfony form without mapped data class
- Now let's do something fun and practice working with Symfony forms
- On the `/parts` we have a little search input
- Right now that's hardcoded form, but could we do the same using Symfony form?
- You bet
- Even though using plain forms is totally OK in your project,
  we still can leverage Symfony forms in such cases too
- We could simply leverage form builder in the controller and bypass
  creating a form type, that would be totally fine
- But let's go with the form type again - we will see more useful tricks with it
- You know the drill
- Run `symfony console make:form`
- Name it `PartSearchType`
- But this time we don't want to tie it to an entity or model - just leave it blank
- Open the `PartSearchType.php`
- Replace `field_name` with `query` to match the current input name
- Next, open `PartController::index()`
- Create `$searchForm = $this->createForm(PartSearchType::class)`
- Pass search form to the template
- Render the form with `{{ form($searchForm) }}` below the hardcoded form
### Hiding form field label
- Nice, we have 2 input fields now
- The rendered field has label, let's hide it
- Since the submit button, we know the trick!
- We can render the field w/o the label, i.e. expand the form with `form_start()`/`form_end()`
- And inside, instead of `form_row()` render the field with `form_widget()` that will render
  only the field w/o its label
- But if we want to completely disable the label - there's a better solution  
- Back to the form type
- Add `null, []`
- Set `label` to `false`
- We can change it to any string we want here, but if we
  set it to `false` - it won't be rendered at all
- And while we're here, let's also add `attr`
- And inside set `placeholder` to `Search...`
- Below, add `class` and set CSS classes from the original input
- Back to the website, refresh - now it looks exactly like the legacy field
- Except for the Search icon, we will add it later
### Submitting Form via GET HTTP Method
- Now we can send the form, but it's sent via POST by default
- We want the search query to be in the URL, so let's change to GET
- In `setDefaults()` set `method` to `Request::METHOD_GET`
- Which is just a fancy way to say a simple `GET` string
- Try to send the form and... yes, we see the query parameters
- But oh, the query params are weird, don't we only have to see `query`?
- Well, Symfony Forms, by default, sent as arrays, first of all, to avoid collisions
- To make it "flat" and get rid of that form type prefix
- Override `getBlockPrefix()`
- And `return ''` inside
- Now if you send the form - here's our query... and CSRF `_token`
- Usually, we don't need CSRF protection search, so let's disable it
- In `setDefaults()`, add `'csrf_protection' => false`
- Try again - perfect! Only the `query` arg now
### The `required` field option
- The finishing touch - if I try to send an empty field,
  we hit HTML5 validation. That can be totally fine for your case, but
  I would like to allow an empty search form - it will mean no search query applied
- In the `setDefaults()`, for the `form` tag we can add `novalidate` attribute
- And technically, it would skip the required validation
- But the correct way - make the field optional instead of required
- For the `query` field, set `required` to false
- Try again - now we can send empty field which technically will just show the full list 
### Form handling and fetching form data
- Well, actually the filter works only because of legacy form logic
- Now let's handle the new form
- Actually, in this specific case it's totally fine to keep
  `$request->query->get('query')`
- But while we're practicing Symfony Forms here - let's handle it the Form-Component way
- First, I will comment out this `$query` to keep it for the reference
- Below, add `$query = null;`
- Next, do `$searchForm->handleRequest($request);`
- Below check for `isSubmitted()` && `isValid()`
- Inside the `if` we can safely fetch the `query` value
- And we already know how to fetch the form data
- Add `$query = $searchForm->get('query')->getData();`
- Last but not least, in the template
- Copy the `svg`
- The `svg` was below the `input` field, but let's try to just
  place it below the whole form
- Paste the `svg`
- And celebrate with deleting the legacy form completely
- Refresh the page - perfect, looks exactly like the hardcoded one
- Well, I have one more little improvement
- By default, Symfony uses `TextType` for this field
- Let's change it to a special `SearchType`
- Refresh the page - it look the same
- But when you start typing - it adds a little `x` icon to clear the field
- Sweet!
