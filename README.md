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

## Deployment

When deploying to production:
1. Make sure `config.php` is properly configured with your production database credentials
2. Ensure proper file permissions are set
3. Consider implementing additional security measures for a production environment

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

Created by Weiyuan
