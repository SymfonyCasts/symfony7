# Security Extra

## Preconditions
- Based on the finished code from "Symfony 7 Security": https://symfonycasts.com/screencast/symfony-security
- Already upgraded to Symfony 8
- Foundry fixtures: `picard@enterprise.space` (`ROLE_CAPTAIN`, owns a ship), `janeway@starfleet.space` (`ROLE_ADMIN`)

## IsGranted 404
- Problem: `/starship/{id}/edit` on someone else's ship returns 403, which tells an attacker the ship exists
- Add `statusCode: 404` to `#[IsGranted('edit', subject: 'starship')]` in `StarshipAdminController`
- Mention the sibling options: `message` and `exceptionCode`

## Customizing Authentication Error Messages
- Where the message comes from: `error.messageKey|trans(error.messageData, 'security')` in `login.html.twig`
- Create `translations/security.en.yaml` and override `Invalid credentials.` with our own wording
- Show `security.expose_security_errors` (default `none`) to surface the *real* reason - bad password vs unknown user vs account status - and why that's a dev-only / account-status-only setting
- Mention `CustomUserMessageAuthenticationException` for when we throw our own errors (user checkers, custom authenticators)

## Super Admin Voter
- Motivate it: `StarshipVoter` starts with a "is this user an admin?" escape hatch - every new voter has to repeat that, and `role_hierarchy` can't say "can do literally everything"
- Add `ROLE_SUPER_ADMIN` to a fixture user in `AppStory` (new user, or promote Janeway)
- `make:voter` -> `src/Security/Voter/SuperAdminVoter.php`: `supports()` returns true for *every* attribute and subject
- Grant by reading the roles off the token (`$token->getRoleNames()`, or `RoleHierarchyInterface::getReachableRoleNames()` so `role_hierarchy` still applies)
- Big gotcha: do *not* call `isGranted('ROLE_SUPER_ADMIN')`/`AccessDecisionManager` inside the voter - it supports every attribute, so it would vote on itself forever
- Skip the `IS_AUTHENTICATED_*`, `IS_IMPERSONATOR` and `PUBLIC_ACCESS` attributes - a super admin shouldn't be able to fake full authentication (this matters for the next chapter)
- Add `$vote?->addReason('...')` and show the decision in the profiler's security panel
- Delete the `ROLE_ADMIN` shortcut from `StarshipVoter` and let `role_hierarchy` give `ROLE_SUPER_ADMIN` -> `ROLE_ADMIN`

## Sudo Mode: Requiring Full Authentication
- Log in with "remember me" checked, delete the `PHPSESSID` cookie, refresh: still logged in via the `REMEMBERME` cookie
- Add `#[IsGranted('IS_AUTHENTICATED_FULLY')]` to something sensitive - `UserAdminController::edit()` (or the whole class)
- Refresh as the remembered user: bounced to the login form, log in, land back on the page
- Compare `IS_AUTHENTICATED_FULLY` vs `IS_AUTHENTICATED_REMEMBERED` vs `IS_AUTHENTICATED`
- Mention the `access_control` version for locking down a whole URL section

## Disabled Users: a Custom `UserChecker`
- Add an `isActive` (or `disabled`) boolean to `User` with `make:entity`, make the migration, default it in `UserFactory`
- Add a checkbox to `UserType` so an admin can deactivate someone from `/admin/user/{id}/edit`
- Create `src/Security/UserChecker` implementing `UserCheckerInterface` and wire it with `user_checker: App\Security\UserChecker` on the `main` firewall
- Throw `CustomUserMessageAccountStatusException` from `checkPreAuth()` - explain `checkPreAuth()` (before the password is verified) vs `checkPostAuth()` (after)
- Important tie-in with the previous chapter: `expose_security_errors: none` normally masks `AccountStatusException` as "Invalid credentials", but a `CustomUserMessageAccountStatusException` always gets through - which is why we throw that one
- Show the gap: the checker only runs while *authenticating*, so a user who is already logged in stays logged in
- Fix it by making `User implement EquatableInterface` and returning `false` from `isEqualTo()` when deactivated - `ContextListener` compares the session user to the fresh one on every request and deauthenticates
- Deactivate Picard while he's logged in, refresh... and he's out
- Mention the built-in `DisabledException`/`LockedException` and that `switch_user` runs `checkPostAuth()` too, so you can't impersonate a disabled user

