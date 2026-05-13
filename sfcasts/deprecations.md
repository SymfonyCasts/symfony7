# Tracking & Fixing Deprecations

Let's revisit deprecations as these are important to fix. As mentioned
earlier, before you can safely jump to Symfony 8.0, you need to ensure
you're running deprecation-free Symfony 7.4. This means that there should be zero
deprecations across your entire app.

We know we can find deprecations in the Web Debug Toolbar, but... this just shows
the deprecations for the current page... None here... but click on this starship. 
This page does have a deprecation.

## Digging into the Deprecations Panel

Let's explore the deprecation panel in more detail. Click the icon in the toolbar to open it.
This deprecation looks like it's coming from Doctrine, something about raw field value
access... But look at this! A link to a pull request right in the message! Very
helpful, I love it! Copy the link and paste it in your browser...

This is the Doctrine PR that modified some behavior and triggered this deprecation. It
looks like it fixes some memory issues. There's a huge discussion to help give us more
context if we want. But how do we fix it? In the PR description, there is a migration
path section. The new behavior is opt-in, and can be enabled by passing `accessRawFieldValues: true`
to the `Criteria` constructor.

Ok... how do we find where our app needs this fix? Deprecation panel to the rescue!

Click "show trace" under the deprecation message. This is the stack trace of how your app got to this deprecation.
You read this from the top down and can mostly ignore the lines that aren't in your `src` directory. The first
line in our `src` directory is likely what's triggering the deprecation. In this case, it's `StarshipPartRepository` line 23.
We can even expand this to see the actual code that's triggering the deprecation. Neat!

Also in this panel is this "show context" link. This shows the actual exception that was thrown, but this mostly
shows the same information. The message... and the stack trace. I usually only ever use the stack trace link.

So... to find all the deprecations with the web debug toolbar, we'd have to locally go to every page,
every form submit, every API endpoint, and so on. That's not very efficient. So, what's the better
way to find deprecations?

There's two primary alternative ways. First is your test suite. PHPUnit can be configured to show deprecations.
But... unless you have 100% test coverage, this won't catch everything. Oh man, I don't even have tests in
this app...

## Logging Deprecations

The final way is a surefire catch-all method. You deploy your Symfony 7.4 app to production for a while, a few
weeks or a month. During that time, you log all the deprecations that are happening in production. Remember, deprecations
aren't errors, so they won't break your app. Either as they happen, or at the end of the time period, you review those
logs and fix the deprecations. Do this cycle a few times until you have no more deprecations in production. Now you're
ready to safely upgrade to Symfony 8.0.

Let's take a look at how logging works and can be improved. Open `config/packages/monolog.yaml`. Scroll down to
the `deprecation` handler under the production config. This handler writes just the deprecations to the
standard error stream. Of course, you can customize this to your liking. Spamming my team's Slack channel with
them is my favorite!

Let's add this handler to our dev environment so we can see it in action. Copy the `deprecation` handler and paste
it in the `when@dev` section... I don't want to stream these to standard error here, so copy the path from the
handler above and paste it here. Suffix the file with `deprecations.json`: 

[[[ code('53ecd5f082') ]]]

This is already using the JSON formatter and using that extension will help me make it pretty in PhpStorm.

Back in our app, refresh the page that causes the deprecation. Now, open `var/log`... Hmm, I don't see the file.
Maybe PhpStorm hasn't picked it up yet. I'll reload from disk... and there it is!

`dev.deprecations.json`. Open that up. I'll format the code to make it easier to read.

## Including Stack Traces in the Logs

We see the same deprecation message as we saw in the profiler panel... and a bunch of other things... but... it's
missing the stack trace. By default, it's hard to know where the deprecation is being triggered.

Luckily, we can include the stack trace! I'll clear this log file so we have a fresh start.

Back in `monolog.yaml`, in our dev deprecation handler config, add `include_stacktraces: true`:

[[[ code('a5f5a65ecb') ]]]

Refresh the page... check the log file... format it... and there we go! The stack trace looks just like it did
in the profiler panel. And sure enough, we can see that `StarshipPartRepository` line 23 is triggering the deprecation.

## Log Processors

There's one thing I think that's still missing... It would be nice to know what URL triggered it. This would help
duplicating the issue locally and confirming the fix. Monolog has something called *processors* that can add extra
data to the log entries. There's a bunch of built-in processors, one to add authentication details, console
command details, and additional debug information. The one I want to enable is the `WebProcessor`. This adds the
request details, like the URL, HTTP method, and IP.

The built-in processors aren't registered as services by default, but it's super easy to do it ourselves. 

I'll clear this log file again.

Open `config/services.yaml` and at the bottom, under `services`, add `monolog.processor.web` with the class `WebProcessor`. Choose
the one from the Monolog Bridge, as it's ready to go with Symfony:

[[[ code('8105a00509') ]]]

That's it! It's autowired and autoconfigured, so we don't need to do anything else.

Refresh the page again... check the log file... format it... and there we go! We have the message, the stack trace, and
down at the bottom, under `extra`, we have the URL, the IP, and the HTTP method.

Just modify our setup for production in your apps and deploy. Finding, duplicating, and fixing deprecations will be a
breeze!

## Fixing the Deprecation

Ok, now to actually fix this deprecation! It was triggered in `StarshipPartRepository` on line 23, right?
Open that up... and find line 23... Remember, we have to pass `true` to the `Criteria` constructor. This
`Criteria::create()` isn't the real constructor, but it's a *named constructor*. When we jump to that
method... sure enough, it has the `accessRawFieldValues` parameter... but it's commented out. What's
up with that?! It's part of the deprecation process. They can't just add the new parameter. This class
and method aren't final, so to maintain backwards compatibility, they have to keep the current signature.
They comment out the new parameter and trigger the deprecation if it's not passed. This `func_num_args()` stuff
is a way to check if the new parameter was passed without actually adding it to the method signature.

The next major version of this package will have the real parameter in the method signature, and the old way will
be removed. That's the last step of the deprecation cycle.

Enough talk, let's fix it! Back in `StarshipPartRepository`, pass `true` to `Criteria::create()`:

[[[ code('2cd4b32bdd') ]]]

Back to the browser. When we refresh, this deprecation should be gone... And it is!

We're finally ready to move to Symfony 8! That's next!
