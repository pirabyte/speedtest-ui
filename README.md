# Speedtest UI

Monitor your internet speed 24/7. Runs an [Ookla speedtest](https://www.speedtest.net/apps/cli) every 3 minutes and visualizes the results in three views: **24 hours**, **weekly**, and **all-time**.

![image](https://github.com/pirabyte/speedtest-ui/assets/24978665/2d19c1fd-15b8-42c0-b61e-89e8e981fbb8)

## Quick Start

```bash
docker run -d \
  --name speedtest \
  -p 8080:80 \
  -e DB_DATABASE=/var/lib/speedtest-ui/database.sqlite \
  -v speedtest-data:/var/lib/speedtest-ui \
  --restart unless-stopped \
  ghcr.io/pirabyte/speedtest-ui:latest
```

Open **http://localhost:8080** — that's it.

To store the database in a local directory (e.g. for easy backup/export):

```bash
docker run -d \
  --name speedtest \
  -p 8080:80 \
  -e DB_DATABASE=/var/lib/speedtest-ui/database.sqlite \
  -v ./speedtest-data:/var/lib/speedtest-ui \
  --restart unless-stopped \
  ghcr.io/pirabyte/speedtest-ui:latest
```

The SQLite database file will be available at `./speedtest-data/database.sqlite`.

## Docker Compose

```yaml
services:
  speedtest:
    image: ghcr.io/pirabyte/speedtest-ui:latest
    ports:
      - "8080:80"
    environment:
      - DB_DATABASE=/var/lib/speedtest-ui/database.sqlite
    volumes:
      - speedtest-data:/var/lib/speedtest-ui
    restart: unless-stopped

volumes:
  speedtest-data:
```

```bash
docker compose up -d
```

## Configuration

All settings are optional. SQLite is used by default — no external database required.

| Variable | Default | Description |
|---|---|---|
| `DB_CONNECTION` | `sqlite` | Database driver (`sqlite`, `mysql`, `pgsql`) |
| `APP_TIMEZONE` | `UTC` | Timezone for timestamps (e.g. `Europe/Berlin`) |
| `APP_ENV` | `production` | Application environment |
| `APP_DEBUG` | `false` | Enable debug mode |
| `SPEEDTEST_PATH` | `/usr/bin/speedtest` | Path to the speedtest CLI binary |

## Using MySQL / MariaDB (Optional)

```yaml
services:
  speedtest:
    image: ghcr.io/pirabyte/speedtest-ui:latest
    depends_on:
      - mariadb
    ports:
      - "8080:80"
    environment:
      - DB_CONNECTION=mysql
      - DB_HOST=mariadb
      - DB_PORT=3306
      - DB_DATABASE=speedtest
      - DB_USERNAME=speedtest
      - DB_PASSWORD=secret
    restart: unless-stopped

  mariadb:
    image: mariadb:latest
    environment:
      - MYSQL_ROOT_PASSWORD=root_secret
      - MYSQL_DATABASE=speedtest
      - MYSQL_USER=speedtest
      - MYSQL_PASSWORD=secret
    volumes:
      - db-data:/var/lib/mysql
    restart: unless-stopped

volumes:
  db-data:
```

## Development

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+
- npm

### Local Setup

```bash
# Clone the repo
git clone https://github.com/pirabyte/speedtest-ui.git
cd speedtest-ui

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create SQLite database and run migrations
touch database/database.sqlite
php artisan migrate

# Start development servers
php artisan serve &
npm run dev
```

Open **http://localhost:8000**.

> **Note:** The speedtest CLI must be installed on your machine for the scheduled tests to work.
> Install it from [speedtest.net/apps/cli](https://www.speedtest.net/apps/cli).

## License

[MIT](LICENSE)
