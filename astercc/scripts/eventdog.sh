#!/bin/bash   
  #   singhead monitor 
  #     
  #        
  #   
  appname="/opt/asterisk/scripts/eventsdaemon/eventsdaemon.pl -d"
#/var/www/html/astercrm/eventsdaemon/eventsdaemon.pl
  
  while   [   1   ]   
  	do
  		sleep  3 
     	ps   -ef   |grep   -v   "grep"|grep   "$appname"   |awk   '{print   $2, $8, $9}' > check_file   
     	if   test   -s   check_file   
  			then   
					: 
  			else     
  				$appname 
  				if   [   $?   -ne   0   ]   
  					then   
  						date >>  monitor.log
  						echo   " 'date' Run   '$appname'   failed!"  >> monitor.log
  						break   
  					else   
  						date >> monitor.log
  						echo   "start   '$appname'   successed!" >> monitor.log   
  				fi   
  		fi   
  	done     

