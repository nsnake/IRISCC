#!/bin/bash

# Auto install astercc package shell
# By Donnie #### du.donnie@gmail.com last modify 2008-11-09
# By Solo  #### solo@astercc.com last modify 2012-01-13

Usage()
{
    echo -e "Usage: `basename $0` [-dbu=] [-dbpw=] [-amiu=] [-amipw=] [-allbydefault] \n";
    exit 1;
}

#######################################################################
ARGS="$@"
curpath=`pwd`

echo "*****************************************************************"
echo "****************** Installing astercc package *******************"
echo "*****************************************************************"

dbuser='root'
dbpasswd=''
astserver='127.0.0.1'
amiport='5038'
amiuser='freeiris'
amisecret='freeiris'
mainpath='/var/www/html/astercc'
allbydefault=0

for ARG in $ARGS
do
	iparm=`echo $ARG |sed s/\-dbu\=//1`
	if [ "X$iparm" != "X$ARG" ];then
		if [ "X$iparm" != "X" ];then
			#echo "dbuser=$iparm"
			dbuser=$iparm
		fi
		continue
	fi
	iparm=`echo $ARG |sed s/\-dbpw\=//1`
	if [ "X$iparm" != "X$ARG" ];then
		if [ "X$iparm" != "X" ];then
			#echo "dbpasswd=$iparm"
			dbpasswd=$iparm
		fi
		continue
	fi
	#iparm=`echo $ARG |sed s/\-amiu\=//1`
	#if [ "X$iparm" != "X$ARG" ];then
	#	if [ "X$iparm" != "X" ];then
	#		#echo "amiu=$iparm"
	#		amiuser=$iparm
	#	fi
	#	continue
	#fi
	#iparm=`echo $ARG |sed s/\-amipw\=//1`
	#if [ "X$iparm" != "X$ARG" ];then
	#	if [ "X$iparm" != "X" ];then
	#		#echo "amipw=$iparm"
	#		amisecret=$iparm
	#	fi
	#	continue
	#fi
	iparm=`echo $ARG |sed s/\-allbydefault//1`
	if [ "X$iparm" != "X$ARG" ];then
		allbydefault=1
		continue
	fi
	echo "Incorrect parameter:$ARG";
	Usage
	exit;
done

if [ "$allbydefault" == "1" ];then
	dbhost='127.0.0.1'
	dbport='3306'
	dbname='astercc'
	dbbin='/usr/bin'
	astserver='127.0.0.1'
	amiport='5038'
	autocreatedb=1
	mainpath='/var/www/html/astercc'
	asterisketc='/etc/asterisk'
	autostartflag='y'
	startflag='y'
fi


#echo "${curpath}/astercrm"
#if [ ! -d "${curpath}/astercrm" ]
#then
#  echo -n 'astercrm directory not in here, are you sure contiue?(y/n):'
#  read ncrmflag
#  if [ "X${ncrmflag}" != "Xy" -a "X${ncrmflag}" != "XY" ]
#  then
#    exit
#  fi
#fi

#if [ ! -d "${curpath}/asterbilling" ]
#then
#  echo -n 'asterbilling directory not in here, are you sure contiue?(y/n):'
#  read nbillingflag
#  if [ "X${nbillingflag}" != "Xy" -a "X${nbillingflag}" != "XY" ]
#  then
#    exit
#  fi
#fi

#if [ ! -d "${curpath}/scripts" ]
#then
#  echo -n 'scripts directory is not found, are you sure contiue?(y/n):'
#  read nscriptflag
#  if [ "X${nscriptflag}" != "Xy" -a "X${nscriptflag}" != "XY" ]
#  then
#    exit
#  fi
#fi

if [ "X$dbhost" == "X" ];then
	echo Please enter database information
	echo -n "database host(default 127.0.0.1):"
	read dbhost
	if [ "X$dbhost" == "X" ];then
		dbhost='127.0.0.1'
	fi
else
	echo "database host is $dbhost";
fi


if [ "X$dbport" == "X" ];then
	echo -n "database port(default 3306):"
	read dbport
	if [ "X$dbport" == "X" ];then
		dbport='3306'
	fi
else
	echo "database port is $dbport";
fi

if [ "X$dbname" == "X" ];then
	echo -n "database name(default astercc):"
	read dbname
	if [ "X$dbname" == "X" ];then
		dbname='astercc'
	fi
else
	echo "database name is $dbname";
fi

if [ "X$dbuser" == "X" ];then
	echo Please enter database information
	echo -n "database user(default root):"
	read dbuser
	if [ "X$dbuser" == "X" ];then
		dbuser='root'
	fi
else
	echo "database user is $dbuser";
fi

if [ "X$dbpasswd" == "X" ];then
	echo Please enter database information
	echo -n "database password(default null):"
	read dbpasswd
