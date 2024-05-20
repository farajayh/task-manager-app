## Task Manager App

This is a task management application. It implements a REST API for Creation and management of tasks.
It provides CRUD endpoints for management of tasks and Authentication endpoints using JWT Authentication.
It also provides real time streaming of task creation and updates using websocket.

Actions like creation, update and deletion of tasks are broadcasted to the frontend using Pusher, when a task is created, the frontend gets the notification and the details of the action.

REST API Documentation: https://documenter.getpostman.com/view/9782302/2sA3QmCZg7

## HOW TO RUN
- Clone this repository locally: git clone https://github.com/farajayh/task-manager-app.git
- CD into the application directory: cd task-manager-app
- Create the .env file for environment variables: cp .env.example .env
- Open the .env file with nano or vim and set the database password DB_PASSWORD, you can use any password of your choice
- In the .env file, set the pusher credentials, PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET and PUSHER_APP_CLUSTER
- Run docker compose command, in detached mode: docker compose up -d
- Run database migration command: docker compose exec task-manager-app php artisan migrate
- Seed the database by running this command: docker compose exec task-manager-app php artisan  db:seed
- Install npm dependencies by running this command: docker compose exec task-manager-app npm install
- Install laravel echo and pusher library on the frontend for receiveing broadcasts, use this command: docker compose exec task-manager-app npm install --save-dev laravel-echo pusher-js
- Run npm build: docker compose exec task-manager-app npm run build
- Run the laravel queue worker for proessing broadcast queues: docker compose exec -d task-manager-app php artisan queue:work

The application should be running now. If you are running this on localhost you can visit http://127.0.0.1:8080/ on your browser and you will see the home page. Make a requests to any of the endpoints using any API client of your choice, e.g Postman, check the API documentation for details on the endpoints. If you are running this on a machine with a public IP with the http port exposed or your IP is tied to a domain name, you can replace 127.0.0.1:8080 with your public IP or your domain name.

On Creation, update and deletion of tasks, the data is streamed to the frontend in realtime, and the details of the action shows as an alert message.

- To stop the app run: docker compose down

## Technologies Used
- PHP 8.2
- MySQL
- Laravel 10
- Web Sockets: laravel websocket
- laravel-echo
- Pusher
- Containerization with docker