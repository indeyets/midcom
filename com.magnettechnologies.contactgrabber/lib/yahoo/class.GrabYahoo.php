<?php

/**
  * Contact Grabber
  * Version 1.0
  * Released 9th May, 2007
  * Author: Magnet Technologies, vishal.kothari@magnettechnologies.com
  * Credits: Janak Prajapati, Pravin Shukla, Tapan Moharana
  * Copyright (C) 2007

  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.

  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.

  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
  **/


/**
 * GrabYahoo - Yahoo Service Grabber class
 * @package GrabYahoo
 * @license GPL
 * @copyright (C) 2006 Ehsan Haque
 * @version 1.2
 * @created 05/22/2006
 * @updated 08/07/2006
 * @author Ehsan Haque 
 */
 
class yahoo
{
  /*-------------------------------------------------------
  Public Variables
  -------------------------------------------------------*/
  /**
  * Service name (1. addressbook, 2. messenger, 3. newmail)
  * @public
  * @var string
  */
  var $service            = "";
  
  /**
  * Yahoo! Account Username
  * @public
  * @var string
  */
  var $login              = "";

  /**
  * Yahoo! Account Password
  * @public
  * @var string
  */  
  var $password           = "";

  /**
  * Abosolute path to save the cookie
  * Default value is DOCUMENT_ROOT
  * @public
  * @var string
  */
  var $cookieJarPath      = "";
  
  /**
  * Abosulte path to the CA Bundle file
  * SSL Certificate required to verify CA cert
  * Usually required when script ran on Localhost
  * Remote servers may not require 
  * Default value is false
  * @public
  * @var string
  */
  var $caBundleFile       = "";

  /**
  * Specifies if Proxy server required as Gateaway
  * Default value is false
  * @public
  * @var boolean
  */  
  var $isUsingProxy       = false;
                          
  /**                     
  * Proxy host name       
  * @public               
  * @var string          
  */                      
  var $proxyHost          = "";
                          
  /**                     
  * Proxy port number     
  * @public               
  * @var int             
  */                      
  var $proxyPort          = 0;

  /*-------------------------------------------------------
  Private Variables
  -------------------------------------------------------*/
  /**
  * URL to Authenticate user on Yahoo!
  * @private
  * @var string
  */
  var $authUrl            = "http://login.yahoo.com/config/login?";

  /**
  * URL for the desired Service
  * @private
  * @var string
  */                          
  var $serviceUrl         = "";

  /**
  * URL to be used by cURL
  * @private
  * @var string
  */                          
  var $url                = "";

  /**
  * User agent (used to trick --  Add a normal cURL handle to a cURL multi handleYahoo!)
  * @private
  * @var string
  */
  var $userAgent          = "YahooSeeker-Testing/v3.9 (compatible; Mozilla 4.0; MSIE 5.5; http://search.rediff.com/)";

  /**
  * Referer URL (used to trick Yahoo!)
  * @private
  * @var string
  */
  var $referer            = "http://my.yahoo.com";

  /**
  * Specifies whether output includes the header
  * @private
  * @var int
  */
  var $showHeader         = 0;

  /**
  * Specifies if cURL should follow the redirected URL
  * @private
  * @var int
  */
  var $follow             = 0;

  /**
  * Specifies number of post fields to pass
  * @private
  * @var int
  */                          
  var $numPostField       = 0;

  /**
  * Specify fields to send via POST method as key=value
  * @private
  * @var string
  */
  var $postFields         = "";

  /**
  * File where output is temporarily saved during authentication
  * @private
  * @var string
  */
  var $authOutputFile     = "";

  /**
  * File where service output is temporarily saved 
  * @private
  * @var string
  */
  var $outputFile         = "";

  /**
  * File where Cookie is temporarily saved 
  * @private
  * @var string
  */                          
  var $cookieFileJar      = "";

  /**
  * Cookie File that is read by service process
  * This carries same value as $cookieFileJar
  * @private
  * @var string
  */
  var $cookieFile         = "";

  /**
  * Specifies if Cookie is to be in header
  * @private
  * @var int
  */
  var $cookie             = 0;

  /**www.yahoomail.com
  * Proxy address as proxy.host:port
  * @private
  * @var string
  */
  var $proxy              = "";

  /**
  * Error Information set by either cURL or Internal process
  * @private
  * @var string
  */
  var $errorInfo          = "";

