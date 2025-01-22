### `README.md`

```markdown
# Project Name: PHP + Nginx + PostgreSQL Docker Setup

This project provides a fully functional environment for running a PHP application with Nginx as a reverse proxy, PHP-FPM for processing PHP, and PostgreSQL as the database. The setup uses Docker Compose to manage the services, making it easy to start, stop, and maintain the environment.

## Features

- **PHP 8.4 FPM**: The project uses PHP 8.4 FPM to handle PHP requests efficiently.
- **Nginx**: Nginx is used as the web server, configured to serve PHP applications and handle static assets.
- **PostgreSQL**: A PostgreSQL database is available (optional), with environment variables for configuring the database connection.
- **Security Headers**: The application includes optional security headers for better protection in production.
- **Health Check Endpoint**: A `/health` endpoint is provided to check if the PHP-FPM service is running correctly.
- **Error Handling**: Custom error pages for 404 and server errors are included.

## Prerequisites

- Docker (version 20.10+)
- Docker Compose (version 1.29+)

## Setup

### 1. Clone the Repository

Clone this repository to your local machine:

```bash
git clone https://github.com/yourusername/your-repository.git
cd your-repository
```

### 2. Configuration

#### Database Credentials

Before you start the services, ensure that the database credentials in the `.env` file are properly configured:

```env
DB_USER=your_user
DB_PASSWORD=your_password
DB_NAME=your_db_name
```

#### `php.ini`

This project uses a custom `php.ini` configuration to set various PHP settings, such as increasing the maximum execution time for PHP scripts. You can adjust the `php.ini` file located in the root directory of this project as needed.

*Please be cautious when sharing the `php.ini` file with others, as it may contain sensitive data or paths related to your local development environment.*

Example:

```ini
extension_dir = "C:/path/to/php/ext"
```

Ensure any sensitive paths or configurations are removed or replaced with placeholders when sharing the file.

### 3. Start the Services

To start all the services (Nginx, PHP-FPM, and PostgreSQL), run the following command:

```bash
docker-compose up -d --build
```

This command will build and start the containers in detached mode.

### 4. Verify Services

You can verify that all services are running correctly by checking their status:

```bash
docker-compose ps
```

You can also check the logs for Nginx:

```bash
docker-compose logs -f nginx
```

### 5. Health Check

You can verify that PHP-FPM is running by visiting the `/health` endpoint:

```bash
http://localhost/health
```

This will return a JSON response indicating whether PHP-FPM is healthy:

```json
{"status": "healthy"}
```

### 6. Stopping Services

To stop all services, use the following command:

```bash
docker-compose down
```

If you want to remove volumes and delete all persisted data, you can add the `--volumes` flag:

```bash
docker-compose down --volumes
```

### 7. Customization

- **Nginx Configuration**: The Nginx configuration is located in the `nginx.conf` file. You can adjust the server settings, add more locations, or modify headers based on your needs.
- **PHP Extensions**: Additional PHP extensions can be installed by modifying the `Dockerfile` and rebuilding the PHP container.
- **Error Pages**: Custom error pages are defined for 404 and 50x errors. You can customize them by editing the `404.html` and `50x.html` files in the `html` directory.

## Docker Compose Services

### Nginx

- Serves as the reverse proxy for the PHP application.
- Exposes port 80 to the host.
- Configured to process PHP files through PHP-FPM.

### PHP-FPM

- Runs PHP 8.4 and handles PHP processing.
- Exposes port 9000 for communication with Nginx.

### PostgreSQL (Optional)

- Runs PostgreSQL and is configured with environment variables for user, password, and database name.
- Persists data using a Docker volume.

## Conclusion

This setup provides a basic environment for running a PHP application with Nginx and PostgreSQL, making it easy to get started with containerized web applications. You can further extend this setup by adding more services, scaling the application, or integrating other components like Redis or a cache layer.

---

**Note**: When deploying to production, ensure all sensitive data such as database credentials are properly managed, and configuration files are appropriately secured.

```
