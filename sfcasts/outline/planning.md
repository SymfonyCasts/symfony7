# Symfony 7 Security

TODO Sync updates with the version in tutorial-planning repo!

## Preconditions:
//- Base on the latest "Symfony 7 Forms: The Basics" tutorial:
https://symfonycasts.com/screencast/symfony-forms
//- But upgrade Symfony to the latest 8
//- And upgrade recipes as well

## Topics
//- Installation: `composer require security` (`symfony/security-bundle`)
//- Explain `config/packages/security.yaml`
//- Create a user entity w/ `make:user` command
//- Use email as the unique "display" name for the user (user identifier)
//- Show `User implements UserInterface, PasswordAuthenticatedUserInterface`
//- Explain use loading w/ *User Provider* - we will use entity user provider, but there are more! (Memory, LDAP, Chain)
//- Modify User class w/ `make:entity` command adding `firstName`
//- Make migration w/ `make:migration` command and migrate
//- Create a user in Foundry fixtures and load fixtures
//- Mention `bin/console security:hash-password` command for manual password hashing
//- Hashing the password in the fixtures (Foundry factories as services to require deps)
//- Mention "Reduce Password Encoder Work Factor" feature: https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#reduce-password-encoder-work-factor
//- We will use traditional login form authentication, but there're more! (HTTP Basic, JSON login, Login link, etc.)
//- Create a login form w/ `make:security:form-login` command
//- Make sure CSRF protection is enabled for our login form
//- Explain *Firewalls*: `main` vs `dev`
//- Explain *Authenticators* (mention we can create custom ones)
//- But use default `form_login` (`FormLoginAuthenticator`) (via configuration)
//- Successful authentication flow
//- Show related info in the WDT
//- Unsuccessful authentication flow
//- Show "last username" feature on the failed auth
//- Translate authentication errors messages
//- Explain Session
//- Show PHPSESSID cookie in Application tab of the Chrome dev tools
//- Explain automatic user refresh on each request
//- Show how changing user in the DB lead to user is logged out automatically
//- Show `hide_user_not_found: false` config option? (was deprecated in favor of `expose_security_errors`)
//- Adding logout button
//- Enable remember me feature
//- Show REMEMBERME cookie in Application tab of the Chrome dev tools
//- Watch the Remember Me cookie authenticate the user (by deleting PHPSESSID)
//- Mention `always_remember_me: true` config option
//- Opt into the Remember Me cookie in the login form
//- Explain `access_control` config option
//- Mention only 1 `access_control` match per request!
//- Explain roles
//- Explain role hierarchy
//- Restrict access to admin with specific ROLE_ADMIN
//- Deny access from the controller w/ `createAccessDeniedException()`
//- Deny access to an action w/ `IsGranted` PHP attr
//- Deny access to the entire controller class w/ `IsGranted` PHP attr
//- Check for access w/ `isGranted()` function in controller or Twig template
//- Check for `PUBLIC_ACCESS`/`IS_REMEMBERED`/`IS_AUTHENTICATED_FULLY`
//- Force remembered users to authenticate fully, e.g. for profile edit page (so-called "sudo mode")
//- Fetch the current user object in the controller w/ `CurrentUser` PHP attr
//- Fetch the current user object in a service w/ `Security` service
//- Fetch the current user object in a Twig template w/ `app.user`
//- Switch user w/ impersonation
//- Tweak styles to warn ourselves we're impersonating the user
//- Create a registration form w/ `make:registration-form` command
//- Add `firstName` field
//- Add `plainPassword` unmapped field
//- Unmapped field persisted to DB like `agreeTerms`
//- The `UniqueEntity` validation constraint
//- Login the user programmatically w/ `Security` after the successful registration
//- Add `login_throttling` feature leveraging `symfony/rate-limiter`
//- Difference between "Authentication" (`firewalls`) and "Authorization" (`access_control`)
//- Make sure your website uses HTTPS... for all pages, that's the best practice now
//- Customize the redirect after successful login form response with `_target_path`: https://symfony.com/doc/current/security/form_login.html
//- Cover a voter with a subject, like only allowing a user to edit their own starships
//- Cover security events, a listener that updates the last login timestamp on the user
- Also cover a super admin voter, which is super practical for a lot of apps

## Advanced bonus topics
- Explain `signature_properties` config option
- OAuth 2 flow (league/OAuth2-Bundle vs knpuniversity/oauth2-client-bundle)
- Create a reset password feature w/ `make:reset-password` command, promoting our `symfonycasts/reset-password-bundle`
- Verify email after registration, promoting our `symfonycasts/verify-email-bundle`
- Create a custom authenticator w/ `make:auth` command (or  `make:security:custom`?)
- Create a custom voter w/ `make:voter` command
- Custom voter (using a subject)
- Custom voter (superadmin role)
- Listen to core security events
- Redirect when account (email) is not verified
- Add 2FA feature
- LDAP & SSO (requested by our customers)
- Magik login link
- Custom user checkers: https://symfony.com/doc/current/security/user_checkers.html
- Add captcha feature (Google Recaptcha, Cloudflare captcha)
- Showing tricks how to test security: use HTTP basic in tests?
- Arbitrary user permission checks: https://symfony.com/blog/new-in-symfony-7-3-arbitrary-user-permission-checks
