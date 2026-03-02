# Doctrine Inheritance: Classes in the Database

Well hi there! This repository holds the code and script for the
[Doctrine Inheritance: Classes in the Database](https://symfonycasts.com/screencast/doctrine-inheritance)
course on SymfonyCasts.

## Setup

If you've just downloaded the code, congratulations!

To get it working, follow these steps:

**Download Symfony CLI**

https://symfony.com/download

Make sure you have [Symfony CLI installed](https://symfony.com/download).
You only need to install it once on your system.

Symfony CLI is a developer tool to help you build, run, and manage
your Symfony applications directly from your terminal.

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/).
You only need to install it once on your system.

Install Composer dependencies with:

```bash
symfony composer install
```

> [!NOTE]
> You may alternatively need to run `symfony php composer.phar install`,
> depending on how you installed Composer.

**Setup the Database**

Create the database (SQLite by default, but if you want to go with
a different DB server - configure the `DATABASE_URL` env var in the `.env`
file first). Create the database and load the initial fixtures with:

```bash
symfony console foundry:load-fixtures
```

**Build Tailwind CSS**

```bash
symfony console tailwind:build
```

> [!NOTE]
> If you use Symfony Web Server below, it will start
> a worker for you that will watch your Tailwind CSS assets.

**Start the Symfony Web Server**

You can use Nginx or Apache, but Symfony's local web server works
even better.

Open a terminal, move into the project dir, and start the web server:

```bash
symfony serve -d
```

> [!NOTE]
> If this is your first time using this command, you may see an
> error that you need to run `symfony server:ca:install` first.

Now check out the site at `https://localhost:8000`.

Have fun!

## Have Ideas, Feedback or an Issue?

If you have suggestions or questions, please feel free to open an issue
on this repository or comment on the course itself. We're watching both :).

## Thanks!

And as always, thanks so much for your support and letting us do what we love!

<3 Your friends at SymfonyCasts
