Hey, look! It's our favorite command!

```terminal
bin/console debug:autowiring
```

This shows us a list of some of the services we can autowire in our code. But how does autowiring actually *work*? Let's run another command:

```terminal
bin/console debug:container
```

This gives us a *huge* list of services, and any Service ID that happens to be a class or interface name is autowirable. This means we can typehint it in the constructor of our service and service container will *inject* that service. Conversely, if a Service ID is *not* an interface or class name, it's *not* autowirable. This is by design, since most services are low-level and just exist to help other services behind the scenes. We'll rarely ever need to use those low-level services directly, and that's why we can't fetch them via autowiring. And *that's* why `debug:container` has *way* more entries compared to `debug:autowiring`.

The service container is basically a giant array where each service has a unique name that points to the corresponding service object. In the case of `twig`, for example, the container knows that to instantiate this service, it needs to create an instance of this `Twig\Environment` class. And even if we don't see the arguments here, it knows exactly which ones it needs to pass to instantiate it. As a bonus, if we make a request for the same service in more than one place, the service container only creates *one instance*, so we'll have the exact same instance everywhere.

You also may have noticed these service classes. This `CacheInterface`, for example, was used earlier as an alias for our `cache.app` service. This is just a way to make a service like `cache.app` *autowirable*. The vast majority of these services use the snake case naming strategy, so to make these autowireable in our code, bundles add some aliases - IDs, class names, or interfaces - that we can typehint in our code. So aliases are *basically* just symbolic links that just refer to other services. *However*, there may be times when there are *multiple* services in the container that implement the *same* class or interface. 

To handle that, back in our code, let's create a custom cache pool. In `config/packages/cache.yaml`, down here, uncomment the `pools` key, and instead of this example, say `iss_location_pool: null`. Now, at your terminal, run:

```terminal
bin/console debug:autowiring
```

And... check it out! This configuration added a brand new service - `iss_location_pool` - which has the *same* `CacheInterface` as `cache.app`. Back over in `src/Controller/MainController.php`, inside `homepage()`, let's change this variable name to `$issLocationPool` and keep the `CacheInterface` typehint the same. Copy that variable name and, down here, paste. This is called "named autowiring" - where our service container looks at the variable name *and* its typehint to implement the correct service. We can also see this with our `logger` service, but it's pretty rare.

Back at our browser, refresh the page and check the cache profile. Here's our `iss_location_pool` and our `iss_location_data` is written to that pool. If we ever need to *clear* the cache for this pool, over in our terminal, run:

```terminal
bin/console cache:pool:clear iss_location_pool
```

That clears the cache for this exact pool without affecting the other pools. Pretty handy!

We can also configure this pool *differently* from other pools. For example, let's set the expiration time for our new pool in the config file. Over in `cache.yaml`, instead of `null`, on a new line, say `default_lifetime: 5`.  The `5` is in *seconds*. This should impact all of the cache items in this pool. Now, in `MainController.php`, we can remove `$item->expiresAfter()`. We can also get rid of this `item` argument altogether. To make sure this is working, over in our browser, refresh the homepage again and... *no errors*. It works! 

Next: Let's talk about *environments* - sets of configurations that help us develop *locally* versus on *production*.
