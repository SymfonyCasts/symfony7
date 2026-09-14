# Returning a 404 Instead of 403 with IsGranted

Hey friends, welcome to Symfony Security: Going Further!
This is a direct continuation of our Security: The Basics course - it picks up right
where that one left off, so I highly suggest you go through that one first. This
course is the extra stuff: the more advanced features we didn't get to cover there.

If you followed along with that course and still have the code, you can pick up
right where we left off. If you need to, download the code for this course at the top of
the page. In the zip file, open the `start/` directory in your favorite IDE, and follow the `README` to
get set up. Run:

```terminal
symfony serve -d
```

...to get the app up and running.

## A Quick Tour of Starshop

Here's our Starshop. If you followed the Security basics course, you're used to
this: we have a registration link, a login link, and some pre-built users in our
fixtures. Open `src/Story/AppStory.php` to see them. We have
`picard@enterprise.space`, password `makeitso` - a captain who owns his own starship - and
`janeway@starfleet.space`, password `coffeeblack`, who is an admin. We also create a bunch of
starships.

Log in as `picard@enterprise.space` with password `makeitso`.

Now head to `/starship` to see a list of all the starships. If you remember from the
last course, we made it so that only the owner of a starship can edit it. Hit the
edit button and check the URL: we're at `/starship/1/edit`. We added authorization
so users can't edit someone *else's* starship. Change that id to `2`... and we get a
403 error.

## A 403 Leaks Information

One thing about a 403 is that it does give away a little bit of information: it
tells us that this starship probably *does* exist in the system. It leaks.

You may have seen this on GitHub. Go to a repository that you know exists but is
private - when you're not authorized to see it - GitHub
gives you a 404. That prevents leaking which repositories, or in our case, which
starships, exist in the system.

## Returning a 404 Instead of 403

We can mimic that behavior really easily with our `#[IsGranted]` attribute. Open up
`src/Controller/StarshipAdminController.php`. For creating a new starship, we
require `ROLE_ADMIN`. That's ok to leave as a normal 403, it doesn't leak anything.

But down on `edit()`, we have this `#[IsGranted('edit', subject: 'starship')]`. Pass
another parameter to that attribute: `statusCode: 404`

Refresh the page... and we get the same error message... But look up here: it's a 404 now!

Remember, this is our development exception page. In production, this would be a
totally generic 404 - like any other 404 page on the site - so you wouldn't see this
message. This is a great way to prevent leaking the existence of your site's content.

Double-check one more thing: copy this URL... and logout. Paste it in the browser while
we're anonymous. We also see a 404. If the default, 403 was thrown, we would be
redirected to the login page with the way our
security is set up right now - and that could still be considered a bit of a leak.
Instead: a 404. Nobody who isn't authorized to edit this starship can even know it exists.

## The Other `IsGranted` Options

There are a few other exception-related parameters on this attribute. Jump into
`IsGranted`. We can set a custom `message` and we can also set `exceptionCode` -
that's the real PHP exception code. It usually isn't necessary to set these.
And remember, by default, the exception messages aren't shown to end users. But be aware that they're there.

One more spot: we also have this `delete()` action, so do the same thing here: `statusCode: 404`.

Cool - we've completely hidden these two endpoints to non-authorized users.

Next up: account statuses! We're going to create the concept of disabling users.
