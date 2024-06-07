# Parameters

Earlier, we talked about our container and all the service objects it has. To see them, we can run:

```terminal
bin/console debug:container
```

But these services aren't the only things in our container. It also holds *parameters*. Run the same command as before, but this time, add
the `--parameters` option:

```terminal-silent
bin/console debug:container --parameters
```

These are basically just variables that you can reference in your code. Most of these are internal, but there *are* some core parameters that you may find useful. For example, any of these that start with `kernel.`, like `kernel.environment`, which is set to the `APP_ENV` environment variable. *Or* this `kernel.project_dir` that's set to the path to the project root directory.

*So* how do we get this from our container? We actually have a special shortcut method for it in our controller. In the `/src` directory, open `Controller/MainController.php`. In the `homepage()` method, right at the beginning, write `dd($this->getParameter())`. And inside *that*, write the parameter name wrapped in `%%`: `'%kernel.project_dir%'`. As you can see, PhpStorm (with the Symfony plugin), has already autocompleted that for us. *Nice*.

Back at the browser, refresh and... there's our path! Most of the time, we'll need to inject parameters into services and we can do that with a special syntax. I'll show you! Open `config/packages/twig.yaml`. You can see that we have `twig.default_path` that's set to `%kernel.project_dir%/templates`. This `%[parameter name]%` is a special syntax used to refer to a parameter in `.yaml` files.

Next: Let's create a *custom* parameter and learn how to fetch it from the service container in different ways.
