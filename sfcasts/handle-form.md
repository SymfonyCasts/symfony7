# Processing the Submitted Form

Alright, we've built, created, rendered, and styled our form (I swear, I’ve
done my best so far!). I've given it my all and now our form is ready for
submission. Now, as any seasoned backend developer will tell you, the real
fun begins when we start dealing with that submitted data. Let's jump back
into our controller and make this form functional. 

## Updating Controller to Handle Form Data

Open up `src/Controller/AdminController.php`, and in the `newStarshipPart()`
method, right below the form object, add `$form->handleRequest()`. 

To pull this off, we need to pass the current request object to this
method. You're familiar with this by now. Inject `Request` from the
HTTP Foundation as the method argument `$request` and pass it to
`handleRequest()`. You might be wondering what this `handleRequest()` is all about.
It simply grabs the submitted data from the request, applies that data to
your form, and now your form contains the user's submitted values. 

## Checking Form Submission

Next, we want to know whether the form has actually been submitted or if we just
loaded the form page. That's a breeze — just write `if ($form->isSubmitted())`.
Then within that `if`, we can retrieve the submitted data with `$form->getData()`.

Since our form type has a `data_class` option set to `StarshipPart::class`,
the data you retrieve here isn't a straightforward PHP array. Instead, it's a
`StarshipPart` entity instance with the fields already filled in for us.
Skeptical? Go ahead and check it yourself. Let's assign it to a `$part`
variable and below `dd($part)`.

## Testing Our Form

Back in the browser, I'll quickly fill in the form. Hit create to submit it...
and voila, a shiny new `StarshipPart` object with the data we sent. Notice
that no ID is set because Doctrine hasn't saved it in the database yet.
I'll quickly add a PHPDoc above the variable to make PhpStorm's autocomplete
happier, and delete the `dd()` statement. 

## Saving Data with Doctrine's EntityManager

To save the new part, we need Doctrine's `EntityManager`. Inject it with
`EntityManagerInterface $entityManager` in the method signature. Then, back
in the `if`, add `$entityManager->persist()`, passing the `$part`
object and next `$entityManager->flush()`.

Back to the browser, I'll set the name to: "Legacy Hyperdrive".
Give it a fair price, and don’t forget about an important note:

> Be careful with high revs!

Now, hit the submit button again. Did it finally work? Well, at least there are
no errors. Head to the `$part` page and search for "legacy hyperdrive".
There it is, your new shiny Starship part, ready for sale. 

## Celebrating Success with Flash Messages

Let's celebrate this moment properly. I'm going to add a successful flash
message. Flash messages are temporary messages stored in the session and
shown exactly once. They are perfect for things like:

> Your part was created successfully!

If you peek into `templates/base.html.twig` You'll see we already have code
that loops over flash messages and renders them with nice styling depending
on the type: success, warning, error, default. Back in our controller,
after saving the part entity to the database, write: `$this->addFlash()`

First argument: the message "type" - it helps to control styling. Write
`success` here. Second argument: the content of the message. How about
`sprintf('The part "%s" was created.', $part->getName())`.

## Avoiding Duplication and Redirecting User

To avoid duplicate form submissions when the user refreshes the page - which
might lead to unwanted parts floating around in space - let's finish the
process with a redirect. This is a classic best practice for POST forms.
Let's return `$this->redirectToRoute()`.

We can redirect anywhere, but I'll send users back to the part list for
convenience. This route name is `app_part_index`. 

## Testing the Overall Flow

Alright, let's create a new part again. How about a quantum reactor for the
name, set a price, and for notes, I'll say:

> Do not exceed 120% core flux

OK, submit the form again, and there it is. Our flash message,
announcing that the part quantum reactor was successfully created. And if I
try to refresh the page, the message is gone, so it was shown only once,
and Chrome does not ask me if I want to resubmit the form again, so it was
redirected properly too. Sweet!

## Adding a Second Submit Button

Right now we only have one submit button: Create. But imagine this, if
you're feeling productive, caffeinated, on a roll, and you want to create
multiple parts quickly, one after the other, you could do this with a few
extra clicks each time, clicking on the link to return to the form. But
wouldn't it be so much faster to have a second submit button, that, instead
of creating and going back to the list, creates and stays on this page with an
empty form open, so you can immediately create another part?

Well, it might not be much faster, but it still could save someone a few hours
of their life over the course of many years of adding those parts.
You might be wondering, is that even possible? Absolutely! And we'll figure out
how in the next chapter.
