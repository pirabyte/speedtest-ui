## Setup


This tool uses the [speedtest.net CLI](https://www.speedtest.net/apps/cli).

In order to use this tool you must first accept its terms of service and license by running the following command:

```bash
docker exec -it speedtest-app speedtest
```

Once that is done an automatic speedtest will be run and logged every 3 minutes.

Example docker-compose.yml:

```yaml
version: '3.8'
services:
    app:
        depends_on:
            -   mariadb
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
        image: mariadb:10.5
        environment:
            - MYSQL_ROOT_PASSWORD=root_password
            - MYSQL_DATABASE=speedtest
            - MYSQL_USER=database_user
            - MYSQL_PASSWORD=database_password
```
