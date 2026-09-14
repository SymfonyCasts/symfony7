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
