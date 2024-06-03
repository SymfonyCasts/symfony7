# The HTTP Client Service

We know that Symfony is a collection of *a ton* of independent, teensy tiny PHP libraries, called "components". We only have a small number of them installed right now, but as we need more features, we'll install more components. In the last tutorial, we installed the `serializer` component to help us *serialize* objects into JSON. Head over and open `StarshipApiController.php`. Down here, hold "cmd" or "control" on a Mac and click this `json()` method. Here, we have our `serializer` component. This checks to see if we have this service, and if we *do*, the `serialize()` method is called.

Okay, our site is pretty cool, but wouldn't it be *much cooler* if we... say... rendered the real-time location of the International Space Station (or ISS)? Of course it would! And luckily, a website already exists that shows that information. We'll navigate to `wheretheiss.at` and... check it out! It looks like the ISS is somewhere over the Pacific Ocean at the moment and - *good news* - they also have an API that we can use to fetch the ISS's coordinates and print that on our website. How convenient! You can copy this URL and open it in a new tab to see the JSON.

But *first*, let's check to see if our application already has an HTTP client to help us execute some API requests. Over at your terminal, run:

```terminal
bin/console debug:autowiring http
```

And... we *do* have some HTTP-related services, but no HTTP client. And that's right! We *don't* have a service in our app that can do HTTP requests yet, but we *can* install it. To do that, we need the `http-client` component, which, as the name might suggest, is *great* at making external HTTP requests. At your terminal, run:

```terminal
composer require symfony/http-client
```

If you're wondering where this package name came from, good question! If you search for "symfony http client" in your browser, one of the top results is this Symfony HTTP Client documentation. Under "Installation", you'll find this terminal command, along with some handy information about the component.

Now, back at our terminal, let's run

```terminal
bin/console debug:autowiring http
```

and... there's our `HttpClient`! Now that we have our new service, we can type hint it in our application. But... *wait*... this didn't install any bundles. If you run

```terminal
git status
```

you can see that the only files changed were `composer.json` and `composer.lock`. That's okay! What we installed was a *pure* PHP package, and while it *does* contain service classes (which are just classes that do work), it *doesn't* contain any configuration that says:

> Hey! I want to have a service called "http_client",
> which should be an instance of `HttpClientInterface`,
> and it should be instantiated with these specific
> arguments.

So where did this service come from? The *answer* is FrameworkBundle. Open `config/bundles.php`. The first bundle here is `FrameworkBundle`.
That's a *core* Symfony bundle, and it has been in our application since the beginning. This bundle's superpower is watching for newly installed Symfony components and automatically registering their services. *Super* convenient.

Now that we have our new `HttpClient`, let's put it to work! Open `MainController.php` and, in `homepage()`, let's type hint our new service. I'll move this onto multiple lines... write `HttpClientInterface`... and call it `$client`. Down here, before the `return` statement, write `$client->`. We have a few methods to choose from, so select `request()`. Inside, write `GET`, and then we need to send a request to this URL. To save you some time, you can copy this link from the page below this video. Over here, add `$response`... and below that, write `$response->toArray()`. That's a handy method that decodes JSON into an array. And finally, we'll add this `$issData` variable. To see if it's working, we can go ahead and `dump($issData)` here.

Over in your browser, refresh the homepage and, down here, if you hover over this icon... *nice*! That's our data! Right beside this is another icon you may have noticed. That's the HTTP Client, and it shows us the total number of requests that were executed on this page. Click this Debug icon to open the Symfony Profiler and inspect it. Our HTTP Client is integrated with the web debug toolbar, and we can see that our request was executed. Awesome!

Back over here, remove the `dump()` and pass that data to the template. In `homepage.html.twig`, down here at the end, add another `<div>`. Inside, add an `<h2>`, and let's call it `ISS Location`. We'll also add some classes to make it look nice. Okay, down here, let's add some `<p>` tags with our data: `Time: {{ issData.timestamp|date }}`, `Altitude: {{ issData.altitude }}`, `Latitude: {{ issData.latitude }}`, `Longitude: {{ issData.longitude }}`, and `Visibility: {{ issData.visibility }}`. Back at our browser, refresh and... here it is! This is the real-time location of the International Space Station with *all* of the data we just rendered! Looking *good*!

As cool as this is, now every time someone navigates to our homepage, we're making an HTTP request to the API, and HTTP requests are *slow*. To fix that, let's leverage *another* Symfony service - the *cache* service.

