## Initial steps

1. Run docker-compose, connect to db by credentials in .env file (outside container: 127.0.0.1:3308)
2. Create database `paxful`
3. Run command `docker-compose exec php bin/console app:fill-db` to fill sample data
4. Request must be sent with header `X-AUTH-TOKEN` and value `111122223333444455556666`
5. There are tests for endpoint. Execute by: `docker-compose exec php bin/phpunit tests/Controller/Api/V1/TransferControllerTest.php`