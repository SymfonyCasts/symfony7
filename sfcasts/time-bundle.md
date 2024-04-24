# KnpTimeBundle: Install the Bundle, Get its Service

On our site, customers have a convenient "Ship Repair Queue" that lists all of the ships being repaired and their status. For this tutorial, we've added a new `$arrivedAt` field to our `Starship` class with some getters and setters. We want to *print* this field on the homepage.

If you forgot which controller is responsible for the homepage, you can always hover over the page info in the web debug toolbar and... boom! It says "MainController :: homepage". Let's open that up - `MainController.php` - and find the `homepage()` action. Down here, we can see that it renders a template: `main/homepage.html.twig`. Open *that*, find the "Ship Repair Queue" and, down here after `{{ ship.name }}`, add a new `<div>` with `Arrived at: {{ ship.arrivedAt }}`. Now, if we head back to the browser and refresh the homepage... ah... an *error*.

`An exception has been thrown during the rendering
of a template ("Object of class DateTimeImmutable
could not be converted to string").`

That makes sense. PHP can't just print `DateTime` objects because it doesn't know which format we want. How do we fix that? Easy! We can use a Twig filter. Back over here, after `arrivedAt`, say `|date`. If we refresh again... awesome! Here's the date and time in a specific format.

We *can* pass an *optional* `DateTime` format as the first argument to this `|date` filter, but if we skip it, the application's default format will be used. Which one, exactly? Good question! Let's check out the config. At your terminal, run:

```terminal
bin/console config:dump twig
```

Here, you can see the date format configuration for your application. And if you want to shorten this command instead of typing the full name - `config:dump` - you'll want to do that while you still have a unique name in your application. Otherwise, the console will ask you which command you want to execute.

All right, back to the browser. We've printed our date, but it would be *so* much cooler if we could say something like "2 hours ago" instead of this long date. *Unfortunately*, we don't have a service in our app that can do that for us yet. And I certainly don't want to write it myself. I've got more fun things to do like playing board games. But, hm... Is there a bundle with a service that can do this? Yep! It's called "KnpTimeBundle". Let's find it on GitHub. Here it is! Scroll down to the "Installation" section and copy this command. At your terminal, paste that command and run it:

```terminal
composer require knplabs/knp-time-bundle
```

This installs the bundle, the required dependencies, and it also executes some recipes. If we run

```terminal
git status
```

check it out! Eevery time we install a new bundle, it changes our `composer.json`, `composer.lock`, `symfony.lock`, and `bundles.php` files. Let's open that. Down here, we can see that `KnpTimeBundle` was added to this array. That's where Symfony activates this bundle in our application. Remember, *bundles* give us *services*, and this one's no exception. But... what services did it give us? We *could* read the docs to learn more about this, but I'm going to be lazy and run:

```terminal
bin/console debug:container time
```

I'll select `datetime_formatter`, which is option `10`, for more information. Cool!

To see if we can autowire it, let's run another command:

```terminal
bin/console debug:autowiring time
```

And... we *can*! If we want to use the `ago` format for our date object, this is the typehint we need to use to inject this service in our PHP classes. *But*, since we only want this in our Twig template, there's a better solution. The bundles also come with a Twig integration that provides some nice Twig filters and functions. We can see that if we run

```terminal
bin/console debug:twig
```

and search for `ago`. Here it is! If this `date` looks familiar, that's because it is. That's the one we used before. Let's try the `ago` filter this time. Back over here, replace `date` with `ago`... save, and open the browser. Refresh the homepage and... there it is! We now have this nice "ago" format. *So*, bundles give us *services*, services are *tools*, and tools are *fun*.

Next, let's take a look at a brand new component - HTTP Client.
