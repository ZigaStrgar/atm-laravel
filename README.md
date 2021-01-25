# ATM Laravel

## Prerequsites

* Composer
* PHP >=7.3
* Database service [supported by Laravel](https://laravel.com/docs/8.x/database)
* Postman

## Setup

There are a few steps needed to get this project up and going. First step is of course cloning the repository and then
navigating into the folder. Afterwards run this set of commands to finish up the setup.

```shell
composer install && cp .env.example .env && php artisan key:generate
```

When the given command finishes executing you should open `.env` file end edit the `DATABASE_*` variables to match your
needs and settings. Make sure to create the database as well before proceeding to the next step.

The last thing we need is to run the migrations. To do so, you have to run the following command.

```shell
php artisan migrate
```

### Postman

Project contains two JSON files describing the environment as well as available routes for the easiest usage. In the top
left corner there is an "Import" button where you can import both files and start to make calls to the API after the
fact that server is up & running.

## Running

### Valet or Homestead

Your computer setup may be powered by [Valet](https://laravel.com/docs/8.x/valet)
or [Homestead](https://laravel.com/docs/8.x/homestead) so keep that in mind while setting up the project as well as
running it.

### Basic

The simplest way to run the application is to execute `serve` command.

```shell
php artisan serve
```

That will start the server listening on port `8000` by default, and you are able to use `localhost:8000` as the access
URL. If you have Postman setup done as well you are ready to go :)
