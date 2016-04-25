# SANS
Simple Automated Network Scanner

This app checks for open ports on a range of targets, if supported (FTP/SSH Only) it then downloads the associated service banner, and stores all data in the SQL database. Analytics are performed on the dataset and the results dashboard is accessable via web server.

# Features
 - Web Interface
 - Scheduled Scanning
 - Comparision with previous scan
 - Find potentially unwanted services
 - Find potentailly vulnerable services
 - Email Alerting

# Installation
This has been tested on Debian only.

1. Firstly download and install software

		cd /opt && git clone https://github.com/sysvar/sans.git && cd /opt/sans && chmod +x /opt/sans/setup.sh && /opt/sans/setup.sh

2. Add the password settings below to your web config file /etc/apache2/sites-available/000-default.conf under 'DocumentRoot /var/www/html'

		<Directory /var/www/html/sans/>
			AuthType Basic
			AuthName \"Password Protected Area\"
			AuthUserFile /opt/sans/htpasswd
			Require valid-user
		</Directory>
	
3. Configure a htpasswd in /opt/sans/htpasswd, the default one is sans:sans - [htpasswd generator](http://www.htaccesstools.com/htpasswd-generator) 

4. Set up the configuration in /opt/sans/sans.py to reflect your settings:

		# Troubleshooting
		webDashboardURL     = 'http://127.0.0.1/sans'
		webServerAddress    = '127.0.0.1'
		webServerPort       = 80
		internetCheck       = '216.58.208.142' # Google

		# MySQL Database
		mysqlServerIP       = '127.0.0.1'
		mysqlUser           = 'root'
		mysqlPass           = 'sans'
		mysqlDB             = 'sans'

		# Email Alerting
		emailServerIP       = 'smtp.example.com'
		emailServerPort     = 587
		emailServerUser     = 'sans@example.com'
		emailServerPass     = "sans"
		emailSenderEmail    = 'sans@example.com'
		emailRecipient      = 'sans@example.com'
		
5. Set up the website sql configuration in /var/www/html/sans/assets/inc/mysqli_connect.php

		DEFINE ('DB_USER', 'root');
		DEFINE ('DB_PASSWORD', 'sans');
		DEFINE ('DB_HOST', '127.0.0.1');
		DEFINE ('DB_NAME', 'sans');
	
6. Give apache a restart and you should be good to good

		service apache2 reload

# Usage
Use the web interface scanner page to start scanning and setup schedule.

# Usage Testing (with no database)
This just acts as a port scanner and banner grabber.

	python test.py -i <target IP(s)> -p <target port(s)> -t <timeout in seconds> -c <numbers of threads>
	python test.py -i 192.168.1.1 -p 21 -t 1 -c 1
	python test.py -i 192.168.1.1,192.168.1.2 -p 21,22 -t 0.3 -c 10
	python test.py -I hosts.txt -P ports.txt -t 3 -c 100

# Disclaimer 
Please take into account computer laws in your country before running SANS. Scanning with the intent to find and access vulenrable systems is illegal.