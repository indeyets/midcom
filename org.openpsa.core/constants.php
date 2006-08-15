<?php
/**
 * Constants for OpenPSA 2
 */

//The OpenPSA Version number and name are defined in version.php (on purpose)

//Constant versions of wgtype bitmasks
define('ORG_OPENPSA_WGTYPE_NONE', 0);
define('ORG_OPENPSA_WGTYPE_INACTIVE', 1);
define('ORG_OPENPSA_WGTYPE_ACTIVE', 3);

//Constants for ACL shortcuts
define('ORG_OPENPSA_ACCESSTYPE_PRIVATE', 100);
define('ORG_OPENPSA_ACCESSTYPE_WGPRIVATE', 101);
define('ORG_OPENPSA_ACCESSTYPE_PUBLIC', 102);
define('ORG_OPENPSA_ACCESSTYPE_AGGREGATED', 103);
define('ORG_OPENPSA_ACCESSTYPE_WGRESTRICTED', 104);
define('ORG_OPENPSA_ACCESSTYPE_ADVANCED', 105);

//org.openpsa.contacts object types
define('ORG_OPENPSA_OBTYPE_OTHERGROUP', 0);
define('ORG_OPENPSA_OBTYPE_ORGANIZATION', 1000);
define('ORG_OPENPSA_OBTYPE_DAUGHTER', 1001);
define('ORG_OPENPSA_OBTYPE_DEPARTMENT', 1002);
define('ORG_OPENPSA_OBTYPE_PERSON', 2000);
define('ORG_OPENPSA_OBTYPE_RESOURCE', 2001);

//org.openpsa.documents object types
define('ORG_OPENPSA_OBTYPE_DOCUMENT', 3000);
//org.openpsa.documents document status
define('ORG_OPENPSA_DOCUMENT_STATUS_DRAFT', 4000);
define('ORG_OPENPSA_DOCUMENT_STATUS_FINAL', 4001);
define('ORG_OPENPSA_DOCUMENT_STATUS_REVIEW', 4002);

//org.openpsa.calendar object types
define('ORG_OPENPSA_OBTYPE_EVENT', 5000);
define('ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT', 5001);
define('ORG_OPENPSA_OBTYPE_EVENTRESOURCE', 5002);


//org.openpsa.reports object types
define('ORG_OPENPSA_OBTYPE_REPORT', 7000);
define('ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY', 7001);

//org.openpsa.directmarketing message types
define('ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT', 8000);
define('ORG_OPENPSA_MESSAGETYPE_SMS', 8001);
define('ORG_OPENPSA_MESSAGETYPE_MMS', 8002);
define('ORG_OPENPSA_MESSAGETYPE_CALL', 8003);
define('ORG_OPENPSA_MESSAGETYPE_SNAILMAIL', 8004);
define('ORG_OPENPSA_MESSAGETYPE_FAX', 8005);
define('ORG_OPENPSA_MESSAGETYPE_EMAIL_HTML', 8006);
//org.openpsa.directmarketing message receipt types
define('ORG_OPENPSA_MESSAGERECEIPT_SENT', 8500); //Created when message has been sent successfully
define('ORG_OPENPSA_MESSAGERECEIPT_DELIVERED', 8501); //Created if we get a delivery receipt
define('ORG_OPENPSA_MESSAGERECEIPT_RECEIVED', 8502); //Created if we get some confirmation from the recipient
//org.openpsa.directmarketing campaign member types
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER', 9000);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER', 9001);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED', 9002);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_BOUNCED', 9003);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_INTERVIEWED', 9004);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_LOCKED', 9005);
//org.openpsa.directmarketing campaign types
define('ORG_OPENPSA_OBTYPE_CAMPAIGN', 9500);
define('ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART', 9501);
?>