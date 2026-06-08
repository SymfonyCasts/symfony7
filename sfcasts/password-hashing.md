# Understanding Password Hashing

Ok, we left off trying to login with a valid username and password, but it didn't work...
Let's take a moment to understand why.

Over in the terminal, dump our user table with:

```terminal
symfony console dbal:run-sql 'select * from user'
```

## Plaintext Passwords are Bad!

The issue here is that the password in the database is stored in plain text. First of all,
this is a huge security risk... If the Romulan's gain access to our database, they can
see all our users' passwords! 

The reason our login isn't working is that the Symfony security system is expecting
this password to be hashed, but it's not.

So let's hash this password! Run:

```terminal
symfony console security:hash-password
```

This gives us a hidden input field, write our plain text password: `makeitso`.

This is the hashed version of `makeitso`. Pretty crazy looking, right? This is what we need
to store in our users' password field in the database.

## Hashing vs Encryption

Let's take a moment to talk about hashing. Hashing is a one-way process that turns a value,
like a password, into a string that you can safely store, but not realistically reverse back
into the original value. That's different from encryption, which is designed to be reversible
with the right key. In other words, encryption is like locking something in a box and unlocking
it later, while hashing is more like transforming it into a unique fingerprint. You can compare
fingerprints to check a match, but you can't reconstruct the original value.

The command output is also telling us what *hasher* is being used - there are several different
hashing algorithms out there, and Symfony supports many of them. The hasher being used is
the `MigratingPasswordHasher`.

This is a special hasher that enables a super cool feature in Symfony - password migration!

## The `auto` Hasher and Password Migration

In your IDE, open `config/packages/security.yaml` and check out the `password_hashers` section.
We have a single hasher configured for our `PasswordAuthenticatedUserInterface` users, and it's set to `auto`.
Does this cover our custom `User`? Open `src/Entity/User.php` and check the class declaration. Since it
implements `PasswordAuthenticatedUserInterface`, it *is* covered by the `auto` hasher.

So what's the `auto` hasher? You're going to love this! `auto` tells Symfony to "choose the best hasher available".
Security is a moving target and new hashing algorithms are developed over time. Currently, the best hasher
algorithm is `bcrypt`. When a new, better algorithm comes out, Symfony will automatically start hashing passwords
using it! This is the behavior `auto` gives us. It's important to note that upgrading your Symfony version is
what allows you to take advantage of this. Another good reason to keep your Symfony version up to date!

Ok, so a new hashing algorithm comes out in a few years, and new users start to use it, what about our
old users? Are they stuck with the old, less secure hash? 

You're going to doubly love this! `auto` also enables the `MigratingPasswordHasher`.
As user's log in, if it's determined their password is using an old hashing algorithm, Symfony will upgrade them
to the new one automatically!

## Rehashing Passwords on Login

How does that work? Well, when a user successfully logs in, Symfony checks their existing password hash.
If it detects that the hash was generated using an older algorithm, it rehashes the password using the
new one. Remember, at this point, Symfony still has access to the plain text password, since the user just
submitted it to log in. It then updates the User with the new hash!

This update process needs to be configured in your app. But since we used the maker bundle to generate our user entity,
this is already set up for us. Open `src/Repository/UserRepository.php`. This generated repository class is a little different
from the standard ones from `make:entity`. It implements `PasswordUpgraderInterface` from the security component.
This interface has a single method: `upgradePassword()`. This is the method that gets called when it's determined
a User's password needs a rehash. It accepts the User in question and the new hashed password.

Check out what the maker-bundle generated for us in `UserRepository`. First, it's just making sure, it is indeed
the correct `User` object. Then is calls `setPassword()` on the User, passing in the new password hash. Finally,
it persists and flushes the User to save it. Boom! Password hash has been upgraded!

This out of the box Symfony feature is fantastic for future-proofing your app's security!

## Using the Hashed Password

Alright, so now that we have our hashed password, let's put it to use. Copy
the hash from the terminal and open `src/Story/AppStory.php`. Replace the
plain text password for `makeitso` with the copied hash:

