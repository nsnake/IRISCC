;<?php
[database]
;
; Database connection parameter

dbtype = mysql
dbport = 3306
dbhost = 127.0.0.1
dbname = astercc01
username = root
password =

[asterisk]
;
; Asterisk connection parameter

server = 127.0.0.1
;should be matched in manager.conf
port = 5038
username = freeiris
secret = freeiris

;defined delimiter of asterisk parameter , or |.
paramdelimiter = ,

; Recorded file path, if you want to lisens records from web,
; the path must could be read by apache and allow php to exec
monitorpath = /var/spool/asterisk/monitor/

; gsm,wav
monitorformat = wav


[system]

log_enabled = 0
;Log file path
log_file_path = /tmp/astercrmDebug.log

;path of astercc daemon
astercc_path = /opt/asterisk/scripts/astercc


; where astercrm get asterisk call events, set to curcdr when using astercc
; option: event, curcdr
eventtype = curcdr

;
; Asterisk context parameter, use which context when dial in or dial out
;

;context when dial out, in trixbox this could be from-internal
outcontext = from-exten-sip

;context when dial in, in trixbox this could be from-trunk
incontext = from-trunk-sip

;context for Answering Machine Detect(AMD) in campaign
amdcontext = from-astercrm-amd

;context for call result Detect(CRD) in campaign
crdcontext = from-astercrm

;agent_pannel_setting: if display following pannels in agent interface (On/Off)
extension_pannel = On
diallist_pannel = On
transfer_pannel = On
dial_pannel = On
monitor_pannel = On
mission_pannel = On

;chang color of records in diallist pannel  when dialitme is approaching
diallist_pannel_tip = 30

;if need to enter accountcode/password of admin or groupadmin when agent stop work
stop_work_verify = 0

; Asterisk context parameter, use which context and extenstion
; when predictive dialer connect the call
;

;predialer_context = from-siptrunk
;predialer_extension =

;
; astercrm wouldnot pop-up unless the length of callerid is greater than
; this number
;
phone_number_length = 0

;how many digits end of callerid remove when incoming call smart matching, disabled if set it to 0
smart_match_remove = 1
;

;time intervals of update event in pages
status_check_interval = 2
;

;
; if astercrm trim fellowing prefix, use gamma to sperate
; leave it blank if no prefix need to be removed

trim_prefix =

;
; if your astercrm work on the same server with asterisk, set to true
; when astercrm start a call, it would drop a .call file to asterisk spool
; or else astercrm would use AMI command: Originate to start a call
;
allow_dropcall = 0

;
; if astercrm allow same customer name
;

allow_same_data = 0

;
; if auto popup the note
;
auto_note_popup = 1

;
; if popup the highest priority note when enable auto_note_popup
;
highest_priority_note = 1

;
; if popup the lastest priority note when enable auto_note_popup
;
lastest_priority_note = 1

default_share_note = 0

;if use customer_leads table move/copy/default_move/default_copy/disabled
customer_leads = copy

enable_code = 0

;how long to update last_update_time field in table astercrm_account
;unit is the minute
update_online_interval = 2

;if enable sms popup   disabled/callerid/campaign_number/trunk_number
enable_sms = disabled

;if set to yes,will popup a tip to record the reasion when the agent pause the queue
;if set to no,will not popup a tip
require_reason_when_pause = yes

;if set default,will create the ticket: systemadmin for all user; groupadmin for all group user; agent for self.
;if set system,allow a ticket to be assigned to any user regardless of the group they belong to.
;if set group,allow a ticket to be assigned to any user who belongs to same group
create_ticket = default

; if need display the recend cdr link
display_recent_cdr = 1

; define what information would be displayed in portal page
; customer | note
portal_display_type = note

;
; astercrm wouldnot pop-up when dial out unless this parameter is true
;
pop_up_when_dial_out = 1

;
; astercrm wouldnot pop-up when dial in unless this parameter is true
;
pop_up_when_dial_in = 1

;
; browser will maximize when pop up
;
browser_maximize_when_pop_up = 1

;
; which phone ring first when using click to dial
;caller | callee
firstring = caller

;
; astercrm will show contact
;
enable_contact =

upload_file_path = ./upload/

;
; astercrm will use external crm software if this parameter is true
;
enable_external_crm = 0

;
; asterCRM will decide how to show the external crm popup
;
open_new_window = both

;
; when using external crm, put default page here
;
external_crm_default_url = http://192.168.1.30/astercrm/road_accident.php

;
; when using external crm, put pop up page here
; %callerid		callerid
; %calleeid		calleeid
; %method		dial_out or dial_in
; %uniqueid
; %calldate     starttime of the call
external_crm_url = http://192.168.1.30/astercrm/road_accident.php

; any fields you need to post which in customer table, use comma between fields
; note: the field must in customer table
external_url_parm = customer,address,zipcode,city,state,phone,email,bankaccount

detail_level = group

astercc_conf_path =

;if check extension status when click "start work" on agent portal
checkworkexten = yes

;if check socket to yes,when the call dialin it will notic the agent by socket
enable_socket = no

socket_url = <?xml version="1.0" encoding="UTF-8"?>  <!DOCTYPE cti PUBLIC "-//DTD cti 1.0//EN"  "http://siebelURL/epublicsector_enu/21211/applets/cti.dtd">\n<cti>\n  <version>1.0</version>\n<event>\n<field>\n<name>CallerNumber</name>\n<value>%callerid%</value>\n</field>\n</event>\n</cti>

fix_port = 5555

;export customer field when export dialedlist
export_customer_fields_in_dialedlist = first_name,last_name,address,city,state,zipcode


;whether to popup the customer window when it had existed
allow_popup_when_already_popup = 1


enable_formadd_popup = 1
[survey]
; if need a note after survey option
enable_surveynote = 1

; if need close all popups after survey saved
close_popup_after_survey = 0

[diallist]
popup_diallist = 0

[billing]
;if astercrm work with asterbilling(set to 1,call which astercrm agent dialed could be billed)
workwithasterbilling = 0
;the reseller id for new group, you need add a reseller in asterbilling first
resellerid =
;default limittype for new group(prepaid,postpaid), leave it blank be no limit
grouplimittype =
;default creditlimit for new group
groupcreditlimit = 0
;default limittype for new clid(prepaid,postpaid), leave it blank be no limit
clidlimittype =
;default creditlimit for new clid
clidcreditlimit = 0

[google-map]

key =


[error_report]
;sets the error level
error_report_level = 0

;?>
