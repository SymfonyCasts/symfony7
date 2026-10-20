# Rate Limiting the Registration Form

In the last course, we added login throttling to our login form to limit how many login
attempts someone can make. Now I want to rate limit the *registration* page, so nobody
can sit there and register a thousand users.

## Fixing that Username Field

First though, let's clean up that weird username field at the bottom of the form. Open
`templates/registration/register.html.twig` and, right after the email row, duplicate
that line and change it to `username`:



Easy peasy. Refresh... perfect.

## Configuring the Limiter

Now, we need a rate limiter. We already installed the rate limiter component back when
we set up login throttling, so it's available. To configure one, go to
`config/packages/` and create a new YAML file called `rate_limiter.yaml`. Inside, the config
lives under the `framework` key, then `rate_limiter`. Name ours `registration`.

Every rate limiter needs a `policy`, and Symfony gives us three to choose from.

`fixed_window` says you're allowed X attempts within Y time - one hit per hour, five
hits per five minutes, that kind of thing.

`sliding_window` is the same idea, except the window *moves* with the times of the hits.
That prevents someone from bursting a bunch of requests at the end of one window and
then immediately bursting again at the start of the next.

And `token_bucket`: you start with a bucket of X tokens, each hit consumes one, and when
the bucket is empty, no more hits allowed! The bucket then refills at a set rate.

The easiest one to reason about - and the most flexible, I think - is `sliding_window`.
Use that. Then we need a `limit` of `1` and an `interval` of `10 minutes`. So: you can
only create one user every 10 minutes:



## Injecting the Limiter Factory

Now over to `src/Controller/RegistrationController.php`. We need to inject a new
service. Let me expand this argument list a bit so we have more room. The service we
want is `RateLimiterFactoryInterface`, and we'll call it `$limiterFactory`:



But hold on - we want *our* configured limiter, not just any one. That means named
autowiring with a `#[Target]` attribute. To figure out what the target string should
be, jump over to a terminal and run:

```terminal
symfony console debug:autowiring ratelimiter
```

The first two results are the login limiters we set up in the last course. And this
one - the registration limiter - is the one we just configured. We can see the target
right here: `registration`. Copy that.

Back in the controller, above the `RateLimiterFactoryInterface` argument, add a
`#[Target]` attribute with the string `registration`:



Notice that we're injecting the *factory*, not the limiter itself.

## Consuming a Token

Now create the limiter from the factory. Where should the rate limiting happen? After
the form has been submitted *and* is valid - right before we actually create the new
user in the database.

So at the top of that block, write `$limit = $limiterFactory->create()`. `create()`
takes the *key* for this limiter - the thing that should be different per user. The
easiest option is `$request->getClientIp()`. That gives us the limiter itself, and now
we consume a token - or really, record a hit: `->consume()`. By default, that consumes a
single token:



What `consume()` returns is a special `RateLimit` object with some interesting methods
on it. We can ask whether we were accepted. We could call `ensureAccepted()`, which
throws a `RateLimitExceededException` if we weren't. And it hands us some cool
information: when you can next retry, how many tokens are left, what the limit is. You
can even call `wait()` to sit there until tokens become available. Pretty cool!

We'll keep it friendly. Write `if (!$limit->isAccepted())`, and inside, add a flash:
`$this->addFlash('error', 'Too many registration attempts. Please try again later.')`.
Then send them back to the registration page so they can wait it out:
`return $this->redirectToRoute('app_register')`:



## Making the Cache Real

One thing we have to do before we can test this: rate limiters store their state in the
cache by default. And earlier, we changed our default cache. Open
`config/packages/cache.yaml`... yep: `app: cache.adapter.array`, which is an in-memory
cache that forgets everything at the end of the request. Switch that to
`cache.adapter.filesystem`:



Time to test. Head to the registration page and register a new user: email
`worf@enterprise.space`, username `worf`, name `Worf`, and for the password, something
complex - `makeitso1engage?`. Agree to the terms and submit. Perfect: we're registered and
logged in as Worf.

Now log out, hit register again, and try a second user: `natasha@enterprise.space`,
username `natasha`, name `Natasha Yar`, the same password, agree to the terms... and
there it is:

> Too many registration attempts. Please try again later.

We are in fact rate limited - we couldn't get past it. In about 10 minutes, we'd be able
to register again.

And that's it for *Symfony Security: Going Further*! Thanks for coding along with me. If
you have any questions, let us know down in the comments.

'Til next time - happy coding!
