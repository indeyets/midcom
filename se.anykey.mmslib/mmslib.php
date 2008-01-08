<?php
/**
 *
 * @package se.anykey.mmslib
 */

/****************************************************************************
 *  mmslib - a PHP library for encoding and decoding MMS
 *
 *  Copyright (C) 2003, 2004  Stefan Hellkvist
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *****************************************************************************/

/* If DEBUG is 1 the decoder will do printouts on stdout */
define( "DEBUG", 		  0 );

/* Field name constants */
define( "BCC", 			0x01 );
define( "CC", 			0x02 );
define( "CONTENT_LOCATION", 	0x03 );
define( "CONTENT_TYPE", 	0x04 );
define( "DATE", 		0x05 );
define( "DELIVERY_REPORT", 	0x06 );
define( "DELIVERY_TIME", 	0x07 );
define( "EXPIRY", 		0x08 );
define( "FROM", 		0x09 );
define( "MESSAGE_CLASS",	0x0A );
define( "MESSAGE_ID", 		0x0B );
define( "MESSAGE_TYPE", 	0x0C );
define( "MMS_VERSION", 		0x0D );
define( "MESSAGE_SIZE", 	0x0E );
define( "PRIORITY", 		0x0F );
define( "READ_REPLY", 		0x10 );
define( "REPORT_ALLOWED", 	0x11 );
define( "RESPONSE_STATUS", 	0x12 );
define( "RESPONSE_TEXT", 	0x13 );
define( "SENDER_VISIBILITY", 	0x14 );
define( "STATUS", 		0x15 );
define( "SUBJECT", 		0x16 );
define( "TO", 			0x17 );
define( "TRANSACTION_ID", 	0x18 );

/* Some constants for Content type (definitely not a complete list) */
define( "IMAGE_GIF", 		0x1D );
define( "IMAGE_JPEG", 		0x1E );
define( "IMAGE_PNG", 		0x20 );
define( "IMAGE_WBMP", 		0x21 );
define( "TEXT_PLAIN", 		0x03 );
define( "MULTIPART_MIXED", 	0x23 );
define( "MULTIPART_RELATED",  	0x33 );



/* Mapping from well-known content-type to string */
$content_types = array( "*/*", "text/*", "text/html", "text/plain",
	 	 	"text/x-hdml", "text/x-ttml", "text/x-vCalendar",
			"text/x-vCard", "text/vnd.wap.wml",
			"text/vnd.wap.wmlscript", "text/vnd.wap.wta-event",
			"multipart/*", "multipart/mixed",
			"multipart/form-data", "multipart/byterantes",
			"multipart/alternative", "application/*",
			"application/java-vm",
			"application/x-www-form-urlencoded",
			"application/x-hdmlc", "application/vnd.wap.wmlc",
			"application/vnd.wap.wmlscriptc",
			"application/vnd.wap.wta-eventc",
			"application/vnd.wap.uaprof",
			"application/vnd.wap.wtls-ca-certificate",
			"application/vnd.wap.wtls-user-certificate",
			"application/x-x509-ca-cert",
			"application/x-x509-user-cert",
			"image/*", "image/gif", "image/jpeg", "image/tiff",
			"image/png", "image/vnd.wap.wbmp",
			"application/vnd.wap.multipart.*",
			"application/vnd.wap.multipart.mixed",
			"application/vnd.wap.multipart.form-data",
			"application/vnd.wap.multipart.byteranges",
			"application/vnd.wap.multipart.alternative",
			"application/xml", "text/xml",
			"application/vnd.wap.wbxml",
			"application/x-x968-cross-cert",
			"application/x-x968-ca-cert",
			"application/x-x968-user-cert",
			"text/vnd.wap.si",
			"application/vnd.wap.sic",
			"text/vnd.wap.sl",
			"application/vnd.wap.slc",
			"text/vnd.wap.co",
			"application/vnd.wap.coc",
			"application/vnd.wap.multipart.related",
			"application/vnd.wap.sia",
			"text/vnd.wap.connectivity-xml",
			"application/vnd.wap.connectivity-wbxml",
			"application/pkcs7-mime",
			"application/vnd.wap.hashed-certificate",
			"application/vnd.wap.signed-certificate",
			"application/vnd.wap.cert-response",
			"application/xhtml+xml",
			"application/wml+xml",
			"text/css",
			"application/vnd.wap.mms-message",
			"application/vnd.wap.rollover-certificate",
			"application/vnd.wap.locc+wbxml",
			"application/vnd.wap.loc+xml",
			"application/vnd.syncml.dm+wbxml",
			"application/vnd.syncml.dm+xml",
			"application/vnd.syncml.notification",
			"application/vnd.wap.xhtml+xml",
			"application/vnd.wv.csp.cir",
			"application/vnd.oma.dd+xml",
			"application/vnd.oma.drm.message",
			"application/vnd.oma.drm.content",
			"application/vnd.oma.drm.rights+xml",
			"application/vnd.oma.drm.rights+wbxml" );