  /**
  * Returns true if there is new mail otherwise false
  * @private
  * @var boolean
  */  
  var $newMailStatus      = false;
  
  /**
  * Sets Service URL
  * @return void
  */
  function setServiceUrl() 
  {
    if (empty($this->service))
    {
      $this->setError("provide_service");
      return false;
    }
    
    // Sets the URL depending on the choosen service
    switch ($this->service)
    {
      case 'addressbook' : $this->serviceUrl   = "http://address.mail.yahoo.com/"; break;
  
      case 'messenger'   : $this->servsetCurlOptioniceUrl   = "http://messenger.yahoo.com/edit/"; break;
      
      case 'newmail'     : $this->serviceUrl   = "http://mail.yahoo.com/"; break;
    }
  }
  
  function yahoo()
  {
  	 require_once('config.php');
    	 $this->dir_path = $DIR_PATH;
    	 $this->error_msg = $ERROR_LOGIN;
  }
  
  /**
  * Sets the Cookie Jar File where Cookie is temporarily saved
  * @return void
  */
  function setCookieJar()
  {
    // Sets the encrypted cookie filename using Yahoo! account username
    $this->cookieFilename = MD5($this->login);
    
    // Sets the Cookie Jar filename with an absolute path
	//echo $this->cookieJarPath . "/contacts/yahoo/" . $this->cookieFilename;
    $this->cookieFileJar  = (!empty($this->cookieJarPath)) ? $this->cookieJarPath . "/" . $this->cookieFilename : $_SERVER['DOCUMENT_ROOT'] . "/" . $this->cookieFilename;
    //$this->cookieFileJar = "c:/wamp/www/contacts/yahoo/".$this->cookieFilename".txt";
	$f = $this->cookieFileJar;
    fopen($f, "w");
  }

  /**
  * Initializes cURL session
  * @return void
  */  
  function initCurl()
  {
    $this->curlSession    = curl_init();
  }

