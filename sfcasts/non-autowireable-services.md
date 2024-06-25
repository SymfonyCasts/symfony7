# Non-Autowireable Services

In the last chapter, we autowired a *non-autowireable* argument. *This time*, let's try to
autowire an non-autowireable *service*. But before we do that, we need to *find* a non-autowireable service.
To do that, at your terminal, run:

```terminal
bin/console debug:container
```

If you think this Twig service can't be autowired since it's just an ID, *think again*.
If we scroll up, we see `Twig\Environment`. *This* is an alias for our Twig service.
*Conversely*, `twig.command.debug` is *not* autowireable. This is the service that
powers the `debug:twig` command we used in previous chapters. When we run that in our terminal,

```terminal-silent
bin/console debug:twig
```

it gives us a list of all of the Twig filters and functions available in our
app. That means, even though it's kind of odd, we *can* grab this service and
use it directly. Good to know!

Back over here, in `homepage()`, typehint `DebugCommand` (the one from Twig) and
let's call this `$twigDebugCommand`.

[[[ code('50bf0e913a') ]]]

If we head back to our browser and refresh... we get an *error*:

> Cannot autowire argument `$twigDebugCommand` of
> `App\Controller\MainController::homepage()`

If you guessed that we'll need to use the attribute above the argument like we
did with our parameters, you're *correct*, but the syntax for services looks a
little different. Over here, above our `DebugCommand`, add a new
attribute - `#[Autowire()]`. Inside, we'll set `service` to the service name.
I'll *cheat* and copy the exact service name from the list in our terminal.

[[[ code('492a7fd416') ]]]

Okay, if we head back and refresh the homepage again... it was successfully
autowired. Nice!

All right, let's see if we can run that command. Below `Response`,
write `$twigDebugCommand->run()`. The first argument should be an input, so we
can say `new ArrayInput`. The *second* argument should be the output that we'll
use below, but before we do that, we need to create an output variable. Above,
write `$output = new BufferedOutput()`. Now we can add `$output` as our second
argument here. Okay, our editor is happy, so finally, below,
let's `dd($output)`. If we head to our browser and refresh... dang... *error*.
It looks like we need to pass an empty array to the `ArrayInput()` class. If we
do that and refresh again... boom! We get a list of functions and filters. It
*works*. This was just an example, so we can get rid of that code, but the key
thing to remember is that, even if something isn't autowireable by default, you
can *make* it autowireable with an `#[Autowire]` attribute, regardless of
whether you need a service or a parameter.

Next: Let's talk about environment variables and the purpose of the `.env` file
we saw earlier. We'll also see how we can leverage them in our app so it behaves
differently in certain environments.