/* Mapping from constant to string for field names */
$field_names = array( 	1 => "Bcc", "Cc", "Content-Location",
			"Content-Type", "Date", "Delivery-Report",
			"Delivery-Time", "Expiry", "From",
			"Message-Class", "Message-ID", "Message-Type",
			"MMS-Version", "Message-Size", "Priority",
			"Read-Reply", "Report-Allowed", "Response-Status",
			"Response-Text", "Sender-Visibility", "Status",
			"Subject", "To", "Transaction-ID" );



function fieldNameToString( $fieldName )
{
	global $field_names;
	return $field_names[$fieldName];
}

function messageTypeToString( $messageType )
{
	switch ( $messageType )
	{
		case 128: return "m-send-req";
		case 129: return "m-send-conf";
		case 130: return "m-notification-ind";
		case 131: return "m-notifyresp-ind";
		case 132: return "m-retrieve-conf";
		case 133: return "m-acknowledge-ind";
		case 134: return "m-delivery-ind";
		default: return "Unknown message type";
	}
}

function statusToString( $status )
{
	switch ( $status )
	{
		case 128: return "Expired";
		case 129: return "Retrieved";
		case 130: return "Rejected";
		case 131: return "Deferred";
		case 132: return "Unrecognized";
		default: return "Unknown status";
	}
}

function mmsVersionToString( $version )
{
	$major = ($version & 0x70) >> 4;
	$minor = ($version & 0x0f);
	return "" . $major . "." . $minor;
}

function dateToString( $date )
{
	return date( "Y-m-d H:i:s", $date );
}

function messageClassToString( $messageClass )
{
	switch ( $messageClass )
	{
		case 128: return "Personal";
		case 129: return "Advertisment";
		case 130: return "Informational";
		case 131: return "Auto";
		default: return "Unknown message class";
	}
}

function priorityToString( $priority )
{
	switch ( $priority )
	{
		case 128: return "Low";
		case 129: return "Normal";
		case 130: return "High";
		default: "Unknown priority";
	}
}

function senderVisibilityToString( $vis )
{
	switch ( $vis )
	{
		case 128: return "Hide";
		case 129: return "Show";
		default: "Unknown sender visibility";
	}
}

function deliveryReportToString( $dr )
{
	switch ( $dr )
	{
		case 128: return "Yes";
		case 129: return "No";
		default: "Unknown delivery report";
	}
}


function readReplyToString( $readReply )
{
	return deliveryReportToString( $readReply );
}

function contentTypeToString( $contentType )
{
	global $content_types;
	if ( is_string( $contentType ) )
		return $contentType;
	return $content_types[$contentType];
}



/**
 * Part class
 *
 * The Part class is just a container for various attachments of different content types.
 * The data itself is not stored though, it merely contains a reference to the file
 */

class Part
{
	var $contentType;
	var $dataLen;
	var $fname;
	var $data;
	var $fileID;

	function Part( $contentType = -1, $fileName = -1 ,$fileID)
	{
		if ( $fileName != -1 && $contentType != -1 )
		{
			$this->contentType = $contentType;
			$this->dataLen = filesize( $fileName );
			$this->fname = $fileName;
			$this->fileID = $fileID;
		}
	}

	function writeToFp( $fp )
	{
  if ( isset( $this->fname ) && !isset( $this->data ) )
		{
			$cfp = fopen( $this->fname, "rb" );
			fwrite( $fp, fread( $cfp, $this->dataLen ), $this->dataLen );
			fclose( $cfp );
		}
		else
		if ( !isset( $this->fname ) && isset( $this->data ) )
		{
			for ( $i = 0; $i < $this->dataLen; $i++ )
				fwrite( $fp, chr( $this->data[$i] ), 1 );

		}
 }

