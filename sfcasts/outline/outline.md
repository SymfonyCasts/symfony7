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

## Hardening Impersonation with POST & CSRF
- Login as `janeway@starfleet.space`, `coffeeblack`, visit `/admin/user`
- We can impersonate users and we have a voter to prevent impersonating ourself
- Hover over: `?_switch_user=`, problems:
    - GET, email in the url, no CSRF protection
- We can make it POST and add CSRF protection in 8.2!
- First we need a route. In `SecurityController` we added a stub just to use `Route`
- `config/routes/security.yaml`
    - `app_impersonate_user:
        path: /impersonate
        methods: [POST]`
    - No need for a controller, the impersonation listener will handle
- `config/packages/security.yaml`, under `switch_user` add:
    - `path: app_impersonate_user
     enable_csrf: true`
- `templates/user_admin/index.html.twig`, inside the `is_granted`
    - `{{ impersonation_button(
        'switch to',
        user.userIdentifier,
        attr: {class: 'bg-indigo-500 hover:bg-indigo-600 rounded p-1.5 whitespace-nowrap'}
    ) }}`
- `templates/base.html.twig` - find the exit button
    - `{{ impersonation_exit_button(
          'Exit Impersonation',
          path('app_user_admin_index'),
          attr: {class: 'hover:text-amber-400 pt-2'}
      ) }}`
- Back to user listing page...
- Inspect one of the buttons... Builds the whole form!
- Click... on homepage and impersonating!
- Exit... and we're back!

## Improved Sudo Mode (8.2)

- https://symfony.com/blog/new-in-symfony-8-2-sudo-mode
- Our sudo mode was a bit of a misnomer
- Real sudo mode is based on time since last pw entry
- First, in our `SuperAdminVoter`, we need to exclude the new attributes
  - `AuthenticatedVoter::IS_AUTHENTICATED_RECENTLY,`
  - `AuthenticatedVoter::IS_AUTHENTICATED_VERY_RECENTLY,`
  - We also have a bug in supports()
    - set `mixed $attribute`
    - `return is_string($attribute) && ...`
- in `login.html.twig`, we need to adjust our remember me check for already authenticated users
  - swap `if app.user` for `if is_granted('IS_REMEMBERED')`
- In `security.yaml` under `access_control`
  - `is_granted('IS_AUTHENTICATED_VERY_RECENTLY')` for the sudo mode route
- Keep in mind this one could be annoying for a route like this!
- On site, login as `janeway@starfleet.space`, `coffeeblack`
- Visit `/admin/user`, all good
- Wait around 5 mins...
- While we wait, in the terminal:
  - `symfony console config:dump security` and scroll up to find the attribute config
- Refresh... We are redirected to `/login` and we see the remember me option
- `coffeeblack`
- Back on user admin
- Next, simplify your rate limiter creation

## RateLimiterBuilder (8.2)

- Our registration limiter is a one-off, but it's managed in two places
- The limit lives in `rate_limiter.yaml`, the usage in the controller, wired up with `#[Target('registration')]`
- To tweak it, you have to bounce between the two
- 8.2 adds `RateLimiterBuilder`
- In `RegistrationController`
  - inject `RateLimiterBuilder $limiterBuilder` instead
  - `$limiterBuilder->` (see the methods)
  - `->slidingWindow()` click into, needs $id, $limit, $interval
  - $id: 'registration', $limit: 5, $interval: '10 minutes'
  - `$limit = $limiterBuilder
         ->slidingWindow('registration', 1, '10 minutes')
         ->create($request->getClientIp())
         ->consume();`
- Since rate_limiter.yaml, is just a single limiter, we can delete
- register worf, then natasha, rate limited
