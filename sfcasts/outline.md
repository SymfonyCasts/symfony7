# OUTLINE

## Setup

- no matter what you'll do with Symfony, it comes down to services
- all about services: the things that do the work in your app
    - and about configuration for those services
    - and about environments... which are just another way to tweak
       those services to act differently locally while developing vs
       on production.
    - Logger service is a good example
- We call this "Fundamentals" because, truly, after this tutorial,
    everything else is just a variation on these themes.
- Download course code!

- Reminder: services are objects that do work
    - logger, mailer, database connection, even our controller!
    - juxtaposed with objects that hold data
- `debug:container`
    - The container is responsible for instantiating each service
    - So it knows the class name and every constructor argument
    - If you ask for a service multiple times, it only creates it once
- Where do these come from?
  - Who tells the container "there should be a `logger` service" and its
    class is `Logger` and it should be instantiated with these arguments?
  - well, some services from OUR code... and we'll about how those are
    registered in a bit
  - But the VAST majority come from "bundles"
- show `bundles.php`
  - bundles are like plugins for Symfony
  - they can give us multiple things... but mainly they give us services
  - Each has a config file that literally says:
  - I want to have a service called `logger` and it should be an instance
    of `Logger` and it should be instantiated with these arguments
  - services are tools, and so bundles give us tools

## KnpTimeBundle: Install bundle, get service

- Before this tutorial, I added a new `arrivedAt` `DateTime` field to
  `Starship`.
- Let's print that in `homepage.html.twig`
- We can't just print the property - you can't print DateTime objects
- But we can use a Twig filter to format it
- Nice!
- But wouldn't it be cooler if we could say "2 hours ago" instead of
  "2018-01-01 12:00:00"?
- There's no service in our app that does this. And I certainly don't want
  to write it myself - I've got more fun things to do, like play board games.
- But maybe there's a bundle that gives us a service that can do this?
- There is! It's called KnpTimeBundle
- `composer require knplabs/knp-time-bundle`
- show `config/bundles.php`
- Remember, bundles give us services
- `debug:container time` to see a few matches
- Also `debug:autowiring` to see `DateTimeFormatter`
  - So if we wanted to do this "ago" conversion in PHP, we could use this
    service
- But one of the other services is what's called a Twig extension
  - It's a service, but one that integrates into Twig to add new stuff, like
    filters
- Heck, you can even see this in `debug:twig`
- Use the `ago` filter in `homepage.html.twig`
- Refresh the page. How cool?
- So bundles give us services. And services are tools. And tools are fun!

## HTTP Client and More Services

- We know that Symfony is a collection of a TON of independent little PHP libraries,
  called "components".
- We only have a small number installed right now, but as we need more features,
  we'll install more components.
- In the last tutorial, we installed the `serializer` component to help us
  serialize objects to JSON.
- Let's install another component: the `http-client` component.
- This is good at making external HTTP requests.
- `composer require symfony/http-client`
- Run `debug:autowiring` to see a new service!
- But wait, this time, no bundle was installed.
  - This is a pure PHP package: just PHP code.
  - So it *does* contain service classes, which are just classes that
    do work.
  - But it does NOT contain any configuration that says "Hey! I want
    to have a service called `http_client` and it should be an instance
    of `HttpClient` and it should be instantiated with these arguments."
- So where did this service come from?
- The answer is FrameworkBundle. That's THE most core bundle of Symfony
   and it's been in our app since the beginning.
- It has a special power: it watches for Symfony components that are
  installed and automatically registers their services.
- So, no big deal... just another way that services will arrive in our app.
- Next, let's put our new service to work!

## Using the HTTP Client

- Let's make a new page that fetches some data from an API.
- We're going to talk to an API that tells us where the ISS is right now.
- You can see it at https://api.wheretheiss.at/v1/satellites/25544
- And we'll show this up in the header


