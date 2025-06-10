# Accessing Data on a ManyToMany

Simple goal really: print all the droids assigned to a `Starship`. If you
got comfortable using the `OneToMany` relation from `Starship` to its parts,
you're going to love this!

Open the template for the `Starship` show page:
`templates/starship/show.html.twig`. I'll steal the `h4` and `p` tag for
`arrived at`, paste them below, and change the `h4` to `Droids`
Clear out `arrived at` ... and break that line up.

We have a `ship` variable, which is a `Starship` object, which, remember,
has a `droids` property and a `getDroids()` method. So: for
`droid in ship.droids`, we just call it `droid`. This calls the
`getDroids()` method, and *that* returns a collection of `Droid` objects.
This means we can say things like `{{ droid.name }}`.

## loop.last

I want commas, but not that extra comma at the end. Say: 
`{% if not loop.last %}, {% endif %}`. There are fancier ways to do this,
but let's keep it simple.

If there aren't any droids, we can use an `else` tag and say
`No droids on board (clean up your own mess)`.

## Droids on the Homepage

Awesome! Now, on the homepage, we want to show off our droids too. Open
up the template for this: `templates/main/homepage.html.twig`. Right after
`parts`, add another div and say `Droids: {{ ship.droidNames ?: 'none' }}`.

## The Smart Method

We could use our `loop.last ` comma thing again, but we've needed the droid names
in two spots, so let's add a smart method for this to the `Starship` class. This
could go anywhere, but I'll stick it at the bottom with the other droid methods.
Create a `public function getDroidNames(): string`. To return a comma-separated
string of droid names, check this out: return
`implode(', ', $this->droids->map(fn(Droid $droid) => $droid->getName())->toArray())`.

Wow, that was a mouthful! Let's break it down:

1. `$this->droids` is our collection of `Droid` objects.
2. `map()` applies a function to each `Droid` in the collection.
3. `fn(Droid $droid) => $droid->getName()` is a hipster way to say "give me the name
    of each droid".
4. `toArray()` converts the collection to an array so it can be used with `implode()`.

Finally, `implode(', ', ...)` takes that array of names and turns it into a
string, with each name separated by a comma.

Now that we've got a `getDroidNames()` method, we can say
`{{ ship.droidNames ?: 'none' }}`.

And we're done! Refresh... and enjoy the droid names on the homepage.

Next: let's use Foundry to set the ManyToMany relationship in the fixtures.
