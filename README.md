# sans
Simple Automated Network Scanner

This app checks for open ports on a range of targets, if supported it then downloads the associated service banner, and stores all data in the MySQL database. Analytics are performed on the dataset and the results dashboard is accessable via web server.

# Installation
mkdir -p /opt/sans
git clone https://github.com/sysvar/sans.git && chmod +x /opt/sans/setup.sh && sudo /opt/sans/setup.sh
