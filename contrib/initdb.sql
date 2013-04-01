#
# initization of freeiris2 database struction
#
#
# 以下列表为语音文件的注册信息
#
# path: /
insert into voicefiles set filename = 'beep',extname='alaw',folder = '',cretime= now(),description='BEEP',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'beeperr',extname='alaw',folder = '',cretime= now(),description='BEEP',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'pbx-transfer',extname='alaw',folder = '',cretime= now(),description='电话转接',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'pbx-invalid',extname='alaw',folder = '',cretime= now(),description='很抱歉,您所拨打的这个号码无效.请您重新拨打其他号码,谢谢.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'conf-onlyperson',extname='alaw',folder = '',cretime= now(),description='会议室中现在只有你一个人',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'queue-periodic-announce',extname='alaw',folder = '',cretime= now(),description='坐席忙请稍候',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'conf-getpin',extname='alaw',folder = '',cretime= now(),description='请输入会议室密码',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'conf-invalidpin',extname='alaw',folder = '',cretime= now(),description='密码错误无法进入会议室',label='sound',associate='cn',readonly=1;

# path: /digits
insert into voicefiles set filename = '0',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '1',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '2',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '3',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '4',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '5',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '6',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '7',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '8',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = '9',extname='alaw',folder = 'digits',cretime= now(),description='数字',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'year',extname='alaw',folder = 'digits',cretime= now(),description='年',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'mon',extname='alaw',folder = 'digits',cretime= now(),description='月',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'day',extname='alaw',folder = 'digits',cretime= now(),description='日',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'hour',extname='alaw',folder = 'digits',cretime= now(),description='点',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'min',extname='alaw',folder = 'digits',cretime= now(),description='分',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'sec',extname='alaw',folder = 'digits',cretime= now(),description='秒',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'number',extname='alaw',folder = 'digits',cretime= now(),description='号码',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'oclock',extname='alaw',folder = 'digits',cretime= now(),description='点',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'ten',extname='alaw',folder = 'digits',cretime= now(),description='十',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'hundred',extname='alaw',folder = 'digits',cretime= now(),description='百',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'thousand',extname='alaw',folder = 'digits',cretime= now(),description='千',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'wan',extname='alaw',folder = 'digits',cretime= now(),description='万',label='sound',associate='cn',readonly=1;

# path: /freeiris
insert into voicefiles set filename = 'busy',extname='alaw',folder = 'freeiris',cretime= now(),description='很抱歉,您所拨打的这个电话正在繁忙.请您稍候再拨,谢谢.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'chanunavail',extname='alaw',folder = 'freeiris',cretime= now(),description='很抱歉,您所拨打的这个号码暂时无法接通.请您稍候再拨,谢谢.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'invalid',extname='alaw',folder = 'freeiris',cretime= now(),description='很抱歉,您所拨打的这个号码无效.请您重新拨打其他号码,谢谢.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'voicemail',extname='alaw',folder = 'freeiris',cretime= now(),description='这个号码暂时无法接听您的电话,请在听到Beep的一声后留言,完成后请挂机.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'password',extname='alaw',folder = 'freeiris',cretime= now(),description='请输入密码',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'incorrect',extname='alaw',folder = 'freeiris',cretime= now(),description='您输入的密码不正确,请重新输入.',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'youhave',extname='alaw',folder = 'freeiris',cretime= now(),description='你有',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'record',extname='alaw',folder = 'freeiris',cretime= now(),description='条记录',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'uservoiceopts',extname='alaw',folder = 'freeiris',cretime= now(),description='1上一条,2重拨,3下一条',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'uservoicemain',extname='alaw',folder = 'freeiris',cretime= now(),description='请选择功能: 1收听语音信箱留言  2收听一键录音',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'playfrist',extname='alaw',folder = 'freeiris',cretime= now(),description='最新留言',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'playnext',extname='alaw',folder = 'freeiris',cretime= now(),description='继续将播放下一条',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'playend',extname='alaw',folder = 'freeiris',cretime= now(),description='已全部播放完毕,谢谢,请挂机',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'callfromqueue',extname='alaw',folder = 'freeiris',cretime= now(),description='这个呼叫来自队列',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'silence5',extname='alaw',folder = 'freeiris',cretime= now(),description='静音5秒',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'ivr-invalid',extname='alaw',folder = 'freeiris',cretime= now(),description='您的输入有误',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'ivr-timeout',extname='alaw',folder = 'freeiris',cretime= now(),description='等待按键超时',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'sendfax',extname='alaw',folder = 'freeiris',cretime= now(),description='正在发送传真',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'fax-localnumber-noanswer',extname='alaw',folder = 'freeiris',cretime= now(),description='无人接听IVR带传真',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'fax-localnumber-busy',extname='alaw',folder = 'freeiris',cretime= now(),description='繁忙IVR带传真',label='sound',associate='cn',readonly=1;

# patch 2009-7-1
insert into voicefiles set filename = 'localnumber-busy',extname='alaw',folder = 'freeiris',cretime= now(),description='很抱歉,对方电话正在繁忙, 重拨请按1, 拨打其他号码请按2, 留言请按3',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'localnumber-noanswer',extname='alaw',folder = 'freeiris',cretime= now(),description='很抱歉,您所拨打的这个号码暂时无人接听, 重拨请按1, 拨打其他号码请按2, 留言请按3',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'inputnumber',extname='alaw',folder = 'freeiris',cretime= now(),description='请输入号码',label='sound',associate='cn',readonly=1;
insert into voicefiles set filename = 'vm-thankyou',extname='alaw',folder = 'freeiris',cretime= now(),description='感谢留言,我们会尽快回复.',label='sound',associate='cn',readonly=1;

# patch 2009-12-11
insert into voicefiles set filename = 'service',extname='alaw',folder = 'freeiris',cretime= now(),description='为您服务',label='sound',associate='cn',readonly=1;

#
# list for system mousiconhold
#
insert into voicefiles set filename = 'calmriver',extname='wav',folder = '',cretime= now(),description='Free的等待音乐',label='moh',associate='default',readonly=0;

#
# voicemail
# voicemailmain key(same in freeiris.conf)
#
insert into localnumber set number = '500',typeof='agi',assign='agi://127.0.0.1/uservoicemain?vmnumber=$accountcode';
insert into localnumber set number = '501',typeof='agi',assign='agi://127.0.0.1/originate_diagnosis?accountcode=$accountcode';

#
# IVR
# 9000000001 is hangup for system
#
insert into ivrmenu set ivrnumber = '9000000001',ivrname='挂机',description='系统默认的挂机IVR.',cretime=now(),readonly='1';
insert into ivraction set ivrnumber = '9000000001',ordinal='0',actmode='99',args='';
insert into ivruserinput set ivrnumber='9000000001',general='1',general_type='invalid',general_args='folder=freeiris&filename=ivr-invalid',input='',gotoivrnumber='9000000001',gotoivractid='';
insert into ivruserinput set ivrnumber='9000000001',general='1',general_type='timeout',general_args='folder=freeiris&filename=ivr-timeout&timeout=10',input='',gotoivrnumber='9000000001',gotoivractid='';
insert into ivruserinput set ivrnumber='9000000001',general='1',general_type='retry',general_args='numberofretry=6',input='',gotoivrnumber='9000000001',gotoivractid='';

#
# 默认管理员
#
insert into admin set adminid='admin',passwd=md5('admin'),remark='',level=4,cretime=now();

