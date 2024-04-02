# OUTLINE

## Setup

- no matter what you'll do with Symfony, it comes down to services
- all about services: the things that do the work in your app
  - and about configuration for those services
  - and about environments... which are just another way to tweak
    those services to act differently locally while developing vs
    on production.
  - Logger service is a good example
- We call this "Fundamentals" because, truly, after this tutorial,
  everything else is just a variation on these themes.
- Download course code!

- Reminder: services are objects that do work
  - logger, mailer, database connection, even our controller!
  - juxtaposed with objects that hold data
- `debug:container`
  - The container is responsible for instantiating each service
  - So it knows the class name and every constructor argument
  - If you ask for a service multiple times, it only creates it once
- Where do these come from?
  - Who tells the container "there should be a `logger` service" and its
    class is `Logger` and it should be instantiated with these arguments?
  - well, some services from OUR code... and we'll about how those are
    registered in a bit
  - But the VAST majority come from "bundles"
- show `bundles.php`
  - bundles are like plugins for Symfony
  - they can give us multiple things... but mainly they give us services
  - Each has a config file that literally says:
  - I want to have a service called `logger` and it should be an instance
    of `Logger` and it should be instantiated with these arguments
  - services are tools, and so bundles give us tools

## KnpTimeBundle: Install bundle, get service

- Before this tutorial, I added a new `arrivedAt` `DateTimeImmutable` field to
  `Starship`.
- Let's print that in `homepage.html.twig`
- We can't just print the property - you can't print DateTime objects
- But we can use a Twig filter to format it
- Nice!
- But wouldn't it be cooler if we could say "2 hours ago" instead of
  "2018-01-01 12:00:00"?
- There's no service in our app that does this. And I certainly don't want
  to write it myself - I've got more fun things to do, like play board games.
- But maybe there's a bundle that gives us a service that can do this?
- There is! It's called KnpTimeBundle
- `composer require knplabs/knp-time-bundle`
- Show `config/bundles.php`
- Remember, bundles give us services
- `debug:container time` to see a few matches
- Also `debug:autowiring` to see `DateTimeFormatter`
  - So if we wanted to do this "ago" conversion in PHP, we could use this
    service
- But one of the other services is what's called a Twig extension
  - It's a service, but one that integrates into Twig to add new stuff, like
    filters
- Heck, you can even see this in `debug:twig`
- Use the `ago` filter in `homepage.html.twig`
- Refresh the page. How cool?
- So bundles give us services. And services are tools. And tools are fun!

## HTTP Client and More Services

- We know that Symfony is a collection of a TON of independent little PHP libraries,
  called "components".
- We only have a small number installed right now, but as we need more features,
  we'll install more components.
- In the last tutorial, we installed the `serializer` component to help us
  serialize objects to JSON.
- Let's install another component: the `http-client` component.
- This is good at making external HTTP requests.
- `composer require symfony/http-client`
- Run `debug:autowiring` to see a new service!
- But wait, this time, no bundle was installed.
  - This is a pure PHP package: just PHP code.
  - So it *does* contain service classes, which are just classes that
    do work.
  - But it does NOT contain any configuration that says "Hey! I want
    to have a service called `http_client` and it should be an instance
    of `HttpClient` and it should be instantiated with these arguments."
- So where did this service come from?
- The answer is FrameworkBundle. That's THE most core bundle of Symfony
  and it's been in our app since the beginning.
- It has a special power: it watches for Symfony components that are
  installed and automatically registers their services.
- So, no big deal... just another way that services will arrive in our app.
- Next, let's put our new service to work!

## Using the HTTP Client

- Let's fetch some data from an API on the homepage.
- We'll show this up in the header later
- Right now we're going to talk to an API that tells us where the ISS is right now.
- You can see it at https://api.wheretheiss.at/v1/satellites/25544
- Remember: when we installed the `http-client` component, it gave us a
    new service, which we can see with `debug:autowiring`
- *How* do we make requests with this service? Well, we can totally read
    the docs, but thanks to the type-hint on the argument, we can guess
- Do the request in `homepage()` and `dump()` the result
- Refresh the page to see the dump
- Show the HTTP Client panel in the WDT
- Pass the data in template and render it

## Cache Service and cache Pools

- But executing real API request on every page load is not a good idea,
  it would be great to cache the result for a moment
  - Ya know, because while the ISS moves super fast, we don't need to
     be SO exact
