# Security Extra

## IsGranted 404

- continuation of security basics
  - can use the code or download
- Starshop tour
  - register
  - login (`src/Story/AppStory.php` has a few users already)
  - login as picard
  - visit `/starship`
- Edit our own starship - ok
- Edit someone else's starship - 403
- Let's make it a 404 - this is what sites like GitHub do
- `StarshipAdminController::edit()`
  - add `statusCode: 404` to `#[IsGranted]`
- Refresh - similar message, but 404
- Logout
- `/starship/1/edit` - 404 also, no redirect to login
- Add to `StarshipAdminController::delete()` too
- Note there are sibling options to IsGranted: `message` and `exceptionCode`

## Disabled Users: a Custom `UserChecker`

- Concept of "disabled users" - kind of a soft delete
- `symfony console make:entity`
    - `User`
    - `disabledAt` - could use bool but this is "auditable"
    - `datetime_immutable`, nullable
- `src/Entity/User`
    - `null` means "enabled"
    - add method: `public function isActive(): bool { return null === $this->disabledAt; }`
- `symfony console make:migration`
    - `add user.disabledAt column`
- `UserFactory` - add foundry "state"
    - `public function disabled(): self { return $this->with(['disabledAt' => new \DateTimeImmutable()]); }`
- in `AppStory`, for picard
    - `UserFactory::new()->disabled()->create()`
- `symfony console foundry:load-fixtures`
- Create a custom user checker `UserChecker` in `src/Security
    - `final class UserChecker implements UserCheckerInterface`
    - implement both methods
    - `checkPostAuth()` - after credentials have been verified
    - `checkPreAuth()` - before credentials have been verified
    - in `checkPreAuth()`
        - `if (!$user instanceof User) { return; }`
        - `if (!$user->isActive()) { throw new DisabledException(); }`
- Dig into `DisabledException` - extends `AccountStatusException`
    - Look at different children
- `config/packages/security.yaml`
    - `firewalls.main.user_checker: App\Security\UserChecker`
- Try to login as picard - "Invalid credentials."

## Forcing Logout with EquatableInterface

- In `AppStory`, create picard as active again
- `symfony console foundry:load-fixtures`
- Login as `picard` - ok
- `symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"`
- Refresh - still logged in...
- We need something on the user to change to force a logout
- In `User::getRoles()`
    - `if (!$this->isActive()) { $roles[] = 'ROLE_DISABLED'; }`
- Refresh - now logged out
- Try and login... can't
- Let's check remember me
- `symfony console foundry:load-fixtures` - picard is active again
- Login as picard with remember me, delete session cookie, refresh
- `symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"`
- Refresh... logged out

## Customizing Authentication Error Messages

- visit `/login`
    - picard@enterprise.space, password: `enterprise` - "Invalid credentials."
    - picard@voyager.space, password: `makeitso` - "Invalid credentials."
- `symfony console dbal:run-sql "UPDATE user SET disabled_at = datetime('now') WHERE email = 'picard@enterprise.space'"`
- picard@enterprise.space, `makeitso` - "Invalid credentials."
- in `security.yml`
    - `expose_security_errors: all`
- picard@enterprise.space, `makeitso` - "Account is disabled."
- `symfony console foundry:load-fixtures` - picard is active again
- picard@enterprise.space, `enterprise` - "Invalid credentials."
- picard@voyager.space, `makeitso` - "Username could not be found." - user enumeration
- `ExposeSecurityLevel`
- in `security.yml`
    - `expose_security_errors: account_status`
- picard@enterprise.space, `enterprise` - "Invalid credentials."
- picard@voyager.space, `makeitso` - "Invalid credentials."
- disable picard again
- picard@enterprise.space, makeitso - "Account is disabled."
- picard@enterprise.space, enterprise - "Account is disabled."
- still a small enumeration
- open `src/Security/UserChecker.php`
    - move the check to `checkPostAuth()
- picard@enterprise.space, enterprise - "Invalid credentials." - perfect!
- Now to customize the message
- `templates/security/login.html.twig` - error message is being translated
- Copy "Invalid credentials." and search the vendor dir
    - used in exception and in `security.<locale>.xlf`
- Create `translations/security.en.yaml`
    - `Invalid credentials.`: 'Your email or password is incorrect.'
- login with bad password

# Sudo Mode: Requiring Full Authentication

- When performing sensitive operations, confirm the users password
- `AuthenticatedVoter::IS_AUTHENTICATED_FULLY` - user has provided their password
- Can use with any IsGranted call
- Let's use access_control
- `config/packages/security.yaml`
    - `access_control: { path: ^/admin, roles: [ROLE_ADMIN, IS_AUTHENTICATED_FULLY] }`?
    - No, this is an OR, not an AND
    - `allow_if: "is_granted('ROLE_ADMIN') and is_granted('IS_AUTHENTICATED_FULLY')"`
- visit app and refresh... error
- `composer require symfony/expression-language`
- refresh... login as `janeway@starfleet.space`, `coffeeblack`
- visit `/admin/user`
- delete session cookie, refresh... redirected to login
- `janeway@starfleet.space`, `coffeeblack`
- All good, but let's make it a little more user friendly
- visit `/admin/user`, delete cookie and refresh
- we don't need to refill email, just the password
- `templates/security/login.html.twig`
    - adjust header if app.user - "Confirm your password"
    - Delete the already logged in message
    - swap email div: `<input type="hidden" name="_username" value="{{ app.user.userIdentifier }}">`
    - swap remember me div: `<input type="hidden" name="_remember_me" value="1">`
    - `{{ app.user ? 'Confirm' : 'Sign in' }}`
- refresh... `coffeeblack`, confirm... all good!
- logout and login again as janeway

## Super Admin Voter

- `StarshipVoter` opens with an admin escape hatch - every voter repeats it
- `config/packages/security.yaml` - role hierarchy can only go so far...
- Let's make a level above admin: super admin
- `src/Story/AppStory.php`
    - change janeway from `ROLE_ADMIN` to `ROLE_SUPER_ADMIN`
- `symfony console foundry:load-fixtures`
- `symfony console make:voter SuperAdminVoter`
- `src/Security/SuperAdminVoter.php`
    - clear out everything...
    - `supports()` - `return true`
    - `voteOnAttribute()` `return in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true);`
- Small problem...
- Some built-in attributes aren't for permission, but authentication status
- `AuthenticatedVoter` constants...
- `private const EXCLUSIONS = []` - add all the consts
- in `supports()` - return `!in_array($attribute, self::EXCLUSIONS, true)`
- login as janeway, visit `/starship`
- We can edit any starship still, edit one...
- Check the profiler - access decision tab

## Redirecting After Login with `_target_path`

- when logged out and visiting `/admin/user`
    - redirected to `/login`, then back to `/admin/user`
- when logged out and on `/starship`
    - click "Login" - redirected to homepage...
- `symfony console config:dump security firewalls`
    - find `form_login`
        - `default_target_path: /`
        - `use_referer: true
