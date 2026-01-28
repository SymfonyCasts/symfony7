# Submitting a Search Form via GET

Right now, we've got a search form. But there's a tiny hiccup. It's
submitted through POST because that's Symfony's default approach.
Typically, that's awesome. But for a search form, POST isn't the best
choice. Why? Because when we search, we want the query to be visible in the
URL. It makes the page results shareable and bookmarkable. Plus, it just feels
right, like our old form. So let's tweak that and switch our form to GET.

To get started, pop open the `PartSearchType.php` file. In the `setDefaults()`
we can fine-tune our form options. In the empty array, add a new row writing
`method` and set it to `Request::METHOD_GET`. This is really just a simple,
Symfony-approved way of saying a simple `GET` string.

Take a moment to submit the form now. Voila! The query pops up right there
in the URL as the query parameters. But wait, those query parameters look a
bit odd. Instead of the simple `query=legacy`, we see
`parts_search[query]=legacy` - those `%%` chars are just encoded `[]`.

What's up with that? By default, Symfony forms submit data as arrays to help
avoid name collisions when you've got multiple forms on the same page.
It's a smart move, but for our search form, it's overkill and, let's be honest,
a bit ugly.

## Cleaning up the Query Parameters

Here's what we'll do. We want to streamline the query parameters. To get
rid of this form name prefix, we can overwrite a special method in the form
type.

Back in `PartSearchType.php`, at the end of this class, I will press
`Cmd` + `N` and choose "Override Methods", then choose the `getBlockPrefix()`
method. Inside it, just return an empty string. This tells Symfony not
to prefix our fields with the form name. Search again and bam! We've got clean,
flat query parameters. Much better!

But we're not quite there yet. We now have a `_token` query in the URL.
That's CSRF protection doing its job, but it's unnecessary for a GET-based
search form. We're only filtering a list of products, so let's turn it off
for this form in `setDefaults()`.

To do this, go back to `PartSearchType.php` and add a new option called
`csrf_protection`, setting it to `false`.

Submit the form again and bingo! We've only got the `query` parameter in
the URL, clean and intentional.

## Making the Search Field Optional

One final tweak. If we try to submit the form with an empty field, the
browser stops us with an HTML5 validation error. That might be fine for
your app, but I'd prefer an empty search to mean "Show me everything". We
can disable validation using the `novalidate` attribute on the form tag,
but a better option is to just make the field optional. For the
`query` field, let's add another parameter called `required` and set it to
`false`.

Refresh the page and if we try to search with an empty input, it simply
shows the full list.


Honestly, right now, the search works because of some legacy business logic
in the `PartController`. Specifically, we're grabbing the query directly
from the request like `$request->query->get('query')`. And for a tiny search
form, that's totally fine. But since we're learning the Symfony Form component,
let's do this the Symfony form-handling way.

## Proper Form Handling in Symfony

Let's comment out the `$query = $request->query->get('query')` line,
keeping it as an example. Then, we'll initialize a new `query` variable set
to `null`. Next, we tell the form to handle the request with
`$searchForm->handleRequest($request)`.

We check the usual combo with an `if` statement:
`$searchForm->isSubmitted()`, `&& $searchForm->isValid()`. The `query` field
is unmapped, but we already know how to access it. Set `$query` variable to
`$searchForm->get('query')->getData()`.

## Final Touches and Turbo Power

Almost there. Let's make the final tweak in the template. Copy the SVG icon
and place it below the entire form. The proper CSS positioning should still
do the trick so we don't need to place it between the open and close form tags.

Now we can finally delete the old form completely. Refresh the page again to
see our form, and if we search, it still works! It looks the same, but now
it's powered entirely by Symfony forms.

By default, Symfony uses `TextType` for the field, but since this is a
search, let's be a bit more semantic and switch it to a special `SearchType`.
In `PartSearchType`, replace `TextType` with `SearchType`. Nothing changed
at the first time, but if start typing - there's a handy little X button
that lets you easily clear the input field.

## Enabling Turbo on the Website

If you recall, I disabled Turbo at the start of this course to keep things
simple. But now it's time to unleash its power. Open `app.js` and uncomment
the Turbo line in the `assets/js` file. Refresh the page again.

Now, navigation through the site happens via AJAX powered by Turbo. You can
see it in action in the WDT when clicking links. Even cooler? Form submission
also happens via Turbo, and form errors still work perfectly.

If you go to Create new starship part and try to send the empty form - you will
see it was sent via AJAX, but we also see the error messages. That's because
we passed the entire `Form` object to the template, not just the `FormView`.
This lets Symfony and Turbo render everything correctly on the subsequent
request. Turbo makes our app feel faster, smoother, and more immersive. And
this is just the start! If you want to go deeper, I would recommend you to
take a look at our standalone [Turbo course](https://symfonycasts.com/screencast/turbo).

## Wrapping Up

Alright, friends! You've officially mastered the basics of the Symfony Form
component. You now know how to install and configure it, build form types,
render them with Twig helper functions, and handle submissions the right way with
`Form::handleRequest()`. We've looked at how forms link up with Doctrine
entities, how validation works, how to customize fields and attributes, and
how to style everything nicely with built-in Symfony form themes.

We're ready to build real, production-ready forms with confidence. So go
build some awesome forms! They're essential in your applications. And enjoy
the magic of Symfony forms this winter.