else
	echo "database password is $dbpasswd";
fi

if [ "X$dbbin" == "X" ];then
	echo -n "mysql bin path(default /usr/bin):"
	read dbbin
	if [ "X$dbbin" == "X" ];then
		dbbin='/usr/bin'
	fi
else
	echo "mysql bin path is $dbbin";
fi

if [ "X${dbpasswd}" != "X" ];
then
  dbpasswdstr="-p"${dbpasswd}
fi


#${dbbin}/mysqladmin --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} ping
#
#if [ $? -ne 0 ]
#then
#  echo "database connection failed!"
#  exit
#fi

${dbbin}/mysqladmin --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} ping >${curpath}/db.test 2>&1
dbtest=`cat ${curpath}/db.test`

if [ "$dbtest" != "mysqld is alive" ]
then
  echo $dbtest
  echo "database connection failed!"
  exit
fi

/bin/rm -rf ${curpath}/db.test 2>&1

if [ "$autocreatedb" == "1" ];then
	${dbbin}/mysqladmin --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} drop ${dbname} -f 2&>/dev/null
	${dbbin}/mysqladmin --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} create ${dbname}
else
	echo "If database:'"${dbname}"' is not exists, press 'y' to create," && echo -n "else press 'n' to skip this step:"
	read dbexisist

	if [ "X${dbexisist}" == "Xy" -o "X${dbexisist}" == "XY" ]
	then
		${dbbin}/mysqladmin --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} create ${dbname}
	else
		echo "Warning: All data could be lost in "${dbname}" by next step," && echo -n "are you sure to continue?[y/n]:"
		read createTable

		if [ "X${createTable}" != "Xy" -a "X${createTable}" != "XY" ]
		then
			echo "User cancel"
			exit
		fi
	fi
fi

if [ $? -ne 0 ];
then
  echo "database operation failed!"
  exit
else
  ${dbbin}/mysql --host=${dbhost} --port=${dbport} -u${dbuser} ${dbpasswdstr} ${dbname} < $curpath/sql/astercc.sql
  if [ $? -ne 0 ];
  then
    exit;
  fi
fi


#if [ "X$astserver" == "X" ];then
#	echo "Please enter the Asterisk infomation:"
#	echo -n "Asterisk Host(default 127.0.0.1):"
#	read astserver
#	if [ "X${astserver}" == "X" ];
#	then
#	  astserver="127.0.0.1"
#	fi
#fi

#if [ "X$amiport" == "X" ];then
#	echo -n "Asterisk Manager API port(default 5038):"
#	read amiport
#	if [ "X${amiport}" == "X" ];
#	then
#	  amiport="5038"
#	fi
#fi


#if [ "X${amiuser}" == "X" ];then
#	while [ "X${amiuser}" == "X" ]
#	do
#	  echo -n "AMI User name:"
#	  read amiuser
#	  if [ "X${amiuser}" == "X" ]
#	  then
#		echo "error: AMI user name can not be blank"
#	  fi
#	done
#else
#	echo "AMI user is $amiuser";
#fi

#if [ "X${amisecret}" == "X" ];then
#	while [ "X${amisecret}" == "X" ]
#	do
#	  echo -n "AMI Secret:"
#	  read amisecret
#	  if [ "X${amisecret}" == "X" ]
#	  then
#		echo "error: AMI secret name can not be blank"
#	  fi
#	done
#else
#	echo "AMI Secret is $amisecret";
#fi

asterv="no"
#
#if [ -e "/var/run/asterisk/asterisk.ctl" ];then
#	asterv=`asterisk -rx "core show version"`
#	asterv=`echo $asterv |cut -f 2 -d\ `
#	asterv=`echo $asterv |cut -f 2 -d\.`
#else
#	echo -n "If your asterisk version is above 1.6, plese enter 'yes', default no":
#	read astervchoose
#	astervchoose=`echo $astervchoose |tr [:upper:] [:lower:]`
#	if [ "${astervchoose}" == "yes" ];then
#		asterv=6
#	else
#		asterv=0
#	fi
#
#fi
#
#if [ $asterv -ge 6 ];then
#  paramdelimiter=',';
#else
#  paramdelimiter='|';
#fi

####modify config file####
#for astercc.conf
sed -i '/\[database\]/,/\[asterisk\]/s/dbhost.*/dbhost = '${dbhost}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[database\]/,/\[asterisk\]/s/dbport.*/dbport = '${dbport}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[database\]/,/\[asterisk\]/s/dbname.*/dbname = '${dbname}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[database\]/,/\[asterisk\]/s/username.*/username = '${dbuser}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[database\]/,/\[asterisk\]/s/password.*/password = '${dbpasswd}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[asterisk\]/,/\[system]/s/server.*/server = '${astserver}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[asterisk\]/,/\[system]/s/port.*/port = '${amiport}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[asterisk\]/,/\[system]/s/username.*/username = '${amiuser}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[asterisk\]/,/\[system]/s/secret.*/secret = '${amisecret}'/1' ${curpath}/scripts/astercc.conf
sed -i '/\[asterisk\]/,/\[system]/s/paramdelimiter.*/paramdelimiter = '${paramdelimiter}'/1' ${curpath}/scripts/astercc.conf

