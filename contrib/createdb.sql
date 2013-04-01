################################################################################################
#                              DATABASE说明
#				freeiris2
################################################################################################

#
# 管理员表
#
CREATE TABLE IF NOT EXISTS admin (
  adminid varchar(80) NOT NULL,
  passwd varchar(80) NOT NULL,
  remark varchar(255) NOT NULL default '',
  level varchar(255) NOT NULL default '4',
  cretime datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (adminid)
);


#
# 号码记录表
#
CREATE TABLE IF NOT EXISTS localnumber (
number varchar(32) NOT NULL ,	#号码
typeof varchar(255) NOT NULL default '',	#号码类型,'extension','trunk','ivr','conference','queue','agi',其他
assign varchar(255) NOT NULL,		#号码关联的号,可能是accountcode也可能是trunkid或其他的什么的,如果是app模式这里是被呼叫的资源
PRIMARY KEY (number,assign)
);

#
# 用户主表
#
CREATE TABLE IF NOT EXISTS extension (
accountcode varchar(20) NOT NULL PRIMARY KEY,	#用户主帐号
cretime datetime,				#创建日期
password varchar(32) NOT NULL default '',	#用户的密码，纯数字
deviceproto varchar(32) NOT NULL,		#分机类型，'sip'--SIP分机,'iax2','fxs','custom','virtual'
devicenumber varchar(32) NOT NULL,		#这个分机号码
devicestring varchar(32) NOT NULL,		#这个分机对应的设备名称,比如sip可能是8000如果是dahdi可能是12-1
fristchecked int(1) NOT NULL default '0',	#是否经过分机测试0未测试,1测试不完整,2测试完成
transfernumber varchar(32) NOT NULL default '',	#呼叫转移的号码,可以为空
diallocal_failed varchar(64) NOT NULL default '', #号码呼叫失败后的处理方法,空表示使用系统设置,有内容则匹配实际方式
info_name varchar(100) NOT NULL default '',	#用户名称
info_email  varchar(30),                        #用户Email
info_detail text,				#详细资料
info_remark text,				#管理员注释信息
INDEX devicenumber (devicenumber)
);

#
# 用户分机分组表
#
CREATE TABLE IF NOT EXISTS extengroup (
groupid int(4) NOT NULL PRIMARY KEY,		#分组编号(暂时分组编号只能为0-63里的一个数字,随机产生)
groupname varchar(20) NOT NULL,			#分组名称
remark text,					#分组备注
cretime datetime				#创建日期
);

#
# 用户分组对应表
#
CREATE TABLE IF NOT EXISTS extengroup_assign (
groupid int(4) NOT NULL,			#分组编号
accountcode varchar(20) NOT NULL,		#用户主帐号
PRIMARY KEY (`groupid`,`accountcode`)
);

#
# 外线主表
#
CREATE TABLE IF NOT EXISTS trunk (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
trunkname varchar(255) NOT NULL ,	#中继名称
trunkproto varchar(32) NOT NULL,	#中继协议:sip,iax2,custom,dahdi
trunkprototype varchar(32) NOT NULL,	#具体类型:sip: ip,reg,iad iax2: ip,reg dahdi: fxo,isdn-pri custom: custom
trunkdevice varchar(100) NOT NULL,	#中继设备,如果是dahdi可能是R0-63名称，如果是sip可能是itsp这种字母
trunkremark text,			#备注
cretime datetime,			#创建日期
UNIQUE KEY (`trunkname`)
);

