#!/bin/bash
#
#****************************************************
#Author:           svenhe
#QQ:               137233130
#Filename:         startup.sh
#URL:              https://github.com/heshiweij/ChatMeteor
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

# check environment
php -v &> /dev/null; [ $? -ne 0 ] && echo -e echo -e "\033[31m Please install PHP7.0+ first \033[0m" && exit 1

swoole_major_version=`php -r 'echo SWOOLE_VERSION;'|egrep -o "[[:digit:]]+"|head -1`
[ ${swoole_major_version} -lt 2 ] && echo -e echo -e "\033[31m The current version of Swoole doesn\'t support it \033[0m" && exit 1

php --ri "swoole" |grep -q 'async redis client => enabled'

if [ $? -ne 0 ]; then
	echo -e echo -e "\033[31m You must install enable swoole async redis first! \033[0m"
	exit 1
fi

# check php version
php_major_version=`php -v|egrep -o "[[:digit:]]+"|head -1`

[ ${php_major_version} -lt 7 ] && echo -e echo -e "\033[31m The current version of PHP doesn\'t support it \033[0m" && exit 1

# check ip format
if [[ ! $1 =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
	echo -e "The IP  \033[31m address \033[0m is incorrect"
	exit 2
fi

# check port format
if [[ ! $2 =~ ^[0-9]{2,5}$ ]]; then
	echo -e "The \033[31m Port \033[0m is incorrect"
	exit 3
fi

# check port occupied
netstat -anp|grep -q $2 &> /dev/null

if [ $? -eq 0 ]; then
	echo -e "Port: \033[31m $2 \033[0m is already in use. "

	process_info=`netstat -anp|grep $2|grep LISTEN|head -1|tr -s " "|cut -d" " -f7`
	echo -e "The Pid/Process is: \033[31m $process_info \033[0m"
	echo ""

	read -p "Would you kill it and continue ? (Y/y/N/n)" is_kill

	if [[ ${is_kill} =~ ^[Yy] ]]; then
		pid=`echo ${process_info}|cut -d"/" -f1`
#		echo "pid" $pid
#		exit -1

		kill -9 $pid
	else
		echo "startup terminated!"
		exit 0;
	fi
fi

# check system log directory, if not exists, make it
system_log_dir='/var/log/chat-meteor/system'
if [ ! -d ${system_log_dir} ]; then
	mkdir -p ${system_log_dir}
fi

# start up by cpu nums
cpu_cores=`cat /proc/cpuinfo |grep 'cores'|cut -d" " -f3`
php ./server/server.php $1 $2 ${cpu_cores}

echo -e "Startup \033[32m success \033[0m & Program running in daemon..."