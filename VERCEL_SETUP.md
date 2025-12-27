# Vercel Deployment Instructions

This document provides detailed instructions for deploying TaskFlow Pro on Vercel while connecting to your remote MySQL database.

## Required Environment Variables

You **MUST** set these environment variables in your Vercel project settings:

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | Your MySQL server's IP address or hostname | `123.456.789.10` |
| `DB_USER` | MySQL username with remote access permissions | `wy` |
| `DB_PASSWORD` | Password for the MySQL user | `your_secure_password` |
| `DB_NAME` | Name of the database | `mydb1` |
| `DB_TABLE` | Name of the table | `todo_list` |

## Setting Environment Variables in Vercel

1. Go to your Vercel dashboard
2. Select your project
3. Click on "Settings" tab
4. Navigate to "Environment Variables" section
5. Add each of the variables listed above
6. Click "Save"
7. Redeploy your application

## MySQL Server Configuration

Your MySQL server must be configured to accept remote connections:

1. Edit your MySQL configuration file:
   ```bash
   sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
   ```

2. Change the bind-address line:
   ```
   # Change from
   bind-address = 127.0.0.1
   
   # To
   bind-address = 0.0.0.0
   ```

3. Create a MySQL user with remote access:
   ```sql
   CREATE USER 'username'@'%' IDENTIFIED BY 'password';
   GRANT ALL PRIVILEGES ON mydb1.* TO 'username'@'%';
   FLUSH PRIVILEGES;
   ```

4. Configure your firewall to allow connections on port 3306:
   ```bash
   sudo ufw allow 3306/tcp
   ```

5. Restart MySQL:
   ```bash
   sudo systemctl restart mysql
   ```

## Troubleshooting

If you see the error "Database connection failed: SQLSTATE[HY000] [2002] No such file or directory", it means:

1. The `DB_HOST` environment variable is not set or is set to "localhost"
2. Vercel cannot connect to your MySQL server

Common solutions:

- Make sure `DB_HOST` is set to your actual server IP address, not "localhost"
- Verify your MySQL server is accepting remote connections
- Check that your firewall allows connections on port 3306
- Confirm the MySQL user has permissions to connect from remote hosts

## Security Considerations

Exposing your MySQL server to the internet has security implications:

- Use strong, unique passwords
- Consider using a VPN or SSH tunnel for more security
- Set up a firewall to only allow connections from specific IP addresses
- Regularly update and patch your MySQL server
