# Criteria

Coming soon...

Okay, we know that calling `$ship->getParts()` is going to return all the
parts related to this ship. What if we only want to return expensive parts,
like parts that cost more than 50,000 credits? We could do a fresh query in
our controller for all `Starship` parts related to the ship and where the
price is greater than 50,000. But lame, I still want to use these cool
`$ship->getParts()` methods because they're so easy. 

So fortunately, we can do this. In `Starship`, search for the `getParts()`
method, though it doesn't matter where you put this. I'm going to copy that
method and right below it, I'm going to paste and rename this to
`getExpensiveParts()`. But for now, we're just going to return all the
parts.

```php /** * @return Collection<int, StarshipPart> */ public function
getExpensiveParts(): Collection { return $this->parts; } ```

Alright, on our show template, let's go ahead and use this. So I'll change
parts here to `expensiveParts` and then `ship.expensiveParts`. So we know
even though there's not an `expensiveParts` property, that's going to call
the `getExpensiveParts()` method that we just created.

```twig Expensive Parts ({{ ship.expensiveParts|length }}) {% for part in
ship.expensiveParts %} ```

Now, how do we make our new method return only the expensive parts?
Remember, `parts` is not an array, it's a special collection object that
has a couple of nice tricks on it. One of them is a `filter()` method.

```php return $this->parts->filter(function (StarshipPart $part) { return
$part->getPrice() > 50000; }); ```

This isn't particularly efficient. In our queries, we're still querying for
every single part that relates to this starship and then we filter that in
PHP. What we really want to do is change the query itself. We want to
change the query so that Doctrine grabs all the parts related to the
starship and where the price is greater than 50,000. We can do that with a
powerful thing called a `Criteria` object.

```php use Doctrine\Common\Collections\Criteria; $criteria =
Criteria::create()->andWhere(Criteria::expr()->gt('price', 50000)); return
$this->parts->matching($criteria); ```

For organization, we can have the best of both worlds by moving this
`Criteria` logic into our repository. In our `StarshipPartRepository`, we
can add a public static function called `createExpensiveCriteria()` that
returns a `Criteria` object.

```php use App\Repository\StarshipPartRepository; return
$this->parts->matching(StarshipPartRepository::createExpensiveCriteria());
```

```php /src/Repository/StarshipPartRepository.php public static function
createExpensiveCriteria(): Criteria { return
Criteria::create()->andWhere(Criteria::expr()->gt('price', 50000)); } ```

Let's create one more method here to combine `Criteria` with Query
Builders. Let's pretend we have some controller where we want to get a list
of all of the expensive parts. Let's do that.

```php use Doctrine\Common\Collections\Collection; /** * @return
Collection<StarshipPart> */ public function getExpensiveParts(int $limit =
10): Collection { return $this->createQueryBuilder('sp')
->addCriteria(self::createExpensiveCriteria()) ->setMaxResults($limit)
->getQuery() ->getResult(); } ```

This is a very powerful concept: combining `Criteria` with Query Builders.
Now, let's do something totally different.