# Security Extra

## Preconditions
- Starts from the finished "Symfony 7 Security" code, upgraded to Symfony 8
- Fixtures: `picard` (`ROLE_CAPTAIN`, owns a ship), `janeway` (`ROLE_ADMIN`)

## IsGranted 404
- A 403 on someone else's ship edit page proves the ship exists
- Add `statusCode: 404` to `#[IsGranted('edit', subject: 'starship')]`
- Mention the `message` and `exceptionCode` options

## Disabled Users: a Custom `UserChecker`
- Disabled users are a kind of soft delete
- Add a nullable `disabledAt` datetime to `User` - auditable, unlike a bool
- Add `isActive()`: true when `disabledAt` is null
- Migrate, add a `disabled()` state to `UserFactory`, use it in `AppStory`
- Create `src/Security/UserChecker` implementing `UserCheckerInterface`
- Wire it with `user_checker:` on the `main` firewall
- Throw `DisabledException` from `checkPreAuth()`
- `checkPreAuth()` = before the password check, `checkPostAuth()` = after
- Note the login page only shows a generic error - a later chapter fixes that
- Mention `LockedException` and the other built-ins
- It also runs on a remember-me login
- But `switch_user` only calls `checkPostAuth()` - check there too
- Cliffhanger: someone already logged in stays logged in

## Forcing Logout on Disabled Users
- Make picard active again, reload the fixtures, log in
- Disable him with `dbal:run-sql`, refresh... still logged in
- Every request, `ContextListener` refreshes the user from the DB
- Then `hasUserChanged()` compares the session user to the fresh one
- It looks at the password hash, the roles and the user identifier
- So something on the user has to change - roles is the easy one
- In `getRoles()`, add `ROLE_DISABLED` when `isActive()` is false
- Refresh - logged out. And he can't log back in either
- Why not `EquatableInterface`: it replaces that comparison wholesale
- `__serialize()` crc32c-hashes the password, so that check is subtle
- Now remember me: reload fixtures, log in with the box checked
- Delete the session cookie, refresh - still in via `REMEMBERME`
- Disable him again, refresh... logged out
- `RememberMeListener` clears the cookie on `TokenDeauthenticatedEvent`

## Customizing Authentication Error Messages
- The message: `error.messageKey|trans(error.messageData, 'security')`
- Override `Invalid credentials.` in `translations/security.en.yaml`
- Now the masking: failures get swapped for a `BadCredentialsException`
- `expose_security_errors` levels: `none`, `account_status`, `all`
- `account_status`: real status message, unknown emails stay generic
- That's the setting most apps want
- Payoff: our `DisabledException` message finally reaches the login page
- But `checkPreAuth()` leaks: probe any email, no password needed
- Move the check to `checkPostAuth()` to close it
- `all` unmasks `UserNotFoundException` too - enumeration, dev only
- Mention `CustomUserMessageAuthenticationException` for custom authenticators

## Super Admin Voter
- `StarshipVoter` opens with an admin escape hatch - every voter repeats it
- And `role_hierarchy` can't express "can do literally everything"
- Give a fixture user `ROLE_SUPER_ADMIN` in `AppStory`
- `make:voter` -> `SuperAdminVoter`, `supports()` returns true for everything
- Grant from `$token->getRoleNames()`
- Or `RoleHierarchyInterface` so `role_hierarchy` still applies
- Gotcha: never call `isGranted()` inside - it would vote on itself forever
- Skip `IS_AUTHENTICATED_*`, `IS_IMPERSONATOR` and `PUBLIC_ACCESS`
- A super admin shouldn't be able to fake full authentication
- Add `$vote?->addReason()` and show it in the profiler
- Drop the `ROLE_ADMIN` shortcut from `StarshipVoter`

## Sudo Mode: Requiring Full Authentication
- Log in with "remember me", delete `PHPSESSID`, refresh: still logged in
- Add `#[IsGranted('IS_AUTHENTICATED_FULLY')]` to `UserAdminController::edit()`
- Refresh: bounced to the login form, log in, land back on the page
- Compare it to `IS_AUTHENTICATED_REMEMBERED` and `IS_AUTHENTICATED`
- Mention the `access_control` version

## Redirecting After Login with `_target_path`
- Login always dumps you on the homepage
- But hit a protected URL first and Symfony returns you there
- The target path is stashed in the session
- Add a hidden `_target_path` input to the login form
- Also: `default_target_path`, `always_use_default_target_path`, `use_referer`
- Ties back to sudo mode

## Login with Username or Email
- Add a unique `username` to `User`, migrate, fill it in the fixtures
- `UserRepository implements UserLoaderInterface`
- `loadUserByIdentifier()` queries email *or* username
- Drop `property: email` from `app_user_provider` so the loader is used
- Login form: `type="text"`, label "Email or Username"
- `getUserIdentifier()` is what lands in the session

## Custom Impersonation Voter
- Anyone with `ROLE_ALLOWED_TO_SWITCH` can impersonate anyone - even an admin
- `SwitchUserListener` votes on that role with the target user as the subject
- So a voter can see who we're switching to
- Deny switching to yourself
- Deny switching to a `ROLE_ADMIN`/`ROLE_SUPER_ADMIN` target
- Hide the "switch to" link with `is_granted('ROLE_ALLOWED_TO_SWITCH', user)`
- `SuperAdminVoter` overrides this on purpose

## `NotCompromisedPassword` Validator
- Add `new NotCompromisedPassword()` to `plainPassword` in registration
- Register with `password123` and watch it fail
- k-anonymity: only the first 5 chars of the SHA-1 hash are sent
- Needs `symfony/http-client`
- Disable it in tests: `framework.validation.not_compromised_password`
- Mention `skipOnError` and `PasswordStrength`

## `#[RateLimit]` Registration (8.1)
- Park until Symfony 8.1 is released
- Login is throttled, registration is wide open
- Define a limiter under `framework.rate_limiter`
- Add `#[RateLimit]` to `RegistrationController::register()`
- Default bucket key: client IP + method + path
- Show `methods`, an expression key and stacking attributes
- Show the automatic 429 + `Retry-After` response

## `logout_form()` Helper (8.2)
- Park until Symfony 8.2 is released
- Our layout hand-rolls the POST logout form and CSRF input
- Replace it with `{{ logout_form() }}`
- Mention the new `target: null` logout option

## Hardening Impersonation (8.2)
- Park until Symfony 8.2 is released
- Impersonation is a `?_switch_user=` GET link, so it's CSRF-able
- New `switch_user` options: `path`, `enable_csrf`, `csrf_token_id`
- A dedicated POST-only route
- Swap in `impersonation_form()` / `impersonation_exit_form()`

## Advanced bonus topics
- `debug:roles` + the role hierarchy graph in the profiler (8.2)
- Reset password w/ `symfonycasts/reset-password-bundle`
