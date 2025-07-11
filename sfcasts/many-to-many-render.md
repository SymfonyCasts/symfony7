# Accessing Data on a ManyToMany

Simple goal really: print all the droids assigned to a `Starship`. If you
got comfortable with the `OneToMany` relation from `Starship` to its parts,
then you're going to love this!

Open the template for the `Starship` show page:
`templates/starship/show.html.twig`. I'll steal the `h4` and `p` tag for
`arrived at`, paste them below, and change the `h4` to `Droids`.
Clear out `arrived at` ... and break that line up:

[[[ code('5cf6e65737') ]]]

We have a `ship` variable, which is a `Starship` object. And remember,
it has a `droids` property and a `getDroids()` method. So, for
`droid in ship.droids`. This calls the `getDroids()` method, and *that*
returns a collection of `Droid` objects. So we can say `{{ droid.name }}`.

## The `loop.last`

I want commas, but not an extra comma at the end. Say: 
`{% if not loop.last %}, {% endif %}`. There are fancier ways to do this,
but keep it simple for now.

If there aren't any droids, use an `else` tag and say
`No droids on board (clean up your own mess)`. Rude!

[[[ code('c379a4fd9f') ]]]

## Droids on the Homepage

On the homepage, we want to show off the droids here too. Open
up the template: `templates/main/homepage.html.twig`. Right after
`parts`, add another div with `Droids: {{ ship.droidNames ?: 'none' }}`:

[[[ code('237c7386af') ]]]

## The Smart Method

We could use our `loop.last ` comma thing again, but we've needed the droid names
in two spots, so let's add a smart method for this in the `Starship` class. This
could go anywhere, but I'll stick it at the bottom with the other droid methods.
Create a `public function getDroidNames(): string`. To return a comma-separated
string of droid names, check this out: return
`implode(', ', $this->droids->map(fn(Droid $droid) => $droid->getName())->toArray())`:

[[[ code('3a5eaaa16e') ]]]

Wow, that was a mouthful! Let's break it down:

First, `$this->droids` is our collection of `Droid` objects. Second, `map()` applies
a function to each `Droid` in the collection. Third, `fn(Droid $droid) => $droid->getName()`
is a hipster way to say:

> Give me the name of each droid

Fourth, `toArray()` converts the collection to an array so it can be used with
`implode()`. Finally, `implode(', ', ...)` takes that array of names and turns it into
a comma-separated string.

Now that we've got a `getDroidNames()` method, we can say
`{{ ship.droidNames ?: 'none' }}`.

We're done! Refresh... and enjoy the droid names on the homepage.

Next: let's use Foundry to set the ManyToMany relationship in the fixtures.
Another place Foundry shines!
