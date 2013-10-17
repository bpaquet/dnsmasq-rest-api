#!/bin/sh -e

php phpunit.phar --include-path='www' --colors tests "$@"