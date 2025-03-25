# Symfony, Doctrine Relations & Warp Drive Basics

Well hi there! This repository holds the code and script for the
[Symfony, Doctrine Relations & Warp Drive Basics](https://symfonycasts.com/screencast/symfony7-doctrine-relations)
course on SymfonyCasts.

## Setup

If you've just downloaded the code, congratulations!!

To get it working, follow these steps:

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

You may alternatively need to run `php composer.phar install`, depending
on how you installed Composer.

**(Optional) Start the Docker database container**

```
docker compose up -d
```

If not using Docker, you can skip this step, but you'll need
to configure `DATABASE_URL` in `.env`.

**Create the database, Schema (Tables) & Load Fixtures**

If using Docker, the database should already be created.

```
symfony console doctrine:database:create --if-not-exists
symfony console doctrine:schema:create
symfony console doctrine:fixtures:load
```

**Start the Symfony web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```

(If this is your first time using this command, you may see an
error that you need to run `symfony server:ca:install` first).

**Build TailwindCSS**

This project uses TailwindCSS, to build the CSS file run:

If you're using the `symfony serve` command to run the site, you're done!
The `tailwind:build` command is already running thanks to the `workers` config
in `symfony.yaml`.

```
php bin/console tailwind:build
```

Now check out the site at `https://localhost:8000`

Have fun!

## Have Ideas, Feedback or an Issue?

If you have suggestions or questions, please feel free to open an issue
on this repository or comment on the course itself. We're watching both :).

## Thanks!

And as always, thanks so much for your support and letting us do what we love!

<3 Your friends at SymfonyCasts