- try again... didn't work... it get's muddled in the redirect
- let's be explicit `target_path_parameter: _target_path`
- `templates/base.html.twig`
    - `{{ path('app_login', { _target_path: app.request.pathInfo }) }}`
- try again... redirected back!
- Query param feels a bit "internal"
- `target_path_parameter: referrer`
- `templates/base.html.twig`
    - `{{ path('app_login', { referrer: app.request.pathInfo }) }}`
- try again... still redirected back!
- Small SEO thing:
    - `templates/base.html.twig`: add `metadata` block
    - `templates/security/login.html.twig`
        - override block
        - add `<link rel="canonical" href="{{ url('app_login') }}">`

## Login with Username or Email

- `symfony console make:entity User` - username
- `User`
    - duplicate `#[UniqueEntity]` and `#[ORM\UniqueConstraint]` for username
    - `#[Assert\NotBlank]` to `$username`
- `symfony console make:migration`
- (migration) - `add user.username column`
- `UserFactory` - `'username' => self::faker()->unique()->userName(),
- `AppStory` - add username for picard and janeway
- `symfony console foundry:load-fixtures`
- `RegistrationFormType` - add username field
- `login.html.twig`
    - "Use your email/username..."
    - label: "Email or Username"
    - `type="text"` for username/email
- `UserRepository` implements `UserLoaderInterface`
    - implement method
    - `return $this->createQueryBuilder('u')
            ->where('u.email = :identifier')
            ->orWhere('u.username = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;`
- `security.yaml` - remove `property: email` from `app_user_provider`
- try it out: login with username, then email - both work!

## Custom Impersonation Voter

- `/admin/user` - admins can impersonate any user
    - rules: can't impersonate yourself! can't impersonate other admins
- `security.yaml` - the default role is `ROLE_ALLOWED_TO_SWITCH` and we gave to admins
- Symfony does an isGranted check for the configured role
    - passes the user to be impersonated as the "subject"
    - Ignored by default, but we can add a voter to customize
- First thing: we need a custom role without the ROLE_ prefix
    - This will prevent the default "role voter" from being called
- `security.yaml` - remove `ROLE_ALLOWED_TO_SWITCH` from the role hierarchy
- `switch_user.role: CAN_IMPERSONATE`
- `user_admin/index.html.twig`, wrap the switch user link in an `is_granted('CAN_IMPERSONATE', user)` check
- `SuperAdminVoter` - add `CAN_IMPERSONATE` to the exclusions
- refresh page - links gone
- `symfony console make:voter ImpersonationVoter`
    - supports() - `return 'CAN_IMPERSONATE' === $attribute && $subject instanceof User;`
    - voteOnAttribute()
        - `@param User $subject`
        - `if (!$currentUser = $token->getUser()) { return false; }`
        - `if ($currentUser->getUserIdentifier() === $subject->getUserIdentifier()) { return false; }`
        - `if (in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true)) { return true; }`
        - inject Security service
        - `return !$this->security->isGrantedForUser($subject, 'ROLE_ADMIN');`
- refresh page - links back just for picard
- AppStory - add some users
    - `UserFactory::createMany(5, ['roles' => ['ROLE_ADMIN']]);`
    - `UserFactory::createMany(5, ['roles' => ['ROLE_USER']]);`
- `symfony console foundry:load-fixtures`
- refresh page - log back in janeway, can switch to everyone but myself
- These buttons are driving me nuts
    - `user_admin/index.html.twig`
        - add `flex-start` to the button container
        - add `whitespace-nowrap` to the switch to button
- copy an admin's email, look at UserFactory - we default the password to `engage`
- login as the admin...
- `/admin/user` - no links to switch to other admins, but can switch to users
