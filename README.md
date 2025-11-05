# Symfony 7 Forms

Well hi there! This repository holds the code and script for the
[Symfony 7 Forms](https://symfonycasts.com/screencast/symfony7-forms)
course on SymfonyCasts.

## Setup

If you've just downloaded the code, congratulations!

To get it working, follow these steps:

### Download Symfony CLI

https://symfony.com/download

Make sure you have [Symfony CLI installed](https://symfony.com/download).
You only need to install it once on your system.

Symfony CLI is a developer tool to help you build, run, and manage
your Symfony applications directly from your terminal.

### Download Composer dependencies

Make sure you have [Composer installed](https://getcomposer.org/download/).
You only need to install it once on your system.

Install Composer dependencies with:

```bash
symfony composer install
```

> NOTE: You may alternatively need to run `symfony php composer.phar install`,
> depending on how you installed Composer.

### Set up the Database

We recommend using SQLite DB server locally. To create the database,
generate the migration, migrate, and load the fixtures, simply run:

```bash
symfony console make:migration
symfony console foundry:load-fixtures
```

> NOTE: If you prefer to use a different database server (e.g. MySQL),
> first configure the `DATABASE_URL` environment variable in your
> `.env` file. Then, create the database, generate the migration, migrate,
> and load the fixtures:
> ```bash
> symfony console doctrine:database:create --if-not-exists
> symfony console make:migration
> symfony console foundry:load-fixtures
> ```

### Build Tailwind CSS

```bash
symfony console tailwind:build
```

> NOTE: If you use Symfony Web Server below, it will start
> a worker for you that will watch your Tailwind CSS assets.

### Start the Symfony Web Server

You can use Nginx or Apache, but Symfony's local web server works
even better.

Open a terminal, move into the project dir, and start the web server:

```bash
symfony serve -d
```

> NOTE: If this is your first time using this command, you may see an
> error that you need to run `symfony server:ca:install` first.

Now check out the site at `https://localhost:8000`.

Have fun!

## Have Ideas, Feedback or an Issue?

If you have suggestions or questions, please feel free to open an issue
on this repository or comment on the course itself. We're watching both :).

## Thanks!

And as always, thanks so much for your support and letting us do what we love!

<3 Your friends at SymfonyCasts