#for astercrm.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/dbhost.*/dbhost = '${dbhost}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/dbname.*/dbname = '${dbname}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/username.*/username = '${dbuser}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/password.*/password = '${dbpasswd}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[asterisk\]/,/\[system]/s/server.*/server = '${astserver}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[asterisk\]/,/\[system]/s/port.*/port = '${amiport}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[asterisk\]/,/\[system]/s/username.*/username = '${amiuser}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[asterisk\]/,/\[system]/s/secret.*/secret = '${amisecret}'/1' ${curpath}/astercrm/astercrm.conf.php
sed -i '/\[asterisk\]/,/\[system]/s/paramdelimiter.*/paramdelimiter = '${paramdelimiter}'/1' ${curpath}/astercrm/astercrm.conf.php

#for asterbilling.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/dbhost.*/dbhost = '${dbhost}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/dbport.*/dbport = '${dbport}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/dbname.*/dbname = '${dbname}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/username.*/username = '${dbuser}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[database\]/,/\[asterisk\]/s/password.*/password = '${dbpasswd}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[asterisk\]/,/\[sipbuddy]/s/server.*/server = '${astserver}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[asterisk\]/,/\[sipbuddy]/s/port.*/port = '${amiport}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[asterisk\]/,/\[sipbuddy]/s/username.*/username = '${amiuser}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[asterisk\]/,/\[sipbuddy]/s/secret.*/secret = '${amisecret}'/1' ${curpath}/asterbilling/asterbilling.conf.php
sed -i '/\[asterisk\]/,/\[sipbuddy]/s/paramdelimiter.*/paramdelimiter = '${paramdelimiter}'/1' ${curpath}/asterbilling/asterbilling.conf.php

#if [ "X${mainpath}" == "X" ];then
#	echo Please enter install directory for astercc
#	echo -n "astercc directory(defalut /var/www/html/astercc):"
#	read mainpath
#
#	if [ "X${mainpath}" == "X" ];
#	then
#	  mainpath='/var/www/html/astercc'
#	fi
#fi
#echo "astercc install path is $mainpath"


mkdir -p ${mainpath}
cp -Rf ${curpath}/asterbilling ${mainpath}
cp -Rf ${curpath}/astercrm ${mainpath}
chmod -R 644 ${mainpath}

#change dir permissions.
for chpath in `find $mainpath -type d`
do
  chmod 755 ${chpath}
done

chmod -R 777 ${mainpath}/astercrm/upload
chmod 777 ${mainpath}/astercrm/astercrm.conf.php
chmod -R 777 ${mainpath}/asterbilling/upload

daemonpath=/opt/asterisk/scripts/astercc
mkdir -p ${daemonpath}
/bin/rm -rf ${daemonpath}/lib
bit=`getconf LONG_BIT`
if [ $bit == 32 ]; then
	cp -Rf ${curpath}/scripts/32/* ${daemonpath}
else
	cp -Rf ${curpath}/scripts/64/* ${daemonpath}
fi
cp -Rf ${curpath}/scripts/lib ${daemonpath}
cp ${curpath}/scripts/* ${daemonpath} -f  >/dev/null 2>&1

chmod +x ${daemonpath}/*

if [ -e "/var/lib/asterisk/agi-bin/astercrm.agi" ];then
	/bin/rm -f /var/lib/asterisk/agi-bin/astercrm.agi
fi
ln -s ${daemonpath}/astercrm.agi /var/lib/asterisk/agi-bin/astercrm.agi

if [ -e "/var/lib/asterisk/agi-bin/reselleroutbound.agi" ];then
	/bin/rm -f /var/lib/asterisk/agi-bin/reselleroutbound.agi
fi
ln -s ${daemonpath}/reselleroutbound.agi /var/lib/asterisk/agi-bin/reselleroutbound.agi

confmainpath=`echo ${mainpath} |sed  's/\//\\\\\//g'`
sed -i 's/my $conf_file =.*/my $conf_file ="'${confmainpath}'\/asterbilling\/asterbilling.conf.php";/' ${daemonpath}/reselleroutbound.agi


if [ -e "/var/lib/asterisk/agi-bin/lib" ];then
	/bin/rm -rf /var/lib/asterisk/agi-bin/lib
fi
ln -s ${daemonpath}/lib /var/lib/asterisk/agi-bin/lib

