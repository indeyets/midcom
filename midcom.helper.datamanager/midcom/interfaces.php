<?php

class midcom_helper_datamanager_midcom {

    function initialize() {
        
        $prefix = MIDCOM_ROOT . "/midcom/helper/datamanager";
        
        require("{$prefix}/datamanager.php");
        
        require("{$prefix}/datatype.php");
        require("{$prefix}/datatype_text.php");
        require("{$prefix}/datatype_blob.php");
        require("{$prefix}/datatype_image.php");
        require("{$prefix}/datatype_collection.php");
        require("{$prefix}/datatype_boolean.php");
        require("{$prefix}/datatype_multiselect.php");
        require("{$prefix}/datatype_unixdate.php");
        require("{$prefix}/datatype_communityhtml.php");
        require("{$prefix}/datatype_account.php");
        require("{$prefix}/datatype_mailtemplate.php");
        require("{$prefix}/datatype_number.php");
        require("{$prefix}/datatype_integer.php");        
        require("{$prefix}/datatype_markdown.php");
        require("{$prefix}/datatype_array.php");
        require("{$prefix}/datatype_privilege.php");

        require("{$prefix}/widget.php");
        require("{$prefix}/widget_text.php");
        require("{$prefix}/widget_select.php");
        require("{$prefix}/widget_multiselect.php");
        require("{$prefix}/widget_radiobox.php");
        require("{$prefix}/widget_html.php");
        require("{$prefix}/widget_blob.php");
        require("{$prefix}/widget_collection.php");
        require("{$prefix}/widget_image.php");
        require("{$prefix}/widget_checkbox.php");
        require("{$prefix}/widget_date.php");
        require("{$prefix}/widget_communityhtml.php");
        require("{$prefix}/widget_account.php");
        require("{$prefix}/widget_mailtemplate.php");
        require("{$prefix}/widget_markdown.php");
        require("{$prefix}/widget_schemaselect.php");
        require("{$prefix}/widget_config_radiobox.php");
        require("{$prefix}/widget_tinymce.php");
        require("{$prefix}/widget_contactchooser.php");
        
        require_once("{$prefix}/helpers_select_lists.php");
        
        return true;
    }

    function properties() {
        return array (
            MIDCOM_PROP_PURECODE => true,
            MIDCOM_PROP_VERSION  => 1,
            MIDCOM_PROP_NAME     => "Datamanager Library"
        );
    }

}

?>
