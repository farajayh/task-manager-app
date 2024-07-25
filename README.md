## Task Manager App

This is a task management application. It implements a REST API for Creation and management of tasks.
It provides CRUD endpoints for management of tasks and Authentication endpoints using JWT Authentication.


REST API Documentation: https://documenter.getpostman.com/view/9782302/2sA3QmCZg7

## HOW TO RUN
- Clone this repository locally: git clone https://github.com/farajayh/task-manager-app.git
- CD into the application directory
- Create the .env file for environment variables: cp .env.example .env or copy .env.example .env
- Install dependencies: composer install
- Generate application key: php artisan key:generate
- Generate JWT secret: php artisan jwt:secret
- Run database migration: php artisan migrate
- To run tests, do: php artisan test
- Seed the database: php artisan db:seed
- Run the application: php artisan serve


The application should be running now on http://127.0.0.1:8080/ or your localhost. Make a requests to any of the endpoints using any API client of your choice, e.g Postman, check the API documentation for details on the endpoints. If you are running this on a machine with a public IP with the http port exposed or your IP is tied to a domain name, you can replace 127.0.0.1:8080 with your public IP or your domain name.


## Technologies Used
- PHP 8.2
- SQL
- Laravel 11
- JWT
- PHPUnit