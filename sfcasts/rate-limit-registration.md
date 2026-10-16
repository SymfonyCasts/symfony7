# Rate Limiting the Registration Form

In the last course, we added login throttling to our login form to limit how many login
attempts someone can make. But our *registration* form is still wide open. Let's rate
limit that one too - except this time, we'll configure everything by hand.

First, we need a rate limiter. We already installed the rate limiter component back when
we set up login throttling, so it's available. To configure one, go to
`config/packages/` and create a new YAML file called `rate_limiter.yaml`. Inside, this
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
Use that. Then we need a `limit` - say, 5 - and an `interval`: `1 hour`. So: five
registration attempts per hour:



## Injecting the Limiter Factory

Now over to `src/Controller/RegistrationController.php`. We need to inject a new
service. Let me expand this argument list a bit so we have more room. The service we
want is `RateLimiterFactoryInterface`, and we'll call it `$limiterFactory`:



But hold on - we want *our* configured limiter, not just any one. That means named
autowiring with a `#[Target]` attribute. To figure out what the target string should
be, jump over to a terminal:

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

Now create the limiter from the factory. We only want this to run when the form is
actually submitted, so right after we handle the request,
add `if ($form->isSubmitted())`. Inside, write `$limit = $limiterFactory->create()`.
`create()` takes the *key* for this limiter - the thing that should be different per
user. The easiest option is `$request->getClientIp()`. That gives us the limiter, and
now we consume some tokens: `->consume()`. By default, that consumes a single token:



What we get back is a `RateLimit` object, and we can ask it whether we're accepted. We
could call `ensureAccepted()`, which throws a `RateLimitExceededException` if we're
not. It also hands us some cool stuff: when you can retry, how many tokens are left,
what the limit is, and so on.

We'll keep it friendlier. Write `if (!$limit->isAccepted())`, and inside, add a flash:
`$this->addFlash('error', 'Too many registration attempts. Please try again later.')`.
Then send them back to the registration page so they can wait it out:
`return $this->redirectToRoute('app_register')`:



## Making the Cache Real

One thing we have to do before we can test this: rate limiters store their state in the
cache by default. And earlier, we changed our default cache. Open
`config/packages/cache.yaml`... yep: `app: cache.adapter.array`, which is an in-memory
cache that forgets everything at the end of the request. Switch that to
`cache.adapter.filesystem`:



Time to test. Go to the registration page and just hit "Register". That failed... but
because our form is invalid. Hit register again, a third time, fourth, fifth... and
the sixth time, we should trip our limiter. And sure enough:

> Too many registration attempts. Please try again later.

We're redirected back to an empty registration page.

## A Caveat Worth Thinking About

One thing to keep in mind with this setup: *every* failed submission consumed a token.
And remember those strict password rules we just added? Someone could easily submit the
form several times in a row - not because they're abusing it, but because they're
legitimately hunting for a password our site will accept. So maybe five is too few.

Another option is to only spend a token when a registration actually *succeeds*. We'd
move the `create()` and `consume()` calls down into the block that runs once the form
is valid, so a failed submission costs nothing. The limit would then cover how many
accounts can be created in an hour - which is probably what we cared about in the first
place - while nobody gets locked out for fumbling our password rules. You could add as
much complexity there as you like, but this is a good start. We could also probably
just bump that limit a little.

Try improving that on your own!

Next up: let's look at some brand-new security features that landed in Symfony 8.2!