#
# 呼叫路由表(包括用户和外线的路由)
#
CREATE TABLE IF NOT EXISTS router (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
proirety INT(12) NOT NULL default '0',		#优先级别,数字越大级别越高
createmode int(1) NOT NULL default '0',		#创建方式,0表示自动创建,1表示人工创建,2表示无法手动删除(优先级别是最低的)
routerline INT(1) NOT NULL default '0',		#路由模型,0未分配,1内线路由,2外线路由
routername varchar(255) NOT NULL default '',	#路由本条规则的名称
optextra varchar(255) NULL,			#扩展参数,特别作用
lastwhendone INT(1) NOT NULL default '0',		#本条匹配执行完成后是否匹配下一条，默认是0不，1表示是
match_callergroup varchar(255) NOT NULL default '',	#主叫分组匹配(仅是主叫为内线用户时有效),当为外线的时候这个地方是主叫来自中继表的id号
match_callerid varchar(255) NOT NULL default '',	#主叫号码匹配
match_callerlen varchar(255) NOT NULL default '',	#主叫号码长度匹配
match_callednum varchar(255) NOT NULL default '',	#被叫号码匹配
match_calledlen varchar(255) NOT NULL default '',	#被叫号码长度匹配
replace_callerid varchar(255) NOT NULL default '',	#匹配后主叫替换
replace_calledtrim varchar(255) NOT NULL default '',	#匹配后删除被叫前几位
replace_calledappend varchar(255) NOT NULL default '',	#匹配后补充被叫前几位
process_mode int(4) NOT NULL default '0',		#处理方式,0表示拒绝,1表示本地处理,2表示通路
process_defined varchar(255) NOT NULL default ''	#方式2时表示通过那条中继送出通路,1时如果为空表示任何本地设备或设备的typeof
);

#------------------------------------------
#
# 呼叫计费费率表
#
# destnation 说明
# dst_prefix 前缀
# persecond 时间单位
# percost 单价
#
CREATE TABLE IF NOT EXISTS billingrate (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
destnation varchar(64) NOT NULL default '',
dst_prefix varchar(64) NOT NULL,
persecond int(12) NOT NULL default '60',
percost double(24,4) NOT NULL default '0.000000',
INDEX dst_prefix (dst_prefix)
);


#
# 呼叫计费记录表
#
# cdrid 关联cdr号
# accountcode 关联的计费帐户(有可能是trunk或exten)
# cretime 计费产生时间
# calldate 通话记录时间
# billsec 计费时长
# persecond 时间单位
# percost 单价
# cost 费用
#
CREATE TABLE IF NOT EXISTS billinginvoice (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
cdrid int(11) NOT NULL,
cretime datetime NOT NULL,
calldate datetime NOT NULL, 
accountcode varchar(255),
src varchar(80) NOT NULL default '',
dst varchar(80) NOT NULL default '',
billsec int(10) NOT NULL,
billroundsec int(10) NOT NULL,
persecond int(12) NOT NULL,
percost double(24,6) NOT NULL,
cost double(24,6) NOT NULL,
INDEX accountcode (accountcode),
INDEX cdrid (cdrid),
INDEX calldate (calldate)
);
# 存储12个月前备份数据的表
CREATE TABLE IF NOT EXISTS billinginvoice_history (
id INT(12) NOT NULL PRIMARY KEY,
cdrid int(11) NOT NULL,
cretime datetime NOT NULL,
calldate datetime NOT NULL, 
accountcode varchar(255),
src varchar(80) NOT NULL default '',
dst varchar(80) NOT NULL default '',
billsec int(10) NOT NULL,
billroundsec int(10) NOT NULL,
persecond int(12) NOT NULL,
percost double(24,6) NOT NULL,
cost double(24,6) NOT NULL
);


#------------------------------------------


#------------------------------------------
#
# Voicefiles
#
# filename文件名称
# extname扩展名(文件类型)
# folder相关目录
# cretime时间
# description说明
# label类型,'sound','moh','voicemail','onetouch','ivrmenu','sysautomon'
# associate关联:  不同类型作用不同: voicemail,onetouch,ivrmenu,sysautomon将存储callsessionid。'sound'存储language。'moh'存储class
# args参数: 不同类型作用不同
# readonly 0表示可以删除可以编辑,1不可以删除可以编辑
# mailprocessed 0表示未发送,1表示发送了
#
# 实际存储全路径由程序解析数据库中不保存
#
CREATE TABLE IF NOT EXISTS voicefiles (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
filename varchar(150) NOT NULL,
extname varchar(150) NOT NULL default '',
folder varchar(150) NOT NULL default '',
cretime datetime,
description varchar(255) NOT NULL default '',
label varchar(32) NOT NULL,
associate varchar(255) NOT NULL default '',
args varchar(255) NOT NULL default '',
readonly int(1) NOT NULL default '0',
mailprocessed int(1) NOT NULL default '0',
INDEX cretime (cretime),
INDEX mailprocessed (mailprocessed),
UNIQUE KEY (label,folder,filename)
);

