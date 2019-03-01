#!/usr/bin/env bash
set -e
set -x
echo memory_limit=-1 >> /usr/local/etc/php/php.ini
git checkout -b tmp
git add -A
git config --global user.email "wercker@localhost"
git config --global user.name "Wercker"
git commit --allow-empty -m "tmp"
export MODULE_DIR=`pwd`
export M2SETUP_DB_HOST=$MYSQL_CI_PORT_3306_TCP_ADDR
export M2SETUP_DB_USER=root
export M2SETUP_DB_PASSWORD=$MYSQL_CI_ENV_MYSQL_ROOT_PASSWORD
export M2SETUP_DB_NAME=magento
export M2SETUP_BASE_URL=http://m2.localhost:8000/
export M2SETUP_ADMIN_FIRSTNAME=Admin
export M2SETUP_ADMIN_LASTNAME=User
export M2SETUP_ADMIN_EMAIL=dummy@example.com
export M2SETUP_ADMIN_USER=magento2
export M2SETUP_ADMIN_PASSWORD=magento2
export M2SETUP_VERSION=$1
export M2SETUP_USE_SAMPLE_DATA=false
export M2SETUP_USE_ARCHIVE=true
export COMPOSER_HOME=$WERCKER_CACHE_DIR/composer
BIN_MAGENTO=magento-command

# Reconfigure composer after COMPOSER_HOME has been changed
[ ! -z "${COMPOSER_MAGENTO_USERNAME}" ] && \
    composer config -a -g http-basic.repo.magento.com $COMPOSER_MAGENTO_USERNAME $COMPOSER_MAGENTO_PASSWORD

mysqladmin -u$M2SETUP_DB_USER -p"$M2SETUP_DB_PASSWORD" -h$M2SETUP_DB_HOST create $M2SETUP_DB_NAME
DEBUG=true magento-installer
cd /var/www/magento
composer config repositories.module '{"type":"path", "url":"'$MODULE_DIR'", "options":{"symlink":false}}'
composer config minimum-stability dev
composer require tddwizard/magento2-fixtures dev-tmp
$BIN_MAGENTO module:enable TddWizard_Fixtures
