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


class rediff
{
	
  var $dir_path = "";
  var $error_msg = "";
  var $fileName = "";
  var $total = "";
  
  function grabRediff()
  {
  	 require_once('./config.php');
    	 $this->dir_path = $DIR_PATH;
    	 $this->error_msg = $ERROR_LOGIN;
  }
  function getAddressbook($login,$password)
  {
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, "http://mail.rediff.com/cgi-bin/login.cgi");
		    $postString = "login=$login&passwd=$password&submit=GO&FormName=existing";
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
		    curl_setopt($ch, CURLOPT_REFERER, true);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_HEADER, true);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		    curl_exec($ch);
		
		    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
		
		    $response = $this->getSocket("mail.rediff.com","http://mail.rediff.com/cgi-bin/login.cgi","POST",80,$fake_proxy,"",$postString,"");
		    $this->splitPage($response, &$header, &$body);
		  
		    $cookies_phase1 = $this->getCookies($header);
		    $redirect_step1 = $this->getLocation($header);
		    $redirect_step1= str_replace("&farm=3","",$redirect_step1);
		
			$fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
		
			
		   ////////////////////////////////// SEPARATE THE LOGIN AND PASSWORD ///////////////////////
		    $parts1=str_replace("?","&",$response)."<br>";
		    $parts = explode("&", $parts1);
		
			array_shift($parts);
			$newPostString = implode("&", $parts);
		    $newPostString=str_replace("&farm=3","",$newPostString);
		      
		#################### STEP 1 END ########################
		
		#################### STEP 2 START ########################
		
		    $this->splitPage($response, &$header, &$body);
		    $cookies_phase2 = $cookies_phase1.";".$this->getCookies($header);
		    $redirect_step2 = $this->getLocation($header);
		    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
		
		
		    $response = $this->getSocket("mail.rediff.com","http://f3mail.rediff.com/bn/folder.cgi?formname=sh_folder&folder=Inbox&$newPostString&SrtFld=2&SrtOrd=1&MsgCnt=0&user_size=1","POST",80,$fake_proxy,$cookies_phase2,"$postString","http://mail.rediff.com/cgi-bin/login.cgi");
		    $this->splitPage($response, &$header, &$body);
		    $cookies_phase3 = $cookies_phase2.";".$this->getCookies($header);
		    $redirect_step3 = $this->getLocation($header);
		
		
		 ////////////////////////////////////////////GET INBOX ///////////////////////////////////
		
		  $ch3 = curl_init();
		  curl_setopt($ch3, CURLOPT_URL, "http://f3mail.rediff.com/bn/folder.cgi?formname=sh_folder&folder=Inbox&$newPostString&SrtFld=2&SrtOrd=1&MsgCnt=0&user_size=1");
		
		  $postString = "formname=sh_folder&folder=Inbox&$newPostString&SrtFld=2&SrtOrd=1&MsgCnt=0&user_size=1";
		  $postString .= "login=$login&passwd=$password&submit=GO&FormName=existing";
		  
		    curl_setopt($ch3, CURLOPT_POST, 1);
		    curl_setopt($ch3, CURLOPT_POSTFIELDS, $postString);
		    curl_setopt($ch3, CURLOPT_REFERER, true);
		    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch3, CURLOPT_HEADER, true);
		    curl_setopt($ch3, CURLOPT_COOKIE,  $cookies_phase3);
		    curl_setopt($ch3, CURLOPT_FOLLOWLOCATION, true);
		     
		    $res1=curl_exec($ch3);
		    if(strpos($res1,'Error.') !== false)
		    {
		    	echo $this->error_msg;
		    	ob_end_flush();
		    	exit;
		    }
		    /*if($res1==0)
			{
		   		echo "Invalid Username and Password";
		 	}*/
		    curl_close($ch3);
		
		
		    $fake_proxy="Via: Proxy\r\nX-Forwarded-For: 220.".rand(100,400).".".rand(100,400).".".rand(100,400);
		
		    $response = $this->getSocket("mail.rediff.com","http://f3mail.rediff.com/bn/address.cgi?$newPostString","POST",80,$fake_proxy,$cookies_phase3,"$postString","http://mail.rediff.com/cgi-bin/login.cgi");
		    $this->splitPage($response, &$header, &$body);
		
		    $cookies_phase4 = $cookies_phase2.";".$this->getCookies($header);
		    $redirect_step5 = $this->getLocation($header);
		
		     $last1='http://f3mail.rediff.com/bn/address.cgi?'.$newPostString;
		
		        //$this->fileName="csvUpload/rediff".$login.time().".csv";
	      		//$handler= fopen($this->fileName,"a");
	                //fwrite($handler,"NAME".","."EMAIL"."\n");
			//echo "<table border='1'>
			//<tr><td align='center'><b>Name</b></td><td align='center'><b>Email Address</b></td></tr>";
			$totalRecords=0;
			do
			{
				$last='';
				$ch4 = curl_init();
		
		       ////////////////// START OF ADDRESS BOOK ///////////////////////////////////
			
			    curl_setopt($ch4, CURLOPT_URL, $last1);
			    $postString="$newPostString";
			    curl_setopt($ch4, CURLOPT_POST, 1);
			    curl_setopt($ch4, CURLOPT_POSTFIELDS, $postString);
			    curl_setopt($ch4, CURLOPT_REFERER, true);
			    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
			    curl_setopt($ch4, CURLOPT_HEADER, true);
			    curl_setopt($ch4, CURLOPT_COOKIE,  $cookies_phase4);
			    curl_setopt($ch4, CURLOPT_FOLLOWLOCATION, true);
			     
			    $res2=curl_exec($ch4);
				preg_match('/a HREF=".*Next/',$res2,$match1);
			        
				// OH WELL
				if (isset($match1[0]))
				{
				    $match1=str_replace('a HREF="','http://f3mail.rediff.com',$match1[0]);
				    $match1=str_replace('">Next','',$match1);
			
				    $last=$match1;
				}
				$last1=$last;
			    $first=stripos($res2,'<INPUT TYPE=hidden NAME=tempnicks VALUE="">');
			    $first1=substr($res2,$first);
			    $sList2 = explode("</TD>",$first1);
			    //$totalRecords= $totalRecords + count($sList2);
		       //////////////////////////////////Display of contents ///////////////////////////////////////
		            
			  	  for ($i=0; $i < count($sList2); $i++)
				  {       
                                              
			                    // $this->fileName="csvUpload/rediff.csv";
          				  //$this->fileName="csvUpload/rediff".$login.time().".csv";
                                          //$handler= fopen($this->fileName,"a");
			  	    $sList3 = explode("<TD class=sb2>&nbsp;&nbsp;", $sList2[$i]);
                                   
			            if (isset($sList3[1]) && $sList3[1] !="")
			            {
                                        
				      $totalRecords = $totalRecords + 1;
			              $sList3[1]=str_replace("\n","",$sList3[1]);
			             // print("<tr><td style='Font-Family:verdana;Font-Size:14'>$sList3[1]</td>");
				     // fwrite($handler,$sList3[1].",");
                                       $result['name'][]=$sList3[1];
			            }
			          
			            if (strpos($sList3[0],"@") && !strpos($sList3[0],"<input type=checkbox") && !strpos($sList3[0],"<TABLE") && $sList3)
			            {
			              $sList3[0]=str_replace(array("<TD class=sb2>","\n"),"",$sList3[0]);
			             // print("<td style='Font-Family:verdana;Font-Size:14'>$sList3[0]</td></tr>");
				     // fwrite($handler,$sList3[0]."\n");
					$result['email'][]=$sList3[0];

			            }
                                  
           
			 	  }  
                            
		   }while($last!='');
		     //fclose($handler);
		  echo "</table>";
                  return $result;
	 //$this->total= $totalRecords;
     }
  
	 function splitPage($response,$header,$body)
	 {
	        $totalLength=strlen($response);
	        $pos=stripos($response,"<html>");
	        $header = substr($response,0,$pos);
	        $body =substr($response,$pos,$totalLength-1);
	        $body=str_replace("\n","",$body);
	        $body=str_replace("\r","",$body);
	        $body = str_replace(" ","",$body);
	 }
	
	 function getCookies($header)
	 { 
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
	        if($port==443) {
	             $fp = pfsockopen("ssl://".$host, $port, &$errno, &$errstr);
	        }
	        else {
	             $fp = pfsockopen($host,$port, &$errno, &$errstr);
	        }
	        $response="";
	
	         if(!$fp) {
	            echo "not read"."<br>";
	         } 
	         else {
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
