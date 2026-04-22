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
