# Bonus: Custom DQL Function

You're still here? Great! I have something I want to show you. Remember the
`filterShips()` method we created in our `StarshipRepository`? To fetch *just*
the freighters, we had to add `NOT INSTANCEOF` to exclude the mining freighters,
which is a subclass. If you had a large class hierarchy, this could get pretty messy.

## Refining the `filterShips()` Method

Let's see if we can improve this. First, remove the `notclass` part and the parameter.
I feel like this could work... `s = :class`, so let's try that:

[[[ code('091c6563cf') ]]]

Before we test this, let's bring back the use of `filterShips` in our
`MainController::homepage()`. We'll revert some previous changes, inject the
`StarshipRepository` again, and call `filterShips()` instead of `findAll()`:

[[[ code('1a049c8710') ]]]

Head over to the browser and refresh the homepage... Hmm, we have this "array_rand...
array cannot be empty" error. We've seen this before, it's what happens if our `filterShips()`
method returns an empty array. Let's check the query to see what's going on.

## Examining the Query

It's subtle, but look at the `WHERE` clause: `s0_.id = ?`. This `s0_` is an
internal SQL table alias Doctrine uses - it's our `starship` table. I expected
this to use our discriminator column, but it's using the id. I guess when you
use just an alias in DQL, it defaults to the id of the entity.

Ok, that didn't work. Let's try something else. Can we just add the discriminator column in
our DQL? What was it again, open the `Starship` entity to check the `DiscriminatorColumn` attribute...
`ship_type`. So set our DQL to `s.ship_type = :class`:

[[[ code('788b53a345') ]]]

and refresh the homepage...

Nope, we can't do that either. At the DQL level, Doctrine isn't aware of the discriminator column, it's trying
to find a property called `ship_type` on our `Starship` entity, which doesn't exist.

I'm going to quickly revert back to the `s = :class` version...

[[[ code('824f4292f0') ]]]

Alright, back to the drawing board.

## Crafting Our Own Function

What we need to do is create our own DQL function. I want it to look like this:
`TYPE(s) = :class`:

[[[ code('5eb98bb541') ]]]

This will take the `s`, our DQL entity alias, and convert it to the correct SQL with the discriminator
column.

## Creating the `TypeFunction` Class

In `src`, create a new PHP class... Call it `TypeFunction` and the namespace will be
`App\Doctrine\ORM\Function`. This seems like a good home for our custom DQL functions.