#
# sysautomontrigger
#
# triggername 触发器名称
# recordout 1开启拨出者触发录音
# recordin 1开启接听者触发录音
# recordqueue 1开启接听者触发录音(队列)
# keepfortype 0表示保存总量,1表示保存天数,2表示永久保存
# keepforargs 保存参数
# members 分机的号码,以&做间隔,不可为空
#
CREATE TABLE IF NOT EXISTS sysautomontrigger (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
triggername varchar(255) NOT NULL,
recordout int(1) NOT NULL default '0',
recordin int(1) NOT NULL default '0',
recordqueue int(1) NOT NULL default '0',
keepfortype int(1) NOT NULL default '0',
keepforargs varchar(255) NOT NULL default '',
members text NOT NULL,
cretime datetime,
INDEX recordout (recordout),
INDEX recordin (recordin),
INDEX recordqueue (recordqueue),
INDEX cretime (cretime)
);
#------------------------------------------

#------------------------------------------
#
# 电话会议室
#
# confno 会议室号码
# pincode 密码
# playwhenevent 如果有人近来和出去是否播放,1表示播放
# mohwhenonlyone 如果只有一个人是否播放音乐,1播放
#
CREATE TABLE IF NOT EXISTS conference (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
confno varchar(32) NOT NULL,
pincode varchar(32) NOT NULL default '',
playwhenevent int(1) NOT NULL default '0',
mohwhenonlyone int(1) NOT NULL default '0',
cretime datetime NOT NULL default '0000-00-00 00:00:00',
UNIQUE KEY (confno)
);
#------------------------------------------

#------------------------------------------
#
# Queue呼叫队列基本信息
# queuenumber 队列号码(同queue.conf相同)
# queuename 队列名称
# announce 播放的语音文件,空表示不播放
# playring 播放ring还是moh  0表示moh  1表示ring
# saymember 向用户读出member号码,0表示不读,1表示读
# queuetimeout 队列有效时间
# failedon 失败后条转的分机号码
# members 队列成员的号码,名字用&间隔
#
CREATE TABLE IF NOT EXISTS queue (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
queuenumber varchar(32) NOT NULL,
queuename varchar(255) NOT NULL,
announce varchar(255) NOT NULL default '',
playring int(1) NOT NULL default '0',
saymember int(1) NOT NULL default '0',
queuetimeout int(11) NOT NULL default '300',
failedon varchar(255) NOT NULL,
members text NOT NULL,
cretime datetime NOT NULL default '0000-00-00 00:00:00',
UNIQUE KEY (queuenumber)
);
#------------------------------------------


#------------------------------------------
#
# IVR系统表
# ivrnumber IVR的号码
# ivrname IVR的名字
# description 描述说明
# cretime
# readonly 1不可以删除,其他可以删除
#
CREATE TABLE IF NOT EXISTS ivrmenu (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
ivrnumber varchar(32) NOT NULL,
ivrname varchar(255) NOT NULL,
description text NOT NULL default '',
cretime datetime NOT NULL default '0000-00-00 00:00:00',
readonly int(1) NOT NULL default '0',
UNIQUE KEY (ivrnumber)
);

#
# IVR动作流程表
# id 这个动作的id
# ivrnumber ivr主id
# ordinal 动作的执行顺序,数字越大执行的越晚
# actmode 动作类型:
#     10    播放语音
#     11    发起录音
#     12    播放录音
#     20    录制0-9字符
#     21    读出0-9字符
#     22    数字方式读出
#     30    读出日期时间
#     31    检测日期
#     40    主叫变换
#     41    拨打本地号码
#     42    跳转到语音信箱
#     43    跳转到IVR菜单
#     80    等待几秒
#     81    播放音调
#     99    挂机
# args 动作的配置参数，内容为key=value以&做分割
#
#
CREATE TABLE IF NOT EXISTS ivraction (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
ivrnumber varchar(32) NOT NULL,
ordinal INT(12) NOT NULL default '0',
actmode int(2) NOT NULL,
args text NOT NULL default ''
);

