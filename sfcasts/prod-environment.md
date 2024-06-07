# The Prod Environment

Open the `.env` file in the root of our project and change this `dev` environment variable to `prod`. To see what changed, back at our browser, refresh. And... hey! Look at that! The web debug toolbar is *gone*. Now, let's try to change something in one of our templates. Open `templates/main/homepage.html.twig` and, at the bottom, let's change `Time` to `Updated at` so it's more descriptive. If we head back and refresh... *nothing changed*. Why? For performance reasons, templates are *cached*. We made our change *after* the template was cached, so our browser can't see it yet. To fix this, we need to *manually* clear our cache. At your terminal, run:

```terminal
bin/console cache:clear
```

To *specify* the environment cache we want to clear, we can add the `--env=` option with the name of the environment we want to clear the cache for to the end of this command, like `--env=prod`, for example:

```terminal-silent
bin/console cache:clear --env=prod
```

This can be helpful when you need to run a command in a specific environment that's different from the one you're currently working in. Since we're *already* developing in the `prod` environment, this part of the command isn't necessary. If we run that... nice! The `prod` environment cache was cleared successfully.

Okay, if we head back over and refresh the page again... *ta da*! We see "Updated at". *Awesome*. If you're ever working in the `prod` environment and you *don't* see changes you've made to your templates, config files, etc. reflected in the browser, you *may* need to manually clear your cache.

Right now, we're using `cache.adapter.array`, which is kind of like a *fake* cache. We can see that in the `config/packages/cache.yaml` file. A fake cache is fine for development, but when we're working in the `prod` environment, we really want to use `cache.adapter.filesystem` instead. Since we now know about the `when@` syntax, let's leverage that. Below, say `when@`, but this time, we need to set it to our `prod` environment with `what@prod:`. Below that, we'll repeat the same structure we see above - `framework`, `cache`, and `app` - followed by `cache.adapter.filesystem`.

Okay, to see this in action, we need to clear the cache again (since we're still in the `prod` environment) with:

```terminal
bin/console cache:clear
```

Back at our browser, if you watch closely, you'll see that our data is cached for about five seconds, and then... new data! It *works*. In our `.env` file, let's change `APP_ENV=prod` back to `dev`. If we go back and refresh again... after *every* refresh... we see an HTTP request.

Next: Let's learn more about *services*.
