sudo apt-get remove mysql-common mysql-server-5.6 mysql-server-core-5.6 mysql-client-5.6 mysql-client-core-5.5
sudo apt-get autoremove
wget http://dev.mysql.com/get/mysql-apt-config_0.6.0-1_all.deb
sudo dpkg -i mysql-apt-config_0.6.0-1_all.deb
sudo apt-get update
sudo apt-get install mysql-server-5.7
sudo service mysql start