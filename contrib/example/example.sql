
#-------------------------------------------------------------------------
# 以下列表为系统的默认数据，这些默认数据可以方便新手快速的了解系统状况
# 但是这些数据不是必须的,其中有的数据跟配置文件有关联因此如果修改记得同时修改配置文件
#


#
# 本地号码登记
#
insert into localnumber set number='301',  typeof='conference', assign='';
insert into localnumber set number='401',  typeof='queue',      assign='';
insert into localnumber set number='8001', typeof='extension',  assign='8001';
insert into localnumber set number='8002', typeof='extension',  assign='8002';
insert into localnumber set number='8003', typeof='extension',  assign='8003';
insert into localnumber set number='8004', typeof='extension',  assign='8004';
insert into localnumber set number='200', typeof='ivr';
insert into localnumber set number='200000', typeof='ivr';
insert into localnumber set number='200100', typeof='ivr';
insert into localnumber set number='200200', typeof='ivr';
insert into localnumber set number='200800', typeof='ivr';


#
# 默认的分组
# 8001 8002 8003 8004
insert into extengroup set groupid='0',groupname='市场部',remark='',cretime=now();
insert into extengroup set groupid='1',groupname='客服部',remark='',cretime=now();

insert into extengroup_assign set groupid='0',accountcode='8001';
insert into extengroup_assign set groupid='0',accountcode='8002';
insert into extengroup_assign set groupid='1',accountcode='8003';
insert into extengroup_assign set groupid='1',accountcode='8004';


#
# 默认的SIP分机[这个功能在文件sip_exten.conf里同样存在数据]
# 8001 8002 8003 8004
insert into extension set accountcode='8001',cretime=now(),password='8001',deviceproto='sip',devicenumber='8001',devicestring='8001',fristchecked='0',info_name='8001';
insert into extension set accountcode='8002',cretime=now(),password='8002',deviceproto='sip',devicenumber='8002',devicestring='8002',fristchecked='0',info_name='8002';
insert into extension set accountcode='8003',cretime=now(),password='8003',deviceproto='sip',devicenumber='8003',devicestring='8003',fristchecked='0',info_name='8003';
insert into extension set accountcode='8004',cretime=now(),password='8004',deviceproto='sip',devicenumber='8004',devicestring='8004',fristchecked='0',info_name='8004';


#
# 默认的分机呼叫规则(默认开启本地处理,因此不需要了)
#
#insert into router set proirety='1',createmode='0',routerline='1',routername='拨三位特殊功能号码',lastwhendone='0',match_calledlen='3',process_mode='1';
#insert into router set proirety='1',createmode='0',routerline='1',routername='拨内线分机四位号码',lastwhendone='0',match_calledlen='4',process_mode='1';

#
# 默认的外线呼叫规则(默认开启本地处理,因此不需要了)
#
# 200进入IVR菜单
#insert into router set proirety='1',createmode='0',routerline='2',routername='PSTN拨入默认队列',lastwhendone='0',match_callednum='200',process_mode='1',process_defined='ivr';

#
# 默认的电话会议室
# 301
insert into conference set confno='301',pincode='301',playwhenevent='1',mohwhenonlyone='1',cretime=now();


#
# 默认呼叫队列[这个功能在文件queues_list.conf里同样存在数据]
# 401
insert into queue set queuenumber='401',queuename='测试队列',announce='',playring='0',saymember='1',queuetimeout='300',failedon='',members='&8001&8002&8003&8004',cretime=now();

#
# 默认的自定义录音文件
#
insert into voicefiles set filename = 'welcome',extname='alaw',folder = 'user_custom',cretime= now(),description='默认提示语音',label='sound',associate='cn',readonly=0;
insert into voicefiles set filename = 'u0',extname='alaw',folder = 'user_custom',cretime= now(),description='转接坐席语音',label='sound',associate='cn',readonly=0;


#
# 默认的IVR菜单
#
# 号码为200: 欢迎致电本公司,请直拨分机号码,咨询请拨1,售后请拨2,人工帮助请拨0,重听请按*
#
# 200100 : 正在为您转接到人工坐席，请稍候--->[连接呼叫队列401]
#
# 200200 : 正在为您转接到人工坐席，请稍候--->[连接呼叫队列401]
#
# 200800 : 直拨8开头的分机号码
#
# 200000 : 正在为您转接到人工坐席，请稍候--->[连接呼叫队列401]
#
insert into ivrmenu set ivrnumber='200',ivrname='自动电话总机',description='演示IVR菜单',cretime=now(),readonly=0;
insert into ivrmenu set ivrnumber='200000',ivrname='人工总机',description='',cretime=now(),readonly=0;
insert into ivrmenu set ivrnumber='200100',ivrname='业务IVR',description='',cretime=now(),readonly=0;
insert into ivrmenu set ivrnumber='200200',ivrname='服务IVR',description='',cretime=now(),readonly=0;
insert into ivrmenu set ivrnumber='200800',ivrname='直拨8开头4位分机',description='',cretime=now(),readonly=0;