  /**
  * Sets cURL options
  * @return boolean
  */  
  function setCurlOption() 
  {
    // Sets the User Agent  
    curl_setopt($this->curlSession, CURLOPT_USERAGENT, $this->userAgent);
    
    // Sets the HTTP Referer
    curl_setopt($this->curlSession, CURLOPT_REFERER, $this->referer);
    
    // Sets the URL that PHP will fetch using cURL
    curl_setopt($this->curlSession, CURLOPT_URL, $this->url);
    
    // Sets the number of fields to be passed via HTTP POST
    curl_setopt($this->curlSession, CURLOPT_POST, $this->numPostField);
    
    // Sets the fields to be sent via HTTP POST as key=value
    curl_setopt($this->curlSession, CURLOPT_POSTFIELDS, $this->postFields);
    
    // Sets the filename where cookie information will be saved
    curl_setopt($this->curlSession, CURLOPT_COOKIEJAR, $this->cookieFileJar);
    
    // Sets the filename where cookie information will be looked up
    curl_setopt($this->curlSession, CURLOPT_COOKIEFILE, $this->cookieFile);
    
    // Sets the option to set Cookie into HTTP header
    curl_setopt($this->curlSession, CURLOPT_COOKIE, $this->cookie);

    // Checwww.yahoomail.comks if the user needs proxy (to be set by user)
    if ($this->isUsingProxy) 
    { 
      // Checks if the proxy host and port is specified
      if ((empty($this->proxyHost)) || (empty($this->proxyPort)))
      { 
        $this->setError("proxy_required");
        $this->unlinkFile($this->cookieFileJar);
        return false;
      }
     
      // Sets the proxy address as proxy.host:port
      $this->proxy          = $this->proxyHost . ":" . $this->proxyPort;
    }
        
    // Sets the proxy server as proxy.host:port
    curl_setopt($this->curlSession, CURLOPT_PROXY, $this->proxy);
    
    // Sets the filename where output will be temporarily saved
    curl_setopt($this->curlSession, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($this->curlSession, CURLOPT_FOLLOWLOCATION, $this->follow);
    
    return true;
  }

  /**
  * Executes the Service
  * @param string $login Username of user's Yahoo! Account
  * @param string $password Password of the user's Yahoo! Account
  * @return array|false
  */  
  function execService($login, $password)
  {
    $login      = trim($login);
    $password   = trim($password);
    
    if (empty($login)) 
    {
      $this->setError("provide_login");
      return false;
    }
    
    if (empty($password)) 
    {
      $this->setError("provide_pass");
      return false;
    }
    
    $this->login      = $login;
    $this->password   = $password;
    
    $this->setServiceUrl();
    
    // Instructs to authenticate user on Yahoo!
    $this->auth  = $this->doAuthentication();

    if ($this->auth)
    {
      // Instructs to fetch output if Authenticated
      $this->getServiceOutput();
      
      return $this->serviceOutput;
    }
    
  }

    function getAddressbook($login, $password)
		{
                 
                $this->service = "addressbook";
                $this->isUsingProxy = false;
			    $this->proxyHost = "";
			    $this->proxyPort = "";
                $client=$_SERVER['HTTP_USER_AGENT'];
                             // if(strstr($client,"Windows"))
                             // {      
			    $this->cookieJarPath = $this->dir_path."/tmp";
                             // }
                              //else 
                              // {
                              //$this->cookieJarPath = "tmp";
                              // }
		         $yahooList=$this->execService($login, $password);
		
				if(is_array($yahooList))
				{
				
					$totalRecords=0;
					// Printing the array generated by the Class
				
					for($i=0;$i < count($yahooList);$i++)
					{
					
					$store=$yahooList[$i];
					$name=$store["First Name"];
					$nam=str_replace("<br>","",$name);
					$email=$store["E-mail Address"];
					$emal=str_replace("<br>","",$email);
					if($nam!="")
					{
					$totalRecords=$totalRecords+1;
					//print("<tr><td style='Font-Family:verdana;Font-Size:14'>$nam</td>");
					$result['name'][]=$nam;
					}
					elseif($nam=="" &&  $emal!="")
					{
						//print("<tr><td>&nbsp;</td>");
					$result['name'][]="&nbsp;";	
					}
					$emal=str_replace("<br>","",$email);
					if($emal!="")
					{
					//print("<td style='Font-Family:verdana;Font-Size:14'>$emal</td></tr>");
					$result['email'][]=$emal;
					}
					elseif($nam!="")
					{
					//print("<td>&nbsp;</td></tr>");
					$result['email'][]="&nbsp;";
					}
					}
				}
                 echo $this->errorInfo;
		 return $result;
		
		} 

  /**
  * Authenticates user on Yahoo!
  * @return boolean
  */
  function doAuthentication()
  {
    // Instructs to initialize cURL session
    $this->initCurl();
    
    // Sets the URL for authentication purpose
    $this->url              = $this->authUrl;
    
    // Sets the number of fields to send via HTTP POST
    $this->numPostField     = 22;
    
    //$password = urlencode($this->password);

    // Sets the fields to be sent via HTTP POST as key=value
    $this->postFields       = "login=$this->login&passwd=$this->password&.src=&.tries=5&.bypass=&.partner=&.md5=&.hash=&.intl=us&.tries=1&.challenge=ydKtXwwZarNeRMeAufKa56.oJqaO&.u=dmvmk8p231bpr&.yplus=&.emailCode=&pkg=&stepid=&.ev=&hasMsgr=0&.v=0&.chkP=N&.last=&.done=" . $this->serviceUrl;

    // Instructs to set Cookie Jar
    $this->setCookieJar();
          
    // Checks if the cURL options are all set properly
    if ($this->setCurlOption())
    {
      // Instructs to execute cURL session
     echo $this->execCurl();

      // Checks if any cURL error is generated
      if ($this->getCurlError())
      {
        $this->unlinkFile($this->cookieFileJar);
        $this->setError("curl_error");
        return false;
      }

      // Checks if the authentication failed, either invalid login or username is not registered
      if ((preg_match("/invalid/i", $this->outputContent)) || (preg_match("/not yet taken/i", $this->outputContent)))
      {
        // Instructs to close cURL session
        $this->closeCurl();
        
        // Unlinks the cookie file
        $this->unlinkFile($this->cookieFileJar);
        
        $this->setError("invalid_login");
        return false;
      }
      
      $this->closeCurl();
    }
    
    unset($this->outputContent);
    
    return true;
  }

  /**
  * Sets the Service Output
  * @return void
  */  
  function getServiceOutput()
  {  
    // Instructs to process the choosen service
    switch ($this->service)
    {
      case 'addressbook'    : $this->showHeader     = 0;
                              $this->serviceOutput  = $this->processAddressBook(); 
                              break;
  
      case 'messenger'      : $this->showHeader     = 0;
                              $this->serviceOutput  = $this->processMessengerList(); 
                              break;

      case 'newmail'        : $this->showHeader     = 0;
                              $this->follow         = 1;
                              $this->serviceOutput  = $this->processNewMail();
                              break;
    }
    
    $this->unlinkFile($this->cookieFileJar);
  }

  /**
  * Processes Yahoo! Address Book
  * @return array|false
  */
  function processAddressBook()
  {
    $this->initCurl();
    $this->url              = $this->serviceUrl;
    $this->numPostField     = 1;
    $this->postFields       = ".crumb=edNtmLWLP1J&VPC=import_export&A=B&submit[action_export_outlook]=Export Now";
    $this->cookieFile       = $this->cookieFileJar;
    //$this->outputFile       = "addressBook." . md5($this->login) . ".txt";
    $this->outputFile       = "." . $this->cookieJarPath."addressBook." . md5($this->login) . ".txt";
    $this->fileHandler      = fopen($this->outputFile, "w");
    
    if ($this->setCurlOption())
    {
      $this->execCurl();
      fwrite($this->fileHandler, $this->outputContent);      
      unset($this->outputContent);
      $this->closeCurl();
      fclose($this->fileHandler);
      
      // Sets the service output as an array
      $fileContentArr       = file($this->outputFile);
      
      // Sets the address book column headings
      $abColumnHeadLine     = trim($fileContentArr[0]);
      $abColumnHeadLine     = str_replace("\"", "", $abColumnHeadLine);
      
      // Sets the address book column headings into an array
      $abColumnHeadArr      = explode(",", $abColumnHeadLine);
      
      // Unsets the heading line from the file content array
      unset($fileContentArr[0]);
     
      foreach ($fileContentArr as $key => $value)
      {
        // Sets the address book list individually
        $listColumnLine     = trim($value);
        $listColumnLine     = str_replace("\"", "", $listColumnLine);
        
        // Sets the individual list into an array
        $listColumnArr      = explode(",", $listColumnLine);
        
        // Iterates through each item of individual address in the list
        foreach ($listColumnArr as $listColumnKey => $listColumnValue)
        {
          // Sets the column heading as key
          $listKey          = $abColumnHeadArr[$listColumnKey];
          
          // Sets the value for the key respectively
          $list_[$listKey]  = $listColumnValue;
        }
        
        // Sets the address book list in an array
        $list[]             = $list_;
      }
      
      $this->unlinkFile($this->outputFile);
      
      return $list;      
    }    
  }

  /**
  * Processes Yahoo! Messenger Friend List (Grouped)
  * @return array|false
  */  
  function processMessengerList()
  {
    $this->initCurl();
    $this->url              = $this->serviceUrl;
    $this->cookieFile       = $this->cookieFileJar;
    $this->outputFile       = "messengerList." . md5($this->login) . ".txt";
    $this->fileHandler      = fopen($this->outputFile, "w");
    
    if ($this->setCurlOption())
    {
      $this->execCurl();
      fwrite($this->fileHandler, $this->outputContent);
      unset($this->outputContent);
      $this->closeCurl();
      fclose($this->fileHandler);
      
      // Sets the service output as an array
      $fileContentArr       = file($this->outputFile);
      
      foreach ($fileContentArr as $key => $value)
      {
        $value            = trim($value);
        
        // Clears all the HTML tags except <b> and <a>
        $value            = strip_tags($value, "<b><a>");
        
        // Sets the pattern for regular expression replacement
        $pattern[0]       = "/(\[.[^\]]+)/i";
        $pattern[10]      = "/\]/";
        
        // Replaces anything matching [anyString]
        $trimmedContent[] = preg_replace($pattern, "", $value);  
      }
      
      foreach ($trimmedContent as $key => $value)
      {
        // Finds only the array items containing pm_friend
        if (preg_match("/pm_friend/i", $value))
        {
          // Clears all <a> tags
          $listArr[] = strip_tags($value, "<b>");
        }
      }
      
      foreach ($listArr as $key => $value)
      {
        $value          = str_replace("&nbsp;", "", $value);
        
        // Sets the array seperating the Messenger Contact Groups
        $listGroupArr   = explode("<b>", $value);
      }
      
      foreach ($listGroupArr as $key => $value)
      {
        if (!empty($value))
        {
          $value              = str_replace("</b>", "", $value);
          
          // Sets the array with contacts seperated in different array index
          $listArrForGroup[]  = explode("&#183;", $value);
        }
      }
      
      foreach ($listArrForGroup as $key => $value)
      {
        foreach ($value as $subKey => $subValue)
        {
          if ($subKey === 0)
          {
            // Sets the Contact Group name as the array key
            $arrKey = trim($subValue);
          }
          
          if ($subKey !== 0)
          {
            // Sets the array of contacts by the Contact Group
            // preg_replace is used to replace any non alphanumeric character or an underscore
            $subValue         = trim($subValue);
            $subValue         = preg_replace("/\Wn/", "", $subValue);
            $list[$arrKey][]  = $subValue;  
          }
        }
      }
      
      $this->unlinkFile($this->outputFile);
      
      return $list;
    }
  }  

  /**
  * Processes Yahoo! Mail for Number of New Messages
  * @return array|false
  */  
  function processNewMail()
  {
    $this->initCurl();
    $this->url              = $this->serviceUrl;
    $this->cookieFile       = $this->cookieFileJar;
    $this->outputFile       = "newMailList." . md5($this->login) . ".txt";
    $this->fileHandler      = fopen($this->outputFile, "w");
    
    if ($this->setCurlOption())
    {
      $this->execCurl();
      fwrite($this->fileHandler, $this->outputContent);
      unset($this->outputContent);
      $this->closeCurl();
      fclose($this->fileHandler);
      
      $fileContent  = file_get_contents($this->outputFile);
      $fileContent  = strip_tags($fileContent);
      
      // Finds out the string You have N unread Message
      $pattern      = "/inbox\s\(\d+\)/i";
      preg_match($pattern, $fileContent, $match);
      
      // Extracts the number of new message(s)
      $numPattern   = "/\d+/";
      preg_match($numPattern, $match[0], $match);
      
      $list['new_mail']   = $match[0];
      
      $this->unlinkFile($this->outputFile);
      
      if ($match[0] > 0)
      {
        $this->setNewMailStatus(true);
      }
      
      return $list;
    }
  }

  /**
  * Sets the new mail status to true or false
  * @return void
  */  
  function setNewMailStatus($status)
  {
    $this->newMailStatus = ($status) ? true : false;
  }
  
  /**
  * Returns the new mail status
  * @return boolean
  */
  function getNewMailStatus()
  {
    return $this->newMailStatus;
  }
  
  /**
  * Executes cURL Session
  * @return void
  */  
  function execCurl()
  {
    $this->outputContent    = curl_exec($this->curlSession);  
  }

  /**
  * Closes cURL session
  * @return void
  */  
  function closeCurl()
  {
    curl_close($this->curlSession); 
    unset($this->curlSession); 
  }

  /**
  * Unlinks any given file
  * @return void
  */  
  function unlinkFile($fileToUnlink)
  {
    if (file_exists($fileToUnlink))
    {
      unlink($fileToUnlink);
    }
  }

  /**
  * Sets any cURL error generated
  * @return boolean
  */  
  function getCurlError()
  {
    $this->curlError    = curl_error($this->curlSession);
    
    return (!empty($this->curlError)) ? true : false;
  }
  
  /**
  * Sets Error Information
  * @return void
  */  
  function setError($error) 
  {
    $msg  = (!empty($error)) ? $this->getErrorInfo($error) : null;
    $this->errorCount++;
    $this->errorInfo = $msg;
  }

  /**
  * Provides the Error message
  * @param string $error Error code for which error message is generated
  * @return string
  */  
  function getErrorInfo($error) 
  {
    switch ($error) 
    {
      case 'provide_service'    : $msg  = "Must specify a Service"; break;
      
      case 'provide_login'      : $msg  = "Must provide Login name"; break;
                                
      case 'provide_pass'       : $msg  = "Must provide Password"; break;
                                
      case 'provide_ca'         : $msg  = "Must provide a SSL Certificate to verfiy CA cert"; break;
                                
      case 'proxy_required'     : $msg  = "Must provide both Proxy host and port"; break;
                                
      case 'invalid_login'      : $msg  = "Login Error..."; break;
                                
      case 'curl_error'         : $msg  = $this->curlError; break;
    }
    
    return $msg;
  }
}
?>