#
# IVR等待用户输入表
# ivrid ivr主id
# general 是否默认必须存在的等待输入,1表示是
# general_type 当为default模式的时候有效,字符invalid表示无效时,timeout表示输入超时,retry表示重复
# general_args 默认的时候之参数
# input 数字0-9,*表示选择,
# gotoivrnumber 跳到哪个ivr的ivrnumber
# gotoivractid 跳到什么位置,空表示默认
#
CREATE TABLE IF NOT EXISTS ivruserinput (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
ivrnumber varchar(32) NOT NULL,
general int(1) NOT NULL default '0',
general_type varchar(255) NOT NULL default '',
general_args varchar(255) NOT NULL default '',
input varchar(32) NOT NULL default '',
gotoivrnumber varchar(32) NOT NULL,
gotoivractid varchar(12) NOT NULL default '',
INDEX ivrnumber (ivrnumber)
);
#------------------------------------------

#------------------------------------------
#
# 自动外呼表
# name 外呼名称
# concurrent 每次并发
# outgoing_callerid 以什么主叫号码发起呼叫
# members 外呼的号码,以&做间隔,不可为空
# members_called 已经完成的记录
# cretime 创建时间
# startime 开始时间
# called 已完成呼叫量
# localnumber 接通后连接本地号码
# tune 0未开始或处理中1完成
#
#CREATE TABLE IF NOT EXISTS outgoing (
#id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
#name varchar(255) NOT NULL,
#concurrent int(4) NOT NULL default '1',
#outgoing_callerid varchar(32) NOT NULL default '',
#outgoing_waittime int(10) NOT NULL default '30',
#members text NOT NULL default '',
#members_called text NOT NULL default '',
#cretime datetime NOT NULL default '0000-00-00 00:00:00',
#startime datetime NOT NULL,
#called int(10) NOT NULL default '0',
#localnumber varchar(32) NOT NULL,
#tune int(1) NOT NULL default '0',
#INDEX startime (startime),
#INDEX tune (tune)
#);
#
# name 外呼名称
# concurrent 每次并发
# outgoing_callerid 以什么主叫号码发起呼叫
# outgoing_waittime 外呼等待时长
# numbercount 号码总量
# calledcount 完成总量
# cretime 创建时间
# startime 开始时间
# localnumber 接通后连接本地号码
# tune 0新记录,1完成,2处理中
CREATE TABLE IF NOT EXISTS outgoing (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
name varchar(255) NOT NULL,
concurrent int(4) NOT NULL default '1',
outgoing_callerid varchar(32) NOT NULL default '',
outgoing_waittime int(10) NOT NULL default '30',
numbercount int(10) NOT NULL,
calledcount int(10) NOT NULL default '0',
cretime datetime NOT NULL default '0000-00-00 00:00:00',
startime datetime NOT NULL,
localnumber varchar(32) NOT NULL,
tune int(1) NOT NULL default '0',
INDEX startime (startime),
INDEX tune (tune)
);

# id 序列号
# outgoingid 对应outgoing序列号
# number 外呼的号码
# status 处理状态,0未处理,1已呼叫,2已接起
CREATE TABLE IF NOT EXISTS outgoing_members (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
outgoingid INT(12) NOT NULL,
number varchar(64) NOT NULL,
status int(1) NOT NULL default '0',
INDEX status (status),
INDEX id (id)
);
#------------------------------------------

