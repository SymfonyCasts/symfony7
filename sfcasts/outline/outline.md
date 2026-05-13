# Upgrading to Symfony 8

## PHP 8.4 & Recipe Updates
- Our PHP version is 8.4 (this is what SF8 requires)
- `composer.json`
  - php -> ">=8.4"
  - config.platform -> "8.4"
  - in replace, add polyfill-php84 & php83
- `symfony composer update`
- open app
  - check Symfony/PHP version
  - check deprecations
- fix `Starship::addDroid()` & refresh (might need to clear cache)
- `git commit -a -m "composer update"`
- symfony composer recipe:update
  - review and commit changes
- Check deprecations again
  - We'll ignore doctrine ones for now
- Fix foundry deprecations

## Upgrade to Symfony 7.4
- In `composer.json`
  - Symfony packages use the * notation, this makes it easy to find...replace
  - edit...find...replace 7.3.* with 7.4.*
  - explain extra.symfony.require
    - Prevents updating some packages to 8.x
- `symfony composer update`
- Check app
- Looks good, same deprecations
- Take a look at `config/reference.php` (https://symfony.com/blog/new-in-symfony-7-4-better-php-configuration)
- Can add to gitignore or commit
- `git add config/reference.php`
- `git commit -a -m "Upgrade to Symfony 7.4"`
- `symfony composer recipe:update`
  - framework-bundle
    - .env: concept of a share directory (https://symfony.com/blog/new-in-symfony-7-4-share-directory)
    - config/services.yaml: updated comments (yaml-language-server - https://symfony.com/blog/new-in-symfony-7-4-deprecated-xml-configuration#better-yaml-autocompletion)
    - `git commit -a -m "update recipes"`
  - routing
    - config/routes.yaml: simplified config
    - `git commit -a --amend`
- App works!

## Upgrading Doctrine & Native Lazy Objects

- Refresher on lazy objects
  - Open `StarshipPart` - Starship is many to one
  - `symfony console make:controller`: Lazy
  - Open and inject `StarshipPartRepository $repository`
  - `$part = $repository->find(1); dump($part)`
  - visit `/lazy`, 1 db query, check dump and proxy
  - `dump($part->getStarship()->getName(), $part)`, 2 db queries, check dump and proxy
  - PHP 8.4 supports native lazy objects: https://www.php.net/manual/en/language.oop5.lazy-objects.php
  - Upgrading to DoctrineBundle 3 will enable this!
- In `composer.json`, `doctrine-bundle` to `^3.0`
- `symfony composer update`
  - error, need dbal 4+
    - Previously, the DoctrineBundle didn't support dbal 4, so we locked at 3
    - remove this requirement entirely
- `symfony composer update`
  - Update happened but we got an error
  - we could fix manually, but I believe the recipe will contain this change
- `git status`
- `git add .`
- `git commit -m "upgrade doctrine-bundle"`
- `symfony composer recipe:update`
  - choose doctrine bundle
- Success!
- `git status` and check changes in `doctrine.yaml`
- refresh `/lazy` still works
- in LazyController, dump($part) so we can see the lazy object
- We can now make all our entities final!
- Pages still work

## Tracking & Fixing Deprecations

- 3 main ways to find deprecations:
  - The profiler... have to check every page and request...
  - Tests... might not have tests for everything - we have none...
  - Logging! Run in prod for a while and check logs for deprecations
- No deprecations on homepage, click a starship
- A Doctrine deprecation...
  - show context
  - show trace
  - find and check url
- Check `monolog.yaml`
  - Explain the `deprecation` channel under prod
  - Copy to dev so we can see how it looks
  - Copy path from above and suffix with deprecations and json extension
- Refresh the part page
  - check the log (format the file)
  - see the deprecation, but no context
  - clear out file
- in `monolog.yaml`, add `include_stacktraces: true` to the deprecation handler
- Refresh again
  - Check the log file again - we now see the stack trace!
  - clear file
- More context: processors (https://symfony.com/doc/current/logging/processors.html)
  - in `services.yaml`, add `monolog.processor.web` with class WebProcessor
- Refresh again
  - check file now, stack trace and request details!
  - Find the culprit...
- `StarshipRepository::23`
  - Check the `Criteria::create()` method
  - Sure enough, there's a new parameter
  - Use `true` to opt-in
- Refresh again, no more deprecation!

## Upgrade to Symfony 8.0!

- `symfony composer -D`
- In `composer.json`
    - Find and replace `7.4.*` with `8.0.*`
- `symfony composer update`
    - tailwind-bundle doesn't support Symfony 8
- check packagist.org for tailwind-bundle
    - need to change `composer.json` to `0.12.0`
- `symfony composer update`
    - monolog bundle, check packagist
    - change to `^4.0`
- `symfony composer update`
    - worked!
    - `symfony console --version`
- `git status`
- `git commit -a -m "Upgrade to Symfony 8.0"`
- `symfony composer recipe:update`
    - no recipes to update!
- Check app, everything works!
