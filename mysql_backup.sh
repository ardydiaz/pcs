#!/bin/bash

DATE=$(date +%F)

PROJECT_DIR="/var/www/postclasssurvey.mcu.edu.ph"
DUMP_DIR="$PROJECT_DIR/mysql_dumps"

DB_NAME="pcs"
DB_USER="root"
DB_PASS="root"

mkdir -p $DUMP_DIR

mysqldump --no-tablespaces -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $DUMP_DIR/${DB_NAME}_$DATE.sql.gz

find $DUMP_DIR -type f -mtime +7 -delete