	function writeToFile( $fileName )
	{
		$fp = fopen( $fileName, "wb" );
		for ( $i = 0; $i < $this->dataLen; $i++ )
			fwrite( $fp, chr( $this->data[$i] ), 1 );
		fclose( $fp );
	}


function writeTopage()
	{

		for ( $i = 0; $i < $this->dataLen; $i++ )
		$vysl .= chr( $this->data[$i] );
		return $vysl;

	}



}




/**
 * MMSDecoder class
 *
 * The MMSDecoder class decodes binary MMS chunks so that you can extract its parts
 *
 * Limitations: The parsing of Content-type for parts is not complete so you won't get
 *              the filename for instance.
 */

class MMSDecoder
{
	/* Parser variables */
	var $data;
	var $curp;

	/* Header variables as found when parsing */
	var $messageType;
	var $transactionId;
	var $mmsVersion;
	var $date;
	var $from;
	var $to;
	var $cc;
	var $bcc;
	var $subject;
	var $messageClass;
	var $priority;
	var $senderVisibility;
	var $deliveryReport;
	var $readReply;
	var $contentType;
	var $bodyStartsAt;
	var $expiryDate;
	var $expiryDeltaSeconds;
	var $status;

	/* Body variables as found when parsing */
	var $nparts;
	var $parts;
	var $druhy;
	var $jmena;

	function MMSDecoder( $data )
	{
		$datalen = strlen( $data );
		for ( $i = 0; $i < $datalen; $i++ )
			$this->data[$i] = ord( $data{$i} );
		$this->curp = 0;
	}


function confirm($TRANSACTIONID,$cislo) {
		$pos = 0;

		$confirm[$pos++] = 0x8C; // message-type
		$confirm[$pos++] = 129;  // m-send-conf
		$confirm[$pos++] = 0x98; // transaction-id

		for ($i = 0; $i < strlen($TRANSACTIONID); $i++)
			$confirm[$i+$pos] = ord(substr($TRANSACTIONID, $i, 1));

		$pos += $i;

		$confirm[$pos++] = 0x00; // end of string
		$confirm[$pos++] = 0x8D; // version
		$confirm[$pos++] = 0x90; // 1.0
		$confirm[$pos++] = 0x92; // response-status
		$confirm[$pos]   = $cislo;  // OK=128,
		//Ok = <Octet 128> Error-unspecified = <Octet 129> Error- service-denied = <Octet 130> Error-message-format-corrupt = <Octet 131> Error-sending-address-unresolved = <Octet 132> Error-message-not-found = <Octet 133> Error-network-problem = <Octet 134> Error- content-not-accepted = <Octet 135> Error-unsupported-message = <Octet 136>


		// respond with the m-send-conf
		foreach ($confirm as $byte)
			$sd .= chr($byte);
			//echo $byte;
			return $sd;
	}


	function parse()
	{
		while ( $this->parseHeader() );

		$this->bodyStartsAt = $this->curp;

		if ( $this->contentType == MULTIPART_MIXED ||
		     $this->contentType == MULTIPART_RELATED )
		{
			$this->parseBody();
		}
	}

	function parseHeader()
	{
		$res = $this->parseMMSHeader();
		if ( !$res )
			$res = $this->parseApplicationHeader();
		return $res;
	}

	function parseApplicationHeader()
	{
		$res = $this->parseToken( $token );
		if ( $res )
			$res = $this->parseTextString( $appspec );
		if ( DEBUG && $res )
			print( $token . ": " . $appspec );
		return $res;
	}

	function parseToken( &$token )
	{
		if ( $this->data[$this->curp] <= 31 ||
		     $this->isSeparator( $this->data[$this->curp] ) )
			return 0;
		while ( $this->data[$this->curp] > 31 &&
		        !$this->isSeparator( $this->data[$this->curp] ) )
		{
			$token .= chr( $this->data[$this->curp] );
			$this->curp++;
		}
		return 1;
	}

	function isSeparator( $ch )
	{
		return $ch == 40 || $ch == 41 || $ch == 60 || $ch == 62 ||
		       $ch == 64 || $ch == 44 || $ch == 58 || $ch == 59 ||
		       $ch == 92 || $ch == 47 || $ch == 123 || $ch == 125 ||
                       $ch == 91 || $ch == 93 || $ch == 63 || $ch == 61 ||
                       $ch == 32 || $ch == 11;
	}

