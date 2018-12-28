#!/bin/bash
#
#****************************************************
#Author:           svenhe
#QQ:               137233130
#Filename:         monitor.sh
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

# check process exists
ps -aux|egrep -q 'monitor.php\ [0-9]+$'

if [ $? -eq 0 ]; then
	echo "The monitor is running. Dont't Need run again "
	exit 1
fi

# check port format
if [[ ! $1 =~ ^[0-9]{2,5}$ ]]; then
	echo -e "The \033[31m Port \033[0m is incorrect"
	exit 2
fi

# check monitor log directory exists, if not, make it
monitor_log_dir='/var/log/chat-meteor/monitor'
if [ ! -d ${monitor_log_dir} ]; then
	mkdir -p ${monitor_log_dir}
fi


nohup php ./server/monitor.php $1 > /var/log/chat-meteor/monitor/`date +%F`.log  &


