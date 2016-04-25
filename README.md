# SANS
Simple Automated Network Scanner

This app checks for open ports on a range of targets, if supported (FTP/SSH Only) it then downloads the associated service banner, and stores all data in the MySQL database. Analytics are performed on the dataset and the results dashboard is accessable via web server.

# Features
 - Web Interface
 - Seceduled Scanning
 - Comparision with previous scan
 - Find potentially unwanted services
 - Find potentailly vulnerable services
 - Email Alerting

# Installation
cd /opt && git clone https://github.com/sysvar/sans.git && cd /opt/sans && chmod +x /opt/sans/setup.sh && /opt/sans/setup.sh

then...
configure the sans.py configuration section for database, web and email.

# Usage
Use the web interface scanner page to start scanning and setup schedule.

# Usage Testing (No Database)
This just acts as a port scanner and banner grabber.

python test.py -i <target IP(s)> -p <target port(s)> -t <timeout in seconds> -c <numbers of threads>
python test.py -i 192.168.1.1 -p 21 -t 1 -c 1
python test.py -i 192.168.1.1,192.168.1.2 -p 21,22 -t 0.3 -c 10
python test.py -I hosts.txt -P ports.txt -t 3 -c 100

# Disclaimer 
Please take into account computer laws in your country before running SANS. Scanning with the intent to find and access vulenrable systems is illegal.