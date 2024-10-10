# **Media Manager Service**

The **Media Manager Service** is a web-based platform that allows users to upload, manage, and retrieve media files. This documentation will guide you through setting up and running the project in a local development environment.

## **Prerequisites**

To run the **Media Manager Service**, you need the following installed on your system:

- **PHP 8.2** or higher
- **Composer** (PHP Dependency Manager)
- **MySQL** or any supported relational database
- **OpenSSL** (for generating JWT keys)

## **Installation**

### **1. Clone the Repository**
First, clone the repository to your local machine:

```bash
git clone https://github.com/your-organization/media-manager.git
cd media-manager
```
### 2. Install PHP Dependencies
Run the following command to install all the necessary PHP dependencies using Composer:

```bash
composer install
```

### Configuration
1. Copy Environment Configuration
The project includes a .env file template that needs to be copied and adjusted for your local environment:

```bash
cp .env .env.local
```
2. Update .env.local
Open .env.local and update the following variables to match your local environment setup:

```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/media_manager"
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=your_secret_key
```

# JWT Configuration (replace with your generated key passphrase)
```bash
JWT_PASSPHRASE="your_passphrase"
```
3. Generate JWT Keys
The Media Manager uses JWT for authentication. You will need to generate a private and public key for this:

```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
When prompted, enter the same passphrase as the one used in your .env.local file for JWT_PASSPHRASE.

# Database Setup
1. Create the Database
Run the following command to create the database:

```bash
php bin/console doctrine:database:create
```
2. Run Migrations
Run the database migrations to set up the schema:

```bash
php bin/console doctrine:migrations:migrate
```
3. Load Initial Data
If there are fixtures or sample data available, load them using the following command:

```bash
php bin/console doctrine:fixtures:load
```
## Running the Application
1. Start the Symfony Server
To start the Symfony development server, run:

```bash
symfony server:start
```
Alternatively, you can use the PHP built-in server:

```bash
php -S localhost:8000 -t public/
```
## Testing
1. Running PHPUnit Tests
To run unit tests and other automated tests, use the following command:

```bash
php bin/phpunit
```

### API Documentation

API Documentation
This project uses NelmioApiDocBundle to generate API documentation in the OpenAPI format.

Viewing the API Documentation
To view the API documentation in your browser, simply visit the following URL after starting the Symfony server:

```bash
https://127.0.0.1:8000/api/doc
```