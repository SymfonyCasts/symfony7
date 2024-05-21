# Non-Autowireable Arguments

Earlier, we learned that we can fetch parameters from the container with `getParameter()` in our controller. We also saw how easy it is to create our own services. And guess what? What's not the only thing we can customize! We can also create our own *parameters*. How? I'll show you!

Open `/config/services.yaml`. Here, we see an empty `parameters` section. Inside, let's create a new parameter - how about `iss_location_cache_ttl` - and let's set it to `5`. We'll use this parameter in the configuration to avoid hard coding anything. But first, head back to `MainController.php`, and instead of dumping `kernel.project_dir`, let's dump our new parameter: `iss_location_cache_ttl`. Over in our browser, refresh and... there it is - 5!

Now, we know we can grab this with `getParameter()` in our controllers. But what do we do if we're not *in* a controller? How can we use paramters in services without this fancy `getParamter()` method? Let's see... If we add a new argument to the homepage - `$issLocationCacheTtl` - and dump *this* instead of `getParamter()`, when we refresh... *error*! Symfony can't autowire that argument. It can autowire *services*, but this isn't a service; It's a parameter. So how do we do this? The answer: *Autowire it*! We can autowire parameters just like services, and it will work in the constructor or controller *just like* normal autowiring. Check it out!

Back in our code, let's add the autowire attribute above the argument. Say `#[Autowire()]` and, inside, `param: 'iss_location_cache_ttl'`. Back at our broswer, if we refresh the page... 5! It *works*! Okay, let's remove that and see how we can use our new parameter in our config.

Open `/config/packages/cache.yaml`. Instead of this hard coded value, say `%iss_location_cache_ttl%`. If we check this in our browser... everything still works! Awesome!

Before we continue, I want to show you one more way you can autowire parameters: *parameter binding*. Open `services.yaml` and, in `services`, below `_defaults`, add a new section: `bind`. Inside, add our variable  ame - `$issLocationCacheTtl` - set to `%iss_location_cache_ttl`. As soon as we match the argument with the name we wrote in `bind`, Symfony will *automatically* autowire it to this parameter. We can also add a typehint - `int` - in case we want to autowire `$issLocationCacheTtl`. In `MainController.php`, we need to add `int` here as well. When we try it... *this works too*! And since we're autowiring globally, we avoid duplicating our autowired PHP attributes in multiple places. Super handy! Since we're not currently using that parameter anywhere except our config, we can get rid of this for now.

Next: Let's see how we can autowire *non*-autowireable services. It's *surprisingly* easy.
