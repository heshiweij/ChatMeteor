#!/bin/bash
#
#****************************************************
#Author:           svenhe
#QQ:               137233130
#Filename:         startup.sh
#URL:              http://github.com/heshiweij/ChartEngine
#Description:      To startup Chat Engine server
#Copyright:        2018 All Right Reserved
#
#                        _ooOoo_
#                         o8888888o
#                         88" . "88
#                         (| -_- |)
#                         O\  =  /O
#                      ____/`---'\____
#                    .'  \\|     |//  `.
#                   /  \\|||  :  |||//  \
#                  /  _||||| -:- |||||-  \
#                  |   | \\\  -  /// |   |
#                  | \_|  ''\---/''  |   |
#                  \  .-\__  `-`  ___/-. /
#                ___`. .'  /--.--\  `. . __
#             ."" '<  `.___\_<|>_/___.'  >'"".
#            | | :  `- \`.;`\ _ /`;.`/ - ` : | |
#            \  \ `-.   \_ __\ /__ _/   .-` /  /
#       ======`-.____`-.___\_____/___.-`____.-'======
#                          `=---='
#       ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
#                    佛祖保佑        永无BUG
#****************************************************

# check php version
php_major_version=`php -v|egrep -o "[[:digit:]]+"|head -1`

[ $php_major_version -lt 7 ] && echo "The current version of PHP doesn\'t support it" && exit 1

# check ip format
if [[ ! $1 =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
	echo "The IP address is incorrect"
	exit 2
fi

# check port format
if [[ ! $2 =~ ^[0-9]{2,5}$ ]]; then
	echo "The Port is incorrect"
	exit 3
fi

# check port occupied
netstat -anp|grep -q $2 &> /dev/null

if [ $? -eq 0 ]; then
	echo "Port: $2 is already in use. "

	process_info=`netstat -anp|grep $2|head -1|tr -s " "|cut -d" " -f7`
	echo "The Pid/Process is: $process_info"
	echo ""

	read -p "Would you kill it and continue ? (Y/y/N/n)" is_kill

	if [[ $is_kill =~ ^[Yy] ]]; then
		pid=`echo $process_info|cut -d"/" -f1`
#		echo "pid" $pid
#		exit -1

		kill -9 $pid
	else
		echo "startup terminated!"
		exit 0;
	fi
fi


# start up by cpu nums
cpu_cores=`cat /proc/cpuinfo |grep 'cores'|cut -d" " -f3`
php ./server/server.php $1 $2 $cpu_cores