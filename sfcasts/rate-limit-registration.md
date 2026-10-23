---
challenges:
    - key: consume-only-on-valid
      question: |
          We put the limiter inside the block that runs only when the form is submitted
          *and* valid, right before the new user is created:

          ```php
          if ($form->isSubmitted() && $form->isValid()) {
              $limit = $limiterFactory->create($request->getClientIp())->consume();

              if (!$limit->isAccepted()) {
                  // ...
              }

              // ... create the user
          }
          ```

          Why not consume a token as soon as the form is submitted?
      answers:
          - Our rule allows one registration every 10 minutes - if invalid submissions counted too, a single typo in the password would lock someone out for the next 10.
          - "`consume()` throws if the form has validation errors, so it can only be called on a valid form."
          - The client IP isn't available until the form has been validated.
          - Tokens consumed before validation are refunded automatically, so calling it earlier would make no difference.
      explanation: |
          Counting *attempts* and counting *registrations* are two very different things.
          Our password rules are strict - 12 characters, real strength, nothing that's
          turned up in a breach - so someone acting in complete good faith can easily
          miss two or three times before getting it right. If each of those cost a token,
          they'd be locked out for 10 minutes for the crime of fumbling a password!
          Keeping `consume()` behind the validity check means we're counting accounts
          actually created, which is the thing we were worried about to begin with.
    - key: limiter-key-client-ip
      question: |
          We created the limiter using the client's IP address:

          ```php
          $limit = $limiterFactory->create($request->getClientIp())->consume();
          ```

          What is that argument actually doing?
      answers:
          - It's the key that separates one counter from another - each distinct value gets its own independent budget, so one visitor's registrations don't eat into anyone else's.
          - "It names which limiter from `rate_limiter.yaml` to build, so a different value would select a different policy."
          - "It's an optional label, used only for the `Retry-After` header and logging."
          - It restricts the limiter to that IP - requests from any other address skip the limiter entirely.
      explanation: |
          A limiter factory builds one limiter *per key*, and the key decides who shares
          a budget. We passed the client IP, so every address gets its own "one
          registration per 10 minutes". Pass a user ID instead and the limit follows that
          person across devices; pass a constant string and the entire internet shares a
          single budget! It's worth a moment's thought, because the key really *is* the
          policy - whatever you pass in is the thing you're limiting.

          Keying on the IP does come with a catch worth knowing about: an office, a
          school or a coffee shop usually shares one public address between everybody in
          the building. So "one registration per 10 minutes" quietly becomes one for the
          whole company - which is fine until a dozen new colleagues try to sign up on
          their first morning. There's no perfect answer here, just a trade-off between
          turning away bots and inconveniencing real people, but it's a good reason to be
          generous with the limit.

          It's also worth remembering that our app doesn't have to carry this on its own.
          A service like Cloudflare sits in front of the site and turns away obvious bot
          traffic before it ever reaches Symfony, which pairs really nicely with a limit
          like ours: the edge handles the floods, and our limiter handles the patient,
          one-at-a-time stuff that slips through.
---

# Rate Limiting the Registration Form

In the last course, we added login throttling to our login form to limit how many login
attempts someone can make. Now I want to rate limit the *registration* page, so nobody
can sit there and register a thousand users.

## Fixing that Username Field

First though, let's clean up that weird username field at the bottom of the form. Open
`templates/registration/register.html.twig` and, right after the email row, duplicate
that line and change it to `username`:

[[[ code('820ec6d9c2') ]]]

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

[[[ code('0bffa32719') ]]]

## Injecting the Limiter Factory

Now over to `src/Controller/RegistrationController.php`. We need to inject a new
service. Let me expand this argument list a bit so we have more room. The service we
want is `RateLimiterFactoryInterface`, and we'll call it `$limiterFactory`:

[[[ code('febc14a593') ]]]

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

[[[ code('5dcf8f9b9f') ]]]

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

[[[ code('bd4ddea7dc') ]]]

What `consume()` returns is a special `RateLimit` object with some interesting methods
on it. We can ask whether we were accepted. We could call `ensureAccepted()`, which
throws a `RateLimitExceededException` if we weren't. And it hands us some cool
information: when you can next retry, how many tokens are left, what the limit is. You
can even call `wait()` to sit there until tokens become available. Pretty cool!

We'll keep it friendly. Write `if (!$limit->isAccepted())`, and inside, add a flash:
`$this->addFlash('error', 'Too many registration attempts. Please try again later.')`.
Then send them back to the registration page so they can wait it out:
`return $this->redirectToRoute('app_register')`:

[[[ code('c78e06e063') ]]]

## Making the Cache Real

One thing we have to do before we can test this: rate limiters store their state in the
cache by default. And earlier, we changed our default cache. Open
`config/packages/cache.yaml`... yep: `app: cache.adapter.array`, which is an in-memory
cache that forgets everything at the end of the request. Switch that to
`cache.adapter.filesystem`:

[[[ code('b8af97756f') ]]]

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
