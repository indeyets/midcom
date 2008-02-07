#!/usr/bin/php
<?php
error_reporting(E_ALL);
require_once('normalize_whitespace_normalizer.php');
if ($argc < 4)
{
    $name = basename($argv[0]);
    echo "\nUsage: {$name} <configfile> <username> <password>\n";
    echo "  For example:\n";
    echo "  {$name} midgard_sgx 'sgadmin+sgname' 'adminpasswd' \n\n";
    echo "(you need to specify the MidgardUser and MidgardPassword\n in the midgard_sgx config file)\n\n";
    exit(1);
}
$conffile =& $argv[1];
$username =& $argv[2];
$password =& $argv[3];
if (!mgd_config_init($conffile))
{
    echo "\nInitialization failed\n\n";
    exit(1);
}
mgd_auth_midgard($username, $password);
if (!$_MIDGARD['user'])
{
    echo "\nAuthentication failed\n\n";
    exit(1);
}
if ($_MIDGARD['sitegroup'] === 0)
{
    echo "\nSG0 usage not supported\n\n";
    exit(1);
}

$normalizer = new midcom_support_wsnormalizer();

// Pages (PONDER: handle Multilang ??)
$qb = new midgard_query_builder('midgard_page');
$qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
$pages = $qb->execute();
unset($qb);
foreach ($pages as $page)
{
    $normalized = $normalizer->normalize($page->content);
    if ($normalized === $page->content)
    {
        unset($normalized, $page);
        continue;
    }
    $page->content = $normalized;
    unset($normalized);
    if (!$page->update())
    {
        echo " Failed to update page #{$page->id} ({$page->name}), " . mgd_errstr() . "\n";
        unset($page);
        continue;
    }
    //echo "DEBUG: updated page #{$page->id} ({$page->name}), " . mgd_errstr() . "\n";
    unset($page);
}
unset($pages);

// Page-elements (PONDER: handle Multilang ??)
$qb = new midgard_query_builder('midgard_pageelement');
$qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
$pageelements = $qb->execute();
unset($qb);
foreach ($pageelements as $pageelement)
{
    $normalized = $normalizer->normalize($pageelement->value);
    if ($normalized === $pageelement->value)
    {
        unset($normalized, $pageelement);
        continue;
    }
    $pageelement->value = $normalized;
    unset($normalized);
    if (!$pageelement->update())
    {
        echo " Failed to update page-element #{$pageelement->id} ({$pageelement->name}), " . mgd_errstr() . "\n";
        unset($pageelement);
        continue;
    }
    //echo "DEBUG: updated page-element #{$pageelement->id} ({$pageelement->name}), " . mgd_errstr() . "\n";
    unset($pageelement);
}
unset($pageelements);

// (Style) elements (PONDER: handle Multilang ??)
$qb = new midgard_query_builder('midgard_element');
$qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
$elements = $qb->execute();
unset($qb);
foreach ($elements as $element)
{
    $normalized = $normalizer->normalize($element->value);
    if ($normalized === $element->value)
    {
        unset($normalized, $element);
        continue;
    }
    $element->value = $normalized;
    unset($normalized);
    if (!$element->update())
    {
        echo " Failed to update style-element #{$element->id} ({$element->name}), " . mgd_errstr() . "\n";
        unset($element);
        continue;
    }
    //echo "DEBUG: updated style-element #{$element->id} ({$element->name}), " . mgd_errstr() . "\n";
    unset($element);
}
unset($elements);



?>