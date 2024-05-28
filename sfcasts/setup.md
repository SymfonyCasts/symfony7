# Setup, Services & the Service Container

Hey friends! Welcome back to Episode *2* of our Symfony 7 tutorial. I'm Kevin, and I'll be your guide. No matter what you do with Symfony, the most important thing you'll use are *services* - little yellow minions that do work in your app. We'll talk about *configuration* for those services as well as *environments*. Environments allow us to influence services so they act differently while developing locally versus on production.

So what exactly *is* a service? That's easy! It's a plain PHP class that does *work*. For example, a `Logger` that helps you log messages is a service. Or a *mailer* that sends emails to your customers. *Or* a database connection object that you use to execute queries to the database. Those are *all* services. Even the controller that handles requests is a service, but it has *super powers*. We'll talk about that later.

This course is titled "Fundamentals" because it's the *foundation*. Everything after this tutorial is just a variation of these themes. *So*, to code along with me, download the course code on this page, unzip it, and inside, you'll find a `start/` directory with the same code that you see here. The `README.md` file has everything you need to get this application up and running. I've already completed most of these steps, so I'm going to move on to the last step and run the built-in Symfony web server. To do that, open your terminal and run:

```terminal
symfony serve -d
```

The `-d` tells Symfony to start this in the background. You can find it at `https://localhost:8000`. I *could* copy and paste this in my browser, but I'm going to *cheat*. Hold "cmd" or "control" on a Mac, click this link, and... welcome back to *Starshop*, the site we created in Episode 1.

*So*, services are objects that do work: `Logger`, mailer, our database connection, and even our controllers. Is *every* object in our app a service? Actually no! We also have objects that hold *data*. For example, our `Starship` class isn't a service. It's just a plain data object. When we need these objects we just instantiate them the normal boring way.

But services - objects that do work - are different. We *could* instantiate them manually, but in practice, some thing else handles that: the *service container*. It's a *huge* fan of our services. It knows *everything* about them, from the class name for to every constructor argument. If you ask it for a service, it will instantiate it *for* you and return a ready-to-use PHP object. And it's smart if you request a service *multiple* times, only creates it *once*. For example, our app only needs one logger. If you ask for the logger 5 times, it's created just once then that same object is returned every time!

Okay, so... how do we know which services we have? To see the list of all of the services we have available, we're going to use a special command. Over at your terminal, run:

```terminal
bin/console debug:container
```

Here you can see a long list of services we can use in our app. But where are these coming from? Who tells the container there should be a `logger` service whose class is `Logger` and it should be instantiated with these arguments? *Some* services are from our code and we'll talk about how those are registered in a bit. But the vast majority comes from bundles. Bundles are just plugins you can add to Symfony applications. They provide a few things, but the most important is services. Each bundle has a config file that says:

> Hey! I want to have a service called `logger` which should be an instance of "Logger", and it
> should be instantiated with these arguments.

So services are tools and bundles *give* us tools. Over in our code, open `config/bundles.php`. *This* is the file that's responsible for registering bundles in our application. And check it out! We already have *ten* of them! Some of these, like `MonologBundle`, are only available for a specific environment. This is what provides the `Logger` service we're using in `StartshipRepository` when we log a message. *Or*, if we delete this `TwigBundle` line completely, the `render()` method we're calling in our controllers won't work anymore. That's because, behind the scenes, this is using the `twig` service to render templates. More on that later.

Next, let's talk about how to install new bundles in your application to get *new* services.
