<?php
date_default_timezone_set('UTC');
 /*
    Freeiris Ami Port

    Freeiris Ami Port Copyright (c) 2009 hoowa sun <hoowa.sun@gmail.com>
    All Rights Reserved.

	this class created from php-agi-asmanager.php
	access http://phpagi.sourceforge.net for more detail about that project.
    Copyright (c) 2004, 2005 Matthew Asham <matthewa@bcwireless.net>, David Eder <david@eder.us>
    All Rights Reserved.

    This software is released under the terms of the GNU Lesser General Public License v2.1
    A copy of which is available from http://www.gnu.org/copyleft/lesser.html

  */


 /**
  * Asterisk Manager class
  *
  * http://www.voip-info.org/wiki-Asterisk+config+manager.conf
  * http://www.voip-info.org/wiki-Asterisk+manager+API
  */
  class freeiris_ami
  {
   /**
    * Config variables
    *
    * @var array
    * @access public
    */
    var $config;

   /**
    * Socket
    *
    * @access public
    */
    var $socket = NULL;

   /**
    * Server we are connected to
    *
    * @access public
    * @var string
    */
    var $server;

   /**
    * Port on the server we are connected to
    *
    * @access public
    * @var integer
    */
    var $port;

   /**
    * Parent AGI
    *
    * @access private
    * @var AGI
    */
    var $pagi;

   /**
    * Event Handlers
    *
    * @access private
    * @var array
    */
    var $event_handlers;

    // freeiris stream timeout in seconds
    var $stream_timeout;

   /**
    * Constructor
    */
    function freeiris_ami($username,$secret,$serverip,$port,$stream_timeout = 15)
    {
      $this->config['asmanager']['server'] = $serverip;
      $this->config['asmanager']['port'] = $port;
      $this->config['asmanager']['username'] = $username;
      $this->config['asmanager']['secret'] = $secret;
      $this->stream_timeout = $stream_timeout;
    }

   /**
    * Send a request
    *
    * @param string $action
    * @param array $parameters
    * @return array of parameters
    */
    function send_request($action, $parameters=array(),$boolTimeout=true)
    {
      $req = "Action: $action\r\n";
      foreach($parameters as $var=>$val)
        $req .= "$var: $val\r\n";
      $req .= "\r\n";
      fwrite($this->socket, $req);
      return $this->wait_response($boolTimeout);
    }

   /**
    * Wait for a response
    *
    * If a request was just sent, this will return the response.
    * Otherwise, it will loop forever, handling events.
    *
    * @param boolean $allow_timeout if the socket times out, return an empty array
    * @return array of parameters, empty on timeout
    */
    function wait_response($allow_timeout=false)
    {
      $timeout = false;
      do
      {
        $type = NULL;
        $parameters = array();

        $buffer = trim(fgets($this->socket, 4096));
        while($buffer != '')
        {
          $a = strpos($buffer, ':');
          if($a)
          {
            if(!count($parameters)) // first line in a response?
            {
              $type = strtolower(substr($buffer, 0, $a));
              if(substr($buffer, $a + 2) == 'Follows')
              {
                // A follows response means there is a miltiline field that follows.
                $parameters['data'] = '';
                $buff = fgets($this->socket, 4096);
                while(substr($buff, 0, 6) != '--END ')
                {
                  $parameters['data'] .= $buff;
                  $buff = fgets($this->socket, 4096);
                }
              }
            }

            // store parameter in $parameters
            $parameters[substr($buffer, 0, $a)] = substr($buffer, $a + 2);
          }
          $buffer = trim(fgets($this->socket, 4096));
        }

        // process response
        switch($type)
        {
          case '': // timeout occured
            $timeout = $allow_timeout;
            break;
          case 'event':
            $this->process_event($parameters);
            break;
          case 'response':
            break;
          default:
            $this->log('Unhandled response packet from Manager: ' . print_r($parameters, true));
            break;
        }
      } while($type != 'response' && !$timeout);
      return $parameters;
    }

   /**
    * Connect to Asterisk
    *
    * @param string $server
    * @param string $username
    * @param string $secret
    * @return boolean true on success
    */
    function connect($server=NULL, $username=NULL, $secret=NULL)
    {
      // use config if not specified
      if(is_null($server)) $server = $this->config['asmanager']['server'];
      if(is_null($username)) $username = $this->config['asmanager']['username'];
      if(is_null($secret)) $secret = $this->config['asmanager']['secret'];

      // get port from server if specified
      if(strpos($server, ':') !== false)
      {
        $c = explode(':', $server);
        $this->server = $c[0];
        $this->port = $c[1];
      }
      else
      {
        $this->server = $server;
        $this->port = $this->config['asmanager']['port'];
      }

      // connect the socket
      $errno = $errstr = NULL;
      $this->socket = @fsockopen($this->server, $this->port, $errno, $errstr);
      if($this->socket == false)
      {
        $this->log("Unable to connect to manager {$this->server}:{$this->port} ($errno): $errstr");
        return false;
      }
      stream_set_timeout($this->socket, $this->stream_timeout);

      // read the header
      $str = fgets($this->socket);
      if($str == false)
      {
        // a problem.
        $this->log("Asterisk Manager header not received.");
        return false;
      }
      else
      {
        // note: don't $this->log($str) until someone looks to see why it mangles the logging
      }

      // login
      $res = $this->send_request('login', array('Username'=>$username, 'Secret'=>$secret));
      if($res['Response'] != 'Success')
      {
        $this->log("Failed to login.");
        $this->disconnect();
        return false;
      }
      return true;
    }

   /**
    * Disconnect
    *
    */
    function disconnect()
    {
      $this->logoff();
      fclose($this->socket);
    }

   // *********************************************************************************************************
   // **                       COMMANDS                                                                      **
   // *********************************************************************************************************

   /**
    * Logoff Manager
    *
    * http://www.voip-info.org/wiki-Asterisk+Manager+API+Action+Logoff
    */
    function Logoff()
    {
      return $this->send_request('Logoff');
    }


   // *********************************************************************************************************
   // **                       MISC                                                                          **
   // *********************************************************************************************************

   /*
    * Log a message
    *
    * @param string $message
    * @param integer $level from 1 to 4
    */
    function log($message, $level=1)
    {
      if($this->pagi != false)
        $this->pagi->conlog($message, $level);
      else
        error_log(date('r') . ' - ' . $message);
    }

   /**
    * Add event handler
    *
    * Known Events include ( http://www.voip-info.org/wiki-asterisk+manager+events )
    *   Link - Fired when two voice channels are linked together and voice data exchange commences.
    *   Unlink - Fired when a link between two voice channels is discontinued, for example, just before call completion.
    *   Newexten -
    *   Hangup -
    *   Newchannel -
    *   Newstate -
    *   Reload - Fired when the "RELOAD" console command is executed.
    *   Shutdown -
    *   ExtensionStatus -
    *   Rename -
    *   Newcallerid -
    *   Alarm -
    *   AlarmClear -
    *   Agentcallbacklogoff -
    *   Agentcallbacklogin -
    *   Agentlogoff -
    *   MeetmeJoin -
    *   MessageWaiting -
    *   join -
    *   leave -
    *   AgentCalled -
    *   ParkedCall - Fired after ParkedCalls
    *   Cdr -
    *   ParkedCallsComplete -
    *   QueueParams -
    *   QueueMember -
    *   QueueStatusEnd -
    *   Status -
    *   StatusComplete -
    *   ZapShowChannels - Fired after ZapShowChannels
    *   ZapShowChannelsComplete -
    *
    * @param string $event type or * for default handler
    * @param string $callback function
    * @return boolean sucess
    */
    function add_event_handler($event, $callback)
    {
      $event = strtolower($event);
      if(isset($this->event_handlers[$event]))
      {
        $this->log("$event handler is already defined, not over-writing.");
        return false;
      }
      $this->event_handlers[$event] = $callback;
      return true;
    }

   /**
    * Process event
    *
    * @access private
    * @param array $parameters
    * @return mixed result of event handler or false if no handler was found
    */
    function process_event($parameters)
    {
      $ret = false;
      $e = strtolower($parameters['Event']);
      $this->log("Got event.. $e");		

      $handler = '';
      if(isset($this->event_handlers[$e])) $handler = $this->event_handlers[$e];
      elseif(isset($this->event_handlers['*'])) $handler = $this->event_handlers['*'];

      if(function_exists($handler))
      {
        $this->log("Execute handler $handler");
        $ret = $handler($e, $parameters, $this->server, $this->port);
      }
      else
        $this->log("No event handler for event '$e'");
      return $ret;
    }
  }
?>
