How to Set Up Locally

1. Clone the repository

git clone https://github.com/your-repo/mediaManager.git
cd mediaManager
2. Configure Environment Variables
You need to configure your .env.local file in the backend/ directory. Example:

bash
Copy code
DATABASE_URL="mysql://root:password@database:3308/RGMediaManager?serverVersion=8.0.32&charset=utf8mb4"

Make sure to setup your own details:
MySQL Database
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=RGMediaManager
MYSQL_USER=root
MYSQL_PASSWORD=password
Make sure the ports and credentials match those in docker-compose.yml.

3. Build and Run Docker Containers
Run the following command to build and start all the containers:

bash
Copy code
docker-compose up --build
4. Access the Services
Backend (Symfony): http://localhost:8000
Frontend (React): http://localhost:3000
Mailpit: http://localhost:8025
5. Database Persistence
The MySQL database is persisted using Docker volumes. This means that even if you stop or restart the containers, your data will be preserved.

Volume for MySQL data: database_data
To check if the database is persisting data, run:

bash
Copy code
docker volume inspect mediaManager_database_data
6. ClamAV Configuration
ClamAV is set to scan and update virus definitions. If you want to adjust the scanning interval and reduce log noise, you can update the clamd.conf and freshclam.conf files in the clamav/ directory.

Example freshclam.conf:
bash
Copy code
DatabaseDirectory /var/lib/clamav
UpdateLogFile /var/log/clamav/freshclam.log
Checks 1
DatabaseMirror database.clamav.net
Example clamd.conf:
bash
Copy code
DatabaseDirectory /var/lib/clamav
LogFile /var/log/clamav/clamd.log
SelfCheck 3600
ScanMail yes
ScanOLE2 yes
ScanPDF yes
ScanArchive yes
Common Docker Commands
Stop all services:

bash
Copy code
docker-compose down
Rebuild containers:

bash
Copy code
docker-compose up --build
View logs:

bash
Copy code
docker-compose logs -f
Troubleshooting
Nginx Errors
If you encounter Nginx configuration errors (e.g., related to worker_processes or user), ensure the nginx/default.conf file is properly configured. Nginx-specific directives like user should not be inside the conf.d/default.conf file.

ClamAV Issues
ClamAV may frequently attempt to update virus definitions. If SSL issues occur, consider updating your freshclam.conf to disable SSL verification:

bash
Copy code
DatabaseMirror database.clamav.net
DNSDatabaseInfo current.cvd.clamav.net