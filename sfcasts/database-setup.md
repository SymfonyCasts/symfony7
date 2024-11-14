# Database Setup & Docker

All right, we have Doctrine installed, but now we need a database. That's kind of
important!

Take a look at our `.env` file. When we installed Doctrine in the last chapter,
the Flex recipe added this *doctrine-bundle* section. The way you configure Doctrine to connect
to a database is with this `DATABASE_URL` environment variable. The recipe
added this for us and set it to a special URL-looking string called a *Data Source
Name*, or DSN for short. This is instructions for Doctrine on how to connect to
the database.

The first part of the DSN is the scheme - the type of database you want to use:
`mysql`, `postgres`, `sqlite`, etc. Next, there's a username and password for the
server, then the host, port, and the path is the database name. Any query parameters
are extra configuration details.

By default, the `DATABASE_URL` is set to connect to a Postgres DSN. We'll be using
Postgres in this course and it'll be running in a Docker container. Since we're
using Docker for our database server, we can leave this as is.

If you don't have Docker, you can comment out this line and uncomment the
`sqlite` `DATABASE_URL` above. SQLite doesn't require a server - the database is
just a file on your filesystem. Because Doctrine *abstracts* the database layer,
for the most part, the code we write will work with any database type.

Remember, don't store any sensitive information in this file. It's committed to
your repository. If you have your own database server locally, create a `.env.local`
file (this is ignored by git), and set your own `DATABASE_URL` there.

Take a look at this `compose.yaml` file. It was added by Flex and configures the
Docker containers required by our application. The `doctrine-bundle` Flex recipe
added this `database` service which configures a Postgres container. Again, we don't
need to change anything here - the defaults are fine!

Time to spin up our database server! Open your terminal and run:

```terminal
docker compose up -d
```

This command started the Docker containers configured in `compose.yaml`. The
`-d` flag tells Docker to run the containers in the background.

The Symfony CLI has a really cool integration with Docker. When the Symfony server
is running, it intercepts your environment variables and sets them to the proper
values to connect to your containers - the `database` container in this case.

Jump over to our app and refresh the page. Down here, hover over "Server". This shows
us some details about the Symfony CLI server. We can see that it's detected our
docker containers and is intercepting our environment variables.

We can see what environment variables Symfony CLI is setting by popping over to our
terminal and running:

```terminal
symfony var:export --multiline
```

Scroll up a bit to see the database related ones.

You're probably used to running Symfony commands with `bin/console`. But when using the
Symfony CLI with a Docker database, you need to run the database-specific commands through
`symfony console` instead. This ensures that the environment variables are intercepted and set
correctly for the command.

Our database server is now running in a Docker container, so let's create the database!
Run:

```terminal
symfony console doctrine:database:create
```

An error?! No worries! The error is telling us the database already exists. This is good though,
it means we connected to our database successfully! The Docker container automatically created
our database when we started it. 

We have Doctrine and a database - now we need a table! We'll do that next.