#------------------------------------------
#
#
# callsession处理流程表
#
#
#CREATE TABLE IF NOT EXISTS callsession (
#  id varchar(64) NOT NULL PRIMARY KEY,		#呼叫主标识
#  accountcode varchar(20) NOT NULL default '',	#帐号
#  routerline INT(1) NOT NULL default '0',	#路由模型,0未分配,1内线路由,2外线路由
#  actions text NOT NULL default '',		#呼叫动作，动作之间用&做分割，以HTTP GET做解码
#  returns text NOT NULL default '',		#呼叫输出变量，数据之间用&做分割，以HTTP GET做解码
#  cretime datetime NOT NULL,			#开始时间
#  INDEX accountcode (`accountcode`)
#);
CREATE TABLE IF NOT EXISTS callsession (
  id varchar(32) NOT NULL PRIMARY KEY,		#呼叫主标识
  accountcode varchar(20) NOT NULL default '',	#主叫帐户
  callernumber varchar(64) NOT NULL default '',	#主叫发起者号码
  extension varchar(64) NOT NULL default '',	#原始被叫号码
  routerline INT(1) NOT NULL default '0',	#呼叫来自,0未知,1内线,2外线
  cretime datetime NOT NULL,			#开始时间
  frist_cdruniqueid varchar(64) NOT NULL default '',	#关联CDRID的第一个
  INDEX accountcode (`accountcode`)
);
CREATE TABLE IF NOT EXISTS callsession_history (
  id varchar(32) NOT NULL PRIMARY KEY,		
  accountcode varchar(20) NOT NULL default '',	
  callernumber varchar(64) NOT NULL default '',	
  extension varchar(64) NOT NULL default '',	
  routerline INT(1) NOT NULL default '0',	
  cretime datetime NOT NULL,		
  frist_cdruniqueid varchar(64) NOT NULL default ''
);

#
# callsession_acts呼叫会话流程动作
#
CREATE TABLE IF NOT EXISTS callsession_acts (
  actid int(16) NOT NULL PRIMARY KEY AUTO_INCREMENT,	#呼叫动作顺序号
  callsessionid varchar(64) NOT NULL,			#关联会话主ID
  cdruniqueid varchar(64) NOT NULL default '',		#关联CDRID
  acttime datetime NOT NULL,				#发生时间
  function varchar(64) NOT NULL,			#动作类型
  var0key varchar(255) NOT NULL default '',		#标识记录0键
  var0value varchar(255) NOT NULL default '',		#标识记录0值
  var1key varchar(255) NOT NULL default '',		#标识记录1键
  var1value varchar(255) NOT NULL default '',		#标识记录1值
  var2key varchar(255) NOT NULL default '',		#标识记录2键
  var2value varchar(255) NOT NULL default '',		#标识记录2值
  var3key varchar(255) NOT NULL default '',		#标识记录3键
  var3value varchar(255) NOT NULL default '',		#标识记录3值
  extradata text NOT NULL default '',			#更多记录,以key=value&key2=value2
  INDEX callsessionid (`callsessionid`),
  INDEX cdruniqueid (`cdruniqueid`),
  INDEX acttime (`acttime`),
  INDEX function (`function`),
  INDEX var0key (`var0key`),
  INDEX var1key (`var0key`),
  INDEX var2key (`var0key`),
  INDEX var3key (`var0key`)
);
CREATE TABLE IF NOT EXISTS callsession_acts_history (
  actid int(16) NOT NULL PRIMARY KEY,
  callsessionid varchar(64) NOT NULL,
  cdruniqueid varchar(64) NOT NULL default '',
  acttime datetime NOT NULL,				
  function varchar(64) NOT NULL,		
  var0key varchar(255) NOT NULL default '',		
  var0value varchar(255) NOT NULL default '',		
  var1key varchar(255) NOT NULL default '',		
  var1value varchar(255) NOT NULL default '',		
  var2key varchar(255) NOT NULL default '',		
  var2value varchar(255) NOT NULL default '',		
  var3key varchar(255) NOT NULL default '',	
  var3value varchar(255) NOT NULL default '',		
  extradata text NOT NULL default ''
);