	function parseMMSHeader()
	{
		if (!$this->parseShortInteger( $mmsFieldName ) )
		{
			return 0;
		}

		switch ( $mmsFieldName )
		{
			case BCC:
				$this->parseBcc(); break;
			case CC:
				$this->parseCc(); break;
			case CONTENT_LOCATION:
				$this->parseContentLocation(); break;
			case CONTENT_TYPE:
				$this->parseContentType( $this->contentType );
				break;
			case DATE:
				$this->parseDate( $this->date ); break;
			case DELIVERY_REPORT:
				$this->parseDeliveryReport(); break;
			case DELIVERY_TIME:
				$this->parseDeliveryTime(); break;
			case EXPIRY:
				$this->parseExpiry(); break;
			case FROM:
				$this->parseFrom(); break;
			case MESSAGE_CLASS:
				$this->parseMessageClass(); break;
			case MESSAGE_ID:
				$this->parseMessageId(); break;
			case MESSAGE_TYPE:
				$this->parseMessageType(); break;
			case MMS_VERSION:
				$this->parseMmsVersion(); break;
			case MESSAGE_SIZE:
				$this->parseMessageSize(); break;
			case PRIORITY:
				$this->parsePriority(); break;
			case READ_REPLY:
				$this->parseReadReply(); break;
			case REPORT_ALLOWED:
				$this->parseReportAllowed(); break;
			case RESPONSE_STATUS:
				$this->parseResponseStatus(); break;
			case SENDER_VISIBILITY:
				$this->parseSenderVisibility(); break;
			case STATUS:
				$this->parseStatus(); break;
			case SUBJECT:
				$this->parseSubject(); break;
			case TO:
				$this->parseTo(); break;
			case TRANSACTION_ID:
				$this->parseTransactionId(); break;
			default:
				if ( DEBUG )
				{
					printf( "Unknown message field (" );
					printf( $mmsFieldName . ")\n");
				}
				break;
		}
		return 1;
	}

	function parseStatus()
	{
		$this->status = $this->data[$this->curp++];
		if ( DEBUG )
		{
			print( "X-Mms-Status: " .
			  statusToString($this->status) .
			  "\n" );
		}

	}

	function parseShortInteger( &$shortInt )
	{
		if ( !($this->data[$this->curp] & 0x80) )
			return 0;
		$shortInt = $this->data[$this->curp] & 0x7f;
		$this->curp++;
		return 1;
	}

	function parseMessageType()
	{
		if ( !($this->data[$this->curp] & 0x80) )
			return 0;
		$this->messageType = $this->data[$this->curp];
		$this->curp++;

		if ( DEBUG )
		{
			print( "X-Mms-Message-Type: " .
				messageTypeToString( $this->messageType ) .
				"\n" );
		}
		return 1;
	}


	function parseTransactionId()
	{
		$this->parseTextString( $this->transactionId );
		if ( DEBUG )
		{
			print( "X-Mms-Transaction-ID: " .
				$this->transactionId . "\n" );
		}
	}

	function parseExpiry()
	{
		$this->parseValueLength($length);
		switch ( $this->data[$this->curp] )
		{
			case 128:
				$this->curp++;
				$this->parseDate( $this->expiryDate );
				break;
			case 129:
				$this->curp++;
				$this->parseDeltaSeconds( $this->expiryDeltaSeconds );
				break;
			default:
		}
	}

	function parseDeltaSeconds( &$deltaSecs )
	{
		$this->parseDate( $deltaSecs );
	}

	function parseTextString( &$textString )
	{
		if ( $this->data[$this->curp] == 127 ) /* Remove quote */
			$this->curp++;
		while ( $this->data[$this->curp] )
		{
			$textString .= chr( $this->data[$this->curp] );
			$this->curp++;
		}
		$this->curp++;
		return 1;
	}

	function parseMmsVersion()
	{
		$this->parseShortInteger( $this->mmsVersion );
		if ( DEBUG )
		{
			print( "X-Mms-MMS-Version: " .
				mmsVersionToString( $this->mmsVersion ) .
				"\n" );
		}
	}


	function parseDate( &$date )
	{
		$this->parseLongInteger( $date );
		if ( DEBUG )
		{
			print( "Date: " . dateToString( $date ) .
				"\n" );
		}
	}


	function parseLongInteger( &$longInt )
	{
		if ( !$this->parseShortLength( $length ) )
			return 0;
		return $this->parseMultiOctetInteger( $longInt, $length );
	}

