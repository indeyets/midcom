<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$_MIDCOM->load_library('midcom.helper.xsspreventer');
if (!function_exists('org_openpsa_products_search_options_helper'))
{
    function org_openpsa_products_search_options_helper($options, $request_key, $request_name = 'org_openpsa_products_search')
    {
        foreach ($options as $key => $value)
        {
            $selected = '';
            if (   isset($_REQUEST[$request_name][$request_key]['value'])
                && $_REQUEST[$request_name][$request_key]['value'] == $key)
            {
                $selected = ' selected';
            }
            $key_esc = midcom_helper_xsspreventer::escape_attribute($key);
            $value_esc = midcom_helper_xsspreventer::escape_element('option', $value);
            echo "        <option value={$key_esc}{$selected}>{$value_esc}</option>\n";
        }
    }
}
if (!function_exists('org_openpsa_products_search_value_helper'))
{
    function org_openpsa_products_search_value_helper($request_key, $request_name = 'org_openpsa_products_search')
    {
        if (isset($_REQUEST[$request_name][$request_key]['value']))
        {
            echo ' value=' . midcom_helper_xsspreventer::escape_attribute($_REQUEST[$request_name][$request_key]['value']);
        }
    }
}
?>
<form method="get" class="datamanager">
    
    <label>
        <span class="field_text">match</span>
        <select name="org_openpsa_products_search_type">
            <option value="AND"<?php if (isset($_REQUEST['org_openpsa_products_search_type']) && $_REQUEST['org_openpsa_products_search_type'] == 'AND') echo ' selected'; ?>>All of the following</option>
            <option value="OR"<?php if (isset($_REQUEST['org_openpsa_products_search_type']) && $_REQUEST['org_openpsa_products_search_type'] == 'OR') echo ' selected'; ?>>Any of the following</option>
        </select>
    </label>

    <input type="hidden" name="org_openpsa_products_search[1][property]" value="title" />
    <input type="hidden" name="org_openpsa_products_search[1][constraint]" value="LIKE" />
    <label>
        <span class="field_text"><?php echo sprintf($data['l10n']->get('%s includes'), $data['l10n_midcom']->get('title')); ?></span>
        <input class="shorttext" type="text" name="org_openpsa_products_search[1][value]"<?php org_openpsa_products_search_value_helper(1); ?> />
    </label>

    <input type="hidden" name="org_openpsa_products_search[2][property]" value="price" />
    <input type="hidden" name="org_openpsa_products_search[2][constraint]" value=">=" />
    <label>
        <span class="field_text"><?php echo sprintf($data['l10n']->get('%s is at least'), $data['l10n']->get('price')); ?></span>
        <input class="shorttext" type="text" name="org_openpsa_products_search[2][value]"<?php org_openpsa_products_search_value_helper(2); ?> />
    </label>

    <input type="hidden" name="org_openpsa_products_search[3][property]" value="orgOpenpsaObtype" />
    <input type="hidden" name="org_openpsa_products_search[3][constraint]" value=">=" />
    <label>
        <span class="field_text"><?php echo sprintf($data['l10n']->get('%s is'), $data['l10n']->get('type')); ?></span>
        <select name="org_openpsa_products_search[3][value]">
<?php
    $options = array
    (
        '' => '',
        ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE => $data['l10n']->get('service'),
        ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS => $data['l10n']->get('material goods'),
        ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SOLUTION => $data['l10n']->get('solution'),
    );
    org_openpsa_products_search_options_helper($options, 3);
?>
        </select>
    </label>

    <div class="form_toolbar">
        <input type="submit" accesskey="s" class="search" value="<?php echo $data['l10n']->get('search'); ?>" />
    </div>
</form>