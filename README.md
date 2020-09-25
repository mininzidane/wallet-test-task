## Initial steps

1. Run `docker-compose up`
    - run `docker-compose exec php composer i`
    - connect to db by credentials in .env file (outside container: 127.0.0.1:3308)
2. Create database `paxful`, run migrations `docker-compose exec php bin/console doctrine:migrations:migrate`
3. Run command `docker-compose exec php bin/console app:fill-db` to fill sample data
4. Request must be sent with header `X-AUTH-TOKEN` and token value from POST `/api/v1/login` and payload
```
{"username": "root", "password": "123456"}
```
5. There are tests for end-point. Execute by: `docker-compose exec php bin/phpunit tests/Controller/Api/V1/`