	function parseShortLength( &$shortLength )
	{
		$shortLength = $this->data[$this->curp];
		if ( $shortLength > 30 )
			return 0;
 		$this->curp++;
		return 1;
	}

	function parseMultiOctetInteger( &$moint, $noctets )
	{
		$moint = 0;
		for ( $i = 0; $i < $noctets; $i++ )
		{
			$moint = $moint << 8;
			$moint |= $this->data[$this->curp];
			$this->curp++;
		}
		return 1;
	}

	function parseFrom()
	{
		$this->parseValueLength( $length );
		if ( $this->data[$this->curp] == 128 ) /* Address present? */
		{
			$this->curp++;
			$this->parseEncodedString( $this->from );
		}
		else
		{
			$this->from = "Anonymous";
			$this->curp++;
		}

		if ( DEBUG )
			print( "From: " . $this->from . "\n" );
	}

	function parseEncodedString( &$encstring )
	{
		$isencoded = $this->parseValueLength( $length );
		if ( $isencoded )
		{
			$this->curp++;
			//die( "Encoded String not implemented fully" );
		}
		$this->parseTextString( $encstring );
	}

	function parseValueLength( &$length )
	{
		$lengthFound = $this->parseShortLength( $length );
		if ( !$lengthFound )
		{
			if ( $this->data[$this->curp] == 31 )
			{
				$this->curp++;
				$this->parseUintvar( $length );
				return 1;
			}
		}
		return $lengthFound;
	}

	function parseUintvar( &$uintvar )
	{
		$uintvar = 0;
		while ( $this->data[$this->curp] & 0x80 )
		{
			$uintvar = $uintvar << 7;
			$uintvar |= $this->data[$this->curp] & 0x7f;
			$this->curp++;
		}
		$uintvar = $uintvar << 7;
		$uintvar |= $this->data[$this->curp] & 0x7f;
		$this->curp++;
	}

	function parseTo()
	{
		$this->parseEncodedString( $this->to );
		if ( DEBUG )
			print( "To: " . $this->to . "\n" );
	}

	function parseBcc()
	{
		$this->parseEncodedString( $this->bcc );
		if ( DEBUG )
			print( "Bcc: " . $this->bcc . "\n" );
	}

	function parseCc()
	{
		$this->parseEncodedString( $this->cc );
		if ( DEBUG )
			print( "Cc: " . $this->cc . "\n" );
	}


	function parseSubject()
	{
		$this->parseEncodedString( $this->subject );
		if ( DEBUG )
			print( "Subject: " . $this->subject . "\n" );
	}

	function parseMessageClass()
	{
		if ( $this->data[$this->curp] < 128 ||
			$this->data[$this->curp] > 131 )
		{
			die( "parseMessageClass not fully implemented" );
		}
		$this->messageClass = $this->data[$this->curp++];

		if ( DEBUG )
		{
			print( "X-Mms-Message-Class: " .
				messageClassToString($this->messageClass) .
				"\n" );
		}
	}


	function parsePriority()
	{
		$this->priority = $this->data[$this->curp++];
		if ( DEBUG )
		{
			print( "X-Mms-Priority: " .
				priorityToString( $this->priority ) .
				"\n" );
		}
	}


	function parseSenderVisibility()
	{
		$this->senderVisibility = $this->data[$this->curp++];
		if ( DEBUG )
		{
			print( "X-Mms-Sender-Visibility: " .
			  senderVisibilityToString($this->senderVisibility) .
			  "\n" );
		}
	}


	function parseDeliveryReport()
	{
		$this->deliveryReport = $this->data[$this->curp++];
		if ( DEBUG  )
		{
			print( "X-Mms-Delivery-Report: " .
			   deliveryReportToString($this->deliveryReport) .
			   "\n" );
		}
	}


	function parseReadReply()
	{
		$this->readReply = $this->data[$this->curp++];
		if ( DEBUG )
		{
			print( "X-Mms-Read-Reply: " .
				readReplyToString($this->readReply) .
				"\n" );
		}
	}


	function parseContentType( &$contentType)
	{
		$typeFound = $this->parseConstrainedMedia( $contentType );
		if ( !$typeFound )
		{
			$this->parseContentGeneralForm($contentType);
			$typeFound = 1;
		}

		if ( DEBUG )
		{
			printf( "Content-type: " .
				contentTypeToString( $contentType ) .
				"\n" );
		}
		return $typeFound;
	}


