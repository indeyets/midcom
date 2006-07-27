<?php
// Bind the view data, remember the reference assignment:

$request_data =& $_MIDCOM->get_custom_context_data('request_data');

$data = $request_data['datamanager']->get_array();

$fieldgroups = $request_data['datamanager']->get_fieldgroups();
$current_fieldgroup = $fieldgroups[0];
//$toolbar = new midcom_helper_toolbar('midcom_toolbar', 'midcom_toolbar');
foreach ($fieldgroups as $key => $fieldgroup) {
    $request_data['toolbars']->form->add_item(
                    Array 
                    (
                        MIDCOM_TOOLBAR_URL => "#", 
                        MIDCOM_TOOLBAR_LABEL => $fieldgroup,
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => null,
                        MIDCOM_TOOLBAR_OPTIONS => array ('onclick' => 'showdiv(event)', 
                                                'rel' => true, 
                                                'id'=> $fieldgroup,
                                                'class' => ($fieldgroup == $current_fieldgroup) ? 'enabled' : 'disabled',
                                                ),
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_HIDDEN => false,
                        //MIDCOM_TOOLBAR_SUBMENU => $childs
                    ));
}

?>
<style type="text/css">

#ais_content_admin form.datamanager div.midcom_datamanager_fieldgroup {    display:none;  }

#ais_content_admin form.datamanager  #midcom_datamanager_fieldgroup_<? echo $current_fieldgroup; ?> {     display:block;}

#ais_container #midcom_toolbar_form a.enabled {
    background-color: #BAB5AB;
    color: #EEEEEE;
    text-decoration: none;
    -moz-border-radius: 6px;
}

</style>

<script language="javascript" type="text/javascript">
var currently_open = 'midcom_datamanager_fieldgroup_<? echo $current_fieldgroup;  ?>';
</script>
<?php echo $request_data['toolbars']->form->render(); ?>

<?php $request_data['datamanager']->display_form (); ?>
