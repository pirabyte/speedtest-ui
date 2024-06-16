## About speedtest-ui

speedtest-ui uses the [speedtest.net CLI](https://www.speedtest.net/apps/cli) in a laravel environment so check your internet speed every 3 minutes.
results are logged every 3 minutes and can be visualized in three categories: [ 24hrs, Week, AllTime ].

This tool uses the [speedtest.net CLI](https://www.speedtest.net/apps/cli).

![image](https://github.com/pirabyte/speedtest-ui/assets/24978665/2d19c1fd-15b8-42c0-b61e-89e8e981fbb8)

For a minimal configuration you need to specify a database:

Supported database types can be checked in the official [laravel docs](https://laravel.com/docs/11.x/database#introduction).

## Example docker-compose.yml

```yaml
version: '3.8'
services:
    app:
        image: ghcr.io/pirabyte/speedtest-ui/speedtest-ui:latest
        depends_on:
            - mariadb
        ports:
            - "8080:80"
        environment:
            - DB_CONNECTION=mysql
            - DB_HOST=mariadb
            - DB_PORT=3306
            - DB_DATABASE=speedtest
            - DB_USERNAME=database_user
            - DB_PASSWORD=database_password
    mariadb:
        image: mariadb:latest
        environment:
            - MYSQL_ROOT_PASSWORD=root_password
            - MYSQL_DATABASE=speedtest
            - MYSQL_USER=database_user
            - MYSQL_PASSWORD=database_password
```