	function parseConstrainedMedia( &$contentType )
	{
		return $this->parseConstrainedEncoding( $contentType );
	}

	function parseConstrainedEncoding( &$encoding )
	{
		$res = $this->parseShortInteger( $encoding );
		if ( !$res )
			$res = $this->parseExtensionMedia( $encoding );
		return $res;
	}

	function parseExtensionMedia( &$encoding )
	{
		$ch = $this->data[$this->curp];
		if ( $ch < 32 || $ch == 127)
			return 0;
		$res = $this->parseTextString( $encoding );
		return $res;
	}

	function parseContentGeneralForm( &$encoding )
	{
		$res = $this->parseValueLength( $length );
		$tmp = $this->curp;
		if ( !$res )
			return 0;
		$res = $this->parseMediaType( $encoding );

		/* Jump over everything regardless of parameters */
		$this->curp = $tmp + $length;
		return $res;
	}

	function parseMediaType( &$encoding )
	{
		$res = $this->parseWellKnownMedia( $encoding );
		if ( !$res )
			$res = $this->parseExtensionMedia( $encoding );
		return $res;
	}

	function parseWellKnownMedia( &$encoding )
	{
		return $this->parseInteger( $encoding );
	}

	function parseInteger( &$integer )
	{
		$res = $this->parseShortInteger( $integer );
		if ( !$res )
			$res = $this->parseLongInteger( $integer );
		return $res;
	}

	function parseBody()
	{
		$this->parseUintvar( $this->nparts );
		if ( DEBUG )
		{
			printf( "Multipart with " . $this->nparts .
				" entries\n" );
		}
		for ( $i = 0; $i < $this->nparts; $i++ )
			$this->parsePart( $i );
	}

	function parsePart( $i )
	{
		$part = new Part;
		$this->parseUintvar( $headersLen );
		$this->parseUintvar( $dataLen );
		$part->dataLen = $dataLen;
		$tmp = $this->curp;
		$this->parseContentType( $part->contentType );

		$this->druhy[$i] =  contentTypeToString( $part->contentType ) ;
		$this->jmena[$i] =   $part->fname  ;

		/* Skip headers for now */
		$this->curp = $tmp + $headersLen;

		/* Store data */
		for ( $j = 0; $j < $dataLen; $j++)
		{
			$part->data[$j] = $this->data[$this->curp];
			$this->curp++;
		}
		$this->parts[$i] = $part;

	}
}



/**
 * MMSEncoder class
 *
 * The class that does the encoding of the mms message and writes it to a file.
 * Use addPart to add parts to the encoder and then use the writeToFile function
 * to write the mms to the file system
 *
 * Limitations: The encoding of headers for parts are incomplete so you would not be
 *              able to add a SMIL part and reference parts in the message from the SMIL
 *              content. The only header currently supported for parts is the simple
 *              Content-type header. So currently this can only be used to deliver
 *              chunks of content collections - no complete presentations
 */

class MMSEncoder
{
	/* Body variables */
	var $nparts;
	var $parts;


	function MMSEncoder()
	{
		$this->nparts = 0;
	}

	function writeToFile( $fileName )
	{
		$fp = fopen( $fileName, "wb" );
		$this->writeHeadersToFp( $fp );
		$this->writePartsToFp( $fp );
		fclose( $fp );
	}

	function writeToFile2( $data ,$fileName)
	{
		$fp = fopen( $fileName, "wb" );
		$this->writeHeadersToFp( $fp );
		$this->writePartsToFp( $fp );
		fclose( $fp );
	}


