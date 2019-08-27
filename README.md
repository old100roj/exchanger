# Exchanger Symfony trial project

Storing exchange rates into DB.

Functionality:
- to refresh today’s exchange rates for IND, EUR and others;
- to view all rates stored in the database on the “/rates” route;
- to add/edit/delete/refresh a rate manually through the web interface.

Technical criteria:
- Symfony Command used;
- Doctrine orm, entities and migrations used;
- Twig templates used;
- Unit tests added.

Install and run:
- `cd docker`
- `docker-compose build`
- `docker-compose up -d`

Ports:
- http://127.0.0.1:8080/ --> phpMyAdmin
- http://127.0.0.1:8084/ --> Exchanger app

Notes:
- there is `update-rates` console command wich updates rates table using `benmajor/exchange-rates-api`;
- this command is added to cronjobs via docker entrypoint script and executes every 10 mins;
- to run this command manually use `docker-compose exec exchanger php bin/console app:update-rates` command;
- phpunit installing and migrations run are also done by docker entrypoint;
- to run unit tests use `docker-compose exec exchanger php bin/phpunit` command.
