#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# mock mail
sudo service postfix stop
echo # print a newline
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# adjust memory limit
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

composer selfupdate --1

# clone main magento github repository
git clone --branch $MAGENTO_VERSION --depth=1 https://github.com/magento/magento2

# install Magento
cd magento2

# add composer package under test, composer require will trigger update/install
composer config minimum-stability dev
composer config repositories.travis_to_test git https://github.com/$TRAVIS_REPO_SLUG.git

if [ ! -z $TRAVIS_TAG  ]
then
    composer require ${COMPOSER_PACKAGE_NAME}:${TRAVIS_TAG}
elif [ ! -z $TRAVIS_PULL_REQUEST_BRANCH ]
then
    # For pull requests, use the remote repository
    composer config repositories.travis_to_test git https://github.com/${TRAVIS_PULL_REQUEST_SLUG}.git
    composer require ${COMPOSER_PACKAGE_NAME}:dev-${TRAVIS_PULL_REQUEST_BRANCH}\#${TRAVIS_PULL_REQUEST_SHA}
else
    composer require ${COMPOSER_PACKAGE_NAME}:dev-${TRAVIS_BRANCH}\#${TRAVIS_COMMIT}
fi

# prepare for test suite
case $TEST_SUITE in
    integration)
        cp vendor/$COMPOSER_PACKAGE_NAME/tests/phpunit.xml.dist dev/tests/integration/phpunit.xml

        cd dev/tests/integration

        # create database and move db config into place
        mysql -uroot -e '
            SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
            CREATE DATABASE magento_integration_tests;
        '
        cp etc/install-config-mysql.php.dist etc/install-config-mysql.php
        # Remove AMQP configuration
        sed -i '/amqp/d' etc/install-config-mysql.php
        # Remove default root password
        sed -i 's/123123q//' etc/install-config-mysql.php

        cd ../../..
        echo "Wait for ElasticSearch on port 9200..."
        curl -X GET localhost:9200/_cluster/health?wait_for_status=yellow&timeout=10s
    ;;
esac