- Let's talk about another service: the cache service
- Run `debug:autowiring cache` to see if we already have any cache
  related services - we do!
- Cache pool is just a unique namespace for cache items
- Typehint with `CacheInterface` to get the cache service
- Cache the API response for 5 seconds with `expiresAfter(5)`
- Refresh the page - notice no HTTP client request, but there's a cache icon
- Show the Cache panel in the WDT
- We didn't create a custom pool for it, so default `app` is used, but you can create custom ones
- Maybe even show `var/cache/dev/pools/`

## Bundle / service config

- So we know that bundles give us services
- And when we autowire a service, it means that the bundle that provides
    it provides all the details about how to instantiate it
- `dd($cache)` to see the class name
  - For example, for the cache service, FrameworkBundle tells the
    service container:
> Hey! When I ask for the `CacheInterface` service, I want you to
> instantiate a `Symfony\Component\Cache\Adapter\TraceableAdapter`
> object with a specific set of arguments.
- So the question now is: how can we control this?
- For example, the cache is this TraceableAdapter, but if you look inside,
    that's just a small wrapper around a filesystem cache. So, apparently,
    the cache is stored on the filesystem. What if we wanted to store the
    cache in memory instead? Or in a different place on the filesystem?
- Show `framework.yaml` config
  - The important part is the root `framework` key: that means we're passing
  - configuration to the `FrameworkBundle`. And it'll use that config to 
  - change how it instantiates its services
- Call `debug:config framework` - this will show you the current config
- In order to see the full config - call `config:dump framework`
- Show `cache.yaml` one
  - Really still part of the `framework` config, just separated out for
    organization
- Set cache provider to `cache.adapter.array`
- See in action

## Autowiring

- Run `debug:container`
- Explain how autowiring works
- Mention service aliases
- So if a service's id is NOT an interface or class name, it
  is NOT autowirable. And most services are this way on purpose, as they're
  low-level. That's why `debug:container` has so many more entries than
  `debug:autowiring`
- Btw, are there ever times when there are *multiple* services in the
    container that implement the same class or interface?
- Create a custom cache pool in the `cache.yaml` config
- Run `debug:autowiring cache` again to see a new service
  - So in this case, the config added a totally NEW service
- Change to `CacheInterface $issLocationPool`
   - Talk about how this is "named" autowiring
   - It's rare, but you can also see it with the logger
- Update the page and see WDT
- Clear the cache with `cache:pool:clear iss_location_pool`
- Refresh the page again
- Let's set `default_lifetime: 5` for our pool in the config
- Now drop the `expiresAfter(5)` call
- Probably also show `config:dump twig`

## Environments

- Show `.env`
- Explain the `APP_ENV` env var
- Explain `when@test` e.g. on the same `framework.yaml` config file
- Also mention `config/packages/{env}` dir
- Show env-specific routing, e.g. `config/routes/web_profiler.yaml`

## prod environment

- Set `APP_ENV=prod`
- Update the page to see that WDT is gone
- Try to change something in the template - nothing updated
- Clear the cache with `cache:clear`
- And update the page again to see changes
- Mention `cache:clear --env=prod`
- Prod only cache config - set framework cache adapter back to `cache.adapter.filesystem`
  and keep `cache.adapter.array` only `when@dev`
- Revert back to `APP_ENV=dev`

## Services

- Maybe we skip this or just review it? We created a service in ep1
- Actually, for this section, let's talk about `services.yaml`,
   autoregistration, etc
- We know that services come from bundles
  - And every service is the combination of an id, a class and a set of arguments
- But we can also create our own services. And we have!
- This all works thanks to the `services.yaml` file
- Walk through the auto-registration of services
- And how `_defaults` works and enables autowiring & autoconfigure (which we'll
   talk about soon).
- `php bin/console debug:autowiring --all`
- How non-services (i.e. model classes) are technically registered as services,
    but removed later because we don't use them.
- Btw, all these YAML files are "identical". It's the root key like
    `services` or `framework` that makes them different
- But you could copy all the config from every YAML file into a single
    file and it would work the same way

## Parameters

- So far, we've talked about the container being full of service objects
- That's true: but it holds one more thing: scalar config called "parameters"
- ... WIP

## Non-Autowireable Args

- ... WIP - probably try to autowire a parameter.
- Fix with the `Autowire` attribute
- Maybe also autowire a non-autowireable service, perhaps something
     silly like the `debug:twig` command object
