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
  */

    
class orkut
{
   var $dir_path = "";
   var $error_msg = "";
   //var $total ="";  
   var $fileName ="";

   function grabOrkut()
   {
         require_once('./config.php');
    	 $this->dir_path = $DIR_PATH;
    	 $this->error_msg = $ERROR_LOGIN;
   }
   
   function getAddressbook($YOUR_EMAIL,$YOUR_PASSWORD)
   {
            $cookies_phase1 = "";	    
	    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
	    $response = $this->getSocket("www.google.com","https://www.google.com/accounts/ServiceLoginBox?service=orkut&nui=2&skipll=true&skipvpage=true&continue=https%3A%2F%2Fwww.orkut.com%2FRedirLogin.aspx%3Fmsg%3D0%26page%3D%252FHome.aspx&followup=https%3A%2F%2Fwww.orkut.com%2FGLogin.aspx&hl=en-US","POST",443,$fake_proxy,$cookies_phase1,"","");
	    $this->splitPage($response, &$header, &$body);
	
	    $cookies_phase1 = $this->getCookies($header)."<br>";
	    $cook=explode(";",$cookies_phase1);
	                
	    $redirect_step1=$this->getLocation($header);
	  
	    // Sets the URL that PHP will fetch using cURL
	    $ch1 = curl_init();
	    curl_setopt($ch1, CURLOPT_URL, "https://www.google.com/accounts/ServiceLoginBoxAuth");
	    $postString="service=orkut&nui=2&skipll=true&skipvpage=true&continue=https%3A%2F%2Fwww.orkut.com%2FRedirLogin.aspx%3Fmsg%3D0%26page%3D%252FHome.aspx&followup=https%3A%2F%2Fwww.orkut.com%2FGLogin.aspx&hl=en-US&Email=$YOUR_EMAIL&Passwd=$YOUR_PASSWORD&null=Sign+in&$cook[1]";
	     curl_setopt($ch1,CURLOPT_POST,1);
	    curl_setopt($ch1, CURLOPT_POSTFIELDS, $postString);
	    curl_setopt($ch1, CURLOPT_REFERER, true);
	    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch1, CURLOPT_HEADER, true);
	    curl_setopt($ch1, CURLOPT_COOKIE,  $cookies_phase1);
	    curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, true);
	    
	    $response=curl_exec($ch1);
	    curl_close($ch1);
	   
	    if(strpos($response,'class="errormsg"') !== false)
	    {
	    	echo $this->error_msg;
	    	ob_end_flush();
	    	exit;
	    }
	    
	    ///////////////////////////////// for windows ////////////////////
    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
	  $postString="service=orkut&nui=2&skipll=true&skipvpage=true&continue=https%3A%2F%2Fwww.orkut.com%2FRedirLogin.aspx%3Fmsg%3D0%26page%3D%252FHome.aspx&followup=https%3A%2F%2Fwww.orkut.com%2FGLogin.aspx&hl=en-US&Email=$YOUR_EMAIL&Passwd=$YOUR_PASSWORD&null=Sign+in&$cook[1]";
  $response = $this->getSocket("www.google.com","https://www.google.com/accounts/ServiceLoginBoxAuth","POST",443,$fake_proxy,$cookies_phase1,$postString,"");
  
  ////////////////////////////////////// end /////////////////////
	      $this->splitPage($response, &$header, &$body);
	       $cookies_phase2 = $cookies_phase1.";".$this->getCookies($header);
	       $cook=explode(";",$cookies_phase1);
	      
	       $redirect_step1=$this->getLocation($header);
	   
	    $ch2=curl_init();
	    curl_setopt($ch2, CURLOPT_URL, "https://www.google.com/accounts/CheckCookie?");
	    $postString="continue=http%3A%2F%2Fwww.orkut.com%2FRedirLogin.aspx%3Fmsg%3D0%26page%3Dhttp%253A%252F%252Fwww.orkut.com%252F&followup=http%3A%2F%2Fwww.orkut.com%2FGLogin.aspx&service=orkut&hl=en-US&chtml=LoginDoneHtml&skipvpage=true";
	  curl_setopt($ch2,CURLOPT_POST,1);
	    curl_setopt($ch2, CURLOPT_POSTFIELDS, $postString);
	    curl_setopt($ch2, CURLOPT_REFERER, true);
	    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch2, CURLOPT_HEADER, true);
	    curl_setopt($ch2, CURLOPT_COOKIE,  $cookies_phase2);
	    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
	    
	    $response=curl_exec($ch2);
	    curl_close($ch2);     
	
	    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
	    $response = $this->getSocket("www.google.com","https://www.google.com/accounts/CheckCookie?continue=http%3A%2F%2Fwww.orkut.com%2FRedirLogin.aspx%3Fmsg%3D0%26page%3Dhttp%253A%252F%252Fwww.orkut.com%252F&followup=http%3A%2F%2Fwww.orkut.com%2FGLogin.aspx&service=orkut&hl=en-US&chtml=LoginDoneHtml&skipvpage=true","POST",443,$fake_proxy,$cookies_phase2,$postString,"https://www.google.com/accounts/ServiceLoginBoxAuth");
	
		  $this->splitPage($response, &$header, &$body);
		  $cookies_Forhere = str_replace("orkut_state=","",$this->getCookies($header));
		  preg_match("/orkut_state.*\=\:\;/",$header,$cookie12);

                  if (isset($cookie12[0]))
		  {
		      $cookies_phase3 = $cookies_phase2.";".$cookies_Forhere.$cookie12[0];
		  }
		  else
		  {
		      $cookies_phase3 = $cookies_phase2.";".$cookies_Forhere;
		  }

		  $tore1=str_replace(array("<html> <head> <title> Redirecting </title>","&amp",";"),array("","","&"),$body);
		  $fin=preg_match("/url=.*'\"/",$tore1,$out);
		  $last=str_replace(array("url='","'\""),array("",""),$out[0]);  
		  $last1=explode("?",$last);
		  $redirect_step2=$this->getLocation($header);
	     
	     // for last step
	     $ch3=curl_init();
	     curl_setopt($ch3, CURLOPT_URL, $last1[0]."?");
	     $postString=$last1[1];
		  curl_setopt($ch3,CURLOPT_POST,1);
	     curl_setopt($ch3, CURLOPT_POSTFIELDS, $postString);
	     curl_setopt($ch3, CURLOPT_REFERER, true);
	     curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
	     curl_setopt($ch3, CURLOPT_HEADER, true);
	     curl_setopt($ch3, CURLOPT_COOKIE,  $cookies_phase3);
	     curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
	
	     $response=curl_exec($ch3);
	     curl_close($ch3);     
	
	
	      $this->splitPage($response, &$header, &$body);
	      $cookies_phasem = str_replace("orkut_state=","",$this->getCookies($header));
	      preg_match("/orkut_state.*\=\:\;/",$header,$cooki);
	     
	      $cookies_phase4 = $cookies_phase3.";".$cookies_phasem.$cooki[0];
	      $redirect_step3=$this->getLocation($header);
	      $redirect_step31=explode("?",$redirect_step3);
	      $redirect_step32=explode("&",$redirect_step31[1]);
	            
	      // get login 
	      $ch4=curl_init();
	      curl_setopt($ch4, CURLOPT_URL, "http://www.orkut.com/Home.aspx");
	     
	      $postString="";
		   curl_setopt($ch4,CURLOPT_POST,1);
	      curl_setopt($ch4, CURLOPT_POSTFIELDS, $postString);
	      curl_setopt($ch4, CURLOPT_REFERER, true);
	      curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
	      curl_setopt($ch4, CURLOPT_HEADER, true);
	      curl_setopt($ch4, CURLOPT_COOKIE,  $cookies_phase4);
	      curl_setopt($ch4, CURLOPT_FOLLOWLOCATION, true);
	     
	      $response=curl_exec($ch4);
	      curl_close($ch4);     
	     
	   // show all the friend  list
		$ch5=curl_init();
		curl_setopt($ch5, CURLOPT_URL, "http://www.orkut.com/Friends.aspx");
		$postString="";
		 curl_setopt($ch5,CURLOPT_POST,1);
		curl_setopt($ch5, CURLOPT_POSTFIELDS, $postString);
		curl_setopt($ch5, CURLOPT_REFERER, true);
		curl_setopt($ch5, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch5, CURLOPT_HEADER, true);
		curl_setopt($ch5, CURLOPT_COOKIE,  $cookies_phase4);
		$response=curl_exec($ch5);
		curl_close($ch5); 
	
		$regexp = "showing <B>[^<]*<\/b> of <b>(.*?)<\/b>";  
		preg_match_all("/$regexp/s", $response, $matches);
		$noOfContacts = $matches[1][0];

		//$this->fileName="csvUpload/orkut".$login.time().".csv";
                //$handler= fopen($this->fileName,"a");
                //fwrite($handler,"NAME".","."EMAIL"."\n");

		$noOfPages = ceil(($noOfContacts / 20));//find out the no of pages of friends
		//echo "<table border='1'><tr><td align='center'><b>Name</b></td><td align='center'><b>Email Address</b></td></tr>";
		//$totalRecords=0;   	 
		for ($i = 1 ; $i <= $noOfPages ; $i++)
		{
                        
			$friendsPage = "http://www.orkut.com/Friends.aspx?show=all&pno=$i";
			$response = "";
			
			$ch6 = "";
			$ch6 = curl_init();
			curl_setopt($ch6, CURLOPT_URL, $friendsPage);
			
			curl_setopt($ch6, CURLOPT_REFERER, true);
			curl_setopt($ch6, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch6, CURLOPT_HEADER, true);
			curl_setopt($ch6, CURLOPT_COOKIE,  $cookies_phase4);
			//curl_setopt($ch6, CURLOPT_FOLLOWLOCATION, true);
			
			$response = curl_exec($ch6);
			
			$response = str_replace("\n","",$response);     
			$friendsArray = array();  //this is the array for friends listing. We initialize it to NULL everytime
			$friendsArray = explode('<td width="140" valign="top" align="left" class="S">',$response);
			$firstElement = array_shift($friendsArray);  //arrayshif used for remove the upper part of the array in the friend list
			
			//$arr = explode('<br />', $friendsArray);
			//print_r($username = strip_tags($arr[0]));
			//print_r($friendsArray);

                                
			foreach($friendsArray as $key=>$value) 
			{
				$arr = explode('<br />', $value);
				$username = strip_tags($arr[0]);//striptags used for remove the a href in the name
				
				$emails = $arr[2];	 
				if(!eregi("waiting for ", $emails) && !eregi(" to approve", $emails))
				{
                                       //$totalRecords=$totalRecords+1;
                                        $result['name'][]=$username; 
                                        $result['email'][]=$emails;
 				
					//$result[]=$emails;
                                        
					//print("<tr><td style='Font-Family:verdana;Font-Size:14'>".$profileLink."</td><td style='Font-Family:verdana;Font-Size:14'>".$email."</td></tr>");
                                        //fwrite($handler,$profileLink.",".$email."\n");
				}
                                
			}
                             
                      
                       
		}
                return $result;
		//echo "</table>";
               //fclose($handler);
              //$this->total=$totalRecords;
      
   }

    //functions that are used on above part...
	function splitPage($response,$header,$body){
	        $totalLength=strlen($response);
	        $pos=stripos($response,"<html>");
	        $header = substr($response,0,$pos);
	        $body =substr($response,$pos,$totalLength-1);
	        $body=str_replace("\n","",$body);
	        $body=str_replace("\r","",$body);
	        $body = str_replace(" ","",$body);
	}
	function getCookies($header){ 
	        $cookies=array();
	        $cookie=""; 
	        $returnar=explode("\r\n",$header);
	        for($ind=0;$ind<count($returnar);$ind++) {
	                if(ereg("Set-Cookie: ",$returnar[$ind]) || ereg("Cookies ",$returnar[$ind])) {
	                        $cookie=str_replace("Set-Cookie: ","",$returnar[$ind]);
	                        $cookie=explode(";",$cookie);
	                        $cookie=explode("=",$cookie[0]);
	                        $cookies[trim($cookie[0])]=trim($cookie[1]);
	                        if(isset($cookie[2])){
	                                $cookies[trim($cookie[0])] .="=".$cookie[2];
	                        }
	                }
	        }
	        $cookie=array();
	        foreach ($cookies as $key=>$value){
	                array_push($cookie,"$key=$value");
	        }
	        $cookie=implode(";",$cookie);
	        return $cookie; 
	}       
	
	function getLocation($header)
	{
	        $location = "";
	        $returnar=explode("\r\n",$header);
	        for($ind=0;$ind<count($returnar);$ind++) {
	                if(ereg("Location: ",$returnar[$ind])) {
	                        $location=str_replace("Location: ","",$returnar[$ind]);
	
				$location = trim($location);
				break;
	                }
	            //$this->splitPage($response, &$header, &$body);
	            $cookies_phase1 = $this->getCookies($header);
	        }
	         return $location;
	}
	
	function getSocket($host,$service_url,$method,$port, $fakeProxy, $cookie='',$postData='',$referer='') 
	{
	        $header  = "$method $service_url HTTP/1.0\r\n";
	        $header .= "Host: $host\r\n";
	
	        if($referer){
	                $header .= "Referer: $referer\r\n";
	        }
	        if($cookie){
	                $header.="Cookie: ".$cookie.";\r\n";
	        }
	        $header .="User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1) Gecko/20061010 Firefox/2.0\r\n";
	        $header .="Content-type: application/x-www-form-urlencoded\r\n";
	        $header .="Content-length: ".strlen($postData)."\r\n";
	        $header .="Proxy-Connection: Keep-Alive\r\n";
	        $header .="\r\n";
	          //echo "sdfdsf".$port;
	        if($port==443) {
	                 //echo "443";
	                $fp = pfsockopen("ssl://".$host, $port, &$errno, &$errstr);
	        }
	        else {
	                  //echo "80";
	                $fp = pfsockopen($host,$port, &$errno, &$errstr);
	        }
	        $response="";
	
	        if (!$fp) {
	            echo "not read"."<br>";
	           //die($errstr);
	        } else {
	           fwrite($fp, $header.$postData);
	           while (!feof($fp)) {
	                 $response .= @fread($fp, 200);
	            }
	           fclose($fp);
	        }
		
	    return $response;
	}
}
?>
