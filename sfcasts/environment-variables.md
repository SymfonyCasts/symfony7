# Environment Variables

Environment variables are for values that differ depending on the environment
we're developing in, like locally vs production. The most common example of this
is the database connection details. We can set *real* environment variables in
our operating system, and while many cloud hosting platforms make it super easy
to set these variables, it's not the easiest thing to do locally. Symfony also
has this `.env` file which helps make life easy, *especially* during
development.

Okay, here's the plan: We want our `iss_location_cache_ttl` value to be
different *locally* versus on production. In our `prod` environment, we want our
cache to last *longer* than the 5 seconds that we have now. 

[[[ code('a07fd72e53') ]]]

The easiest way to do this would be to create a *custom* environment variable and
set it to a different value for each environment - `dev` and `prod`.

## Creating and Reading Environment Variables

In our `.env` file, down here, write `ISS_LOCATION_CACHE_TTL` in all uppercase
letters, which is standard for environment variables. Let's set this to `5` by
default. 

[[[ code('5e092fbd05') ]]]

Now, over in `services.yaml`, we're going to keep the `iss_location_cache_ttl` parameter,
but instead of `5`, let's set it to the environment variable we just created. To do that,
we need to leverage a special syntax. Write `'%env()'` and select our
new `ISS_LOCATION_CACHE_TTL` environment variable. Nice!

[[[ code('f60f607874') ]]]

To debug this, in `/src/Controller/MainController.php`, find `homepage()`.
Inside *that*, below `Response`, let's write `dd($this->getParameter())` and
add `iss_location_cache_ttl`. 

[[[ code('85bafcb921') ]]]

## Environment Variable Processors

Back at the browser, refresh. There's `5`. It's subtle, but you may have noticed
that this value is a string right now. All environment variable values are just
simple strings by default, but Symfony has a way to *typecast* them to a *different* type.
They're called "environment variable processors", and one of them can help us typecast
this to an integer instead.

Back in our code, open `services.yaml`. Before the environment variable,
add `int:`. 

[[[ code('dc10111d2f') ]]]

If we go refresh... now we have a real integer `5`. If we were
deploying this project to production, we'd probably want to set
this `ISS_LOCATION_CACHE_TTL` variable to something a little longer, like `60`,
so it will cache the data for *1 minute* instead of 5 seconds. The shorter time
frame is just more practical while we're testing things out.

## The `.env.local` File

While we're here, I want to talk about some *other* `.env` files. *This* `.env`
file is committed to your Git repository, and as you see here, when I make
changes to it, those changes are unstaged. So if you have some *secrets* that
you don't want to commit to your Git repository, like sensitive tokens,
passwords, etc, you can create a *different* file - `.env.local`. This one is
*ignored* by Git, which we can see in our `.gitignore` file. Any sensitive info
can be stored here and it won't be committed to the repository. We could, for
example, move this `APP_SECRET` environment variable into our `.env.local` file.
Inside our `.env` file, we can keep this empty or set it to some fake value.
It's generally good practice to still keep the environment variables in
the `.env` so that other developers can see them and set real values for them in
*their* `.env.local` file. This was just an example, so we can change this back.

## Debugging Environment Variables

Along with these two files, there's also the less commonly used `.env.test`
and `.env.prod`. Those are only loaded in the `test` and `prod` environments
respectively. We also have a handy command to debug environment variables.
At your terminal, run:

```terminal
bin/console debug:dotenv
```

This can help us understand what order those files will be loaded in and, as a
bonus, it lists all of the environment variables it found in each file. So far,
we only have three and we can see their actual values and in which files those
values are set.

If you're *serious* about securing your sensitive information, Symfony has a
special tool for this called the "Secrets Vault". If you Google "Symfony
secrets", one of the top results is "How to Keep Sensitive Information Secret",
which leads us to some documentation. With the Secrets Vault, we can safely
commit environment variables to our Git repository because they're encrypted and
*can't* be read without *decrypting*. If you need this level of data protection,
I encourage you to read the docs or check out our related videos on
SymfonyCasts. I'll revert the changes we made to our homepage and remove
this `dd()`. We don't need that anymore.

Next: Let's talk more about *auto-configuration*.