	function writeHeadersToFp( $fp )
	{
		$header[0] = 0x8c; /* X-Mms-Message-Type */
        	$header[1] = 0x84; /*    = m-retrieve-conf */
            $i = 2;
        	//$header[$i++] = 0; /* Terminate string */
        	$header[$i++] = 0x8D; /* X-Mms-Version */
        	$header[$i++] = 0x90; /*    = 1.0      */

		$header[$i++] = 0x85; /* Date          */
		$date = mktime();
		$this->encodeLongInteger( $date, $longInt );
		$len = sizeof( $longInt );
		for ( $j = 0; $j < $len; $j++ )
			$header[$i++] = $longInt[$j];
        //   by hatak -->>>
			$header[$i++] = 0x96; /* subject*/
		    $subject = "test";
        	for ( $d = 0; $d < strlen( $subject ); $d++ )
               	$header[$i++] = ord( $subject{$d} );



		$header[$i++] = 0;
		$header[$i++] = 0x8A; /* mes. class  */
		$header[$i++] = 0x80; /* personal  */
		$header[$i++] = 0x8F; /* priority  */
		$header[$i++] = 0x81; /* normal  */
		$header[$i++] = 0x94; /* sender visibility */
		$header[$i++] = 0x81; /* no  */
		$header[$i++] = 0x86; /* delivery report  */
		$header[$i++] = 0x81; /* no  */
		$header[$i++] = 0x90; /* read reply  */
		$header[$i++] = 0x81; /* no  */

		$header[$i++] = 0x84; /* Content-type  */
		if ( $this->nparts == 0 )
		{
			print( "No content added to message\n" );
			return;
		}

        $header[$i++]= 0x1B;
		//$header[$i++]= 0x23;
		$this->encodeContentType( "application/vnd.wap.multipart.related", $contentType );

		for ( $j = 0; $j < sizeof( $contentType ); $j++ )
			$header[$i++] = $contentType[$j];

		$header[$i++]= 0x8A;      //   Content-type-parameter: Type   /
		$idecko = "<SMIL>";
		for ( $e = 0; $e < strlen( $idecko ) ; $e++ )
		$header[$i++]= ord($idecko{$e} );
		$header[$i++]= 0;


		$header[$i++]= 0x89; //    parametr Start     /
		$typ = "application/smil";
        for ( $w = 0; $w < strlen( $typ ) ; $w++ )
                	$header[$i++]= ord($typ{$w} );

		$header[$i++]= 0;

		for ( $j = 0; $j < $i; $j++ )
				fwrite( $fp, chr( $header[$j] ), 1 );

		//<<<<<<<<---  by hatak

	}

	function writePartsToFp( $fp )
	{
		// Write number of parts in the multipart message
		$this->encodeUintvar( $this->nparts, $uintVar );
		for ( $j = 0; $j < sizeof( $uintVar ); $j++ )
			fwrite( $fp, chr( $uintVar[$j] ), 1 );

		for ( $i = 0; $i < $this->nparts; $i++ )
		{
			$p = $this->parts[$i];
			$IDfile =  $p->fileID;
			$fname =  $p->fname;

			$add_len = strlen( $fname ) + strlen( $IDfile ) +7;
			$this->encodeContentType( $p->contentType, $contentType );
			$this->encodeUintvar( sizeof( $contentType )+$add_len, $headerLen );

			$this->encodeUintvar( $p->dataLen, $dataLen );

			// Write size of header
			for ( $j = 0; $j < sizeof( $headerLen ); $j++ )
				fwrite( $fp, chr( $headerLen[$j] ), 1 );

			// Write length of data
			for ( $j = 0; $j < sizeof( $dataLen ); $j++ )
				fwrite( $fp, chr( $dataLen[$j] ), 1 );

			// Write content type header (no other headers implemented right now)
			for ( $j = 0; $j < sizeof( $contentType ); $j++ )
				fwrite( $fp, chr( $contentType[$j] ), 1 );

             //Content-ID by hatak --->>>>
			 fwrite( $fp, chr(0xC0), 1 );
			 fwrite( $fp, chr(0x22), 1 );
			 fwrite( $fp, chr(0x3C), 1 );

			 for ( $j = 0; $j < strlen( $IDfile ); $j++ )
				fwrite( $fp, chr (ord( $IDfile{$j} )), 1 );
			 fwrite( $fp, chr(0x3E), 1 );
			 fwrite( $fp, chr(0), 1 );
             //Content-Location by hatak --->>>>
             fwrite( $fp, chr(0x8E), 1 );
			 for ( $j = 0; $j < strlen(  $fname ); $j++ )
				fwrite( $fp, chr (ord(  $fname{$j} )), 1 );
			 fwrite( $fp, chr(0), 1 );
             //<<<--------Content by hatak

			$p->writeToFp( $fp );
		}
	}


	function addPart( $contentType, $fileName, $fileID )
	{
		if ( is_readable( $fileName ) )
		{
			$p = new Part( $contentType, $fileName,$fileID );
			$this->parts[ $this->nparts ] = $p;
			$this->nparts++;
		}
		else
		{
			print( "Could not find file: " . $fileName . "\n" );
		}
	}


