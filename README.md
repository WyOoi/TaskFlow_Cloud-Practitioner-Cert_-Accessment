# TaskFlow Pro

A simple and elegant task management system built with PHP and MySQL.

## Features

- Task management with priority levels
- Task completion tracking
- Responsive design
- Clean and modern UI

## Installation

### Prerequisites

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, etc.)

### Setup Instructions

1. Clone this repository to your web server:
   ```
   git clone https://github.com/yourusername/taskflow-pro.git
   ```

2. Create a configuration file:
   ```
   cp config.sample.php config.php
   ```

3. Edit the `config.php` file with your database credentials:
   ```php
   $user = "your_database_username";
   $password = "your_database_password";
   $database = "your_database_name";
   $table = "todo_list";
   ```

4. Make sure your web server has write permissions to the directory.

5. Access the application through your web browser.

## Database Setup

The application will automatically:
- Create the database if it doesn't exist
- Create the required table with the proper structure
- Add sample data if the table is empty

## Deployment to Vercel

This application can be deployed to Vercel using the following steps:

1. Ensure your project structure follows the Vercel PHP requirements:
   - Main PHP files should be in the `api` directory
   - A `vercel.json` file must be present in the root directory

2. Configure your database:
   - Your MySQL database should be accessible from the internet
   - Update your MySQL server configuration to allow remote connections
   - Open the necessary firewall ports (default MySQL port is 3306)

3. Set up environment variables in Vercel:
   - Go to your project settings in Vercel
   - Add the following environment variables:
     - `DB_HOST`: Your MySQL server hostname or IP address
     - `DB_USER`: Your database username
     - `DB_PASSWORD`: Your database password
     - `DB_NAME`: Your database name
     - `DB_TABLE`: The table name (default is 'todo_list')

4. Deploy to Vercel:
   ```
   vercel login
   vercel
   ```

5. Troubleshooting:
   - If you see a file download instead of the application, check that your `vercel.json` file is correctly configured
   - If you have database connection issues, verify that your MySQL server allows remote connections and that your environment variables are set correctly

## Standard Deployment

When deploying to a standard production environment:
1. Make sure `config.php` is properly configured with your production database credentials
2. Ensure proper file permissions are set
3. Consider implementing additional security measures for a production environment

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

Created by Weiyuan