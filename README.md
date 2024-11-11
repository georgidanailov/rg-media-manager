# RG Media Manager Project Documentation

### 1. Backend (Symfony)
- **Service Name**: `backend`
- **Container Name**: `symfony_php`
- **Build Context**: `./backend`
- **Dockerfile**: `Dockerfile`
- **Environment**:
    - `APP_ENV=dev`: Set to `dev` for development; use `prod` for production.
    - `ELASTICSEARCH_HOST=http://elasticsearch:9200`: Elasticsearch is used for logging and monitoring activities.
- **Volumes**:
    - `./backend:/var/www/symfony`: Mounts the local backend directory.
    - `./php.ini:/etc/phpINI/php.ini`: Custom PHP configuration.
- **Networks**: `app_network` for intra-service communication.

The Symfony backend manages core functionalities like user management, file uploads, previews, etc. It interacts with Elasticsearch for activity logging and MySQL for data persistence.

---

### 2. Database (MySQL)
- **Service Name**: `database`
- **Image**: `mysql:8.0`
- **Environment**:
    - `MYSQL_ROOT_PASSWORD`: Set in the `.env` file.
    - `MYSQL_DATABASE`: Database name.
    - `MYSQL_PASSWORD`: Password for the MySQL user.
- **Ports**: Exposes MySQL on port `3307` (mapped to `3306` inside the container).
- **Volumes**: `database_data:/var/lib/mysql` to persist data.
- **Networks**: `app_network`.

MySQL is the primary database used to store application data.

---

### 3. Nginx for Symfony
- **Service Name**: `nginx`
- **Container Name**: `symfony_nginx`
- **Image**: `nginx:alpine`
- **Ports**: Exposes Nginx on port `9000`.
- **Volumes**:
    - `./nginx:/etc/nginx/conf.d`: Custom Nginx configuration files.
    - `./backend:/var/www/symfony`: The Symfony backend directory.
- **Depends On**: `backend`
- **Networks**: `app_network`.

Nginx serves as the reverse proxy for the Symfony backend, handling HTTP requests and serving static assets.

---

### 4. Frontend (React)
- **Service Name**: `frontend`
- **Container Name**: `react_frontend`
- **Build Context**: `./frontend`
- **Dockerfile**: `Dockerfile`
- **Ports**: Exposes React development server on port `3000`.
- **Volumes**:
    - `./frontend/src:/app/src`: Enables hot reload for `src` directory.
    - `./frontend/public:/app/public`: Enables hot reload for `public` directory.
- **Environment**:
    - `CHOKIDAR_USEPOLLING=true`: Ensures hot-reload inside Docker.
- **Networks**: `app_network`.

The React frontend provides the user interface, which interacts with the Symfony backend through API endpoints.

---

### 5. Mailer (Mailpit)
- **Service Name**: `mailer`
- **Image**: `axllent/mailpit`
- **Ports**:
    - SMTP on port `1025`.
    - Web UI on port `8025`.
- **Environment**:
    - `MP_SMTP_AUTH_ACCEPT_ANY=1`
    - `MP_SMTP_AUTH_ALLOW_INSECURE=1`
- **Networks**: `app_network`.

Mailpit handles email-related tasks such as sending registration confirmations and notifications.

---

### 6. ClamAV
- **Service Name**: `clamav`
- **Image**: `mkodockx/docker-clamav:alpine`
- **Ports**: Exposes ClamAV on port `3310`.
- **Volumes**:
    - `clamav_data:/var/lib/clamav`: Stores virus definitions.
- **Networks**: `app_network`.

ClamAV is used for virus scanning during file uploads, enhancing security by preventing malicious files from being uploaded.

---

### 7. Worker
- **Service Name**: `worker`
- **Image**: `rg-media-manager-backend:latest`
- **Container Name**: `symfony_worker`
- **Command**: `php bin/console messenger:consume async --time-limit=3600`
- **Depends On**: `backend`
- **Volumes**:
    - `./backend:/var/www/symfony`: Symfony backend directory.
- **Networks**: `app_network`.

The worker handles background tasks, such as media processing or notifications, managed by Symfony Messenger.

---

### 8. Elasticsearch
- **Service Name**: `elasticsearch`
- **Image**: `docker.elastic.co/elasticsearch/elasticsearch:7.17.10`
- **Environment**:
    - `discovery.type=single-node`
    - `xpack.security.enabled=false`
    - `ES_JAVA_OPTS=-Xms1g -Xmx1g`
- **Ports**: Exposes Elasticsearch on port `9200`.
- **Volumes**: `es_data:/usr/share/elasticsearch/data` to persist data.
- **Networks**: `app_network`.

Elasticsearch provides scalable logging and activity monitoring for the application.

---

## Environment Variables

Create a `.env` file with the following variables to configure the environment:

```env
MYSQL_ROOT_PASSWORD=<your-root-password>
MYSQL_DATABASE=<your-database-name>
MYSQL_PASSWORD=<your-database-password>
```
## Network

A single bridge network, `app_network`, connects all containers, enabling internal communication between services.

---

## Volumes

- **database_data**: Persists MySQL data.
- **clamav_data**: Persists ClamAV data.
- **es_data**: Persists Elasticsearch data.

---

## Usage

### Build and Run Containers:

```bash
docker-compose up --build
```

### Accessing Services

- **Backend (Symfony)**: [http://localhost:9000](http://localhost:9000)
- **Frontend (React)**: [http://localhost:3000](http://localhost:3000)
- **Mailpit Web UI**: [http://localhost:8025](http://localhost:8025)
- **Elasticsearch**: [http://localhost:9200](http://localhost:9200)
- **MySQL Database**: Host `localhost`, Port `3307`

### Stopping Containers

```bash
docker-compose down
```

## Additional Notes

- **Elasticsearch and Activity Logging**: Elasticsearch is integrated to handle application activity logging. This setup can be expanded with Kibana for visualization of logs and monitoring user activities.
- **File Upload and Virus Scanning**: ClamAV is configured to scan uploaded files for viruses, enhancing security by preventing malicious files from being stored in the application.
- **Worker for Background Jobs**: A Symfony Messenger worker manages background processing tasks, such as media processing, file scanning, and other asynchronous tasks required by the application.
