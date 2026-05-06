# Upgrading to Symfony 7.4

Alright, let's dive right into upgrading to Symfony 7.4. Open your
`composer.json` file. Notice how the Symfony packages use a `7.3.*`
format. This is slightly different from the rest of our packages, which
typically use the `^` prefix format. This makes it super easy to find and update
the `symfony` packages.

If you're using PhpStorm, go to edit... find... replace. Search for
`7.3.*` and replace with `7.4.*`. We can see we have 19 occurences to
replace. Hit replace all... and... boom!

[[[ code('d2578f21c8') ]]]

Let's just do a double check to make sure we didn't miss any. Under `require`...
yep, looks good. Now down to `require-dev`... 

[[[ code('3f1507918a') ]]]

Yep, looks good there too. The
`maker-bundle` has a different versioning strategy than the Symfony
core components and bundles. That's why it looks different.

Notice under the `extra` section, we have this `symfony` `require` config.

[[[ code('fc6afcd1be') ]]]

This tells
Symfony Flex which version of Symfony to use when installing Symfony components.
Some of our required Symfony components require other Symfony components as dependencies.
These are called *transitive* dependencies (fancy word!), and can allow a
wide range of Symfony versions. Like Symfony 6, 7, or 8. This config ensures
that only the `7.4` versions are installed. So when upgrading Symfony, it's
important to also update this config.

Great! Now that we've updated our `composer.json`, let's run our composer
update:

```terminal
symfony composer update
```

## Verifying the Upgrade

Perfect, it looks like it worked! Let's hop over to our app and refresh.
Yep, we're now on 7.4.8, the latest 7.4 version. 

Back in our terminal, run:

```terminal
git status
```

Our `composer.json` and `composer.lock` files are modified, this is expected.
But we also have this new `config/reference.php`.

Open that up in your editor. This is an auto-generated file Symfony creates when
building the container. Symfony now has a PHP array-based config format - an
alternative to YAML. This file is generated to provide better auto-completion when
using that format. YAML is still the recommended format, and what we're using in this app,
so this file isn't important for us right now. If Symfony changes it's recommendation
in the future, we'll be ready! Check out
[this blog post](https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration)
to learn more about it.

You can either add this file to your `.gitignore` or commit it. The best practice right now
is to commit it, so let's do that. In your terminal, run:

```terminal
git add config/reference.php
```

After running `git status` to confirm we're good, we can commit with:

```terminal
git commit -a -m "update composer"
```

(Yeah, we should really use a better commit message here)

## Updating Recipes

Let's see if there are any recipe updates for 7.4! Run:

```terminal
symfony composer recipe:update
```

Just two, start with the `framework-bundle`. Run `git status` to see the changes.
`.env` and `config/services.yaml` have been modified.

Open `.env` first. A new environment variable was added: `APP_SHARE_DIR`.
When running Symfony in a multiserver architecture, this is a directory that
should be shared between the servers. Previously, you had to share the
entire cache directory, which isn't idea. This new config allows you to have
more fined-grained control over what is shared between servers. If you're interested in
learning more about this, check out [this blog post](https://symfony.com/blog/new-in-symfony-7-4-share-directory).

Open our second modified file, `config/services.yaml`. Just the comments at the top were
modified. But it does provide a cool new feature! See this `yaml-language-server $schema` stuff?
This configures a JSON schema for this file. Wait, JSON? This is YAML. Since YAML is JSON-compatible,
we can use JSON schemas to validate our YAML files. This is cool, but the better part, is that it
gives us auto-completion in our IDEs if supported. And PhpStorm does support this!
Here's [a blog post](https://symfony.com/blog/new-in-symfony-7-4-deprecated-xml-configuration#better-yaml-autocompletion)
if you want to learn more about it!

Ok, let's commit these changes at our terminal with:

```terminal
git commit -a -m "update recipes"
```

Run the recipe update command again, and update the `routing` package. Run `git status` to see
the changes. `config/routes.yaml`: open that up. At the top, a YAML JSON schema config was added.

Down below, check out the new simplified config. With the previous config, only controllers
in `src/Controller` would be loaded. With this new config, any class with `#[Route]` attributes
in it will be loaded as controllers. It's still the best practice to put all your controllers
in `src/Controller`. But if you have a more complex app with multiple domains and their own controllers,
these would be loaded no matter where they're located.

Let's commit these changes with:

```terminal
git commit -a --amend
```

## Testing the Upgrade

Perfect! Pop over to our app and refresh the homepage to make
sure everything's still running smoothly. Nice, 7.4 upgrade was a success!

Next, we're going to update Doctrine and explore a cool improvement in the
latest version of the ORM. Stay tuned!