if [ "X${asterisketc}" == "X" ];then
	echo Please enter absolute path of asterisk etc
	echo -n "asterisk etc (default /etc/asterisk):"
	read asterisketc

	if [ "X${asterisketc}" == "X" ];
	then
	  asterisketc="/etc/asterisk"
	fi

	while [ 1 ]
	do
	  if [ ! -d "${asterisketc}/" ]
	  then
		echo "error: Can not found ${asterisketc}"
		echo -n "asterisk etc:"
		read asterisketc
	  else
	    break
	  fi
	done
fi
echo "asterisk etc path is $asterisketc"

touch ${asterisketc}/agents_astercc.conf
chmod 777 ${asterisketc}/agents_astercc.conf

if [ ! -f "${asterisketc}/agents.conf" ]
then
  cp -f ${curpath}/scripts/agents.conf ${asterisketc}
else
	if grep -q "^#include agents_astercc.conf" ${asterisketc}/agents.conf
	then
		echo ""
	else
		echo "#include agents_astercc.conf" >> ${asterisketc}/agents.conf
	fi
fi

touch ${asterisketc}/sip_astercc.conf


 cp -f ${curpath}/scripts/extensions_astercc.conf_1.6 ${asterisketc}/extensions_astercc.conf
 sed -i 's/DEADAGI/AGI/g' ${asterisketc}/extensions_astercc.conf


if grep -q "^#include sip_astercc.conf" ${asterisketc}/sip.conf
then
        echo ""
else
        echo "#include sip_astercc.conf" >> ${asterisketc}/sip.conf
fi

if grep -q "^#include extensions_astercc.conf" ${asterisketc}/extensions.conf
then
        echo ""
else
        echo "#include extensions_astercc.conf" >> ${asterisketc}/extensions.conf
fi


#echo "Do you want to auto convert wav monitor records to mp3 format every hour?"
#echo -n "Press 'y' to auto convert:"
#read monitorconvertflag

#if [ "X${monitorconvertflag}" == "Xy" -o "X${monitorconvertflag}" == "XY" ]
#then
  if [ ! -f "/usr/bin/lame" -a ! -f "/usr/local/bin/lame" ]
  then
    sed -i '/\[system\]/,/\[licence]/s/convert_mp3.*/convert_mp3 = '0'/1' ${daemonpath}/scripts/astercc.conf
    echo "Warning: can't locate command:lame in /usr/bin/ and /usr/local/bin/, please install"
  fi

  if [ ! -f "/usr/bin/sox" -a ! -f "/usr/local/bin/sox" -a ! -f "/usr/bin/soxmix" -a ! -f "/usr/local/bin/soxmix" ]
  then
    echo "Warning: can't locate command: 'sox' or 'soxmix' in /usr/bin/ and /usr/local/bin/ , please install"
  fi

  if [ -f "/etc/redhat-release" ]
  then
        echo "0 0 * * * ${daemonpath}/processmonitors.pl -d" >> /var/spool/cron/root
	echo "*/5 * * * * ${daemonpath}/processcdr.pl -d" >> /var/spool/cron/root
  else
        echo "0 0 * * * ${daemonpath}/processmonitors.pl -d" >> /var/spool/cron/crontabs/root
	echo "*/5 * * * * ${daemonpath}/processcdr.pl -d" >> /var/spool/cron/crontabs/root
        chown root:crontab /var/spool/cron/crontabs/root
        chmod 600 /var/spool/cron/crontabs/root
  fi
#fi

echo "*****************************************************************************"
echo "*******************astercc install finished**********************************"
echo "*****Your astercc web directory at ${mainpath}."
echo "*****Your astercc daemon directory at ${daemonpath}."
echo "*****Suggestion: Adjust your asterisk AMI user(manager.conf) :"
echo "*****set 'read = agent,call,system' for astercc running of greater efficiency"
echo "*****Note: write for AMI user must be 'all'"
echo "******************************************************************************"

if [ "X${autostartflag}" != "Xy" ];then
	echo "Do you want to auto start astercc daemon when system startup?"
	#echo "Must be redhat-release system"
	echo -n "Press 'y' to auto start:"
	read autostartflag
fi

if [ "X${autostartflag}" == "Xy" -o "X${autostartflag}" == "XY" ]
then
  if [ -f "/etc/redhat-release" ]
  then
        cp -f ${daemonpath}/asterccd /etc/rc.d/init.d
	chkconfig --add asterccd
  else
        echo "${daemonpath}/asterccd start" >> /etc/rc.local
  fi
fi


if [ "X${startflag}" != "Xy" ];
then
	echo "Do you want to start astercc daemon now?"
	echo -n "Press 'y' to start:"
	read startflag
fi

if [ "X${startflag}" == "Xy" -o "X${startflag}" == "XY" ]
then
  echo "starting asterccd..."
  /bin/bash ${daemonpath}/asterccd restart
fi

exit
