<?php
// This is a style element so the XML output can easily be modified to whatever is needed: DOAP, ...
$mapper = new midcom_helper_xml_objectmapper();
echo $mapper->dm2data($data['datamanager'], 'product');
?>