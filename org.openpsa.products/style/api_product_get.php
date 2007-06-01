<?php
// This is a style element so the XML output can easily be modified to whatever is needed: DOAP, ...
$mapper = new midcom_helper_xml_objectmapper();
$label = $data['datamanager']->schema->name;
if ($label == 'default')
{
    $label = 'product';
}
$xml = $mapper->array2data($data['view_product'], $label);
echo $xml;
?>