insert into ivraction set ivrnumber='200',ordinal='1',actmode='10',args="folder=user_custom&filename=welcome&interruptible=true&";
insert into ivraction set ivrnumber='200000',ordinal='1',actmode='10',args="folder=user_custom&filename=u0&interruptible=&";
insert into ivraction set ivrnumber='200000',ordinal='2',actmode='41',args="typeof=queue&dialvarname=&dialdigits=401&";
insert into ivraction set ivrnumber='200100',ordinal='1',actmode='10',args="folder=user_custom&filename=u0&interruptible=&";
insert into ivraction set ivrnumber='200100',ordinal='2',actmode='41',args="typeof=&dialvarname=&dialdigits=401&gotoivr=200&actpoint=&playbackinvalid=&";
insert into ivraction set ivrnumber='200200',ordinal='1',actmode='10',args="folder=user_custom&filename=u0&interruptible=&";
insert into ivraction set ivrnumber='200200',ordinal='2',actmode='41',args="typeof=&dialvarname=&dialdigits=401&gotoivr=200&actpoint=&playbackinvalid=&";
insert into ivraction set ivrnumber='200800',ordinal='1',actmode='20',args="beepbeforereceive=false&addbeforeuserinput=true&maxdigits=3&receivevarname=calluser&";
insert into ivraction set ivrnumber='200800',ordinal='2',actmode='41',args="typeof=extension&dialvarname=calluser&dialdigits=&gotoivr=200&actpoint=&playbackinvalid=true&";
insert into ivraction set ivrnumber='200800',ordinal='3',actmode='10',args="folder=&filename=pbx-transfer&interruptible=&";

insert into ivruserinput set ivrnumber='200',general='1',general_type='invalid',general_args="folder=freeiris&filename=ivr-invalid&",gotoivrnumber='200';
insert into ivruserinput set ivrnumber='200',general='1',general_type='timeout',general_args="folder=freeiris&filename=ivr-timeout&timeout=10&",gotoivrnumber='200';
insert into ivruserinput set ivrnumber='200',general='1',general_type='retry',general_args="numberofretry=6",gotoivrnumber='9000000001';
insert into ivruserinput set ivrnumber='200000',general='1',general_type='invalid',general_args="folder=freeiris&filename=ivr-invalid&",gotoivrnumber='200000';
insert into ivruserinput set ivrnumber='200000',general='1',general_type='timeout',general_args="folder=freeiris&filename=ivr-timeout&timeout=10&",gotoivrnumber='200000';
insert into ivruserinput set ivrnumber='200000',general='1',general_type='retry',general_args="numberofretry=6",gotoivrnumber='9000000001';
insert into ivruserinput set ivrnumber='200100',general='1',general_type='invalid',general_args="folder=freeiris&filename=ivr-invalid&",gotoivrnumber='200100';
insert into ivruserinput set ivrnumber='200100',general='1',general_type='timeout',general_args="folder=freeiris&filename=ivr-timeout&timeout=10&",gotoivrnumber='200100';
insert into ivruserinput set ivrnumber='200100',general='1',general_type='retry',general_args="numberofretry=6",gotoivrnumber='9000000001';
insert into ivruserinput set ivrnumber='200200',general='1',general_type='invalid',general_args="folder=freeiris&filename=ivr-invalid&",gotoivrnumber='200200';
insert into ivruserinput set ivrnumber='200200',general='1',general_type='timeout',general_args="folder=freeiris&filename=ivr-timeout&timeout=10&",gotoivrnumber='200200';
insert into ivruserinput set ivrnumber='200200',general='1',general_type='retry',general_args="numberofretry=6",gotoivrnumber='9000000001';
insert into ivruserinput set ivrnumber='200800',general='1',general_type='invalid',general_args="folder=freeiris&filename=ivr-invalid&",gotoivrnumber='200800';
insert into ivruserinput set ivrnumber='200800',general='1',general_type='timeout',general_args="folder=freeiris&filename=ivr-timeout&timeout=10&",gotoivrnumber='200800';
insert into ivruserinput set ivrnumber='200800',general='1',general_type='retry',general_args="numberofretry=6",gotoivrnumber='9000000001';

insert into ivruserinput set ivrnumber='200',general='0',input='1',gotoivrnumber='200100';
insert into ivruserinput set ivrnumber='200',general='0',input='2',gotoivrnumber='200200';
insert into ivruserinput set ivrnumber='200',general='0',input='8',gotoivrnumber='200800';
insert into ivruserinput set ivrnumber='200',general='0',input='0',gotoivrnumber='200000';
insert into ivruserinput set ivrnumber='200',general='0',input='*',gotoivrnumber='200';
