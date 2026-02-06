# Submitting a Form via GET

Right now, we've got a search form. But there's a tiny hiccup. It's
submitted through POST because that's Symfony's default approach.
Typically, that's awesome. But for a search form, POST isn't the best
choice. Why? Because when we search, we want the query to be visible in the
URL. It makes the page results shareable and bookmarkable. Plus, it just feels
right, like our old form. So let's tweak that and switch our form to GET.

To get started, pop open our `PartSearchType` class. In `setDefaults()`
we can fine-tune our form options. In the empty array, add a new key called
`method` and set it to `Request::METHOD_GET`. This is really just a nerdy way
of writing the string `GET`:

[[[ code('a7bd19f947') ]]]

Take a moment to submit the new form with "Legacy". Whoa, check out the URL.
The query parameters look a bit crazy. Instead of the simple `query=legacy`,
we see `parts_search[query]=legacy` - those `%5B` and `%5D`'s are just
encoded `[]`.

What's up with that? By default, Symfony forms submit data as nested arrays,
keyed by the form name. This helps avoid name collisions when you've got
multiple forms on the same page. It's a smart move, but for our search form,
it's overkill, and let's be honest, a bit ugly.

## Cleaning up the Query Parameters

Here's what we'll do. We want to *flatten* the query parameters. To get
rid of this form name prefix, we can override a special method in the form
type.

Back in `PartSearchType`, at the end of this class, I'll press
`Cmd` + `N` and choose "Override Methods", then choose the `getBlockPrefix()`
method. Inside it, just return an empty string:

[[[ code('29ddb690bb') ]]]

This tells Symfony not to prefix our fields with the form name. Search
again and bam! We've got clean, flat query parameters. Much better!

But we're not quite there yet. We have this `_token` query in the URL.
That's CSRF protection doing its job, but it's unnecessary for a GET-based
search form. We're only filtering a list of products, so let's turn it off
for this form in `setDefaults()`.

To do this, go back to `PartSearchType` and add a new option called
`csrf_protection`, setting it to `false`:

[[[ code('3d74bd13e7') ]]]

Submit the form again, and bingo! We've only got the `query` parameter in
the URL, clean and intentional.

## Making the Search Field Optional

What if we try to submit the form with an empty query? Hmm, an HTML5 validation
error... That might be fine for your app, but I'd prefer an empty search
to mean "Show me everything". We can disable validation using the `novalidate`
attribute on the form tag, but a better option is to just make the field
optional. For the `query` field, add another parameter called `required`
and set it to `false`:

[[[ code('1003d7f4c3') ]]]

Refresh the page, and if we try to search with an empty input, it simply
shows the full list.

Honestly, right now, the search works because of some legacy business logic
in the `PartController`. Specifically, we're grabbing the query directly
from the request with `$request->query->getString('query')`:

[[[ code('f7347b4aa1') ]]]

And for a tiny search form, that's totally fine. But since we're learning
the Symfony Form component, let's do this the Symfony form-handling way.

## Proper Form Handling in Symfony

Comment out that line. Then, initialize a new `query` variable set
to `null`. Next, tell the form to handle the request with
`$searchForm->handleRequest($request)`:

[[[ code('3e70a3812b') ]]]

Add our usual form submission check. `if ($searchForm->isSubmitted()`,
`&& $searchForm->isValid())`. The `query` field is considered unmapped
because the form isn't associated with an object. Inside the `if`, set the
`$query` variable to `$searchForm->get('query')->getData()`:

[[[ code('1032103be5') ]]]

This grabs the query field and retrieves its submitted value.

***TIP
To grab all the form data at once, as an array, you can use
`$searchForm->getData()`. 
***

## Final Touches and Turbo Power

Almost there. Let's make the final tweak in the template. Copy the SVG icon
and place it below the entire form. The proper CSS positioning should still
do the trick so we don't need to place it between the open and close form tags.
Now we can finally delete the old form completely:

[[[ code('1acdeb3ea7') ]]]

Refresh the page again to see our form, and if we search, it still works!
It looks the same, but now it's powered entirely by Symfony Forms.

Back in `PartSearchType.php`, one last enhancement. By default, if you don't
specify the field type, Symfony uses `TextType`. But since this is a search field,
let's be a bit more semantic and switch it to a special `SearchType`:

[[[ code('536137acbc') ]]]

Back in the browser, refresh the page again. Nothing changed at first glance,
but if you start typing - there's a handy little X button that lets you
easily clear the input.

## Enabling Turbo on the Website

If you recall, I disabled Turbo at the start of this course to keep things
simple. But now it's time to unleash its power. Open `assets/app.js` and
uncomment `import '@hotwired/turbo';`:

[[[ code('2db26c9d5e') ]]]

Now, navigation through the site happens via AJAX powered by Turbo. You can
see it in action in the web debug toolbar when clicking links. Even cooler?
Form submission also happens via Turbo, and form errors still work perfectly.

On the parts list, if you go to "Create new part" and try to send the empty
form... Yep, that's an AJAX request and we still see the expected validation
errors. Turbo makes our app feel faster, smoother, and more immersive. And
this is just the start!

***SEEALSO
If you want to go deeper, I recommend you take a look at our standalone
[Turbo course](https://symfonycasts.com/screencast/turbo).
***

## Wrapping Up

Alright, friends! You've officially mastered the basics of the Symfony Form
component. You now know how to install and configure it, build form types,
render them with Twig helper functions, and handle submissions the right way
with `Form::handleRequest()`. We've looked at how forms link up with Doctrine
entities, how validation works, how to customize fields and attributes, and
how to style everything nicely with built-in Symfony form themes.

We're ready to build real, production-ready forms with confidence. So go
build some awesome forms! They're essential in your applications. And enjoy
the magic of Symfony forms.

'Til next time, happy coding!
