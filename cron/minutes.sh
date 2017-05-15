#!/bin/bash
cron_path="/home/www/api"
ProcessCount=`ps -ef|grep "algorithm:rebate" |grep -v "grep"|wc -l`
if [ $ProcessCount -lt 1 ]
then
nohup php ${cron_path}/artisan algorithm:rebate >> /dev/null 2>&1 &
echo "algorithm:rebate"
fi
