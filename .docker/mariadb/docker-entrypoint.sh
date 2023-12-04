#!/bin/bash

set -e

# Import SQL file
mysql -h localhost -u tdi -p "$MYSQL_PASSWORD" tdi < /docker-entrypoint-initdb.d/init.sql

# Start MariaDB
exec mysqld
