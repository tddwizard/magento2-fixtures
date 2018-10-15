#!/usr/bin/env bash
set -e
set -x
export MODULE_DIR=`pwd`
export M2SETUP_DB_HOST=$MYSQL_CI_PORT_3306_TCP_ADDR
export M2SETUP_DB_USER=root
export M2SETUP_DB_PASSWORD=$MYSQL_CI_ENV_MYSQL_ROOT_PASSWORD
export TEST_DB_NAME=magento_integration_tests
mysqladmin -u$M2SETUP_DB_USER -p"$M2SETUP_DB_PASSWORD" -h$M2SETUP_DB_HOST create $TEST_DB_NAME
cp $MODULE_DIR/tests/phpunit.xml.dist /var/www/magento/dev/tests/integration/phpunit.xml
cp $MODULE_DIR/tests/install-config-mysql.php /var/www/magento/dev/tests/integration/etc/
sed -i -e "s/DB_HOST/$M2SETUP_DB_HOST/g" /var/www/magento/dev/tests/integration/etc/install-config-mysql.php
sed -i -e "s/DB_USER/$M2SETUP_DB_USER/g" /var/www/magento/dev/tests/integration/etc/install-config-mysql.php
sed -i -e "s/DB_PASSWORD/$M2SETUP_DB_PASSWORD/g" /var/www/magento/dev/tests/integration/etc/install-config-mysql.php
sed -i -e "s/DB_NAME/$TEST_DB_NAME/g" /var/www/magento/dev/tests/integration/etc/install-config-mysql.php
cd /var/www/magento/dev/tests/integration
php ../../../vendor/phpunit/phpunit/phpunit
