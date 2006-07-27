<?php 
/*
Really Simple Discoverability format document for de.linkm.newsticker

RSD is used by several desktop blogging tools for autoprobing blog capabilities.

More information about the format can be found in:
http://archipelago.phrasewise.com/rsd
*/
$server_url = $GLOBALS["midcom"]->get_host_name();
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
echo '<?xml version="1.0" ?>';
?>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd" >
    <service>
        <engineName>Midgard CMS/<?php echo mgd_version(); ?></engineName> 
        <engineLink>http://www.midgard-project.org/</engineLink>
        <homePageLink><?php echo htmlspecialchars($server_url.$prefix);?></homePageLink>
        <apis>
          <?php if ($GLOBALS["view_blogging_enabled"]) 
          { 
            ?>
                <api name="MetaWeblog" preferred="true" apiLink="<?php echo htmlspecialchars($server_url.$prefix);?>rpc/metaweblog/" blogID="<?php echo $GLOBALS["view_topic"]->guid(); ?>" />
                <api name="Blogger" preferred="false" apiLink="<?php echo htmlspecialchars($server_url.$prefix);?>rpc/metaweblog/" blogID="<?php echo $GLOBALS["view_topic"]->guid(); ?>" />
            <?php 
          } ?>
        </apis>
    </service>
</rsd>