# Database Setup & Docker

All right, we have Doctrine installed! But now we need, ya know, to get a database
server running.

## `DATABASE_URL` Environment Variable

Take a look at our `.env` file. When we installed Doctrine,
the Flex recipe added this *doctrine-bundle* section. The 
`DATABASE_URL` environment variable is where we tell Doctrine *how* to connect to our database.
It's a special URL-looking string called a DSN, if you want some nerdy terminology.

It holds the type of database we're connecting to -
`mysql`, `postgres`, `sqlite`, `borgsql`, etc, a username, password, host, port, and the database name.
Any query params are extra config.

By default, the `DATABASE_URL` is set to connect to a Postgres database and that's
what we'll use. We'll get it running super easily with Docker.

If you don't want to use Docker, no problem! Comment out this line and *uncomment* the
`sqlite` one. SQLite doesn't require a server: it's 
just a file on your filesystem. Because Doctrine *abstracts* the database layer,
for the most part, the code we write will work with any database type. Cool!

Remember, don't store any sensitive info in this file: it's committed to
your repo. If you have your own database server locally, create a `.env.local`
file (this is ignored by git), and set your own `DATABASE_URL` there.

## Starting a Postgres Container with Docker

Ok, so how can we get a Postgres database server running?

Take a look at `compose.yaml`. This was added by a Flex recipe and holds 
Docker config, including this `database` service to spin up a Postgres container.
Fantastic! You can do whatever you want, but we are only going to use Docker
as a convenient way to run a database server locally. PHP itself is installed 
normally on my machine.

Open your terminal and run:

```terminal
docker compose up -d
```

This start the Docker containers and `-d` tells Docker to do it all in the background.

But *where* is the database server running? Like what port? Don't we need to know
so we can update `DATABASE_URL` to point to it?

## The Symfony CLI is Awesome!

No! The `symfony` CLI binary that's running the web server has some Docker magic!
Jump over and refresh the app. Down here, hover over "Server". This holds
details about the Symfony CLI server. This part means that it automatically detected our
docker containers and set up the environment variables for us!

I'll show you. Pop over to our terminal and run:

```terminal
symfony var:export --multiline
```

This shows us some *extra* environment variables that the Symfony CLI is setting for us,
in addition to the ones in `.env`. 
 
Scroll up a bit to see.... Ah! Here it is! `DATABASE_URL`! This overrides 
the one in `.env` and points to the Postgres database running in Docker.
That port number will randomly change, but the Symfony CLI will always
use the correct one.

## `symfony console` vs `bin/console`

Now, we're used to running Symfony commands with `bin/console`. But when using the
Symfony CLI with a Docker database, we need to run the database-specific commands through
`symfony console` instead. It's the same as `bin/console`, but it gives the
Symfony CLI a chance to add the environment variables.

## Creating the Database

Ok! Database server running in a Docker container and `DATABASE_URL` is pointing to it.
To create the database, run:

```terminal
symfony console doctrine:database:create
```

An error?! No worries! The error is telling us the database already exists: apparently
the server comes with one. But this is good,
it means we *are* connecting to our database server!

Ok crew, we have Doctrine and a database. Now we need a table! We'll do that next
by jump lifting off into the world of entities and migrations.
