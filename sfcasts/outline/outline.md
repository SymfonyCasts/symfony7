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