#
# cdr呼叫记录表
# userfield 这个字段只存储callflow其他不存储
#
CREATE TABLE IF NOT EXISTS cdr (
  id int(11) NOT NULL AUTO_INCREMENT,
  calldate datetime NOT NULL default '0000-00-00 00:00:00',  
  clid varchar(80) NOT NULL default '',
  src varchar(80) NOT NULL default '',
  dst varchar(80) NOT NULL default '',
  dcontext varchar(80) NOT NULL default '',
  channel varchar(80) NOT NULL default '',
  dstchannel varchar(80) NOT NULL default '',
  lastapp varchar(80) NOT NULL default '',
  lastdata varchar(80) NOT NULL default '',
  duration int(11) NOT NULL default '0',
  billsec int(11) NOT NULL default '0',
  disposition varchar(45) NOT NULL default '',
  amaflags int(11) NOT NULL default '0',
  accountcode varchar(20) NOT NULL default '',
  userfield varchar(255) NOT NULL default '',
  uniqueid varchar(255) NOT NULL default '',
PRIMARY KEY  (id),
INDEX amaflags (amaflags),
INDEX calldate (calldate),
INDEX accountcode (accountcode),
INDEX dcontext (dcontext),
INDEX src (src),
INDEX dst (dst),
INDEX disposition (disposition),
INDEX uniqueid (uniqueid)
);
#
# 上表的备份表
#
CREATE TABLE IF NOT EXISTS cdr_history (
  id int(11) NOT NULL PRIMARY KEY,
  calldate datetime NOT NULL default '0000-00-00 00:00:00',  
  clid varchar(80) NOT NULL default '',
  src varchar(80) NOT NULL default '',
  dst varchar(80) NOT NULL default '',
  dcontext varchar(80) NOT NULL default '',
  channel varchar(80) NOT NULL default '',
  dstchannel varchar(80) NOT NULL default '',
  lastapp varchar(80) NOT NULL default '',
  lastdata varchar(80) NOT NULL default '',
  duration int(11) NOT NULL default '0',
  billsec int(11) NOT NULL default '0',
  disposition varchar(45) NOT NULL default '',
  amaflags int(11) NOT NULL default '0',
  accountcode varchar(20) NOT NULL default '',
  userfield varchar(255) NOT NULL default '',
  uniqueid varchar(255) NOT NULL default ''
);
#------------------------------------------

#------------------------------------------
#
# Event内存表
# 每个event保存240个字符，超过的保存到下一个event
#
#
CREATE TABLE IF NOT EXISTS ami_event(
`id` INT(16) PRIMARY KEY AUTO_INCREMENT NOT NULL,
`cretime` datetime not null default '0000-00-00 00:00:00',
`event` varchar(255),
`event2` varchar(255),
`event3` varchar(255),
`event4` varchar(255),
INDEX `cretime` (`cretime`)
) ENGINE = MEMORY;

#------------------------------------------

#------------------------------------------
#
# faxqueue传真队列表
#
# id 序列编号
# accountcode 管理帐户,如果不填写就表示临时文件无关联且不能被访问和控制
# number 发送和接受的号码
# filename 关联的数据文件名称
# cretime 创建时间
# mode 模式,0是发送队列,1是接受队列
# status 处理状态:  (发送) 0 未处理, 1 拨打号码 , 2 发送处理中 , 3 发送成功, 4 发送失败.
#                   (接受) 0 正在接收, 3 接收成功, 4 接收失败
# retry 已尝试重复次数: 0
# sendannounce 发送提示音 默认提示音N次
# mailprocessed 0表示未发送,1表示发送了
# fax_status 处理状态
# fax_statusstr 状态消息 
# fax_error 错误信息
# fax_pages 页数
# fax_bitrate 速率
# fax_remotestationid 对端设备抬头
# fax_resolution 分辨率
# fax_ecm ECM是否打开
#
CREATE TABLE IF NOT EXISTS faxqueue (
id INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
accountcode varchar(20) NOT NULL default '',
number varchar(255) NOT NULL default '',
filename varchar(255) NOT NULL,
cretime datetime,
mode int(1) NOT NULL,
status int(1) NOT NULL default '0',
retry int(1) NOT NULL default '0',
sendannounce int(1) NOT NULL default '3',
mailprocessed int(1) NOT NULL default '0',
fax_status varchar(255) NOT NULL default '',
fax_statusstr varchar(255) NOT NULL default '',
fax_error varchar(255) NOT NULL default '',
fax_pages varchar(255) NOT NULL default '',
fax_bitrate varchar(255) NOT NULL default '',
fax_remotestationid varchar(255) NOT NULL default '',
fax_resolution varchar(255) NOT NULL default '',
fax_ecm varchar(255) NOT NULL default '',
INDEX accountcode (accountcode),
INDEX mode (mode),
INDEX status (status),
INDEX mailprocessed (mailprocessed),
INDEX cretime (cretime)
);
#------------------------------------------