	function encodeLongInteger( $data, &$longInt )
	{
		$len = 0;

		while ( $data > 0 )
		{
			$tmp[$len] = $data & 0xff;
			$data = $data >> 8;
			$len++;
		}

		$longInt[0] = $len; /* Set short-length */

		/* tmp is the reverse of what we want so we reverse it */
		for ( $i = 0; $i < $len; $i++ )
			$longInt[$i+1] = $tmp[ $len - $i - 1 ];

	}

	function encodeUintvar( $data, &$uintVar )
	{
		$i = 0;
		$reversed[$i] = $data & 0x7f; // The lowest
		$data = $data >> 7;

		$i++;
		while ( $data > 0 )
		{
			$reversed[$i] = 0x80 | ($data & 0x7f);
			$i++;
			$data = $data >> 7;
		}

		// Reverse it because it is in reverse order
		for ( $j = 0; $j < $i; $j++ )
		{
			$uintVar[$j] = $reversed[$i - $j - 1];
		}
	}

	function encodeContentType( $textContentType, &$contentType )
	{
		$index = $this->findWellKnownContentType( $textContentType );
		if ( $index >= 0 )
		{
			$contentType[0] = 0x80 | $index;  /* Encode short-integer */
		}
		else
		{
			for ( $i = 0; $i < strlen( $textContentType ); $i++ )
				$contentType[$i] = ord( $textContentType[$i] );
			/* Null terminated */
			$contentType[$i] = 0;
		}
	}

	function findWellKnownContentType( $contentType )
	{
		global $content_types;
		for ( $i = 0; $i < sizeof( $content_types ); $i++ )
			if ( !strcmp( $content_types[$i], $contentType ) )
				return $i;
		return -1;
	}

}


/**
 * MMSNotifyer class
 *
 * This class is used to send out the notification that will point the phone to the
 * MMS that is located at a certain URL. The SMS service is your simple web interface
 * using HTTP 1.1 GET. Change this to fit your system and your way of SMS sending
 */
class MMSNotifyer
{
	var $sms_host;
	var $sms_port;
	var $headers;

	function MMSNotifyer( $sms_host, $sms_port )
	{
		$this->sms_host = $sms_host;
		$this->sms_port = $sms_port;
		$this->headers = "E9";
		$this->headers .= "06";
		$this->headers .= "22";
		$this->headers .= "6170706C69636174"; // 'application/vnd.wap.mms-message' ...
		$this->headers .= "696F6E2F766E642E"; // hex ...
		$this->headers .= "7761702E6D6D732D"; // encoded ...
		$this->headers .= "6D65737361676500"; // ...
		$this->headers .= "AF84";
	}

	function sendNotify( $mms_url, $to, $subject )
	{
		//---------- X-Mms-Message-Type ----------
		$message = "8C" . "82"; // m-notification-ind

		//---------- X-Mms-Transaction-Id ----------
		$message .="98" . $this->hex_encode("mmslib") . "00";

		//---------- X-Mms-Version ----------
		$message .= "8D" . "90"; // 1.0

		//---------- Subject ----------------
		$message .= "96";
		$message .= $this->hex_encode( $subject ) . "00";

		//---------- X-Mms-Message-Class ----------
		$message .= "8A80"; // 80 = personal, 81 = ad, 82=info, 83 = auto

		//---------- X-Mms-Message-Size ----------
		$message .= "8E020B05";

		//---------- X-Mms-Expiry ----------
		$message .= "88058103015180";

		//---------- X-MMS-Content-Location ----------
		$message .= "83" . $this->hex_encode($mms_url) . "00";

		print( "SMS size is: " . strlen( $this->headers . $message ) . "\n" );
		print( "And the data part is: " . $this->headers . $message . "\n" );
		$this->httpSend( "/mmstry/sendmms1.html?phone=$to&UDH=0605040B8423F0&Data=" .
				  $this->headers . $message );
	}

	function httpSend( $path )
	{
 		$connection = fsockopen( $this->sms_host,
					 $this->sms_port,
					 &$errno,
					 &$errdesc,
					 60 );

  		fputs( $connection,
		       "GET $path HTTP/1.1\r\nHost: " . $this->sms_host .
	       	       "\r\n\r\n");

  		while (!feof($connection))
		{
   			$myline = fgets($connection, 128);
			// possibly do something here to check reply
   		}

 		fclose ($connection);
	}

	function hex_encode($text)
	{
       		$encoded = strtoupper(bin2hex($text));
       		return $encoded;
	}

}



?>