During my research, I found this [GitHub Gist](https://gist.github.com/nanotronic/f967239f1b4613b3b01110072f0c8a31)...
Or is it *gist*? Oh man, this is the jif, gif argument...

Anyway, the author had the same idea as us. And this gist works... but needs some cleanup and modernization.
Copy the entire file except for the namespace, and paste it over the empty class we just created.

## Fixing Errors and Cleaning Up

As you can see, this class extends `FunctionNode`, which is the base class you use for custom DQL functions.

There are some errors we need to fix. First, it looks like the `getSql()` method probably
requires a return type. If we look at the parent, yep, `string`, so add that. 

This `ClassMetadataInfo` has been renamed to just `ClassMetadata`, so I'll update that. This isn't
really an error because it's in a comment, but it'll help with autocompletion and readability.

I think the `parse()` method also requires a return type. Look at the parent... yep, `void`, add that.

This `Lexer` class doesn't seem to have these constants anymore. If we jump to that class and scroll
down, I see these constants have been moved to this `TokenType` enum. So, back in the `parse()` method,
replace `Lexer` with `TokenType` in the three places.

Now that our PHP code is valid, let's do a bit of cleanup. Remove these redundant docblocks, they no
longer add value. This `$dqlAlias` property doesn't need to be public, make it a `private` nullable `string`
that defaults to `null`. Up at the top, we can remove the `Lexer` import since it's not used anymore.

[[[ code('03759221d4') ]]]

## Anatomy of the `TypeFunction` Class

Now let's see how this works. We're going to register this function with the name `TYPE` to match the function
we used in our DQL. When Doctrine parses DQL and encounters a function, it runs through the registered functions and
calls the `parse()` method on each to find a match.

You can see here it's trying to parse this `IDENTIFIER`, this will be `TYPE`, the name we register it as. Next,
we're matching an `OPENING_PARANTHESIS`. Then we're setting the `dqlAlias` property to this `IdentificationVariable()`,
basically, the string that comes after the opening parenthesis, which in this case will be `s`. Finally, we match
the `CLOSING_PARANTHESIS`.

If all of that matches, then Doctrine calls this `getSql()` method to convert it to SQL.

I'm not going to go into deep detail here. Basically, we're grabbing this "query component", which is an array. One
of the keys is `metadata`, which is an instance of `ClassMetadata`. Next, we grab the SQL table alias. This alias is
that internal thing Doctrine generates. You can see it over in the query profiler panel. In this case, the `starship`
table is aliased to `s0_`. That's what we're grabbing here.

This check and exception is to ensure this function is only usable on entities that are in an inheritance hierarchy.

Finally, we're returning `$tableAlias`... dot... discriminator column name from the class metadata.

## Registering the `TypeFunction` with Symfony

Now we need to tell Doctrine about our new function by registering it. There's some Symfony [documentation](https://symfony.com/doc/current/doctrine/custom_dql_functions.html)
that shows this! Looks like we register it in `doctrine.yaml`, under the `orm.dql` section. Our function is
a string function, so I'll copy this chunk... open `config/packages/doctrine.yaml`... find the `orm` section...
and paste it here.

This key under `string_functions` is the function name, so use `TYPE`. The value is the class name: `App\Doctrine\ORM\Function\TypeFunction`:

[[[ code('00201396af') ]]]

## Testing the `TypeFunction`

To make sure it's registered and working as we expect, back in `TypeFunction::getSql()`, `dd($this->dqlAlias)`. This
should dump the contents of our `TYPE` function, `s` in our case.

Back in the browser, go to the homepage... and we get an error: "Expected known function, got TYPE". Hmm, this means
our function isn't being registered correctly... Maybe I have a typo in the namespace. I'll copy it and paste it
in `doctrine.yaml` to make sure it's correct. Hmm, it was right. Oh, duh, I have the function name as `TEST`! I'll
rename to `TYPE` and refresh the homepage... great! `s` is dumped!

Remove the `dd()` and refresh again... our old friend, the "array cannot be empty" exception is back, but that's
a good sign because it means our function is at least registered and working without throwing an error. Let's check
the query profiler panel to see what's going on. Ok cool, the `WHERE` clause is now `s0_.ship_type` - which is our
discriminator column. Ahh, the problem is the passed parameter. It's the Freighter class name, but this column uses
the mapping aliases.

In `StarshipRepository::filterShips()`, replace this parameter value with just a simple string: `freighter`:

[[[ code('3dcc362105') ]]]

and refresh the homepage. Woo! That worked, and check it out, we now just have the freighters, no mining freighters in sight!

## Improving Our Function

It's perhaps kind of a bummer we have to use the string alias. If we changed the alias in our `Starship` entity, we'd
have to remember to change it here as well. I think we can improve this!

In our `Starship` entity, we have this `getType()` method that flips the `TYPE_MAP` and grabs the alias for the current
class. Right now, it's an instance method, so you need an instantiated `Starship` object to call it on. But, there's
nothing about this method that requires it to be an instance method. Make it `static`:

[[[ code('86f8c2979a') ]]]

Don't worry, PHP is forgiving when calling static methods as instance methods, so this won't break anything.

Now, back in the `filterShips()` method, set the parameter value to `Freighter::getType()`:

[[[ code('5391f5c4dc') ]]]

Refresh the homepage again... perfect, nothing changed! That logic is working as expected!

Ok, so we found a bit of a missing feature in Doctrine, and added it ourselves as a custom DQL function!
This is a really powerful feature of Doctrine, and it allows you to extend the capabilities of DQL to fit your specific
needs. You can create all sorts of custom functions for different use cases. The
[beberlei/doctrineextensions](https://packagist.org/packages/beberlei/doctrineextensions) Composer package
contains a whole pile of pre-created functions. If you ever find yourself needing a function that isn't built in,
check out that package, you might find what you need!

Til next time, happy coding!
