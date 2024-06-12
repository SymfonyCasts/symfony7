# Cache Service and Cache Pools

Okay, we injected `HttpClientInterface` and made an HTTP request to fetch some
JSON data that we rendered on our website. *But* executing an HTTP request on
*every single* page load is *not* a good idea. HTTP requests are *slow*, and we
can already see that happening here, where our homepage loads are taking longer
than they were before. And the ISS moves *fast*, so it's not very efficient to
update this information constantly. Is there a service that can cache that data
instead? You bet!

Open your terminal and run

```terminal
bin/console debug:autowiring cache
```

to see if we have any cache-related services and... we do! These `cache.app`
aliases are ready to be used in our application. Another thing to note is
this `CacheItemPoolInterface`. Pools are just unique namespaces for cached
items. You might think of them as "subfolders" in the global cache directory.
That means you can clear one cache pool without affecting the others. We'll talk
about that more later.

For now, we're going to keep it simple and use `CacheInterface`. Back in our
code, inside `homepage()`, write `CacheInterface` (the one from Contracts) and
call it `$cache`. 

[[[ code('988434a43c') ]]]

Now, down here, copy these two lines, delete them, and write `$issData = $cache->get()`. 
The first argument should be the cache key, which we'll call... how about `iss_location_data`. 
The *second* argument should be an anonymous `function ()`. Now we can paste the two 
lines we copied earlier below. *But* instead of setting the variable, 
let's just `return`. *But* before we can use this `$client` variable in an anonymous function, 
we need to *use* it. Write `use($client): array`. 

[[[ code('6bae6b469d') ]]]

If we head over to our browser and refresh... we still made the HTTP Client request and, 
over here, we now have a cache icon that shows us if something was written in the cache. 
We have one! I'll click on this cache icon to open the profiler, and... how cool is that? We didn't create
a custom pool for this so the default pool is being used, but we *can* create
custom pools and we'll do that in a moment.

If we head back to the home page and refresh... the HTTP request is *gone*. And
if we hover over the cache icon... *nothing was written*. And now, our page
loads are noticeably faster. Right now, that data is cached forever unless we
clear the cache, but for dev purposes, over in our function, let's change that.
Add `ItemInterface` as the first argument and call it `$item`. Inside,
write `$item->expiresAfter()` and pass `time: 5`. 

[[[ code('4843905263') ]]]

This number is in *seconds*, after which the cache will expire. Back at our browser, 
if we refresh, nothing changes because the value was already cached. To see our changes, 
we need to clear it manually so it can be re-cached with our new time frame of five
seconds.

The default cache adapter is a file system, which means the cache is stored in
the `var/cache/dev/pools/` directory. Here, we can see our `/app` folder which
corresponds to our `app` cache. We *could* delete this directory manually, but
there's a better way. At your terminal, run:

```terminal
bin/console cache:pool:list
```

This is the list of available pools in our application. To *clear*
the `cache.app` pool, we can use another command:

```terminal
bin/console cache:pool:clear cache.app
```

And... *cache cleared*! If we go back to our browser and refresh now...here's
our HTTP request. If we quickly refresh again... now the data we have is coming
from our cache. If we refresh one more time after five seconds have passed...
here's our HTTP request again!

Next: Let's learn how to *configure* our cache service.
