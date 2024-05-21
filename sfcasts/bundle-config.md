# Bundle Config: Configuring the Cache Service

So far, we've learned how to use the HTTP Client and cache services, and we've injected that into the `homepage()`. *But*, we're not responsible for *creating* their objects. We already know that bundles give us services, and when we *autowire* a service, our bundle provides all of the details we need to instantiate it. But if something *else* is responsible for instantiating those objects, how can we control it? The answer is *bundle configuration*.

Open the `/config/packages` directory. All of these `.yaml` config files are *automatically* loaded into our Symfony application, and their job is to configure services that each bundle give us. In our `homepage()` method, right in the beginning, let's `dd($cache)` so we can see the class name of the object we're getting. For example, for the cache service, `FrameworkBundle` tells the service container:

> Hey! When I ask for the `CacheInterfaceService`, I
> want you to instantiate this `TraceableAdapter`
> object with a specific set of arguments it needed.

So it looks like our cache is just this `TraceableAdapter`, but if we look *closer*, we can see that it's *actually* a wrapper around a file system, and the cache is stored *inside* the file system. That's cool, but what if we want to store the cache in memory instead? Or somewhere else in the file system? This is where bundle configuration *shines*. Open `framework.yaml` and find this `framework` key. This means we're passing configuration to the `FrameworkBundle`, and it'll *use* that config to change how it instantiates its services. By the way, the file name here isn't important. We *could* call this `pizza.yaml` and it would work just the same.

Okay, head over to your terminal and run:

```terminal
bin/console debug:config framework
```

This shows us the current config. To see the *full* config, run:

```terminal
bin/console config:dump framework
```

That is *a lot* of information. Let's narrow that down. If we want to see the configuration that's responsible for the cache service *only*, run:

```terminal
bin/console config:dump framework cache
```

*Much* better. Over in `cache.yaml`, we can see that this is still part of the `framework` config - just *separated* for organization. Below this example, let's set `app` to `cache.adapter.array`.

Okay, back at the browser, refresh. Awesome! This changed to `ArrayAdapter`. Head over and remove `dd($cache)` so we can see the `cache.array.adapter` in action. Refresh the page again, and... ah! Every time we refresh the page, we're executing the HTTP request, so the cache is *only* live during the request. When we start a new request, the cache invalidates and we see that HTTP request.

Next: Let's take a closer look at *autowiring*.
