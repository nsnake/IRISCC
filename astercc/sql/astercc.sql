
###########   Table structure for asterCC database   ################
###########   astercc current version: 0.21 beta          ################

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

## 
## database: `astercc`
## 

############ For astercc ####################################

#############################################################


## 
## table `servers`
## 

DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `ip` varchar(80) NOT NULL default '',
  `port` varchar(6) NOT NULL default '',
  `username` varchar(30) NOT NULL default '',
  `secret` varchar(30) NOT NULL default '',
  `note` varchar(250) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `account`
## 

DROP TABLE IF EXISTS `account`;

CREATE TABLE `account` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `password` varchar(30) NOT NULL default '',
  `usertype` varchar(20) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `accountcode` varchar(20) NOT NULL default '',
  `callback` varchar(10) NOT NULL default '',
  UNIQUE KEY `id` (`id`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

###########################################################

INSERT INTO `account` (
`id` ,
`username` ,
`password` ,
`usertype` ,
`addtime`
)
VALUES (
NULL , 'admin', 'admin', 'admin' , now()
);


##########################################################

## 
## table `accountgroup`
## 

DROP TABLE IF EXISTS `accountgroup`;

CREATE TABLE `accountgroup` (
  `id` int(11) NOT NULL auto_increment,
  `groupname` varchar(30) NOT NULL default '',
  `grouptitle` varchar(50) NOT NULL default '',
  `grouptagline` varchar(80) NOT NULL default '',
  `grouplogo` varchar(30) NOT NULL default '',
  `grouplogostatus` int(1) NOT NULL default 1,
  `accountcode` varchar(20) NOT NULL default '',
  `allowcallback` varchar(10) NOT NULL default 'no',
  `creditlimit` double(24,4) NOT NULL default '0.0000',
  `limittype` varchar(10) NOT NULL default '',
  `group_multiple` double(8,4) NOT NULL default '1.0000',
  `customer_multiple` double(8,4) NOT NULL default '1.0000',
  `curcredit` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `resellerid` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `callback`
## 

DROP TABLE IF EXISTS `callback`;

CREATE TABLE `callback` (
  `id` int(11) NOT NULL auto_increment,
  `lega` varchar(30) NOT NULL default '0',
  `legb` varchar(30) NOT NULL default '',
  `credit` double(24,4) NOT NULL default '0.0000',
  `groupid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  KEY `leg` (`lega`,`legb`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `callshoprate`
## 

DROP TABLE IF EXISTS `callshoprate`;

CREATE TABLE `callshoprate` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  UNIQUE rate (dialprefix,numlen,resellerid,groupid),
  KEY `dialprefix` (`dialprefix`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `clid`
## 

DROP TABLE IF EXISTS `clid`;

CREATE TABLE `clid` (
  `id` int(11) NOT NULL auto_increment,
  `clid` varchar(20) NOT NULL default '',
  `accountcode` varchar(40) NOT NULL default '',
  `pin` varchar(30) NOT NULL default '',
  `creditlimit` DOUBLE NOT NULL default '0.0000',
  `curcredit` DOUBLE NOT NULL default '0.0000',
  `limittype` VARCHAR( 10 ) NOT NULL,
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `display` varchar(20) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  `isshow` enum('yes','no') NOT NULL default 'yes',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `pin` (`pin`),
  UNIQUE KEY `clid` (`clid`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## ########################################################

## 
## table `myrate`
## 

DROP TABLE IF EXISTS `myrate`;

CREATE TABLE `myrate` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  UNIQUE rate (dialprefix,numlen,resellerid,groupid),
  KEY `dialprefix` (`dialprefix`),
  INDEX `destination` (`destination`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `resellergroup`
## 

DROP TABLE IF EXISTS `resellergroup`;

CREATE TABLE `resellergroup` (
  `id` int(11) NOT NULL auto_increment,
  `resellername` varchar(30) NOT NULL default '',
  `accountcode` varchar(20) NOT NULL default '',
  `allowcallback` varchar(10) NOT NULL default '',
  `creditlimit` double(24,4) NOT NULL default '0.0000',
  `curcredit` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `limittype` varchar(10) NOT NULL default '',
  `multiple` double(8,4) NOT NULL default '1.0000',
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',  
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `epayment_account` varchar(255) NOT NULL default '',                 
  `epayment_status` enum('enable','disable') NOT NULL default 'disable',
  `epayment_item_name` varchar(30) NOT NULL default '',     
  `epayment_identity_token` varchar(255) NOT NULL default '',           
  `epayment_amount_package` varchar(30) NOT NULL default '',            
  `epayment_notify_mail` varchar(60) NOT NULL default '',
  `trunk1_id` int(11) NOT NULL default 0,
  `trunk2_id` int(11) NOT NULL default 0,
  `callshop_pay_fee` ENUM('yes','no') NOT NULL DEFAULT 'no',
  `clid_context` varchar(30) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## ########################################################

## 
## table `resellerrate`
## 

DROP TABLE IF EXISTS `resellerrate`;

CREATE TABLE `resellerrate` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  UNIQUE rate (dialprefix,numlen,resellerid),
  KEY `dialprefix` (`dialprefix`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `callshop_customers`
## 

DROP TABLE IF EXISTS `callshop_customers`;

CREATE TABLE `callshop_customers` (
  `id` int(11) NOT NULL auto_increment,
  `pin` varchar(30) NOT NULL default '',
  `first_name` varchar(50) NOT NULL default '',
  `last_name` varchar(50) NOT NULL default '',
  `amount` double(24,4) NOT NULL default '0.0000',
  `discount` double(8,4) NOT NULL default -1,
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  UNIQUE `pin` (`pin`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `discount`
## 

DROP TABLE IF EXISTS `discount`;

CREATE TABLE `discount` (
  `id` int(11) NOT NULL auto_increment,
  `amount` double(24,4) NOT NULL default '0.0000',  
  `discount` double(8,4) NOT NULL default '0.0000',  
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  UNIQUE `amount` (`amount`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## ########################################################

## 
## table `credithistory`
## 

DROP TABLE IF EXISTS `credithistory`;

CREATE TABLE `credithistory` (
  `id` int(11) NOT NULL auto_increment,
  `modifytime` datetime NOT NULL default '0000-00-00 00:00:00',
  `resellerid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `clidid` int(11) NOT NULL default '0',
  `srccredit` double(24,4) NOT NULL default '0.0000',
  `modifystatus` varchar(20) NOT NULL default '',
  `modifyamount` double(24,4) NOT NULL default '0.0000',
  `comment` varchar(20) NOT NULL default '',
  `epayment_txn_id` varchar(60) NOT NULL default '',
  `operator` varchar(20) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  key `resellerid` (`resellerid`,`groupid`,`clidid`,`modifytime`,`modifystatus`,`modifyamount`,`operator`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `trunk`
## 

DROP TABLE IF EXISTS `trunks`;

CREATE TABLE `trunks` (
  `id` int(11) NOT NULL auto_increment,
  `trunkname` varchar(30) NOT NULL default '',
  `trunkidentity` varchar(50) NOT NULL default '',
  `trunkprotocol` enum('sip','iax') NOT NULL default 'sip',
  `registrystring` varchar(254) NOT NULL default '',
  `trunkdetail` text NOT NULL,
  `trunktimeout` int(5) NOT NULL default 30,
  `trunkusage` bigint(20) NOT NULL default '0',
  `trunkprefix` varchar(20) NOT NULL default '',
  `removeprefix` varchar(20) NOT NULL default '',
  `property` enum('normal','new','edit','delete') NOT NULL default 'normal',
  `creby` int(11) NOT NULL default '0',
  `created` datetime  NULL,
  `updated` datetime  NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE `trunkname` (`trunkname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_general_ci;


###############################################################

############### Both for astercc and astercrm #################

###############################################################

## 
## table `peerstatus`
## 

DROP TABLE IF EXISTS `peerstatus`;

CREATE TABLE peerstatus (
    peername varchar(50) NOT NULL default '',
    username varchar(50) NOT NULL default '',
    host varchar(50) NOT NULL default '',
    mask varchar(50) NOT NULL default '',
    dyn char(1) NOT NULL default '',
    nat char(1) NOT NULL default '',
    acl char(1) NOT NULL default '',
    port varchar(5) NOT NULL default '',
    status varchar(50) NOT NULL default '',
    responsetime int(4) NOT NULL default '0',
    freshtime datetime NOT NULL default '0000-00-00 00:00:00',
    protocol enum ('sip','iax') not null default 'sip',
    pbxserver varchar(50) NOT NULL default '',
    UNIQUE KEY peer (`peername`,`protocol`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## ########################################################

## 
## table `curcdr`
## 

DROP TABLE IF EXISTS `curcdr`;

CREATE TABLE `curcdr` (
  `id` int(11) NOT NULL auto_increment,
  `src` varchar(50) NOT NULL default '',
  `dst` varchar(50) NOT NULL default '',  
  `srcname` varchar(100) NOT NULL default '',  
  `srcchan` varchar(100) NOT NULL default '',
  `dstchan` varchar(100) NOT NULL default '',
  `didnumber` varchar(30) NOT NULL default '',
  `starttime` datetime NOT NULL default '0000-00-00 00:00:00',
  `answertime` datetime NOT NULL default '0000-00-00 00:00:00',
  `calldate` datetime not null default '0000-00-00 00:00:00',
  `srcuid` varchar(40) NOT NULL default '',
  `dstuid` varchar(40) NOT NULL default '',
  `queue` varchar(30) NOT NULL DEFAULT '',
  `disposition` varchar(10) NOT NULL default '',
  `userid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `accountid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `credit` double(24,4) NOT NULL default '0.0000',
  `callshopcredit` double(24,4) NOT NULL default '0.0000',
  `resellercredit` double(24,4) NOT NULL default '0.0000',
  `creditlimit` double(24,4) NOT NULL default '0.0000',
  `destination` varchar(100) NOT NULL default '',
  `dialstring` varchar(100) not null default '',
  `dialstatus` VARCHAR(40) NOT NULL DEFAULT '',
  `agentchan` varchar(100) NOT NULL default '',
  `memo` varchar(100) NOT NULL default '',
  `accountcode` varchar(100) NOT NULL default '',
  `pushcall` varchar(10) default 'no',
  `monitored` int(11) NOT NULL default 0,
  UNIQUE KEY `id` (`id`),
  KEY `srcid` (`src`,`dst`,`didnumber`,`srcchan`,`dstchan`,`srcuid`,`dstuid`,`disposition`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `ap_curchannels`;

CREATE TABLE `ap_curchannels` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime not null default '0000-00-00 00:00:00',
  `updated` datetime not null default '0000-00-00 00:00:00',
  `ended` datetime not null default '0000-00-00 00:00:00',
  `timestamp` varchar(40) not null default '',
  `channel` varchar(100) not null default '',
  `uniqueid` varchar(40) not null default '',
  `channelstate` int(4) not null default '0',
  `channelstatedesc` varchar(20) not null default '',
  `calleridnum` varchar(50) not null default '',
  `calleridname` varchar(50) not null default '',
  `accountcode` varchar(40) not null default '',
  `exten` varchar(50) not null default '',
  `context` varchar(50) not null default '',
  `cid_callingpres` varchar(100) not null default '',
  `hangupcause` varchar(10) not null default '',
  `hangupcausetxt`  varchar(100) not null default '',
  `moh`  datetime not null default '0000-00-00 00:00:00',
  `agent`  varchar(30) not null default '',
  `processed` enum('yes','no') NOT NULL DEFAULT 'no',
  `delflag` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `ap_mychannels`;

CREATE TABLE `ap_mychannels` (
  `id` int(11) NOT NULL ,
  `created` datetime not null default '0000-00-00 00:00:00',
  `updated` datetime not null default '0000-00-00 00:00:00',
  `ended` datetime not null default '0000-00-00 00:00:00',
  `timestamp` varchar(40) not null default '',
  `channel` varchar(100) not null default '',
  `uniqueid` varchar(40) not null default '',
  `channelstate` int(4) not null default '0',
  `channelstatedesc` varchar(20) not null default '',
  `calleridnum` varchar(50) not null default '',
  `calleridname` varchar(50) not null default '',
  `accountcode` varchar(40) not null default '',
  `exten` varchar(50) not null default '',
  `context` varchar(50) not null default '',
  `cid_callingpres` varchar(100) not null default '',
  `hangupcause` varchar(10) not null default '',
  `hangupcausetxt`  varchar(100) not null default '',
  `moh`  datetime not null default '0000-00-00 00:00:00',
  `agent`  varchar(30) not null default '',
  `processed` enum('yes','no') NOT NULL DEFAULT 'no'
 ) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `curmeetmes`
## 

DROP TABLE IF EXISTS `curmeetmes`;

CREATE TABLE `curmeetmes` (
  `id` int(11) NOT NULL auto_increment,
  `starttime` datetime NOT NULL default '0000-00-00 00:00:00',
  `channel` varchar(100) NOT NULL default '',
  `uniqueid` varchar(40) NOT NULL default '',
  `meetme` varchar(20) NOT NULL default '',
  `usernum` int(4) NOT NULL default '0',
  `calleridnum` varchar(50) NOT NULL default '',
  `calleridname` varchar(50) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `pbxlogs`;

CREATE TABLE `pbxlogs` (
  `id` int(11) NOT NULL auto_increment,
  `event` varchar(100) NOT NULL default '',
  `data` varchar(200) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `cdrs`;

CREATE TABLE `cdrs` (
  `id` int(11) NOT NULL auto_increment,
  `srcnum` varchar(40) NOT NULL default '',
  `srcname` varchar(40) NOT NULL default '',
  `dstnum` varchar(40) NOT NULL default '',
  `dstname` varchar(40) NOT NULL default '',
  `starttime` datetime NOT NULL default '0000-00-00 00:00:00',
  `answertime` datetime NOT NULL default '0000-00-00 00:00:00',
  `endtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `duration` int(11) NOT NULL default 0,
  `billsec` int(11) NOT NULL default 0,
  `disposition` varchar(45) NOT NULL default '',
  `target` enum('DialIn','DialOut','Consult','Conference','Local','Queue') NOT NULL default 'DialIn',
  `uniqueid` varchar(40) not null default '',
  `destuniqueid` varchar(40) not null default '',
  `dialstring` varchar(100) not null default '',
  `srcchannel` varchar(100) not null default '',
  `dstchannel` varchar(100) not null default '',
  `srcchannelstate` int(4) not null default '0',
  `srcchannelstatedesc` varchar(20) not null default '',
  `dstchannelstate` int(4) not null default '0',
  `dstchannelstatedesc` varchar(20) not null default '',
  `accountcode` varchar(40) not null default '',
  `exten` varchar(50) not null default '',
  `monitorid` varchar(30) NOT NULL default '',
  `localcall` enum('yes','no') not null default 'no',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `parkedcalls`;

 CREATE TABLE `parkedcalls` (
`id` INT NOT NULL AUTO_INCREMENT ,
`Num` VARCHAR( 10 ) NOT NULL ,
`Channel` VARCHAR( 50 ) NOT NULL ,
`Context` VARCHAR( 50 ) NOT NULL ,
`Extension` VARCHAR( 50 ) NOT NULL ,
`Pri` VARCHAR( 50 ) NOT NULL ,
`Timeout` VARCHAR( 10 ) NOT NULL ,
PRIMARY KEY ( `id` ) ,
UNIQUE (
`id`
)
) ENGINE = HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci; 

## ########################################################

## 
## table `mycdr`
## 

DROP TABLE IF EXISTS `mycdr`;

CREATE TABLE `mycdr` (
  `id` int(11) NOT NULL auto_increment,
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `src` varchar(40) NOT NULL default '',
  `dst` varchar(40) NOT NULL default '',
  `srcname` varchar(100) NOT NULL default '',
  `channel` varchar(80) NOT NULL default '',
  `dstchannel` varchar(80) NOT NULL default '',
  `didnumber` varchar(30) NOT NULL default '',
  `duration` int(11) NOT NULL default '0',
  `billsec` int(11) NOT NULL default '0',
  `billsec_leg_a` int(11) NOT NULL DEFAULT 0,
  `disposition` varchar(45) NOT NULL default '',
  `accountcode` varchar(100) NOT NULL default '',
  `userfield` varchar(255) NOT NULL default '',
  `srcuid` varchar(40) NOT NULL default '',
  `dstuid` varchar(40) NOT NULL default '',
  `queue` varchar(30) NOT NULL DEFAULT '',
  `calltype` varchar(255) NOT NULL default '',
  `credit` double(24,4) NOT NULL default '0.0000',
  `callshopcredit` double(24,4) NOT NULL default '0.0000',
  `resellercredit` double(24,4) NOT NULL default '0.0000',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `accountid` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `transfertime` datetime NOT NULL default '0000-00-00 00:00:00',
  `transfertarget` varchar(50) NOT NULL default '',
  `monitored` int(11) NOT NULL default 0,
  `memo` varchar(100) NOT NULL default '',
  `dialstring` varchar(100) not null default '',
  `dialstatus` VARCHAR(40) NOT NULL DEFAULT '',
  `agentchan` varchar(100) NOT NULL default '',
  `children`  varchar(255) not null default '',
  `ischild`  enum('yes','no') not null default 'no',
  `processed` int(1) NOT NULL default '0', #1->已处理CDR,2已处理录音
  `customerid` int(11) NOT NULL default 0,
  `crm_customerid` int(11) NOT NULL default 0,
  `contactid` int(11) NOT NULL default 0,
  `discount` double(8,4) NOT NULL default '0.0000',
  `payment`  varchar(15) NOT NULL default '',
  `note` text default '',
  `setfreecall` enum('yes','no') default 'no',
  `astercrm_groupid` int(11) NOT NULL default 0,
  `hangupcause` varchar(3) NOT NULL DEFAULT '',
  `hangupcausetxt` varchar(50) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`),
  INDEX `customerid` (`customerid`),
  KEY `srcid` (`src`,`dst`,`channel`,`didnumber`,`dstchannel`,`duration`,`billsec`,`disposition`),
  INDEX `src` ( `srcuid` ),
  INDEX `dst` ( `dstuid` )
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

########################################################

## 
## table `historycdr`
## 

DROP TABLE IF EXISTS `historycdr`;

CREATE TABLE `historycdr` (
  `id` int(11) NOT NULL auto_increment,
  `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `src` varchar(30) NOT NULL default '',
  `dst` varchar(30) NOT NULL default '',
  `srcname` varchar(100) NOT NULL default '',
  `channel` varchar(50) NOT NULL default '',
  `dstchannel` varchar(50) NOT NULL default '',
  `didnumber` varchar(30) NOT NULL default '',
  `duration` int(11) NOT NULL default '0',
  `billsec` int(11) NOT NULL default '0',
  `billsec_leg_a` int(11) NOT NULL DEFAULT 0,
  `disposition` varchar(45) NOT NULL default '',
  `accountcode` varchar(20) NOT NULL default '',
  `userfield` varchar(255) NOT NULL default '',
  `srcuid` varchar(20) NOT NULL default '',
  `dstuid` varchar(20) NOT NULL default '',
  `queue` varchar(30) NOT NULL DEFAULT '',
  `calltype` varchar(255) NOT NULL default '',
  `credit` double(24,4) NOT NULL default '0.0000',
  `callshopcredit` double(24,4) NOT NULL default '0.0000',
  `resellercredit` double(24,4) NOT NULL default '0.0000',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `accountid` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `monitored` int(11) NOT NULL default 0,  
  `memo` varchar(100) NOT NULL default '',
  `dialstring` varchar(100) not null default '',
  `dialstatus` VARCHAR(40) NOT NULL DEFAULT '',
  `children`  varchar(255) not null default '',
  `ischild`  enum('yes','no') not null default 'no',
  `processed` int(1) NOT NULL default '0', #1->已处理CDR,2已处理录音
  `customerid` int(11) NOT NULL default 0,
  `crm_customerid` int(11) NOT NULL default 0,
  `contactid` int(11) NOT NULL default 0,
  `discount` double(8,4) NOT NULL default '0.0000',
  `payment`  varchar(15) NOT NULL default '',
  `note` text default '',
  `setfreecall` enum('yes','no') default 'no',
  `astercrm_groupid` int(11) NOT NULL default 0,
  `hangupcause` varchar(3) NOT NULL DEFAULT '',
  `hangupcausetxt` varchar(50) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`),
  KEY `srcid` (`src`,`dst`,`channel`,`didnumber`,`dstchannel`,`duration`,`billsec`,`disposition`),
  INDEX `dst` (`dst`),
  INDEX `destination` (`destination`),
  INDEX `calldate` (`calldate`),
  INDEX `customerid` (`customerid`),
  INDEX `resellerid` (`resellerid`),
  INDEX `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

############### For astercrm ###################################

##########################################################

## 
## table `agentlogin_history`
## 

DROP TABLE IF EXISTS `agentlogin_history`;

CREATE TABLE `agentlogin_history` (
 `agent` varchar(30) NOT NULL default '',
 `channel` varchar(30) NOT NULL default '',
 `agentlogin` datetime NOT NULL default '0000-00-00 00:00:00',
 `agentlogout` datetime NOT NULL default '0000-00-00 00:00:00',
 `uniqueid` varchar(40) NOT NULL,
 `online` int(11) NOT NULL default '0'
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `astercrm_account`
## 

DROP TABLE IF EXISTS `astercrm_account`;

CREATE TABLE `astercrm_account` (
 `id` int(11) NOT NULL auto_increment,
 `username` varchar(30) NOT NULL default '',
 `password` varchar(30) NOT NULL default '',
 `firstname` varchar(15) NOT NULL default '',		
 `lastname` varchar(15) NOT NULL default '',		
 `agent` varchar(50) NOT NULL default '',
 `callerid` varchar(30) NOT NULL default '',
 `extension` varchar(15) NOT NULL default '',
 `extensions` varchar(200) NOT NULL default '',
 `channel` varchar(30) NOT NULL default '',
 `usertype` varchar(20) NOT NULL default '',
 `usertype_id` int(11) NOT NULL default 0,
 `dialinterval` int(5) NULL,
 `accountcode` varchar(20) NOT NULL default '',
 `last_login_time` datetime NOT NULL default '0000-00-00 00:00:00',#agent last_login_time
 `last_update_time` datetime NOT NULL default '0000-00-00 00:00:00',#agent last_update_time
 `groupid` int(11) NOT NULL default '0',
 UNIQUE KEY `id` (`id`),
 UNIQUE KEY `username` (`username`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

INSERT INTO `astercrm_account` (
 `id` ,
 `username` ,
 `password` ,
 `extension` ,
 `extensions` ,
 `usertype` 
)
VALUES (
 NULL , 'admin', 'admin', '0000', '', 'admin'
);

##########################################################

## 
## table `mailboxes`
## 
DROP TABLE IF EXISTS `mailboxes`;

CREATE TABLE `mailboxes` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `mailbox` varchar(50) NOT NULL default '',
  `newmessages` int(11) NOT NULL default '0',
  `oldmessages` int(11) NOT NULL default '0',
UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

###########################################################

## 
## table `queuestatus`
## 

DROP TABLE IF EXISTS `queuestatus`;

CREATE TABLE `queuestatus` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `channel` VARCHAR( 60 ) NOT NULL ,
 `callerid` VARCHAR( 40 ) NOT NULL ,
 `calleridname` VARCHAR( 40 ) NOT NULL ,
 `queue` VARCHAR( 40 ) NOT NULL ,
 `position` INT NOT NULL ,
 `count` INT NOT NULL ,
 `uniqueid` varchar(50) NOT NULL DEFAULT '',
 `cretime` DATETIME NOT NULL ,
 UNIQUE (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `astercrm_accountgroup`
## 

DROP TABLE IF EXISTS `astercrm_accountgroup`;

CREATE TABLE `astercrm_accountgroup` (
 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `groupname` VARCHAR( 30 ) NOT NULL ,
 `groupnote` VARCHAR( 255 ) NOT NULL ,				
 `groupid` INT NOT NULL ,
 `incontext` VARCHAR( 50 ) NOT NULL  ,
 `outcontext` VARCHAR( 50 ) NOT NULL  ,
 `monitorforce` INT(1) NOT NULL default 0,
 `agentinterval` int(5) NULL,
 `notice_interval` int(11) NOT NULL default '0',#the ticket notice interval time(任务提醒时间间隔)
 `billingid` int(11) NOT NULL default 0,
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 `firstring` ENUM('caller','callee') NOT NULL DEFAULT 'caller',
 `allowloginqueue` ENUM('yes','no') NOT NULL DEFAULT 'no',
 `clear_popup` int(5) NULL,
 `creby` varchar(30) NOT NULL default '',
 UNIQUE (`groupid`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `campaign`
## 

DROP TABLE IF EXISTS `campaign`;

CREATE TABLE `campaign` ( #added by solo 2008#2#5
 `id` int(11) NOT NULL auto_increment,
 `groupid` int(11) NOT NULL default '0',
 `serverid` int(11) NOT NULL default '0',
 `campaignname` varchar(30) NOT NULL default '',
 `campaignnote` varchar(255) NOT NULL default '',
 `enable` int(1) NOT NULL default '0',
 `firstcontext` varchar(60) NOT NULL default '',
 `outcontext` varchar(60) NOT NULL default '',
 `incontext` varchar(60) NOT NULL default '',
 `nextcontext` varchar(60) NOT NULL default '',
 `inexten` varchar(30) NOT NULL default '',
 `callerid` varchar(30) NOT NULL default '',
 `queuename` varchar(15) NOT NULL default '',
 `bindqueue` BOOL NOT NULL DEFAULT '0',
 `limit_type` varchar(15) NOT NULL default 'channel',
 `max_channel` int(4) NOT NULL default '5',
 `queue_increasement` float(8,2) NOT NULL default '1.00',
 `status` varchar(4) NOT NULL default 'idle',
 `max_dialing` int(4) NOT NULL default '0',
 `fileid` int(11) NOT NULL default '0',		#added by solo 2008#5#4
 `end_fileid` int(11) NOT NULL default '0',		#added by solo 2008#5#4
 `phonenumber` varchar(255) NOT NULL default '',	#added by solo 2008#5#4
 `waittime`  varchar(3) NOT NULL default '45',
 `worktime_package_id` int(11) NOT NULL default '0',
 `maxtrytime` int(11) NOT NULL default '1',
 `recyletime` int(11) NOT NULL default '3600',
 `enablerecyle` enum ('yes','no') not null default 'no',
 `minduration` int(11) NOT NULL default '0',
 `minduration_billsec` int(11) NOT NULL default '0',
 `minduration_leg_a` int(11) NOT NULL default '0',
 `dialtwoparty` enum ("yes","no") not null default "no",
 `queue_context` varchar(60) not null default '',
 `use_ext_chan` ENUM('yes','no') NOT NULL default 'no',
 `billsec` int(4) NOT NULL default '0',
 `billsec_leg_a` int(4) NOT NULL default '0',
 `duration_answered` int(4) NOT NULL default '0',
 `duration_noanswer` int(4) NOT NULL default '0',
 `answered` int(4) NOT NULL default '0',
 `dialed` int(4) NOT NULL default '0',
 `transfered` int(11) NOT NULL default '0',
 `sms_number` varchar(30) NOT NULL default '',
 `balance` int(11) NOT NULL default 0,#可用余额
 `init_billing` int(11) NOT NULL default 0,#初始计费
 `billing_block` int(11) NOT NULL default 0,#计费周期
 `enablebalance` ENUM('yes','no','strict') NOT NULL default 'yes',#余额控制
 `creby` varchar(30) NOT NULL default '',
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `worktimes`
## 

DROP TABLE IF EXISTS `worktimes`;

CREATE TABLE `worktimes` (
`id` int(11) NOT NULL auto_increment,
`starttime` time default null,
`endtime` time default null,
`startweek` int(1)  NOT NULL default '0',
`endweek` int(1)  NOT NULL default '0',
`groupid` INT NOT NULL DEFAULT '0',
`cretime` datetime default NULL ,
`creby` varchar(30) NOT NULL default '',
UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `worktimepackages`
## 

DROP TABLE IF EXISTS `worktimepackages`;

CREATE TABLE `worktimepackages` (
`id` int(11) NOT NULL auto_increment,
`worktimepackage_name` varchar(30) NOT NULL,
`worktimepackage_note` varchar(255) NOT NULL,
`worktimepackage_status` enum('enable','disabled') DEFAULT 'enable',
`groupid` INT NOT NULL DEFAULT '0',
`cretime` datetime default NULL ,
`creby` varchar(30) NOT NULL default '',
UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `worktimepackage_worktimes`
## 

DROP TABLE IF EXISTS `worktimepackage_worktimes`;

CREATE TABLE `worktimepackage_worktimes` (
`id` int(11) NOT NULL auto_increment,
`worktimepackage_id` int(11) NOT NULL,
`worktime_id` int(11) NOT NULL,
`cretime` datetime default NULL ,
`creby` varchar(30) NOT NULL default '',
UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################
## 
## table `contact`
## 

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
 `id` int(11) NOT NULL auto_increment,
 `contact` varchar(30) NOT NULL default '',
 `gender` varchar(10) NOT NULL default 'unknown',	#add 2007#10#5 by solo
 `position` varchar(100) NOT NULL default '',
 `phone` varchar(50) NOT NULL default '',
 `ext` varchar(8) NOT NULL default '',
 `phone1` varchar(50) NOT NULL default '',
 `ext1` varchar(8) NOT NULL default '',
 `phone2` varchar(50) NOT NULL default '',
 `ext2` varchar(8) NOT NULL default '',
 `mobile` varchar(50) NOT NULL default '',
 `fax` varchar(50) NOT NULL default '',
 `fax_ext` varchar(8) NOT NULL default '',					
 `email` varchar(100) NOT NULL default '',
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 `creby` varchar(30) NOT NULL default '',
 `customerid` int(11) NOT NULL default '0',
 `groupid` INT NOT NULL ,
 UNIQUE KEY `id` (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `customer`
## 

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
 `id` int(11) NOT NULL auto_increment,
 `customer` varchar(120) NOT NULL default '',
 `first_name` varchar(50) NOT NULL default '',#add 2011#7#14 by shixb
 `last_name` varchar(50) NOT NULL default '',#add 2011#7#14 by shixb
 `customertitle` varchar(30) default '',
 `address` varchar(200) NOT NULL default '',
 `zipcode` varchar(10) NOT NULL default '',
 `website` varchar(100) NOT NULL default '',
 `category` varchar(255) NOT NULL default '',
 `city`	varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `state` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `country` varchar(50) NOT NULL default '',			
 `phone` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `phone_ext` varchar(8) NOT NULL default '',		#add 2008#10#24 by solo
 `fax` varchar(50) NOT NULL default '',		#add 2007#10#24 by solo
 `fax_ext` varchar(8) NOT NULL default '',		#add 2008#10#24 by solo
 `mobile` varchar(50) NOT NULL default '',	#add 2007#10#24 by solo
 `email` varchar(50) NOT NULL default '',	#add 2007#10#24 by solo
 `contact` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `contactgender` varchar(10) NOT NULL default 'unknown',	#add 2007#10#5 by solo
 `bankname` varchar(100) NOT NULL default '',	#add 2007#10#15 by solo
 `bankaccount` varchar(100) NOT NULL default '',	#add 2007#10#15 by solo
 `bankzip` varchar(100) NOT NULL default '',	#add 2007#10#26 by solo
 `bankaccountname` varchar(100) NOT NULL default '',	#add 2007#10#25 by solo
 `last_note_id` int(11) NOT NULL default 0,
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 `creby` varchar(30) NOT NULL default '',
 `groupid` INT NOT NULL ,
  UNIQUE KEY `id` (`id`),
  INDEX `groupid` (`groupid`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `dialedlist`
## 

DROP TABLE IF EXISTS `dialedlist`;

CREATE TABLE `dialedlist` (
  `id` int(11) NOT NULL auto_increment,
  `dialednumber` varchar(30) NOT NULL default '',
  `dialtime` datetime NOT NULL default '0000-00-00 00:00:00',		#added by solo 2008/05/04
  `answertime` datetime NOT NULL default '0000-00-00 00:00:00',		#added by solo 2008#2#1
  `duration` int(11) NOT NULL default '0',											#added by solo 2008#2#1
  `billsec` int(11) NOT NULL DEFAULT 0,
  `billsec_leg_a` int(11) NOT NULL DEFAULT 0,
  `credit` float(8,2) NOT NULL default '0.00',   #added by menglj   2011#5#6
  `transfertime` int(11) NOT NULL default '0',				#added by solo 2008#5#4										#added by solo 2008#2#1
  `response` varchar(20) NOT NULL default '',											#added by solo 2008#2#1
  `customerid` int(11) NOT NULL default 0,
  `customername` varchar(100) default '',
  `callresult` varchar(60) default '',
  `campaignresult` varchar(60) default '',
  `detect` varchar(30) NOT NULL default '',
  `resultby` varchar(30) NOT NULL default '',
  `uniqueid` varchar(40) NOT NULL default '',										#added by solo 2008#2#1
  `channel` varchar(50) NOT NULL DEFAULT '',
  `amd` enum('yes','no') NOT NULL DEFAULT 'no',
  `groupid` INT NOT NULL DEFAULT '0',															#added by solo 2008#2#3
  `campaignid` INT NOT NULL DEFAULT 0,														#added by solo 2008#2#5
  `assign` varchar(20) NOT NULL default '',												#added by solo 2008#2#10
  `trytime` INT(11) NOT NULL DEFAULT '0',
  `dialedby` varchar(30) NOT NULL default '',
  `dialedtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `callOrder` INT(11) NOT NULL DEFAULT '1',				#added by solo 2009/10/31
  `memo` varchar(255) NOT NULL default '',				#added by shixb 2010#8#12
  `creby` varchar(30) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE = MEMORY DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `campaigndialedlist`;

CREATE TABLE `campaigndialedlist` (
  `id` int(11) NOT NULL auto_increment,
  `mycdr_id` int(11) NOT NULL default 0,
  `dialednumber` varchar(30) NOT NULL default '',
  `dialtime` datetime NOT NULL default '0000-00-00 00:00:00',       
  `answertime` datetime NOT NULL default '0000-00-00 00:00:00',       
  `duration` int(4) NOT NULL default '0',               
  `billsec` int(4) NOT NULL default '0',
  `credit` float(8,2) NOT NULL default '0.00',   #added by menglj   2011#5#6
  `billsec_leg_a` int(4) NOT NULL default '0',               
  `transfertime` datetime NOT NULL default '0000-00-00 00:00:00',
  `transfertarget` varchar(50) NOT NULL default '',
  `response` varchar(20) NOT NULL default '',
  `customerid` int(11) NOT NULL default 0,
  `customername` varchar(100) default '',
  `callresult` varchar(60) default '',
  `campaignresult` varchar(60) default '',
  `detect` varchar(30) NOT NULL default '',
  `memo` varchar(255) not null default '',
  `resultby` varchar(30) NOT NULL default '',
  `uniqueid` varchar(40) NOT NULL default '',               
  `channel` varchar(40) NOT NULL default '',               
  `groupid` INT NOT NULL DEFAULT '0',                   
  `campaignid` INT NOT NULL DEFAULT 0,                   
  `assign` varchar(20) NOT NULL default '',               
  `trytime` INT(11) NOT NULL DEFAULT '0',
  `dialedby` varchar(30) NOT NULL default '',
  `dialedtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `callOrder` INT(11) NOT NULL DEFAULT '1',
  `processed` enum('yes','no') NOT NULL default 'no',
  `recycles` int(11) NOT NULL default 0,
  `creby` varchar(30) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `diallist`
## 

DROP TABLE IF EXISTS `diallist`;

#store Predictive dialer phone list
CREATE TABLE `diallist` (
  `id` int(11) NOT NULL auto_increment,
  `dialnumber` varchar(30) NOT NULL default '',
  `dialtime` datetime NOT NULL default '0000-00-00 00:00:00',		#added by solo 2008/05/04
  `assign` varchar(20) NOT NULL default '',
  `status` varchar(50) NOT NULL default '',				#added by solo 2008/05/04
  `customerid` INT(11) NOT NULL DEFAULT '0',				#added by solo 2009#09#03
  `customername` varchar(100) default '',				#added by solo 2009#09#09
  `groupid` INT(11) NOT NULL DEFAULT '0',				#added by solo 2007#12#17
  `trytime` INT(11) NOT NULL DEFAULT '0',				#added by solo 2008/05/04
  `callOrder` INT(11) NOT NULL DEFAULT '1',				#added by solo 2009/10/31
  `campaignid` INT NOT NULL DEFAULT 0,					#added by solo 2008#2#5
  `memo` varchar(255) NOT NULL default '',				#added by shixb 2010#8#9
  `creby` varchar(30) NOT NULL default '',			#added by solo 2008#1#15
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',	#added by solo 2008#1#15
  UNIQUE KEY `id` (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `events`
## 

DROP TABLE IF EXISTS `events`;

CREATE TABLE `events` (
  `id` int(16) NOT NULL auto_increment,
  `timestamp` datetime default NULL,
  `event` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `event` (`event`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `survey`
## 

DROP TABLE IF EXISTS `survey`;

CREATE TABLE `survey` (
  `id` int(11) NOT NULL auto_increment,
  `surveyname` varchar(30) NOT NULL default '',
  `surveynote` varchar(255) NOT NULL default '',							#add 2008#1#11 by solo
  `enable` smallint(6) NOT NULL default '0',									#add 2007#10#15 by solo
  `campaignid` int(11) not null default 0,
  `groupid` int(11) NOT NULL default '0',											#added by solo 2008#1#15
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  `creby` varchar(30) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `surveyoptions`
## 

DROP TABLE IF EXISTS `surveyoptions`;

CREATE TABLE `surveyoptions` (
  `id` int(11) NOT NULL auto_increment,
  `surveyoption` varchar(50) NOT NULL default '',
  `optionnote` varchar(255) NOT NULL default '',	#added by solo 2008#1#14
  `optiontype` ENUM( 'checkbox', 'radio', 'text' ) NOT NULL DEFAULT 'radio',
  `surveyid` int(11) NOT NULL default '0',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  `creby` varchar(30) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `surveyresult`
## 

DROP TABLE IF EXISTS `surveyresult`;

CREATE TABLE `surveyresult` (
  `id` int(11) NOT NULL auto_increment,
  `customerid` int(11) NOT NULL default '0',
  `contactid` int(11) NOT NULL default '0',
  `phonenumber` varchar(30) not null default '',
  `campaignid` int(11) not null default '0',
  `surveyid` int(11) NOT NULL default '0',
  `surveytitle` VARCHAR( 30 ) NOT NULL,
  `surveyoptionid` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `itemcontent` VARCHAR( 50 ) NOT NULL,
  `surveyoption` varchar(50) NOT NULL default '',
  `surveynote` text NOT NULL,
  `uniqueid` varchar(40) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `creby` varchar(30) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `surveyoptionitems`
## 

DROP TABLE IF EXISTS `surveyoptionitems`;

CREATE TABLE `surveyoptionitems` (
`id` int(11) NOT NULL AUTO_INCREMENT ,
`optionid` INT NOT NULL ,
`itemtype` ENUM( 'checkbox', 'radio', 'text' ) NOT NULL DEFAULT 'radio',
`itemcontent` VARCHAR( 254 ) NOT NULL ,
`creby` VARCHAR( 30 ) NOT NULL ,
`cretime` DATETIME NOT NULL ,
PRIMARY KEY ( `id` ) ,
UNIQUE (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `note`
##

DROP TABLE IF EXISTS `note`;

CREATE TABLE `note` (
  `id` int(11) NOT NULL auto_increment,
  `note` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `callerid` varchar(30) NOT NULL default '',
  `priority` int(11) NOT NULL default '0',
  `attitude` int(11) NOT NULL default '0',												#add 2007#10#26 by solo
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  `creby` varchar(30) NOT NULL default '',
  `customerid` int(11) NOT NULL default '0',
  `contactid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `codes` varchar(50) NOT NULL default '',
  `private` int(1) default '1',
  UNIQUE KEY `id` (`id`),
  INDEX `customerid` (`customerid`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `remind`
## 

DROP TABLE IF EXISTS `remind`;

CREATE TABLE `remind` (
 `id` int(11) NOT NULL auto_increment,
 `title` varchar(100) NOT NULL default '',	
 `content` text NOT NULL default '',		
 `remindtime`  datetime NOT NULL default '0000-00-00 00:00:00',
 `remindtype` int(10) not null default 0 ,
 `priority` int(10) NOT NULL default 0,		
 `username` varchar(30) not  null default '' ,	
 `remindabout` varchar(255) not  null default '',      
 `readed` int(10) not null default 0 ,		
 `touser` varchar(50) not null default '',	
 `creby` varchar(30) NOT NULL default '',
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `monitorrecord`
## 

DROP TABLE IF EXISTS `monitorrecord`;

CREATE TABLE `monitorrecord` (
 `id` INT NOT NULL AUTO_INCREMENT,
 `callerid` VARCHAR( 20 ) NOT NULL DEFAULT '',
 `filename` VARCHAR( 128 ) NOT NULL DEFAULT '',
 `fileformat` enum('wav','gsm','mp3','error') NOT NULL DEFAULT 'error',
 `processed` enum('yes','no') NOT NULL DEFAULT 'no',
 `groupid` INT NOT NULL DEFAULT 0,
 `accountid` INT NOT NULL DEFAULT 0,
 `extension` VARCHAR( 15 ) NOT NULL DEFAULT '',
 `uniqueid` varchar(40) NOT NULL default '',
 `creby` VARCHAR( 30 ) NOT NULL ,
 `cretime` DATETIME NOT NULL ,
 UNIQUE (`id`),
KEY `monitorid`(`uniqueid`,`filename`,`creby`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `trunkinfo`
## 

DROP TABLE IF EXISTS `trunkinfo`;

CREATE TABLE `trunkinfo` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `trunkname` VARCHAR( 30 ) NOT NULL ,
 `trunkchannel` VARCHAR( 50 ) NOT NULL ,
 `trunk_number` varchar(30) NOT NULL default '',
 `didnumber` VARCHAR(30) NOT NULL,
 `trunknote` TEXT NOT NULL ,
 `creby` VARCHAR( 30 ) NOT NULL ,
 `cretime` DATETIME NOT NULL ,
 INDEX ( `trunkchannel` ) ,
 INDEX ( `didnumber` ) ,
 UNIQUE (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `asteriskcalls`
## 

DROP TABLE IF EXISTS `asteriskcalls`;

CREATE TABLE `asteriskcalls` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `asteriskcallsname` VARCHAR( 50 ) NOT NULL ,
 `outcontext` VARCHAR( 50 ) NOT NULL ,
 `incontext` VARCHAR( 50 ) NOT NULL ,
 `inextension` VARCHAR( 50 ) NOT NULL ,
 `groupid` INT NOT NULL ,
 `cretime` DATETIME NOT NULL ,
 `creby` VARCHAR( 30 ) NOT NULL ,
 UNIQUE ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `remindercalls`
## 

DROP TABLE IF EXISTS `remindercalls`;

CREATE TABLE `remindercalls` (
 `id` INT NOT NULL AUTO_INCREMENT ,
 `customerid` INT NOT NULL ,
 `contactid` INT NOT NULL ,
 `phonenumber` VARCHAR( 50 ) NOT NULL ,
 `asteriskcallsid` INT NOT NULL ,
 `creby` VARCHAR( 30 ) NOT NULL ,
 `cretime` DATETIME NOT NULL ,
 `note` VARCHAR( 255 ) NOT NULL ,
 `result` VARCHAR( 255 ) NOT NULL ,
 `groupid` INT NOT NULL ,
 `dialtime` DATETIME NOT NULL ,
 `status` VARCHAR( 50 ) NOT NULL ,
 UNIQUE ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

## 
## table `speeddial`
## 

DROP TABLE IF EXISTS `speeddial`;

CREATE TABLE `speeddial` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(30) NOT NULL default '',
  `number` varchar(20) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `creby` VARCHAR( 30 ) NOT NULL ,
  `cretime` DATETIME NOT NULL ,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################


####### FOR QUEUE STATS ###########

##
## Table structure for table `qagent`
##

DROP TABLE IF EXISTS `qagent`;

CREATE TABLE `qagent` (
  agent_id int(6) NOT NULL auto_increment,
  agent varchar(40) NOT NULL default '',
  PRIMARY KEY  (agent_id)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##
## Table structure for table `qevent`
##

DROP TABLE IF EXISTS `qevent`;

CREATE TABLE `qevent` (
  event_id int(2) NOT NULL default '0',
  event varchar(40) default NULL,
  PRIMARY KEY  (event_id)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##
## Dumping data for table `qevent`
##

INSERT INTO qevent VALUES (1,'ABANDON');
INSERT INTO qevent VALUES (2,'AGENTDUMP');
INSERT INTO qevent VALUES (3,'AGENTLOGIN');
INSERT INTO qevent VALUES (4,'AGENTCALLBACKLOGIN');
INSERT INTO qevent VALUES (5,'AGENTLOGOFF');
INSERT INTO qevent VALUES (6,'AGENTCALLBACKLOGOFF');
INSERT INTO qevent VALUES (7,'COMPLETEAGENT');
INSERT INTO qevent VALUES (8,'COMPLETECALLER');
INSERT INTO qevent VALUES (9,'CONFIGRELOAD');
INSERT INTO qevent VALUES (10,'CONNECT');
INSERT INTO qevent VALUES (11,'ENTERQUEUE');
INSERT INTO qevent VALUES (12,'EXITWITHKEY');
INSERT INTO qevent VALUES (13,'EXITWITHTIMEOUT');
INSERT INTO qevent VALUES (14,'QUEUESTART');
INSERT INTO qevent VALUES (15,'SYSCOMPAT');
INSERT INTO qevent VALUES (16,'TRANSFER');
INSERT INTO qevent VALUES (17,'PAUSE');
INSERT INTO qevent VALUES (18,'UNPAUSE');
INSERT INTO qevent VALUES (19,'ADDMEMBER');
INSERT INTO qevent VALUES (20,'REMOVEMEMBER');

##
## Table structure for table `qname`
##

DROP TABLE IF EXISTS `qname`;

CREATE TABLE `qname` (
  qname_id int(6) NOT NULL auto_increment,
  queue varchar(40) NOT NULL default '',
  PRIMARY KEY  (qname_id)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##
## Table structure for table `queue_stats`
##

DROP TABLE IF EXISTS `queue_stats`;

CREATE TABLE `queue_stats` (
  queue_stats_id int(12) NOT NULL auto_increment,
  uniqueid varchar(40) default NULL,
  datetime datetime NOT NULL default '0000-00-00 00:00:00',
  qname int(6) default NULL,
  qagent int(6) default NULL,
  qevent int(2) default NULL,
  info1 varchar(40) default NULL,
  info2 varchar(40) default NULL,
  info3 varchar(40) default NULL,
  src varchar(32) default NULL,
  dst varchar(32) default NULL,
 PRIMARY KEY  (queue_stats_id),
  UNIQUE KEY unico (datetime,qname,qagent,qevent)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `queue_name`
## 

DROP TABLE IF EXISTS `queue_name`;

CREATE TABLE `queue_name` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `queuename` varchar(32) NOT NULL default '',
  `curcalls` int NOT NULL default 0,
  `limit_type` varchar(32) NOT NULL default '',
  `strategy` varchar(32) NOT NULL default '',
  `holdtime` int NOT NULL default 0,
  `talktime` int(11) not null default 0,
  `w` int NOT NULL default 0,
  `calls_answered` int NOT NULL default 0,
  `calls_unanswered` int NOT NULL default 0,
  `service_level` int NOT NULL default 0,
  `t` int NOT NULL default 0,
  `data` varchar(255) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY unico (`queuename`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `queue_agent`
## 

DROP TABLE IF EXISTS `queue_agent`;

CREATE TABLE `queue_agent` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `queuename` varchar(32) NOT NULL default '',
  `agentname` varchar(50) NOT NULL default '',
  `agent` varchar(255) NOT NULL default '',
  `agent_status` varchar(32) NOT NULL default '',
  `ispaused` int(1) NOT NULL default 0,
  `isdynamic` int(1) NOT NULL default 0,
  `takencalls` int NOT NULL default 0,
  `lastcall` int NOT NULL default 0,
  `data` varchar(255) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `queue_caller`
## 

DROP TABLE IF EXISTS `queue_caller`;

CREATE TABLE `queue_caller` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `queuename` varchar(32) NOT NULL default '',
  `corder` int NOT NULL default 0,
  `caller` varchar(32) NOT NULL default '',
  `waittime` int NOT NULL default 0,
  `prio` int NOT NULL default 0,
  `data` varchar(255) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `agentlogin_history`
## 

DROP TABLE IF EXISTS `agentlogin_history`;

CREATE TABLE `agentlogin_history` (
 `agent` varchar(30) NOT NULL default '',
 `channel` varchar(30) NOT NULL default '',
 `agentlogin` datetime NOT NULL default '0000-00-00 00:00:00',
 `agentlogout` datetime NOT NULL default '0000-00-00 00:00:00',
 `uniqueid` varchar(40) NOT NULL,
 `online` int(11) NOT NULL default '0'
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `uploadfile`
## 

DROP TABLE IF EXISTS `uploadfile`;

CREATE TABLE `uploadfile` (
`id` int(11) NOT NULL auto_increment,
`filename` varchar(100) NOT NULL default '',
`originalname` varchar(100) NOT NULL default '',
`type` enum('astercrm','asterbilling') NOT NULL default 'astercrm',
`cretime` datetime default NULL ,
`creby` varchar(30) NOT NULL default '',
`resellerid` int(11) NOT NULL default 0,
`groupid` int(11) NOT NULL default 0,
UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `registry`
## 

DROP TABLE IF EXISTS `registry`;

CREATE TABLE `registry` (
`id` int(11) NOT NULL auto_increment,
`host` varchar(100) NOT NULL default '',
`username` varchar(30) NOT NULL default '',
`refresh` varchar(10) NOT NULL default '',
`state` varchar(50) NOT NULL default '',
`reg_time` varchar(50) NOT NULL default '',
`protocal` enum('sip','iax2','other') not null default 'sip',
 UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

##########################################################

###################################################

DROP TABLE IF EXISTS `meetmes`;

CREATE TABLE `meetmes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `confnum` varchar(10) NOT NULL DEFAULT '',
  `parties` varchar(5) NOT NULL DEFAULT '',
  `marked` varchar(30) NOT NULL DEFAULT '',
  `activity` varchar(8) NOT NULL DEFAULT '',
  `creation` varchar(20) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `meetmelists`;

CREATE TABLE `meetmelists` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `confnum` varchar(10) NOT NULL DEFAULT '',
  `userid` varchar(2) NOT NULL DEFAULT '',
  `callerid` varchar(30) NOT NULL DEFAULT '',
  `callername` varchar(30) NOT NULL DEFAULT '',
  `channel` varchar(100) NOT NULL DEFAULT '',
  `monitorstatus` varchar(20) NOT NULL DEFAULT '',
  `duration` varchar(20) NOT NULL DEFAULT '',
  `durationsrc` int(11) NOT NULL DEFAULT '0',
  `data` varchar(255) NOT NULL DEFAULT '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `campaignresult`;

CREATE TABLE `campaignresult` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `resultname` varchar(30) NOT NULL DEFAULT '',
  `resultnote` varchar(255) NOT NULL DEFAULT '',
  `status` enum('ANSWERED','NOANSWER'),
  `parentid` int(11) NOT NULL DEFAULT '0',
  `campaignid` int(11) NOT NULL DEFAULT '0',
  `groupid` int(11) NOT NULL DEFAULT '0',
  `creby` varchar(30) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `knowledge`;

CREATE TABLE `knowledge` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `knowledgetitle` varchar(200) NOT NULL DEFAULT '',
  `content` text NOT NULL DEFAULT '',
  `groupid` int(11) NOT NULL DEFAULT '0',
  `creby` varchar(30) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `ap_curspools`;

CREATE TABLE `ap_curspools` (
 `id`  int(11) unsigned NOT NULL auto_increment,
 `action` varchar(40) NOT NULL DEFAULT '',
 `creby` varchar(30) NOT NULL default '',
 `created` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `scheduler` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `priority` int(4) DEFAULT '1',
 `lasttry` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `try` int(4) DEFAULT '1',
 `tried` int(4) DEFAULT '1',
 `connected` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `status` enum("new","pending","success","failed") default 'new',
 `account` varchar(40) NOT NULL DEFAULT '',
 `channel` varchar(40) NOT NULL DEFAULT '',
 `exten` varchar(40) NOT NULL DEFAULT '',
 `context` varchar(40) NOT NULL DEFAULT '',
 `waittime` int(4) NOT NULL DEFAULT '45',
 `callerid` varchar(40) NOT NULL DEFAULT '',	
 `variable` varchar(255) NOT NULL DEFAULT '',	
 `application` varchar(255) NOT NULL DEFAULT '',
 `data` varchar(255) NOT NULL DEFAULT '',
 `actionid` varchar(35) NOT NULL DEFAULT '',
 `interval` int(4) NOT NULL DEFAULT '3600',
 `async` varchar(4) NOT NULL DEFAULT '0',
 `callback_start` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `callback_end` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `callback_status` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `asteriskserver_id` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE = HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `ap_myspools`;

CREATE TABLE `ap_myspools` (
 `id`  int(11) unsigned NOT NULL auto_increment,
 `action` varchar(40) NOT NULL DEFAULT '',
 `creby` varchar(30) NOT NULL default '',
 `created` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `scheduler` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `priority` int(4) DEFAULT '1',
 `lasttry` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `try` int(4) DEFAULT '1',
 `tried` int(4) DEFAULT '1',
 `connected` datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
 `status` enum("new","pending","success","failed") default 'new',
 `account` varchar(40) NOT NULL DEFAULT '',
 `channel` varchar(40) NOT NULL DEFAULT '',
 `exten` varchar(40) NOT NULL DEFAULT '',
 `context` varchar(40) NOT NULL DEFAULT '',
 `waittime` int(4) NOT NULL DEFAULT '45',
 `callerid` varchar(40) NOT NULL DEFAULT '',	
 `variable` varchar(255) NOT NULL DEFAULT '',	
 `application` varchar(255) NOT NULL DEFAULT '',
 `data` varchar(255) NOT NULL DEFAULT '',
 `actionid` varchar(35) NOT NULL DEFAULT '',
 `interval` int(4) NOT NULL DEFAULT '3600',
 `async` varchar(4) NOT NULL DEFAULT '0',
 `callback_start` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `callback_end` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `callback_status` varchar(200) NOT NULL DEFAULT '',	# 是否需要将结果写到其他的表
 `asteriskserver_id` int(11) NOT NULL DEFAULT '0',
 `duration` int(4) NOT NULL DEFAULT '0',
 `agentno` varchar(30) NOT NULL DEFAULT '',
 `agent_group_id` int(11) NOT NULL DEFAULT '0',
 `team_id` int(11) NOT NULL DEFAULT '0',
 `modeltype` varchar(30) NOT NULL DEFAULT '',
 `model_id` int(11) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE = HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `account_log`;

CREATE TABLE `account_log` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(30) NOT NULL default '',
  `usertype` varchar(30) NOT NULL default '',
  `ip` varchar(30) NOT NULL default '',
  `action` varchar(30) NOT NULL default '',
  `status` enum("success","failed") default 'failed',
  `failedcause` varchar(100) NOT NULL default '',
  `failedtimes` int(11) NOT NULL default 0,
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `dnc_list`;

CREATE TABLE `dnc_list` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `number` varchar(30) NOT NULL DEFAULT '',
  `campaignid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `status` enum('enable','disabled') default 'enable',
  `creby` varchar(30) NOT NULL default '',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`ticketname` VARCHAR(100) NOT NULL DEFAULT '',
`campaignid` int(11) NOT NULL DEFAULT 0,
`groupid` int(11) NOT NULL DEFAULT  0,
`fid` int(11) NOT NULL DEFAULT 0,
`cretime` datetime DEFAULT NULL,
`creby` varchar(30) NOT NULL DEFAULT '',
 UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;
ALTER TABLE `tickets` AUTO_INCREMENT=100000;

DROP TABLE IF EXISTS `ticket_details`;
CREATE TABLE `ticket_details`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`ticketcategoryid` int(11) NOT NULL DEFAULT 0,
`ticketid` int(11) NOT NULL DEFAULT 0,
`parent_id` varchar(30) NOT NULL DEFAULT '',#parent ticket_detail_id
`customerid` int(11) NOT NULL DEFAULT 0,
`status` ENUM('new','panding','closed','cancel') NOT NULL DEFAULT 'new',
`assignto` int(11) NOT NULL DEFAULT 0,
`groupid` int(11) NOT NULL DEFAULT 0,
`memo` varchar(100) NOT NULL DEFAULT '',
`cretime` datetime DEFAULT NULL,
`creby` varchar(30) NOT NULL DEFAULT '',
 UNIQUE KEY `id` (`id`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


DROP TABLE IF EXISTS `hold_channel`;
CREATE TABLE `hold_channel`(
`id` int(11) NOT NULL AUTO_INCREMENT,
`number` VARCHAR(30) NOT NULL DEFAULT '',
`channel` VARCHAR(100) NOT NULL DEFAULT '',
`uniqueid` VARCHAR(100) NOT NULL DEFAULT '',
`status` VARCHAR(16) NOT NULL DEFAULT '',
`agentchan` VARCHAR(100) NOT NULL DEFAULT '',
`direction` enum('in','out') NOT NULL DEFAULT 'in',
`accountid` int(11) NOT NULL DEFAULT  0,
`cretime` datetime DEFAULT NULL,
 UNIQUE KEY `id` (`id`)
)ENGINE = MEMORY DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


DROP TABLE IF EXISTS `localchannels`;
CREATE TABLE `localchannels` (
`localchannel`  VARCHAR( 60 ) NOT NULL,
`channel` VARCHAR( 60 ) NOT NULL,
`channelstate` varchar(10) NOT NULL DEFAULT '',
`calleridnum` varchar(50) NOT NULL DEFAULT '',
`calleridname` VARCHAR( 50 ) NOT NULL DEFAULT '',
`accountcode` VARCHAR( 50 ) NOT NULL DEFAULT '',
`exten` varchar(20) NOT NULL DEFAULT '',
`context` VARCHAR(20) NOT NULL DEFAULT '',
`uniqueid` VARCHAR( 50 ) NOT NULL ,
`lastupdate` datetime not null default '0000-00-00 00:00:00',
PRIMARY KEY ( `channel` ) ,
UNIQUE (`channel`)
) ENGINE = HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `sendevent`;

CREATE TABLE `sendevent` (
  `id` int(11) NOT NULL auto_increment,
  `src` varchar(50) NOT NULL default '',
  `dst` varchar(50) NOT NULL default '',
  `srcname` varchar(100) NOT NULL default '',  
  `srcchan` varchar(100) NOT NULL default '',
  `dstchan` varchar(100) NOT NULL default '',  
  `didnumber` varchar(30) NOT NULL default '',
  `starttime` datetime NOT NULL default '0000-00-00 00:00:00',
  `answertime` datetime NOT NULL default '0000-00-00 00:00:00',
  `calldate` datetime not null default '0000-00-00 00:00:00',  
  `queue` varchar(30) NOT NULL DEFAULT '',  
  `disposition` varchar(10) NOT NULL default '',
  `curcdrid` int(11) NOT NULL default 0,
  `pushstr` varchar(555) NOT NULL default '',
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY eventid(disposition,curcdrid)
) ENGINE=HEAP DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `customer_leads`
## 

DROP TABLE IF EXISTS `customer_leads`;

CREATE TABLE `customer_leads` (
 `id` int(11) NOT NULL auto_increment,
 `customer` varchar(120) NOT NULL default '',
 `first_name` varchar(50) NOT NULL default '',#add 2011#7#14 by shixb
 `last_name` varchar(50) NOT NULL default '',#add 2011#7#14 by shixb
 `customertitle` varchar(30) default '',
 `address` varchar(200) NOT NULL default '',
 `zipcode` varchar(10) NOT NULL default '',
 `website` varchar(100) NOT NULL default '',
 `category` varchar(255) NOT NULL default '',
 `city`	varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `state` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `country` varchar(50) NOT NULL default '',			
 `phone` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `phone_ext` varchar(8) NOT NULL default '',		#add 2008#10#24 by solo
 `fax` varchar(50) NOT NULL default '',		#add 2007#10#24 by solo
 `fax_ext` varchar(8) NOT NULL default '',		#add 2008#10#24 by solo
 `mobile` varchar(50) NOT NULL default '',	#add 2007#10#24 by solo
 `email` varchar(50) NOT NULL default '',	#add 2007#10#24 by solo
 `contact` varchar(50) NOT NULL default '',	#add 2007#9#30 by solo
 `contactgender` varchar(10) NOT NULL default 'unknown',	#add 2007#10#5 by solo
 `bankname` varchar(100) NOT NULL default '',	#add 2007#10#15 by solo
 `bankaccount` varchar(100) NOT NULL default '',	#add 2007#10#15 by solo
 `bankzip` varchar(100) NOT NULL default '',	#add 2007#10#26 by solo
 `bankaccountname` varchar(100) NOT NULL default '',	#add 2007#10#25 by solo
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 `creby` varchar(30) NOT NULL default '',
 `groupid` INT NOT NULL ,
 `last_note_id` int(11) NOT NULL default 0,
  UNIQUE KEY `id` (`id`),
  INDEX `groupid` (`groupid`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `note_leads`
## 

DROP TABLE IF EXISTS `note_leads`;

CREATE TABLE `note_leads` (
  `id` int(11) NOT NULL auto_increment,
  `note` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `callerid` varchar(30) NOT NULL default '',
  `priority` int(11) NOT NULL default '0',
  `attitude` int(11) NOT NULL default '0',
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  `creby` varchar(30) NOT NULL default '',
  `customerid` int(11) NOT NULL default '0',
  `contactid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `codes` varchar(50) NOT NULL default '',
  `private` int(1) default '1',
  UNIQUE KEY `id` (`id`),
  INDEX `customerid` (`customerid`)
)ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `codes`
## 

DROP TABLE IF EXISTS codes;

CREATE TABLE codes (
 `id` int(11) NOT NULL auto_increment,
 `code` varchar(50) not null default '',
 `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
 `creby` varchar(30) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;
INSERT INTO codes (id,code,cretime,creby) VALUES
(1,'fax',now(),'admin'),
(2,'email',now(),'admin'),
(3,'fax no-time',now(),'admin'),
(4,'email no-time',now(),'admin'),
(5,'T-O',now(),'admin'),
(6,'NI INFO',now(),'admin'),
(7,'CALL BACK INT',now(),'admin'),
(8,'BUSY BUT INFO',now(),'admin'),
(9,'NA',now(),'admin'),
(10,'MANAGER',now(),'admin'),
(11,'CORP',now(),'admin'),
(12,'OC',now(),'admin'),
(13,'OD',now(),'admin'),
(14,'H-UP',now(),'admin'),
(15,'AM',now(),'admin'),
(16,'NP',now(),'admin'),
(17,'MAIL',now(),'admin'),
(18,'WP',now(),'admin'),
(19,'ADDON',now(),'admin'),
(20,'WN',now(),'admin'),
(21,'HOLD',now(),'admin'),
(22,'DA',now(),'admin'),
(23,'NI H-UP',now(),'admin'),
(24,'1800',now(),'admin');


## 
## table `agent_online_time`
## 

DROP TABLE IF EXISTS `agent_online_time`;

CREATE TABLE `agent_online_time` (
 `id` int(11) NOT NULL auto_increment,
 `username` varchar(30) NOT NULL default '',
 `login_time` datetime NOT NULL default '0000-00-00 00:00:00',
 `logout_time` datetime NOT NULL default '0000-00-00 00:00:00',
 `onlinetime` int(11) NOT NULL default 0,
 UNIQUE KEY `id` (`id`)
) ENGINE = MYISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `sms_templates`
## 

DROP TABLE IF EXISTS `sms_templates`;

CREATE TABLE `sms_templates` (
  `id` int(11) NOT NULL auto_increment,
  `templatetitle` varchar(80) NOT NULL default '',
  `belongto` enum('all','campaign','trunk') NOT NULL default 'all',
  `campaign_id` int(11) NOT NULL default 0,
  `trunkinfo_id` int(11) NOT NULL default 0,
  `content` varchar(70) NOT NULL default '',
  `is_edit` enum('yes','no') NOT NULL default 'yes',
  `cretime` datetime NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `sms_sents`
## 

DROP TABLE IF EXISTS `sms_sents`;

CREATE TABLE `sms_sents` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `callerid` varchar(30) NOT NULL default '',
  `target` varchar(20) NOT NULL default '',
  `is_edit` enum('yes','no') NOT NULL default 'yes',
  `content` varchar(70) NOT NULL default '',
  `cretime` datetime NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

## 
## table `user_types`
## 

DROP TABLE IF EXISTS `user_types`;

CREATE TABLE `user_types` (
  `id` int(11) NOT NULL auto_increment,
  `usertype_name` varchar(50) NOT NULL default '',
  `memo` varchar(255) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;
INSERT INTO `user_types` (`id`,`usertype_name`,`memo`,`created`) VALUES (1,'groupoperator','groupoperator',now());

## 
## table `user_privileges`
## 

DROP TABLE IF EXISTS `user_privileges`;

CREATE TABLE `user_privileges` (
  `id` int(11) NOT NULL auto_increment,
  `action` enum('view','edit','delete') NOT NULL default 'view',
  `page` varchar(100) NOT NULL default '',
  `user_type_id` varchar(255) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;
INSERT INTO `user_privileges` (`action`,`page`,`user_type_id`,`created`) VALUES
('view','import','1',now()),
('view','surveyresult','1',now()),
('view','account','1',now()),
('view','customer','1',now()),
('view','predictivedialer','1',now()),
('view','systemstatus','1',now()),
('view','survey','1',now()),
('view','diallist','1',now()),
('view','trunkinfo','1',now()),
('view','cdr','1',now()),
('view','speeddial','1',now()),
('view','report','1',now()),
('view','queuestatus','1',now()),
('view','agent','1',now()),
('view','knowledge','1',now()),
('view','dnc','1',now()),
('view','ticketcategory','1',now()),
('view','useronline','1',now()),
('view','user_online','1',now()),
('view','codes','1',now()),
('view','sms_templates','1',now()),
('view','sms_sents','1',now()),
('view','contact','1',now()),
('view','note','1',now()),
('view','customer_leads','1',now()),
('view','note_leads','1',now()),
('view','dialedlist','1',now()),
('view','campaign','1',now()),
('view','campaignresult','1',now()),
('view','worktimepackages','1',now()),
('view','worktime','1',now()),
('view','ticket_details','1',now()),
('edit','account','1',now()),
('edit','customer','1',now()),
('edit','survey','1',now()),
('edit','diallist','1',now()),
('edit','trunkinfo','1',now()),
('edit','cdr','1',now()),
('edit','speeddial','1',now()),
('edit','report','1',now()),
('edit','agent','1',now()),
('edit','knowledge','1',now()),
('edit','dnc','1',now()),
('edit','ticketcategory','1',now()),
('edit','codes','1',now()),
('edit','sms_templates','1',now()),
('edit','sms_sents','1',now()),
('edit','contact','1',now()),
('edit','note','1',now()),
('edit','customer_leads','1',now()),
('edit','note_leads','1',now()),
('edit','dialedlist','1',now()),
('edit','campaignresult','1',now()),
('edit','worktimepackages','1',now()),
('edit','worktime','1',now()),
('edit','ticket_details','1',now()),
('delete','account','1',now()),
('delete','customer','1',now()),
('delete','survey','1',now()),
('delete','diallist','1',now()),
('delete','trunkinfo','1',now()),
('delete','cdr','1',now()),
('delete','speeddial','1',now()),
('delete','report','1',now()),
('delete','agent','1',now()),
('delete','knowledge','1',now()),
('delete','dnc','1',now()),
('delete','ticketcategory','1',now()),
('delete','codes','1',now()),
('delete','sms_templates','1',now()),
('delete','sms_sents','1',now()),
('delete','contact','1',now()),
('delete','note','1',now()),
('delete','customer_leads','1',now()),
('delete','note_leads','1',now()),
('delete','dialedlist','1',now()),
('delete','campaignresult','1',now()),
('delete','worktimepackages','1',now()),
('delete','worktime','1',now()),
('delete','ticket_details','1',now());


## 
## table `ticket_op_logs`
## 

DROP TABLE IF EXISTS `ticket_op_logs`;
CREATE TABLE `ticket_op_logs` (
  `id` int(11) NOT NULL auto_increment,
  `operate` enum('add','update','assign','delete') not null default 'add',
  `op_field` varchar(30) NOT NULL default '',
  `op_ori_value` varchar(30) NOT NULL default '',
  `op_new_value` varchar(30) NOT NULL default '',
  `curOwner` varchar(30) NOT NULL default '',
  `groupid` int(11) NOT NULL default 0,
  `operator` varchar(30) NOT NULL default '',
  `optime` varchar(250) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


## 
## table `agent_queue_log`
## 

DROP TABLE IF EXISTS `agent_queue_log`;
CREATE TABLE `agent_queue_log` (
  `id` int(11) NOT NULL auto_increment,
  `action` varchar(50) NOT NULL default '',
  `queue` varchar(30) NOT NULL default '',
  `account` varchar(30) NOT NULL default '',
  `pausetime` int(11) NOT NULL default 0,
  `reasion` text not null,
  `groupid` int(11) NOT NULL default 0,
  `cretime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;


##########################        history 2012-01-05           ####################################
CREATE TABLE `account_history` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `password` varchar(30) NOT NULL default '',
  `usertype` varchar(20) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `accountcode` varchar(20) NOT NULL default '',
  `callback` varchar(10) NOT NULL default '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `accountgroup_history` (
  `id` int(11) NOT NULL auto_increment,
  `groupname` varchar(30) NOT NULL default '',
  `grouptitle` varchar(50) NOT NULL default '',
  `grouptagline` varchar(80) NOT NULL default '',
  `grouplogo` varchar(30) NOT NULL default '',
  `grouplogostatus` int(1) NOT NULL default 1,
  `accountcode` varchar(20) NOT NULL default '',
  `allowcallback` varchar(10) NOT NULL default 'no',
  `creditlimit` double(24,4) NOT NULL default '0.0000',
  `limittype` varchar(10) NOT NULL default '',
  `group_multiple` double(8,4) NOT NULL default '1.0000',
  `customer_multiple` double(8,4) NOT NULL default '1.0000',
  `curcredit` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `resellerid` int(11) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `resellergroup_history` (
  `id` int(11) NOT NULL auto_increment,
  `resellername` varchar(30) NOT NULL default '',
  `accountcode` varchar(20) NOT NULL default '',
  `allowcallback` varchar(10) NOT NULL default '',
  `creditlimit` double(24,4) NOT NULL default '0.0000',
  `curcredit` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `limittype` varchar(10) NOT NULL default '',
  `multiple` double(8,4) NOT NULL default '1.0000',
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',  
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `epayment_account` varchar(255) NOT NULL default '',                 
  `epayment_status` enum('enable','disable') NOT NULL default 'disable',
  `epayment_item_name` varchar(30) NOT NULL default '',     
  `epayment_identity_token` varchar(255) NOT NULL default '',           
  `epayment_amount_package` varchar(30) NOT NULL default '',            
  `epayment_notify_mail` varchar(60) NOT NULL default '',
  `trunk1_id` int(11) NOT NULL default 0,
  `trunk2_id` int(11) NOT NULL default 0,
  `callshop_pay_fee` ENUM('yes','no') NOT NULL DEFAULT 'no',
  `clid_context` varchar(30) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `clid_history` (
  `id` int(11) NOT NULL auto_increment,
  `clid` varchar(20) NOT NULL default '',
  `accountcode` varchar(40) NOT NULL default '',
  `pin` varchar(30) NOT NULL default '',
  `creditlimit` DOUBLE NOT NULL default '0.0000',
  `curcredit` DOUBLE NOT NULL default '0.0000',
  `limittype` VARCHAR( 10 ) NOT NULL,
  `credit_clid` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_group` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `credit_reseller` DOUBLE( 24, 4 ) NOT NULL default '0.0000',
  `display` varchar(20) NOT NULL default '',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '1',
  `isshow` enum('yes','no') NOT NULL default 'yes',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `billingtime` datetime NOT NULL default '0000-00-00 00:00:00',
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
  #UNIQUE KEY `pin` (`pin`),
  #UNIQUE KEY `clid` (`clid`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `myrate_history` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`),
  #UNIQUE rate (dialprefix,numlen,resellerid,groupid),
  #KEY `dialprefix` (`dialprefix`),
  INDEX `destination` (`destination`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `resellerrate_history` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
  #UNIQUE rate (dialprefix,numlen,resellerid),
  #KEY `dialprefix` (`dialprefix`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;

CREATE TABLE `callshoprate_history` (
  `id` int(11) NOT NULL auto_increment,
  `dialprefix` varchar(20) NOT NULL default '',
  `numlen` int(11) NOT NULL default '0',
  `destination` varchar(100) NOT NULL default '',
  `connectcharge` double(24,4) NOT NULL default '0.0000',
  `initblock` int(11) NOT NULL default '0',
  `rateinitial` double(24,4) NOT NULL default '0.0000',
  `billingblock` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  `resellerid` int(11) NOT NULL default '0',
  `addtime` datetime NOT NULL default '0000-00-00 00:00:00',
  UNIQUE KEY `id` (`id`)
  #UNIQUE rate (dialprefix,numlen,resellerid,groupid),
  #KEY `dialprefix` (`dialprefix`)
) ENGINE=MyISAM DEFAULT CHARSET utf8 DEFAULT COLLATE utf8_general_ci;
