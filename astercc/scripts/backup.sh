#!/bin/bash
basepath=`dirname $0`
cd ${basepath}
thispath=`pwd`
thispath='/opt/asterisk/scripts/astercc/'

config=${thispath}"/backup.conf"  # if this run for crontab please assign absolute path for config
asterccconfig=${thispath}"/astercc.conf"
if [ "X$1" != "X" ];then
  config="$1"
fi

if [ ! -e ${config} ];then
  echo "can not find config file "${config}
  exit
fi

localpath=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep localpath[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
savelocal=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep savelocal[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
saveremote=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep saveremote[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
mailfrom=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep mailfrom[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
mailto=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep mailto[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
hostname=`sed -n '/\[local]/,/\[.*\]/p' ${config} |grep hostname[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`

ftphost=`sed -n '/\[ftp]/,/\[.*\]/p' ${config} |grep ftphost[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
ftpuser=`sed -n '/\[ftp]/,/\[.*\]/p' ${config} |grep ftpuser[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
ftppasswd=`sed -n '/\[ftp]/,/\[.*\]/p' ${config} |grep ftppasswd[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
ftpdbpath=`sed -n '/\[ftp]/,/\[.*\]/p' ${config} |grep ftpdbpath[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
ftpdirpath=`sed -n '/\[ftp]/,/\[.*\]/p' ${config} |grep ftpdirpath[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`

if [ "X${localpath}" == "X" ];then
  localpath=${thispath}
fi

dblocalpath=${localpath}/db_backup

if [ ! -d ${dblocalpath} ];then
  mkdir -p ${dblocalpath}
fi

dirlocalpath=${localpath}/dir_backup

if [ ! -d ${dirlocalpath} ];then
  mkdir -p ${dirlocalpath}
fi

if [ "X${ftpdbpath}" == "X" ];then
  ftpdbpath=db_backup
fi

if [ "X${ftpdirpath}" == "X" ];then
  ftpdirpath=dir_backup
fi

m=`echo ${saveremote} |tr -d [1-9]`
if [ "X${m}" == "X" -a "X${savelocal}" != "X" ];then
  rmbkdate=`date -d -${saveremote}day "+%Y-%m-%d"`
else
  rmbkdate=`date -d -5day "+%Y-%m-%d"`
fi

if [ "X${hostname}" == "X" ];then
hostname=`hostname`
fi

filedate=`date +"%Y-%m-%d-%H"`

### for dir ###

dircount=0

for dirinfo in `sed -n '/\[dirlist]/,/\[.*\]/p' ${config} |grep -v "\[.*\]" |grep -v "^\ *#"`
do
  let dircount=dircount+1
  dirpath=`echo $dirinfo |cut -d: -f1`
  dstfile=`echo $dirinfo |cut -d: -f2`
  if [ "X${dstfile}" == "X${dirpath}" ];then
    dstfile=`basename ${dirpath}`
  fi
  if [ -e ${dirpath} ];then
    tar czPf "${dirlocalpath}/${dstfile}.${filedate}.tar.gz" ${dirpath}
  fi
done


### for database ###

errortip=none
mailcontent=""
dbcount=0
dberrorcount=0

for dbinfo in `sed -n '/\[dblist]/,/\[.*\]/p' ${config} |grep -v "\[.*\]" |grep -v "^\ *#"`
do
 let dbcount=dbcount+1
 logbktime=`date "+%Y-%m-%d %H:%M:%S"`
 dbname=`echo $dbinfo |cut -d: -f1`
 dbuser=`echo $dbinfo |cut -d: -f2`
 dbpasswd=`echo $dbinfo |cut -d: -f3`

 if [ "X${dbuser}" == "X" ];then
  dbuser="root"
 fi

 if [ -e ${dblocalpath}/curremotedbfile ];then
  rmfilename=`cat ${dblocalpath}/curremotedbfile |grep ${dbname}"."${rmbkdate} |awk '{print $9}'`
  if [ "X${rmfilename}" != "X" ];then
ftp -n -i "${ftphost}" <<CONTENT
user "${ftpuser}" "${ftppasswd}"
binary
mkdir ${ftpdbpath}
cd ${ftpdbpath}
delete ${rmfilename}
dir *.tar.gz "${dblocalpath}/curremotedbfile"
bye
CONTENT
  fi
 fi

 dst=${dblocalpath}/${dbname}"."${filedate}.sql

 if [ "$dbpasswd" == "" ]; then
   echo "exporting database: ${dbname}..."
   mysqldump --opt -Q -u$dbuser $dbname > ${dst}
   if [ $? -ne 0 ];then
     let dberrorcount=dberrorcount+1
     errortip="have ${dberrorcount} error when backup databases please check dbbk.log!!!"
     echo "connect database: ${dbname} error at ${logbktime} " >> ${dblocalpath}/dbbk.log
   else
     mailcontent="${mailcontent}|${dbname}|"
   fi
 else
   echo "exporting database: ${dbname}..."
   mysqldump --opt -Q -u$dbuser -p$dbpasswd $dbname > ${dst}
   if [ $? -ne 0 ];then
     let dberrorcount=dberrorcount+1
     errortip="have ${dberrorcount} error when backup databases please check dbbk.log!!!"
     echo "connect database: ${dbname} error at ${logbktime} " >> ${dblocalpath}/dbbk.log
   else
     mailcontent="${mailcontent}|${dbname}|"
   fi
 fi

 tarsrc=${dbname}"."${filedate}.sql
 tardst=${dbname}"."${filedate}.tar.gz

 if [ -e ${dst} ];then
   cd ${dblocalpath}
   tar czPf ${tardst} ${tarsrc}
   rm -f ${dst}
 fi

 echo 'back up '$dbname' at '$logbktime >> ${dblocalpath}/dbbk.log

unset dst
done

### ftp transfer ###

if [ "X${ftphost}" != "X" -a ${dberrorcount} -lt ${dbcount} -a ${dbcount} -gt 0 ];then
echo "ftp transfering"
ftp -n -i "${ftphost}" <<CONTENT
user "${ftpuser}" "${ftppasswd}"
binary
mkdir ${ftpdbpath}
cd ${ftpdbpath}
lcd "${dblocalpath}"
mput *.tar.gz
dir *.tar.gz "curremotedbfile"
bye
CONTENT
mailcontent="${mailcontent} on ${hostname}, storage to ftp://${ftphost} in dir ${ftpdbpath}"
fi

if [ "X${ftphost}" != "X"  -a ${dircount} -gt 0 ];then
echo "ftp transfering"
ftp -n -i "${ftphost}" <<CONTENT
user "${ftpuser}" "${ftppasswd}"
binary
mkdir ${ftpdirpath}
cd ${ftpdirpath}
lcd "${dirlocalpath}"
mput *.tar.gz
dir *.tar.gz "curremotedirfile"
bye
CONTENT
fi

### local file ###
#dir
if [ ${dircount} -gt 0 ];then
  if [ ! -d "${dirlocalpath}/localsave" ];then
    mkdir -p ${dirlocalpath}/localsave
  fi
  rm -f ${dirlocalpath}/localsave/*.tar.gz
  mv ${dirlocalpath}/*.tar.gz ${dirlocalpath}/localsave
fi

#database
if [ ${dbcount} -gt 0 ];then
  if [ ! -d "${dblocalpath}/localsave" ];then
    mkdir -p ${dblocalpath}/localsave
  fi

  if [ "${savelocal}" == "no" -o "${savelocal}" == "NO" ];then
    curcount=`ls ${dblocalpath}/localsave/ |wc -l`
    if [ $curcount -gt 0 ];then
      rm -f ${dblocalpath}/localsave/*.tar.gz
    fi
  else
    n=`echo ${savelocal} |tr -d [1-9]`
    if [ "X${n}" == "X" -a "X${savelocal}" != "X" ];then
      oldbkdate=`date -d -${savelocal}day "+%Y-%m-%d"`
      rm -f ${dblocalpath}/localsave/*.${oldbkdate}*.tar.gz
    fi
  fi
  mv ${dblocalpath}/*.tar.gz ${dblocalpath}/localsave
fi

if [ ! -e ${asterccconfig} ];then
  echo "can not find asterccconfig file "${asterccconfig}
else
   startTime=`date "+%Y-%m-%d %H:%M:%S"`
   echo "Start to delete historycdr and mycdr...... at ${startTime}" >> ${dblocalpath}/db_cdr_delete.log

   keep_cdr_days=`sed -n '/\[system]/,/\[.*\]/p' ${asterccconfig} |grep keep_cdr_days[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
   dbhost=`sed -n '/\[database]/,/\[.*\]/p' ${asterccconfig} |grep dbhost[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
   dbname=`sed -n '/\[database]/,/\[.*\]/p' ${asterccconfig} |grep dbname[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
   dbport=`sed -n '/\[database]/,/\[.*\]/p' ${asterccconfig} |grep dbport[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
   username=`sed -n '/\[database]/,/\[.*\]/p' ${asterccconfig} |grep username[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`
   password=`sed -n '/\[database]/,/\[.*\]/p' ${asterccconfig} |grep password[\ ]*= |grep -v "^\ *#" |cut -d= -f2 |tr -d " "`

   if [ "X${password}" == "X" ];then
      if [ "X${dbport}" == "X" ];then
        mysql -h$dbhost -u$username -e "USE ${dbname};DELETE FROM mycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
        mysql -h$dbhost -u$username -e "USE ${dbname};DELETE FROM historycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
      else
        mysql -h$dbhost -u$username --port=$dbport -e "USE ${dbname};DELETE FROM mycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
        mysql -h$dbhost -u$username --port=$dbport -e "USE ${dbname};DELETE FROM historycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
      fi
   else
      if [ "X${dbport}" == "X" ];then
        mysql -h$dbhost -u$username -p$password -e "USE ${dbname};DELETE FROM mycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
        mysql -h$dbhost -u$username -p$password -e "USE ${dbname};DELETE FROM historycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
      else
        mysql -h$dbhost -u$username -p$password --port=$dbport -e "USE ${dbname};DELETE FROM mycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
        mysql -h$dbhost -u$username -p$password --port=$dbport -e "USE ${dbname};DELETE FROM historycdr WHERE calldate < now() - INTERVAL ${keep_cdr_days} DAY ;"
      fi
   fi
fi
endTime=`date "+%Y-%m-%d %H:%M:%S"`
echo "delete historycdr and mycdr END at ${endTime}" >> ${dblocalpath}/db_cdr_delete.log



curremotefile=""
if [ -e ${dblocalpath}/curremotedbfile ];then
  curremotefile=`cat ${dblocalpath}/curremotedbfile`
  if [ "X${curremotefile}" == "X" ];then
    curremotefile="no file storage in ftp://${ftphost}"
  fi
else
  curremotefile="no file storage in ftp://${ftphost}"
fi

if [ "X${mailto}" != "X" ];then
echo "sending mail"
/usr/sbin/sendmail -t <<EOF
From: backup shell <${mailfrom}> 
To: <${mailto}>
Subject: backup notice
----------------------------------
misson:
backup databases: ${mailcontent} at ${logbktime}

error:
${errortip}

remote file list at ftp://${ftphost}:
${curremotefile}
----------------------------------
EOF
fi

exit

