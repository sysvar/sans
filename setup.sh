#!/bin/bash
printf  "\n"

# Make sure only root can run our script
if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

printf "\033[93m   SANS Setup Script \033[0m \n"
printf "\n"
printf "\033[91m   This script will install the following software \033[0m"
printf "
    - apache2
    - php5
    - mysql-server
    - phpmyadmin
    - python2.7
    - python-mysqldb
"
printf "\n"
printf "\033[91m   The web dashboard will be installed to /var/www/sans \033[0m \n"
printf "\n"
printf "\033[91m   The application will be installed to /opt/sans \033[0m \n"
printf "\n"
printf "\033[91m   A cron job will be installed to /etc/cron.d/sans \033[0m \n"
printf "\n"

# Check before continuing
read -n1 -r -p "Press SPACE or ENTER to continue otherwise press anything else to exit" key

if [ "$key" = '' ]; then
    printf "\033[92mSetup Initiated... \033[0m \n"
	apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xcbcb082a1bb943db
	apt-get update
	apt-get install software-properties-common -y
	add-apt-repository 'deb http://mirror.jmu.edu/pub/mariadb/repo/5.5/ubuntu trusty main'
	apt-get update
    apt-get install apache2 php5 python2.7 phpmyadmin mariadb-server python-mysqldb -y
	chown -R www-data:www-data /opt/sans
	chmod +x /opt/sans/schedule.sh
	chmod +x /opt/sans/sans.py
	touch /var/log/sans.log
	chown -R www-data:www-data /var/log/sans.log
	echo '0 0 * * *   root     /opt/sans/schedule.sh' > /etc/cron.d/sans
	mv /opt/sans/sans /var/www/html/
	sed -i 's:upload_max_filesize = 2M:upload_max_filesize = 10M:g' /etc/php5/apache2/php.ini
	printf "\n"
	
	printf "\033[92mDatabase password, take time to type correctly \033[0m \n"
	printf "DATABASE 1: Set a high number of database connections /n"
	printf "\n"
	mysql -u root -p -e 'set global max_connections = 9999;'
	printf "\n"
	printf "DATABASE 2: Create sans database /n"
	mysql -u root -p -e 'CREATE DATABASE sans;'
	printf "DATABASE 3: Import Database /n"
	mysql -u root -p sans < database.sql
	printf "\n"
	
	printf "\033[92m   MANUAL PART \033[0m \n"
	printf "\033[92m   1. Please add the settings below to this file /etc/apache2/sites-available/000-default.conf \033[0m \n"
	
	printf "
<Directory /var/www/html/sans/>
        AuthType Basic
        AuthName \"Password Protected Area\"
        AuthUserFile /opt/sans/htpasswd
        Require valid-user
</Directory>
"
	printf "\n"
	printf "\033[92m   2. Please configure htpasswd in /opt/sans/htpasswd - http://www.htaccesstools.com/htpasswd-generator \033[0m \n"
	
	printf "\n"
	printf "\033[92m   3. Set up the configuration in /opt/sans/sans.py (Database, Web, Email) \033[0m \n"

	printf "\n"
	printf "\033[92m   3. Set up the configuration in /var/www/html/sans/assets/inc/mysqli_connect.php (Web Dashboard) \033[0m \n"

	printf "\n"
	printf "\033[92m   Then issue the restart service command: \033[0m \n"
	printf "\n"
	printf "   service apache2 reload"
	printf "\n"

else
   printf "/n"
   exit 2
fi
