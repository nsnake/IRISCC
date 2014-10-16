#<?
[database]
;
dbtype = mysql
dbhost = localhost
dbname = astercc01
dbport = 3306
username = root
password =

tb_curchan = curcdr
tb_cdr = mycdr

[asterisk]

server = 127.0.0.1
port = 5038
username = freeiris
secret = freeiris

;defined delimiter of asterisk parameter , or |.
paramdelimiter = ,

[sipbuddy]
type = friend
host = dynamic
insecure = port,invite
canreinvite = no
nat = yes
disallow = all
allow = ulaw,alaw,g729,g723.1
context = from-internal
dtmfmode=rfc2833
qualify = yes

[system]

log_enabled = 1

;Log file path
log_file_path = /tmp/asterbillingDebug.log

;path of astercc daemon
astercc_path = /opt/asterisk/scripts/astercc

;
; Asterisk context parameter, use which context when dial in or dial out
;

;if set to 'admin', the index page will link to "manager login" page,
;else if set to 'user' defaulf page is user login page
useindex = admin

;when the times of login failed with one ip greater than this, the ip will be locked for login
max_incorrect_login = 0

;context when dial out, in trixbox this could be from-internal
outcontext =  from-internal

; individual: set the limit in credit limit field to the call
; balance: set limit in balance to the call
creditlimittype = balance

upload_file_path = ./upload/

; astercc will refresh the balance of the group
; set to 0 if you dont want it refresh automaticly
refreshBalance = 0

; if we use history cdr(move the billed cdr to historycdr and read the cdr from historycdr)
useHistoryCdr = 1

; when we set useHistoryCDR = 1, then here set if we move the no answer cdr to historycdr
keepNoAnswerCDR = 1

; if we set clid credit
setclid = 1

;set length of clid pin number, max 20; min 10.
pin_len = 10;

; not .conf need
; if you dont want astercc generate the conf for u, just leave this value blank
sipfile = /etc/asterisk/sip_astercc

; if require valid code when login
validcode = no

;if open new window when click pannel button on systemstatus(callshop portal) page
sysstatus_new_window = yes

;which status will be displayed when click call status in systemstatus(callshop portal) page
callshop_status_amount = 1
callshop_status_cost = 1
callshop_status_limit = 1
callshop_status_credit = 1
callshop_status_balance = 1

;controll the booth window,how to show the cdr(calldate_ASC,calldate_DESC)
booth_cdr_order = calldate_DESC

[epayment]
;if enable online payment by paypaly (enable,disable)
epayment_status = enable

;Define here the URL of paypal payment (to test with sandbox)
;paypal_payment_url = "https://secure.paypal.com/cgi-bin/webscr"
paypal_payment_url = "https://www.sandbox.paypal.com/cgi-bin/webscr"

;paypal PDT verification url (to test with sandbox)
;paypal_verify_url = "ssl://www.paypal.com"
paypal_verify_url = "ssl://www.sandbox.paypal.com"

;paypal PDT identity token
pdt_identity_token = EKnEHrMcYjaZ_tpGyYXxAk1clspll-yWwJy9rktx6wDc9mnVH9PCJgq8RLK

;email address for your paypal account
paypal_account = seler_plane@126.com

;name of payment item
item_name = Credit Purchase

;PayPal-Supported Currencies and Currency Codes, default USD(U.S. Dollar)
;('AUD'=>'Australian Dollar','CAD'=>'Canadian Dollar','CZK'=>'Czech Koruna','DKK'=>'Danish Krone','EUR'=>'Euro','HKD'=>'Hong Kong Dollar','HUF'=>'Hungarian Forint','ILS'=>'Israeli New Sheqel','JPY'=>'Japanese Yen','MXN'=>'Mexican Peso','NOK'=>'Norwegian Krone','NZD'=>'New Zealand Dollar','PLN'=>'Polish Zloty','GBP'=>'Pound Sterling','SGD'=>'Singapore Dollar','SEK'=>'Swedish Krona','CHF'=>'Swiss Franc','USD'=>'U.S. Dollar')
currency_code = USD

;Available amount for payer
amount = 10,20,50,100

;if callshop pays fee of paypal
callshop_pay_fee = 0

;for IPN notify return, request internet url of asterbilling, like http://yourdomain/callshop
asterbilling_url = http://123.185.228.112/asterbilling

;your email address for receice a notice when someone payment
notify_mail = donnie@astercc.org

;if log the pdt result 0/1
pdt_log = 1

;if log the ipn result 0/1
ipn_log = 1

[a2billing]
enable = 0
dbtype = mysql
dbhost = localhost
dbname = astercc01
dbport = 3306
cidtable = cc_callerid
cardtable = cc_card
calltable = cc_call
username = root
password =

[customers]
enable = 0
dbtype = mysql
dbhost = localhost
dbname = astercc01
dbport = 3306
customertable = callshop_customers
discounttable = discount
username = root
password =

[resellertrunk]
trunk1_type = sip
trunk1= reselleroutbound1
trunk2_type = sip
trunk2= reselleroutbound2

[error_report]
;sets the error level
error_report_level = 3

[synchronize]
;id will follow the last id of the local server when save data
id_autocrement_byset = 0

;if display the server which the data belongs to
display_synchron_server = 0

;if use the rate history table when delete data
delete_by_use_history = 0

[local_host]
minId = 1
maxId = 10000

[synchronize_host]
Host = host45
host45 = 192.168.1.45
host45_minId = 10001
host45_maxId = 20000

#?>
