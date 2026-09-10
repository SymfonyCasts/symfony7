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
