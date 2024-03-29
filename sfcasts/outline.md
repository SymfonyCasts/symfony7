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
