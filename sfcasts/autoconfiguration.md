# Autoconfiguration

This real-time ISS location feature is *cool*, but it would be *even cooler* if we could see this on *every* page, not just the homepage. How can we do that? We *could* pass the data in every action, *but* that's not ideal. Instead, we're going to create a custom Twig function that will fetch the *actual* data in the template. That way, we can render ISS location data in our `base.html.twig` file without passing it from every controller. Sound good? Let's get started!

First, we need to create a Twig extension. In a previous course, we installed Symfony Maker Bundle. Let's see if that can help generate some boilerplate code. At your terminal, run:

```terminal
bin/console make:
```

and hit enter. We get an error but this shows us a list of all the commands at our disposal that *make* something, and... check it out! We have one that's Twig related - `make:twig-extension`. That's what we're looking for! Run that:

```terminal silent
bin/console make:twig-extension
```

This asks us for a Twig extension class name. We can keep the default name - `AppExtension`. And... *awesome*. We can see that it created two files: `AppExtension.php` and `AppExtensionRuntime.php`. Let's open the first one - `/src/Twig/Extension/AppExtension.php`. It already has a couple of methods: `getFilters()` and `getFunctions()`. Right now, we're only interested in functions, so we can get rid of the `getFilters()` method completely. Inside `getFunctions()`, let's replace the demo `function_name` with something more relevant. How about `get_iss_location_data`. This is the actual Twig function name that we're going to call in templates.

Over here, we can see that a method is called on `AppExtensionRuntime::class`. Right now, it's just called `doSomething`. Hold "command" (or "control" on a Mac) and click this method to open it. While I'm sure this method *is*, in fact, *doing something*, let's rename it so we know *what* it's doing. How about `getIssLocationData()` to match our function? We can also delete this argument since we don't need it.

Back in `AppExtension.php`, replace `doSomething` with our new method name - `getIssLocationData`. As you can see, PhpStorm autocompletes that for me. *Now* we can go grab the code responsible for that data fetch from the `homepage()` action. Here it is. Copy that, delete it, and we can also clean up some of our code while we're here. We don't need to pass this `issData` to the template anymore, and we can also get rid of these two arguments. Much better! Now we can head back over to `AppExtensionRuntime.php` and, down here, *paste*. We don't need a variable for this data, so we can just `return`. We *do* have some undefined variables like `$issLocationPool` and `$client`; Those are our dependencies. We can't inject these directly into the method like we do with our controllers because method injection only works for controllers. We *can*, however, inject dependencies into our *constructor*, and we can even use a handy PHP 8 feature called "Constructor Property Promotion".

Check it out! Up here, write `private readonly` - we'll typehint our first argument - `HttpClientInterface`, and call it `$client`. Below that, once again, write `private readonly`, but *this time* write `CacheInterface` (the one from `Symfony\Contracts\Cache`) and call it `$issLocationPool`. We can also get rid of this comment here. Cool.

By the way, if we needed to inject our `issLocationCacheTtl` here, we could do that the same way, using the `#[Autowire]` PHP attribute. We don't need to do that for this example, but it's a good thing to keep in mind.

Down here, let's update this method. This should be `$this->issLocationPool`, `$this->client`, and since we can call it directly from the anonymous function, we don't need this `use ($client)` anymore. Okay, at the browser, *refresh* to see... an *error*.

> Variable "issData" does not exist.

Hm... We removed that from the controller. Open `/templates/main/homepage.html.twig` and, below, let's use our custom Twig function. Write `{% set issData = get_iss_location_data() %}`. If we refresh the page again... our custom function is *working*. But wait... how does Twig know to use this class? We didn't add any configuration for the Twig extension. Is it looking at the `/src/Twig/` directory? *Not exactly*. We could rename this directory and it would still work.

The reason this works is thanks to the `autoconfigure: true` option in `/config/services.yaml`. Symfony *automatically* configures all of our services, like this Twig extension or even the `ShipReportCommand` we created earlier. When that option is enabled, it basically tells Symfony:

> Hey! Please look at the base class or interface
> of each service. If that's a command, twig extension,
> or any other class that should be hooked into a part of
> Symfony, please go ahead and integrate that
> service into the system for us.

Yep! Symfony sees that our class extends a base command class and *knows* that it's a command that should be integrated into the system. In our extension's case, it *extends* `AbstractExtension`, so Symfony knows it should be integrated into the Twig system. I *love* this! It means that the only thing we need to care about is creating a PHP class that extends a certain class or implements a specific interface. The documentation will help you navigate this, and autoconfiguration will do the rest.

Internally, autoconfiguration just adds a special tag for our services, like `console.command`, that helps the system notice it. But *other* times, it works via an *attribute*, like the command. In both cases, we create a class, extend a base class, implement an interface, or add a special attribute, and *bam* - Symfony understands what you're doing and integrates it.

By the way, if you're curious on what this separate `AppExtensionRuntime` is for, great eye! *Extension runtimes* have always been in Twig but only recently have they been promoted - mostly thanks to the maker bundle. We could inject the services directly in our Twig extension and house all the logic there, but this comes with a disadvantage: since Twig extensions are loaded whenever Twig is used, the extension, and all its dependencies are also loaded. Even when not using a particular extension's function or filter. Twig extension runtimes are a way to make the extension logic *lazy*. The runtime service is only instantiated when and if its needed. In our example here, it isn't really helping us as we're showing the ISS location data on every page but you can imagine a function or filter that's only used on a few pages in your app. It's a best practice to keep your Twig extensions as light-weight as possible with none, or very few dependencies and push all the heavy-lifting to extension runtimes.

All right, in `homepage.html.twig`, copy this HTML code, delete it, and open `base.html.twig`. Down here, below our logo, *paste*. Okay, let's simplify this a little in order to make it more compact. Create a new `<div>` above this and, inside, write `ISS Location`. Then, in parentheses, write `{{ issData.visibility }}`. We'll also give our `<div>` a `title` and set that to this `Updated At` line from below. Now we can clean up our code. We don't need this `<h2>`, we moved `Updated At` so that's no longer necessary here, and we can also get rid of `Visibility`. *Much* better! Over at our browser, refresh and... the ISS information is in a new position in the header. And if we open a different page, like one of our ship pages, the ISS location info is there too.

And that's it! We've covered the fundamentals of Symfony services, configuration, *and* environments. We are *powerful*! No, *unstoppable*.

In the next tutorial, we'll introduce Doctrine - the industry standard way to work with databases in PHP. Until then practice. Go build something - *anything* - and tell us about it. And if you have any questions, thoughts, or just want to say "hi", we're here for you down in the comments. All right, friends. See you next time!
