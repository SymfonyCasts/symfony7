# CSS & JavaScript with Asset Mapper

What about images, CSS and JavaScript? How does that work in Symfony?

## Stuff in public is... Public

First off, the `public/` directory is known as your document
root. Anything inside `public/` is accessible to your end user. Anything
*not* in `public/` is *not* accessible, which is great! None of our top secret source
files can be downloaded by our users.

So if you want to create a CSS file or an image file or anything else, life
*can* be as simple as putting it in `public/`. I can now go to `/foo.txt`... and
we see the file.

## Hello Asset Mapper

*However*, Symfony has a *great* component called Asset Mapper that lets us
*effectively* do the same thing... but with some important, extra features. We
have a few tutorials that go deeper into this topic: one about Asset Mapper
specifically and another about building things *with* Asset Mapper called
[LAST Stack](https://symfonycasts.com/screencast/last-stack). Check those out to go deeper.

But let's dive into the friendly waters of Asset Mapper! Commit all your changes -
I already have - then install it with:

```terminal
composer require symfony/asset-mapper
```

This recipe makes several changes... and we'll walk through each little-by-little as
they're important.

But already, if we move over and refresh, our background is blue! Inspect Element
in your browser and go to the console. We also have a console log!

> This log comes from `assets/app.js`. Welcome to asset mapper.

Why thank you!

## Asset Mapper's 2 Super Powers

Asset Mapper has two big superpowers. The first is that it helps us load CSS and
JavaScript. The recipe gave us a new `assets/` directory with an `app.js` file and
a `styles/app.css` file. As we saw, the console log is coming from `app.js`. 

[[[ code('963c6fbd7c') ]]]

So this file *is* being loaded. It's also apparently including `app.css`, which is what
gives us that blue background.

[[[ code('240833ed57') ]]]

We're going to talk more later about how these files are loaded and how this all
works. But for right now, it's enough to know that `app.js` and `app.css` are
included on the page.

The second big superpower of Asset Mapper is a bit simpler. The recipe
created a `config/packages/asset_mapper.yaml` file. There's not a lot here:

[[[ code('391af354c5') ]]]

just `paths` pointing to our `assets/` directory. But because of this line, *any*
file that we put in the `assets/` directory becomes available publicly.
It's as if the `assets/` directory physically lives inside `public/`. This is
useful because, along the way, Asset Mapper adds asset *versioning*: an
important frontend concept that we'll see in a minute.

## Listing Assets & the Logical Path

But first, head to your terminal and run *another* new `debug` command:

```terminal
php bin/console debug:asset
```

This shows *every* asset that's exposed publicly through Asset Mapper. Right
now it's just two: `app.css` and `app.js`. 

If you download the course code from this page and unzip it, you'll find a `tutorial/`
directory with an `images/` subdirectory. I'll cut this... then paste into
`assets/`.

So now we have an `assets/images/` directory with 5 files inside. And, by the way,
you can organize the `assets/` directory however you want.

But now, spin back over and run `debug:asset` again:

```terminal-silent
php bin/console debug:asset
```

The new files are there!

## Rendering an Image

On the left, see this "logical path"? That's the path we'll use to *reference*
that file in Asset Mapper.

I'll show you: let's render an `img` tag to the logo. Copy the `starshop-logo.png`
logical path. Then head into `templates/base.html.twig`. Right above the body
block - so it's not overridden by our page content - add an `<img>` tag with
`src=""`. Instead of trying to hardcode a path, say `{{` and use a new
Twig function called `asset()`. Pass *this* the logical path.

That's it! Ok, I'll add an `alt` attribute... to be a good citizen of the
web. 

[[[ code('36753afdc0') ]]]

Let's try this. Refresh and... it explodes!

> Did you forget to run` composer require symfony/asset`. Unknown function "asset".

Remember: our app starts tiny. And then, as we need more features, we install
more Symfony components. And often, if you try to use a
feature from a component that's *not* installed, it'll tell you. The Twig
`asset()` function comes from another tiny component called `symfony/asset`.
All *we* need to do is follow the advice. Copy the `composer require` command, spin
over to your terminal and run it:

```terminal-silent
composer require symfony/asset
```

When it finishes, move over and refresh. There's our logo!

## Automatic Asset Versioning

The most interesting part? View the page source and check out the URL:
`/assets/images/starshop-logo-` then a long string of letters and numbers, `.png`.
This string is called the version hash and its generated based on the *content* of
the file. That means that if we update our logo later on, this hash will change
automatically.

That's *super* important. Browsers like to cache images, JavaScript, and CSS files,
which is great: it helps performance. But when we *change* those files, we want
our users to download the *new* version: not keep using the outdated, cached version.

But because the filename will change when we update the image, the browser is going
to automatically use the new one! It looks like this:

* User goes to our site and downloads `logo-abc123.png`. Their browser caches it.
* On the next visit, their browser sees the `img` tag for `logo-abc123.png`,
  finds the file in its cache and uses it.
* Then we come along, update that file and deploy.
* The next time the user goes to our site, the `img` tag will be pointing at
  a *different* filename: `logo-def456.png`. And since the browser doesn't have
  *that* file in its cache, it downloads it fresh.

This is kind of a small detail, but it's also *incredibly* important
to make sure our users are always using the latest files. And the best part?
It just works. Now that I've explained it, you'll never need to think about this again.

Ok team, let's install & start using Tailwind CSS next.