[[[ code('d18f691117') ]]]

Back in the terminal, reload our fixtures with:

```terminal
symfony console foundry:load-fixtures
```

Go back to our login page... refresh... enter `makeitso` as the password... and submit!

No error, well, my browser is complaining about the bad password, but we're on the homepage
and successfully logged in! Down in the web debug toolbar, we can see our user identifier: `picard@enterprise.space`.
Hovering over shows us more details about the user. Roles, inherited roles, and token class. Don't worry,
these things will be covered a bit later. And cool, we even have a logout link!

## More Convenient Fixture Passwords

One thing that's a bit annoying about our fixtures, is having to manually hash the password everytime we
create a user. It certainly isn't obvious this crazy long hash is for the password
`makeitso`.

I'd like to be able to use the plain text password when creating fixtures.

We can do that! Open `src/Factory/UserFactory.php`. Foundry factories are *also*
Symfony services, so we can inject other services into them!

In the constructor, inject `private UserPasswordHasherInterface $passwordHasher`:

[[[ code('20067b51a0') ]]]

This is the service that hashes the password based on our security configuration.

Down in the `default()` method, we'll still keep this plain text password as the default value. We'll hash the
password in a Foundry hook. These are defined in `initialize()`. Uncomment the
`afterInstantiate()` line to enable the hook. This runs right after the object is
created, but before it's saved to the database. This is the time to hash the password.
The callback accepts the created object, in this case, a `User`.

Inside, write `$user->setPassword()`, and inside that, `$this->passwordHasher->hashPassword()`. The
first argument for this method is the `$user` object, this is needed to determine the correct
hashing algorithm for this user. We only have one, but multiple can be configured.
The second argument is the plain-text password. Use `$user->getPassword()`. Remember,
at this point, the password set to the user is still plain text. Finsh with a semicolon
at the end, and that's it!

[[[ code('df9be89f27') ]]]

In `AppStory`, when this user object is created, it will have its password set to `makeitso`:

[[[ code('f12d791ad4') ]]]

Our `afterInstantiate()` hook will replace that with the hashed version, *then* save it to the
database.

Back in the terminal, reload our fixtures again:

```terminal-silent
symfony console foundry:load-fixtures
```

Go back to the browser, and refresh the homepage. Did you see what happened? We got logged out!

That's not a bug, that's a feature! A security feature in fact. When we reloaded our fixtures,
the password hash for our user changed. Our plain text password is still `makeitso`, but the
hash changed. Every time you hash a password, even if it's the same, you get a different hash.

## Salt... and Peppa!

Good password hashing algorithms include a random value called a *salt*, so the same password won't
always produce the same hash. This helps protect against precomputed attacks where common passwords
are matched against known hashes. This is called a *rainbow table attack*. The salt breaks that shortcut.

Symfony saw our password changed, so, for security, it logged us out.

Have you ever seen a "logout of other devices" feature in another app? This is how you could
implement the same in Symfony! When the user chooses this option, they confirm their password,
you then hash it, and update the hash in the database. Boom! All other sessions just became
invalid!

Let's log in again with `picard@enterprise.space`, password: `makeitso`... Yeah I know, I know,
bad password. We're back on the homepage and logged in successfully!

Our User fixture is now much easier to work with!

## Optimizing Test Speed

By design, the process of hashing passwords is intentionally slow and CPU-intensive. This is a
security feature. Trying to guess the original passwords by brute force takes much longer.

If you have a lot of feature or integration tests that involve password hashing, like creating users,
and logging in, this can slow down your test suite significantly, for really, no reason. There's no
security risk in your tests.

Don't worry, Symfony has you covered! Jump back to our `security.yaml` file and scroll down to
the `when@test` section. This overrides the password hasher configured above in our `test` environment.
These options adjust the cost and time it takes to hash a password, making it much faster, practically
instantaneous.

Be sure these low values are never used in your production environment. You could increase these values
in production, but, for most applications, the default values are already a good balance between security
and performance.

Next up, let's adjust our base template to include login and logout links in the navbar!
