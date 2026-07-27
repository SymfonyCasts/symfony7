# Role Hierarchy & Access Control

Did you know roles can have children? Yep! And a parent role inherits all the child
roles. This is called role hierarchy.

To illustrate role hierarchy, let's create probably the most common parent role:
`ROLE_ADMIN`. Users with this role will be considered admins and will be able to
do more than a standard user, like a captain.

First, let's create an admin user. Open `src/Story/AppStory`, find where we're creating
Picard, and duplicate it. For the name, use `Kathryn Janeway`... email,
`janeway@starfleet.space`. She's an admiral at Starfleet command, so it makes
sense she'd have admin privileges. Set her password to `coffeeblack` and her role
to `ROLE_ADMIN`:

[[[ code('10228e1bca') ]]]

Over in your terminal, reload the fixtures with:

```terminal
symfony console foundry:load-fixtures
```

## Verifying Admin Access

Head over to the browser and click the "Parts" link. Even though she isn't a captain,
Janeway should be able to access the parts page as an admin.

Login as her with email: `janeway@starfleet.space` and password: `coffeeblack`.

We're on the parts page, but we're getting a 403 access denied error because
Janeway doesn't have `ROLE_CAPTAIN`. While she has `ROLE_ADMIN`, there's nothing
special about that role yet.

What we need to do is set `ROLE_CAPTAIN` as a child of `ROLE_ADMIN`.

To do this, open `config/packages/security.yaml`, and under the `security` section,
add `role_hierarchy`. Under that, add the key `ROLE_ADMIN`. This key is the parent
role. Under that, we can add a list of child roles. So add `ROLE_CAPTAIN` as a child:

[[[ code('6b11b08905') ]]]

## Checking the Changes

Now refresh the `/parts` page... Nice! We have access!

If you open the security profiler panel you can see, yep, her roles are `ROLE_ADMIN` and
`ROLE_USER`. But there's a new section below: "Inherited Roles". This shows that she has
inherited `ROLE_CAPTAIN`.

In the access decision log, you can see that when `ROLE_CAPTAIN` was checked, it was granted.

## Admin Access Control

Our site has the concept of an admin section. These are pages whose URLs start with `/admin`.
These pages are spread across a few controller classes.

Back in the IDE, I'll close some files... Now, open `src/Controller/AdminController`. This
class-level route, prefixes all the routes in this controller with `/admin`:

[[[ code('b78fa1a7d0') ]]]

If you now open `StarshipAdminController`, you can see this one is prefixed with `/admin/starship`:

[[[ code('20118b4eba') ]]]

These controllers are all public now, but really should be available to admins only. From
the last chapter, you know whe could add the `#[IsGranted('ROLE_ADMIN')]` attribute to each of
these classes. But... that could create a lot of duplication, and if you had dozens of admin
controllers, you might miss one.

An alternative is to tell Symfony that any URL that starts with `/admin` requires `ROLE_ADMIN` to
access. This is called "access control" and is configured in our `security.yaml` file:

[[[ code('992db8f2e6') ]]]

Under `security`, Flex added this `access_control` section stub. Since what we want to do is so
common, Flex already has a commented out example of how to do this. Uncomment the first example:

[[[ code('797325e267') ]]]

Here, we are setting the `path` to `^/admin`. This is a regular expression to match the path. The `^`
means "starts with", so this expression will match any path that "starts with `/admin`", but won't
match if `/admin` is in the middle of the path, like `/starship/admin/edit`.

The `roles` key is where we specify the role that's required to access this path. And yep, `ROLE_ADMIN`
is the role we want to require.

## Verifying Admin Permissions

Back in the browser, we're authenticated as Janeway, and she has `ROLE_ADMIN`. So she should be able to
access the admin pages. Try it out by visiting `/admin/startship`. Yep, we have access!

To prove this is working, log out... and try to access `/admin/starship`. We're redirected to the login page,
good! Now login as Picard with `picard@enterprise.space`, password `makeitso`. Remember, he doesn't have
`ROLE_ADMIN`.

403 access denied... Perfect!

Next, we'll see how to access the currently logged-in user in our services and controllers.
