# More about Services

We already know that services come from *bundles*. And every *service* is a
combination of an ID, a class, and a set of arguments that are needed to
instantiate it. *But*, did you know that we can also create our *own* services
to organize our code for better maintainability? Yep! Believe it or not, we
already created one in the previous episode.

Open up `StarshipRepository.php`. We created this *without* configuration and
we're still able to use it in `StarshipApiController.php`. But how can we do
that? This works thanks to `config/services.yaml`. Let's open that. Down here,
below our `services` key, we see this `App\` section. This code registers
everything in our `src/` directory as a *service*. But it also *excludes* some
things, like `DependencyInjection`, `Entity`, and `Kernel.php`.

[[[ code('774ce78c59') ]]]

This `services.yaml` file, *including* this config, comes with the core `symfony/framework-bundle`.

Up here, we have this `_defaults` key. 

[[[ code('46b9d47a6d') ]]]

That's the configuration for *all* of the services in this file. 
This `autowire` key, set to `true`, *automatically*injects dependencies into our services. 
We also have this `autoconfigure` key, set to `true`, which automatically registers
our services as *commands*, *event subscribers*, etc. Pretty cool! We will talk more
about `autoconfigure` later.

To see a list of services, at your terminal, run:

```terminal
bin/console debug:autowiring
```

But *this time*, let's add the `--all` option at the end:

```terminal-silent
bin/console debug:autowiring --all
```

This will show us *all* of our services - even the ones that aren't aliased.
*Technically* non-services like our `Model` class are registered as services
too, but they're *removed* later because we're not using them in our code. The
point is, to create a service, all we need to do is create a *class* somewhere
in our `src/` directory and autowiring is *automatically* enabled for it.

By the way, all these `.yaml` files are *identical*. The *root key*,
like `services` or `framework`, is what makes them different. This means you
could copy all of the config from every file into a *single* `.yaml` file and it
would work the same way. We just keep them separated for maintainability,
*and*our sanity.

Next: You've heard me say over and over that the container holds *services*, and
that's *true*. But it *also* holds one other thing - a simple configuration
called *parameters*.
