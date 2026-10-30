# Symfony 8.2 Security Upgrades

## Upgrade to 8.2

- `composer.json` replace `8.0.*` with `8.2.*`
- `composer update` - big fail: dev deps
- `minimum-stability` set to `dev` in `composer.json`
- `composer update` - big fail: requires php 8.4.1
- php.net: find latest 8.4
- `config.platform.php` - set to `8.4.26` in `composer.json`
- `composer update` - symfony/ux-turbo
- `composer.json`: require `^3.0` for `symfony/ux-turbo`
- same for stimulus bundle
- `git status`, add `config/schema.json` to `.gitignore`
- `git add .`, `git commit -m "Upgrade to Symfony 8.2"`
- `composer recipe:update` - lots of recipes, I'll leave those updates for now
  Nothing should be broken, just deprecations

## Rendering a Logout Form with logout_button()

- `SecurityController::logout()` 'POST'
- `config/packages/security.yaml` - csrf is enabled
- `templates/base.html.twig` - find the manual logout button
- `{{ logout_button() }}` - click into
    - `{{ logout_button(
        'Logout',
        attr: {class: 'hover:text-amber-400 pt-2 cursor-pointer'},
        formAttr: {class: 'inline'}
    ) }}`
- test login as `picard@enterprise.space`, `makeitso`
- inspect element
- Logout works!
