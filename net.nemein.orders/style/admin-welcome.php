<?php

/*
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$product =& $_MIDCOM->get_custom_context_data("product");
$order =& $_MIDCOM->get_custom_context_data("order");
$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$auth =& $_MIDCOM->get_custom_context_data("auth");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);

/* no style defined yet, but prepared for it at least */
$toolbar = new midcom_helper_toolbar('net_nemein_orders_admin_welcome_toolbar');

if ($auth->is_owner())
{
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => 'order/show_undelivered.html',
        MIDCOM_TOOLBAR_LABEL => $l10n->get('pending orders'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    if (! $auth->is_mailing_company()) 
    {
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'order/query_delivered.html',
            MIDCOM_TOOLBAR_LABEL => $l10n->get('query delivered orders'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    }        
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => 'product/update_storage.html',
        MIDCOM_TOOLBAR_LABEL => $l10n->get('update storage amount'),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true
    ));
    if ($auth->is_poweruser()) 
    {
        // Add the topic configuration item 
        $config_topic = $config->get('symlink_config_topic');
        if (is_null($config_topic))
        {
            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        else
        {
            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$GLOBALS['view_contentmgr']->viewdata['adminprefix']}{$config_topic->id}/data/config.html",
                MIDCOM_TOOLBAR_LABEL => $l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        // Add the create subcategory link. 
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'create_subcategory.html',
            MIDCOM_TOOLBAR_LABEL => $l10n->get('create subcategory'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        // Add the new article link at the beginning
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'product/create.html',
            MIDCOM_TOOLBAR_LABEL => $l10n->get('create product'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ), 0);
    }
    if ($auth->is_admin()) 
    {
        $toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'order/maintain.html',
            MIDCOM_TOOLBAR_LABEL => $l10n->get('incomplete/corrupt orders'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    }
}

$commands = $toolbar->render();

?>

<h2>&(topic.extra);</h2>

&(commands:h);