## Composer Audit and Security Updates

One last thing! A bonus topic: security updates.
This isn't necassarily related to upgrading to Symfony 8, but it's an
important topic, now more than ever.

Recently, the Symfony ecosystem [underwent a large security review](https://symfony.com/blog/claude-mythos-audited-symfony-and-found-19-vulnerabilities)
that uncovered many vulnerabilities across various packages. The reason? AI has
changed the game.

Modern AI-assisted auditing tools can help security researchers analyze
large codebases and identify potential vulnerabilities much faster than
before. That's leading to more discoveries, more disclosures, and
ultimately more fixes.

And that's a good thing.

Finding these vulnerabilities doesn't mean Symfony is becoming less
secure. If anything, it's the opposite. Better tooling means issues are
being identified and fixed faster.

As developers, that means it's more important than ever to stay on top of
dependency updates and pay attention when security advisories are
published.

Thankfully, Composer has a built-in tool that makes this super easy.

## Composer Audit

To see it in action, at your terminal, run:

```terminal
symfony composer audit
```

This checks the packages currently installed in our project against known
security advisories.

And... looks like I have a few! This output is pretty gross on this small
screen, so I'll run the command again with `--format=plain`.

For each vulnerability, Composer shows the affected package, a severity
level, and links where we can learn more about the issue.

Most of the time, the solution is simple: update your dependencies.

Composer will often upgrade the vulnerable package to a patched version
and the problem disappears.

But not always.

Sometimes the fix exists only in a newer major version that you're not
ready to upgrade to yet. Sometimes the update introduces changes that
break your application.

If you fall into either of these cases, then it's important to understand
the vulnerability and the risk it poses to your application.

## Following the Advisory

Let's start with the last vulnerability in my list.

Each advisory includes a URL with more information. If we open this one,
we're redirected to a blog post on the Symfony website with all the details.

This is usually the best place to start our investigation.

The post explains:

* what the vulnerability is;
* which versions are affected;
* under what circumstances it can be exploited;
* and which versions contain the fix.

Not all packages will use a blog post to share the details of the vulnerability. Back in our terminal,
we can follow the *Advisory ID* link, and it takes us to the advisory on packagist.org. This shows
us the package-related details and, if it was created via GitHub, the link to the GitHub advisory.

That's this GHSA link here. If you follow that, we can see a standardized vulnerability page.
Here we have the package name, the affected versions, the patched versions, the severity, and a
description of the issue and how the fix resolves it.

To see all the published security advisories for a GitHub package, from its repository page, click
"Security and Quality". Here is the full listing. Clicking one goes to its advisory page.

## What is a CVE?

You've probably noticed each vulnerability has a CVE identifier. What's that?

CVE stands for **Common Vulnerabilities and Exposures**.

It's a public database of security vulnerabilities where each issue is
assigned a unique identifier. The goal is simple: give everyone a common
way to refer to the same vulnerability.

And this isn't specific to PHP, Composer, or even open-source software.
CVEs are used across the entire software industry. Operating systems,
web browsers, databases, cloud platforms, hardware devices, and open-
source libraries can all have CVEs assigned to them.

For example, if someone mentions `CVE-2026-46634`, developers, security
researchers, package maintainers, and security tools all know they're
talking about the exact same issue.

CVEs are assigned by organizations called **CVE Numbering Authorities**,
or *CNAs*.

GitHub is a CNA, which is great news for open-source maintainers because
they can create security advisories and request CVE identifiers directly
through GitHub's tooling.

You can view the official CVE records on [cve.org](https://www.cve.org/).

For vulnerabilities created via GitHub, I find their interface a bit
easier to navigate than cve.org, but it's good to know both places.

## Ignoring a Vulnerability

If you cannot upgrade to a patched version right away, and you understand
the vulnerability and the risk it poses, you have the option to ignore it
with Composer.

Copy the *Advisory ID* (the CVE identifier works too) and open your `composer.json` file.

Under the `config` key, add an `audit` section with an `ignore` key. Use the copied ID
as the key... and for the value, provide a reason for ignoring. This is important for
future you and your teammates to understand why you accepted the risk. I also like
to include *when* we can remove it.

Now if I run:

```terminal
symfony composer audit
```

The vulnerability hasn't disappeared. Instead, it's been moved into an
**Ignored Advisories** section - waaaay up top. This shows the reason as well.

It's important that we don't hide it completely. It should simply record that
we've reviewed it, understand the risk, and are temporarily choosing not
to address it.

Of course, ignoring a vulnerability should be the exception, not the
rule. Whenever possible, upgrading to a patched version is the safest
option.

Let's remove this ignore configuration and fix the issue properly.

## Applying the Fixes

Update our dependencies with:

```terminal
symfony composer update
```

> No security vulnerability advisories found.

Sweet, that seemed to have done it. Double check by running our the audit command again.

```terminal-silent
symfony composer audit
```

Beautiful! No more vulnerabilities.

## Automating Security Audits

Finally, don't rely on remembering to run audits manually. Automate them.

If you're using GitHub to host your code, a great solution is a scheduled GitHub Action that
runs `composer audit` on a regular basis. Running that command will cause the action to fail
if vulnerabilities are found, which can alert you and your team to the issue.

I'll show you the one I use! Create a new file: `.github/workflows/composer-audit.yaml`. I'll paste
the definition, but you can find it in the script below.

Let's go through this workflow. First, it's triggered on a schedule - every Monday at noon UTC.

***NOTE
Note that scheduled jobs run on your *default* branch.
***

Ok, onto the actual job. First, we checkout the code. Then we setup PHP and Composer using a popular action.
Finally, we run `composer audit`. Notice this `--locked` flag. This tells Composer to check the installed versions
in `composer.lock` against the advisories. This prevents us from having to run `composer install`, saving some
action minutes. It also means we don't have to specify the PHP version in the step above, keeping it simpler.

This job will fail if any non-ignored vulnerabilities are found.

If you and your team use Slack, this commented out section is an example of how you can post an alert there
on `failure()`.

***TIP
One final tip: when possible, create dedicated pull requests for security updates. Keeping them separate from
other work makes reviews easier and helps ensure security fixes get merged quickly.
***

And there you have it.

Upgrading your application is only part of the story. Ongoing maintenance
means keeping an eye on your dependencies, understanding security
advisories, and applying updates when needed.

Fortunately, Composer gives us excellent tools to make that process much
easier.

***SEEALSO
For more information, check out our blog post on
[Composer security advisories](https://symfonycasts.com/blog/composer-security-advisory).
***

Until next time,
Happy, vulnerable-free, coding!
