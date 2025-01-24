```markdown
# Project Name: PHP + Nginx + PostgreSQL Docker Setup

![Docker](https://img.shields.io/badge/Docker-3.8%2B-blue)  
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-brightgreen)  

Modern Docker setup for PHP applications with Nginx reverse proxy and PostgreSQL database. Managed through Docker Compose for easy deployment.

## Key Features 🚀

- **PHP 8.3 FPM** - Latest stable version with PostgreSQL extensions
- **Nginx 1.25** - Optimized for performance and security
- **PostgreSQL 14** - Relational DB with persistent storage
- **Automated Management** - Bash script for start/stop/status
- **Health Monitoring** - `/status` endpoint for DB checks

## Prerequisites 📋

- Docker Engine 24.0+
- Docker Compose 2.20+
- 512 MB free RAM
- Linux/Windows/macOS with WSL2

## Quick Start ⚡

```bash
git clone https://github.com/1244Matt1244/php_app.git
cd php_app
cp .env.example .env  # Set credentials in .env file
./manage.sh start
```

Visit http://localhost:8080 in your browser

## Configuration ⚙️

### Basic Settings (`.env`)
```ini
# PostgreSQL
POSTGRES_USER=app_user
POSTGRES_PASSWORD=strong_password123
POSTGRES_DB=app_db

# PHP
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=300
```

### Advanced Customization
- **Nginx**: Edit `nginx.conf` for custom routing
- **PHP**: Add extensions in `Dockerfile`
- **Timezone**: Configure in `php.ini`

## System Management 🕹️

| Command               | Description                      |
|-----------------------|----------------------------------|
| `./manage.sh start`   | Start all services              |
| `./manage.sh stop`    | Stop services                   |
| `./manage.sh status`  | Show container status           |
| `./manage.sh logs`    | View Nginx logs                 |

## Docker Services 🐳

### 1. PHP Application (`php-app`)
- PHP 8.3 FPM with pdo_pgsql
- Automatic ENV-based configuration
- Workdir: `/var/www/html`

### 2. Nginx Server (`nginx-web`)
- Reverse proxy for PHP
- Port mapping: `8080:80`
- Security headers + GZIP compression

### 3. PostgreSQL Database (`postgres-db`)
- Persistent storage via Docker volume
- Automatic health checks
- Backup via `pg_dump`

## Testing ✅

```bash
# Check database status
curl http://localhost:8080/status

# Sample response:
# {"status": "healthy", "database": "connected"}
```

## Security Notes 🔒
1. Never commit `.env` file
2. Use SSL in production (Let's Encrypt)
3. Regularly update Docker images

## Extending Functionality ➕
- Add Redis for caching: modify `docker-compose.yml`
- Implement cron jobs in PHP container
- Use `docker-compose.override.yml` for local development

---
**License**: [MIT](LICENSE)  
**Author**: Matej Martinović  
**Version**: 1.1.0
```
