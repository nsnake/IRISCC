[$queuenumber]
musicclass = default
announce =
strategy = $strategy
servicelevel = 0
context = from-exten-queue
timeout = $timeout
retry = 3
;weight=0
wrapuptime=2
;autofill=yes
;autopause=yes
;maxlen = 0
setinterfacevar=yes
;announce-frequency = 90 
periodic-announce-frequency=$periodic-announce-frequency
;announce-holdtime = yes|no|once
; announce-round-seconds = 10
			;	("You are now first in line.")
;queue-youarenext = queue-youarenext		
			;	("There are")
;queue-thereare	= queue-thereare
			;	("calls waiting.")
;queue-callswaiting = queue-callswaiting
			;	("The current est. holdtime is")
;queue-holdtime = queue-holdtime
			;	("minutes.")
;queue-minutes = queue-minutes
			;	("seconds.")
;queue-seconds = queue-seconds
			;	("Thank you for your patience.")
;queue-thankyou = queue-thankyou
			;       ("less than")
;queue-lessthan = queue-less-than
			;       ("Hold time")
;queue-reporthold = queue-reporthold
			;       ("All reps busy / wait for next")
periodic-announce = queue-periodic-announce
; monitor-format = gsm|wav|wav49
; monitor-type = MixMonitor
; queue events
joinempty = yes
; leavewhenempty = yes
; eventwhencalled = yes|no|vars
; eventmemberstatus = no
; reportholdtime = no
; ringinuse = no
; memberdelay = 0
; timeoutrestart = no
$members
