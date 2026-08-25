# Limiting Login Attempts

Time to look at login throttling: a way to rate limit *failed* login attempts.
This protects your app, at the application level, against someone
brute forcing an account - trying every possible password combination until they get
in.

## Enabling `login_throttling`

To enable this feature, over in your IDE, open `config/packages/security.yaml`. Down
in our `main` firewall, add `login_throttling: true`.

Now refresh the login page... and we get an error! Login throttling requires Symfony's rate
limiter component. Copy the `composer require` from the error message, jump over to
your terminal, prefix it with `symfony`... and paste:

```terminal
symfony composer require symfony/rate-limiter
```

Perfect.

## Making Throttling Work Locally

Back in our IDE, there's one other thing to change. By default, the rate limiter uses
our cache configuration to store its state. So open `config/packages/cache.yaml`.

Down here, our `app` cache is using the *array adapter* - an in-memory cache
that doesn't persist between requests. This is the default for our dev environment.
In production, the default is `filesystem` which does persist between requests.

Because I want to demo how login throttling works locally, I'm going to temporarily change this
to `filesystem`.

For the most part, you *don't* want this: login throttling getting in your way during
development is annoying.

## Tripping the Limiter

Refresh the page and try logging in as `picard@enterprise.space`. For the password,
use `makeitgo` - the wrong password on purpose. Hit enter.

That's one failed attempt. `makeitgo`. Two. Three. Four. Five. Six...

And now we've tripped the rate limiter:

> Too many failed login attempts, please try again in 1 minute.

So the default is five failed attempts per minute. After a minute, it resets and you can try
again. This *really* cuts down on someone blasting thousands of requests a second at
this page to try and brute force a login.

## The `login_throttling` Config

Let's look at the config options we have for this feature. Back in your terminal, run:

```terminal
symfony console config:dump security firewalls
```

Then scroll up to find the `login_throttling` section... here we go.

The two most important options are `max_attempts` and `interval`. Like I said, the
default is five attempts within one minute, and these are how you adjust that.

Now, here's how the default login throttler actually works: it creates *two* rate limiters.

The first is keyed by IP address *and* username - that's the one that gets our five
per minute. So the same username can't be attempted from the same IP more than five
times before tripping.

But it also creates a second one, called the *global* rate limiter. This one is keyed
by IP address *only*, and its limit is `max_attempts` multiplied by five - so 25 by default.
It's another layer, to stop someone trying thousands of *different* username
combinations from the same IP.

Keep going down. The rate limiter can optionally use a lock, `lock_factory`
is how you can configure it. A lock helps when multiple requests hit the same rate limiter at
basically the same time. Without it, two requests could both see that the limit hasn't been
reached yet, and both be allowed through.

`cache_pool` is where you choose the cache the rate limiter uses. In production, you want this
to be something that *persists* - by default it's built off our `cache.app` pool. It's
important that this is a *shared* cache between all your app servers (if you have multiple),
and that it isn't reset during deployment.

`storage_service` lets you override the rate limiter's storage system entirely.

And finally, at the very top, `limiter` is where you can pass your own service and
take over completely. If you use this, none of these other options mean anything.
You can see in the comment that it has to be a service implementing
`RequestRateLimiterInterface`.

## Adjusting the Limits

Now fiddle with these a bit. Go to `security.yaml` and set `max_attempts` to `3`, and
`interval` to `10 minutes`.

So now the IP-and-username limiter allows three attempts within 10 minutes... and the
global IP limiter - remember, times five - allows 15 attempts in 10 minutes from the
same IP.

## Be Careful with Shared IPs

One thing to be careful about - and the reason that global limiter exists at all - is
shared IP addresses. If one of your customers is a large corporation, like a bank, all
of their employees might reach the internet through the same IP. So don't restrict
this *too* much. Imagine everyone logging in at nine in the morning, and a whole whack
of them fat-fingering their password: you could lock out a bunch of people at once.
That's exactly why the global limiter is more generous than the per-username one.

One more thing to keep in mind: this is *application-level* rate limiting. Every
request still reaches your app and still uses server resources, even when it gets
throttled. So don't lean on this as real DDoS protection. For that, you want something
like Cloudflare that stops the attack before it ever reaches your server.

That's it for login throttling. Next up: security events!