## Redirecting After Login with `_target_path`
- Today, login always dumps you on the homepage
- Show what already works: hit a protected URL, get sent to login, and Symfony returns you there (the target path is stashed in the session)
- Add a hidden `_target_path` input to the login form so a "Login" link from any page comes back to that page
- Config options to mention: `default_target_path`, `always_use_default_target_path`, `use_referer`
- Ties back to sudo mode: re-authenticating returns you to the page you were on

## Login with Username or Email
- Add a unique `username` field to `User` with `make:entity`, make the migration, fill it in `UserFactory` + `AppStory`
- Make `UserRepository` implement `UserLoaderInterface` with a `loadUserByIdentifier()` query matching email *or* username
- Drop `property: email` from the `app_user_provider` config so Symfony uses the loader instead
- Update the login form: `type="text"`, label "Email or Username"
- Note that `getUserIdentifier()` is what ends up in the session and in `app.user.userIdentifier`

## Custom Impersonation Voter
- Problem: anyone with `ROLE_ALLOWED_TO_SWITCH` can impersonate *anyone* - including an admin, or themselves
- `SwitchUserListener` decides on `ROLE_ALLOWED_TO_SWITCH` with the *target user* as the subject - so a voter can see who we're switching to
- New voter: deny switching to yourself, deny switching to anyone who has `ROLE_ADMIN`/`ROLE_SUPER_ADMIN` (check the target's reachable roles)
- Use the same check to hide the "switch to" link in `user_admin/index.html.twig`: `is_granted('ROLE_ALLOWED_TO_SWITCH', user)`
- Point out that `SuperAdminVoter` deliberately overrides this - a super admin can still impersonate anyone

## `NotCompromisedPassword` Validator
- Add `new NotCompromisedPassword()` to `plainPassword` in `RegistrationFormType`
- Register with `password123` and watch it get rejected
- How it works: k-anonymity against the haveibeenpwned API - only the first 5 chars of the SHA-1 hash are sent - and it needs `symfony/http-client`
- Turn it off in the test env with `framework.validation.not_compromised_password.enabled: false`; mention `skipOnError`
- Good spot to also mention `PasswordStrength`

## `#[RateLimit]` Registration (8.1)
- Requires Symfony 8.1 - park this chapter until it's released
- Login is throttled (`login_throttling`) but registration is wide open
- Define a limiter under `framework.rate_limiter` and add `#[RateLimit]` to `RegistrationController::register()`
- Default bucket key is client IP + method + path; show `methods`, an expression-based key, and stacking multiple attributes
- Show the automatic 429 + `Retry-After` response

## `logout_form()` Helper (8.2)
- Requires Symfony 8.2 - park until released
- Our layout hand-rolls the POST logout form plus the CSRF hidden input
- Replace it all with `{{ logout_form() }}`
- Also mention the new `target: null` logout option (no redirect)

## Hardening Impersonation (8.2)
- Requires Symfony 8.2 - park until released
- Problem: impersonation is a `?_switch_user=` GET link, so it's CSRF-able
- New `switch_user` options: `path`, `enable_csrf`, `csrf_token_id`, `csrf_parameter` - a dedicated POST-only route
- Swap `impersonation_path()`/`impersonation_exit_path()` for the new `impersonation_form()` / `impersonation_exit_form()` Twig functions

## Advanced bonus topics
- `debug:roles` command + the role hierarchy graph in the profiler (8.2)
- Reset password w/ `symfonycasts/reset-password-bundle`

