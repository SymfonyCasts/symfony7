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
- Mention that you can output the subconfig with `config:dump framework cache`
- Show `cache.yaml` one
  - Really still part of the `framework` config, just separated out for
    organization
- Set cache provider to `cache.adapter.array`
- Update to see it in the dump
- Remove the dump
- See `cache.adapter.array` in action
- TODO We need to revert that `cache.adapter.array` somewhere, as it almost means no cache now

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
- Run `debug:container --parameters`
- Mention useful `kernel.project_dir` or `kernel.environment` in the output
- But how to get it from the controller? Dump `getParameter('kernel.project_dir')`
- But most of the time you need to inject them into services, you can do it
    with a special `%` syntax, open `config/packages/twig.yaml` to see

## Non-Autowireable Args

- But how to inject non-autowirable args into services?
- Create `iss_location_cache_ttl` param in `services.yaml`?
- Let's see how we can inject parameters in services
- Yes, we already know we can easily get it with `getParameter('iss_location_cache_ttl')`
    in a controller, but let's try to autowire it in the `homepage()` - that's how
    you will do it in a service's constructors
- Update the page to see an error about Symfony cannot autowire that arg
- Fix with the `Autowire` attribute
- Dump the value
- Comment the dumped value in the `homepage()` and use it in the `cache.yaml`
    as `%iss_location_cache_ttl%` 
- ? Should we also mention `services._defaults.bind` in addition to the new `Autowire`?
    It might be a useful thing to autowire something globally in the project and avoid
    duplicating `Autowire` in several places, but if we don't have a good example for it
    probably ignore or just mention it?

## Non-autowireable services

- Find a service in `debug:container` that is not autowirable, how about
    `twig.command.debug` service?  Probably would be nice to use a different one
    just to be different from the old course.
- Mention that controllers are also services, but kinda special with a super-power
    to autowire arguments in actions, not only in the constructor.
- Inject the service into `homepage()` controller - makes sure Symfony shows an error
- Add PHP attr above the arg: `#[Autowire(service: 'twig.command.debug')]`
- Refresh the page to see no errors
- Create an output: `$output = new BufferedOutput();`
- And run the command: `$this->twigDebugCommand->run(new ArrayInput([]), $output);`
- Dump the output and refresh the page
- Comment out the code to keep it for users 

## Env vars

- The key about env vars are that they're config that needs to be different
      on different environments - like dev vs prod.
  - The most common example is the database connection info
- You can set real env vars in your OS. But since that's tricky, we also
    have `.env` files that simplify our life
- Show `.env` file
- Mention `.env.local` and how it's special because it's not committed
- Also mention other, less-common `.env` files like `.env.test` and `.env.prod`
- We can show `%env(APP_SECRET)%` in `framework.yaml`
- Then we can convert the `iss_location_cache_ttl` param to an env var, with the idea
    that we might want to change it to be longer on production
- Basically, we will keep `iss_location_cache_ttl` but set it to `%env(ISS_LOCATION_CACHE_TTL)%`
- The `debug:dotenv` command
- Env var processors, e.g. we can use `int:` for `ISS_LOCATION_CACHE_TTL`
- Dump the value again in the `homepage()` see we're working with integer instead of a string
- Secrets vault - I think just mention this, but not show it.
  - This is a way to have env vars that you can commit to your repo, but
      are encrypted.

## Autoconfiguration

- We already have Maker bundle - we installed it in the previous course
- Create a Twig extension so we can show the ISS location data in `base.html.twig`
- We would show how the interface causes the "tag" so that Twig is aware
  - I like the idea of asking "How does Twig know to use this class? Is it the
    class directory?
- We can also show our already-created command. And say that sometimes 
    `autoconfigure` is powered by an interface. But other times, it works,
    via an attribute, like the command.
    - In both cases, you create a class, add the interface or attribute...
        and... bam! Symfony recognizes what you're creating and integrates it
- Create a Twig function
- Move the logic from the `homepage()` to the Twig function
- Inject missing dependencies in the Twig extension: HttpClient, Cache, ttl param.
- Render the data in the header (base template) so its available on any page
    not only on homepage.
