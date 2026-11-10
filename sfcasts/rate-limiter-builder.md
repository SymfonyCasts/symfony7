---
challenges:
    - key: shared-rate-limiter
      question: |
          You want the registration form and the password reset form to share *one* rate
          limiter: a single count of attempts per IP, with its limit and interval defined in
          one place. How should you set it up?
      answers:
          - Define it in the rate limiter config, then inject it into both controllers
          - Call `RateLimiterBuilder` in each controller, copying the same ID, limit and interval
          - Call `RateLimiterBuilder` in one controller, which makes it available to the other
          - Define it in a Twig template, so both forms can reach it
      explanation: |
          `RateLimiterBuilder` shines for one-off limiters, defined right where they're used.
          When a limiter is shared, the config is still the way to go: its settings live in
          one place, and every controller that uses it stays in sync.
---

# Creating Rate Limiters with RateLimiterBuilder

This last one isn't strictly security-related, but you could argue it is. In
`src/Controller/RegistrationController.php`, we use rate limiting so nobody can register
new users over and over. And Symfony 8.2 adds some cool improvements to rate limiters.

In our `register()` action, we inject a `RateLimiterFactoryInterface`, using named
autowiring - `#[Target('registration')]` - to get the `registration` one. That's
configured in `config/packages/rate_limiter.yaml`. But this is a single-use rate limiter:
we only ever use it for registration. So it's a bit annoying to configure it in one place,
then jump over and use it in the controller.

## Hello `RateLimiterBuilder`

In 8.2, there's a new `RateLimiterBuilder` that lets you build rate limiters inline.
Delete the `#[Target]` argument, and in its place, inject `RateLimiterBuilder` and call it
`$limiterBuilder`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "6-rate-limiter-builder", "file": "src/Controller/RegistrationController.php", "lines": "15,19-22,27-28,62-63"}) ]]]

Down below, expand the line where we create the limit a bit. Instead of `$limiterFactory`,
which no longer exists, use `$limiterBuilder`. Its methods let you create any of the
limiters inline: `slidingWindow()`, `compound()` - a bunch of limiters combined -
`fixedWindow()`, `noop()` - a null rate limiter - and `tokenBucket()`. All the available
rate limiters are here.

Choose `slidingWindow()`. The first thing it needs is an ID. Use the name we had in the
config: `registration`. Look inside `slidingWindow()`, and you'll see it needs a limit
and an interval, just like the config. Our limit is `1` and our interval is
`'10 minutes'`:

[[[ code_block({"sha": "a8f893665d4e61508c27feaffaad85109a6c0ecc", "step": "6-rate-limiter-builder", "file": "src/Controller/RegistrationController.php", "lines": "19-22,28,33-37,57,62-63"}) ]]]

That's it! We no longer need the config at all. And since it's the only rate limiter
we've configured, delete `config/packages/rate_limiter.yaml` entirely.

Only inline a rate limiter like this if it's truly a one-off. If you use the same rate
limiter in multiple places, keep configuring it like we did before. That approach isn't
going anywhere - nothing is being deprecated.

## Trying It Out

Let's test it! Go to the registration page and register as `worf@enterprise.space`,
username `worf`, name `Worf`, with something complex for the password. Agree to the
terms and... cool! We're registered and logged in as Worf.

Now log out and register again, this time as `natasha@enterprise.space`, username
`natasha`, name `Natasha Yar`, another complex password, agree to the terms and... we're
rate limited! Sure enough, it works exactly the same as before - it just makes your life
a little easier.

## That's a Wrap!

And that's it for our tour of what's new in Symfony 8.2 security! Like I said at the
start, 8.2 is turning out to be one of the biggest Symfony releases yet. Check out the
[Symfony blog](https://symfony.com/blog/) for posts on all the new features - there are
dozens of them, and it's a great way to catch up. There are even more security features
I didn't cover here.

If you have any questions about any of this, we're here for you down in the comments.

Alright friends...

Til next time, happy coding!
