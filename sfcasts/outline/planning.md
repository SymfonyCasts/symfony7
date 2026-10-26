# Symfony 8.2 Security Upgrades

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

## `CheckRefreshedUserEvent` (8.2)
- Park until Symfony 8.2 is released
- Our `ROLE_DISABLED` trick works, but it is a hack: a role that isn't a role
- 8.2 adds `CheckRefreshedUserEvent`, dispatched right after the user refresh
- It is seeded with the verdict of the built-in comparison
- A listener can change that verdict either way
- Create a `DisabledUserListener` with `#[AsEventListener]`
- Grab `$event->getRefreshedUser()`, check `isEnabled()`
- Then `$event->setUserChanged(true, new DisabledException())`
- Now delete `ROLE_DISABLED` from `getRoles()` - still logged out
- `setUserChanged(false)` keeps a user in, overruling the built-in check
- `EquatableInterface` *replaces* the comparison; this one adds to it
- The exception rides along to `TokenDeauthenticatedEvent::getException()`
- So we can finally tell them *why* they were logged out
- It also carries `getToken()` and `getOriginalUser()`
- Fires on every request of a stateful firewall - keep the listener cheap
- Scope it per firewall with `dispatcher: 'security.event_dispatcher.main'`

## Improved Sudo Mode (8.2)
- Park until Symfony 8.2 is released
- Our sudo mode chapter leans on `IS_AUTHENTICATED_FULLY`
- But that says *how* you logged in, not *when*
- A login from three hours ago still counts as "fully"
- 8.2 adds `IS_AUTHENTICATED_RECENTLY` - credentials within 2 hours
- And `IS_AUTHENTICATED_VERY_RECENTLY` - within 5 minutes
- Tune both with `*_authentication_lifetime` config keys
- Swap our `access_control` expression over to the new attribute
- Expression functions too: `is_recently_authenticated()` and the very_ variant
- Put `IS_AUTHENTICATED_VERY_RECENTLY` on something scary, like delete
- Fix a bug in that form: it always sends a hidden `_remember_me=1`
- Before, only remember-me users saw the form, so they had already opted in
- Now a fully authenticated user with an old login lands there too
- So a "don't remember me" login quietly becomes a remembered one
- Fix: only send the hidden field `{% if is_granted('IS_REMEMBERED') %}`, else show the normal checkbox
- No session needed, the remember-me cookie already records the choice
- Promised in a comment reply on the original course, so don't skip it
- Blog post: https://symfony.com/blog/new-in-symfony-8-2-sudo-mode

## Advanced bonus topics
- `debug:roles` + the role hierarchy graph in the profiler (8.2)
