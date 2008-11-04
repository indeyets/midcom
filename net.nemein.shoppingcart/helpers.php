<?php
/**
 * @package net.nemein.shoppingcart
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @param mixed $width iframe width
 * @param mixed $height iframe height
 */
function net_nemein_shoppingcart_render_shortlist_iframe($width = false, $height = false)
{
    $url = net_nemein_shoppingcart_get_shortlist_content_url();
    if (empty($url))
    {
        return;
    }
    // TODO: Do not render the iframe if we are inside the cart component
    /* This is over-zealous
    $context_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    if (strpos($url, $context_prefix) !== false)
    {
        return;
    }
    */

    echo "<iframe name='net_nemein_shoppingcart_shortlist' class='net_nemein_shoppingcart_shortlist' src='{$url}'";
    if ($width)
    {
        echo " width='{$width}'";
    }
    if ($height)
    {
        echo " height='{$height}'";
    }
    echo "></iframe>\n";
}

function net_nemein_shoppingcart_get_shortlist_content_url()
{
    $prefix = net_nemein_shoppingcart_get_node_url();
    if (empty($prefix))
    {
        return false;
    }
    return "{$prefix}shortlist/";
}

function net_nemein_shoppingcart_get_node_url()
{
    static $node = null;
    if (is_null($node))
    {
        $node = midcom_helper_find_node_by_component('net.nemein.shoppingcart');
    }
    if (empty($node))
    {
        return false;
    }
    return $node[MIDCOM_NAV_FULLURL];
}

function net_nemein_shoppingcart_get_additem_url(&$product)
{
    if (!is_a($product, 'org_openpsa_products_product_dba'))
    {
        return false;
    }
    if (empty($product->guid))
    {
        return false;
    }
    $prefix = net_nemein_shoppingcart_get_node_url();
    if (empty($prefix))
    {
        return false;
    }
    return "{$prefix}add/{$product->guid}/";
}

function net_nemein_shoppingcart_render_additem_shortlist(&$product)
{
    $url = net_nemein_shoppingcart_get_additem_url($product);
    if (empty($url))
    {
        return;
    }
    $midcom_i18n =& $_MIDCOM->get_service('i18n');
    $l10n =& $midcom_i18n->get_l10n('net.nemein.shoppingcart');
    $add_str = $l10n->get('add to cart');
    echo "<a class='net_nemein_shoppingcart_addtocart' href='{$url}' target='net_nemein_shoppingcart_shortlist'>{$add_str}</a>";
}

if (   !function_exists('mb_str_pad')
    && function_exists('mb_strlen'))
{
    // Copiued from php.net str_pad comments
    function mb_str_pad($ps_input, $pn_pad_length, $ps_pad_string = " ", $pn_pad_type = STR_PAD_RIGHT, $ps_encoding = NULL) {
      $ret = "";

      if (is_null($ps_encoding))
        $ps_encoding = mb_internal_encoding();

      $hn_length_of_padding = $pn_pad_length - mb_strlen($ps_input, $ps_encoding);
      $hn_psLength = mb_strlen($ps_pad_string, $ps_encoding); // pad string length

      if ($hn_psLength <= 0 || $hn_length_of_padding <= 0) {
        // Padding string equal to 0:
        //
        $ret = $ps_input;
        }
      else {
        $hn_repeatCount = floor($hn_length_of_padding / $hn_psLength); // how many times repeat

        if ($pn_pad_type == STR_PAD_BOTH) {
          $hs_lastStrLeft = "";
          $hs_lastStrRight = "";
          $hn_repeatCountLeft = $hn_repeatCountRight = ($hn_repeatCount - $hn_repeatCount % 2) / 2;

          $hs_lastStrLength = $hn_length_of_padding - 2 * $hn_repeatCountLeft * $hn_psLength; // the rest length to pad
          $hs_lastStrLeftLength = $hs_lastStrRightLength = floor($hs_lastStrLength / 2);      // the rest length divide to 2 parts
          $hs_lastStrRightLength += $hs_lastStrLength % 2; // the last char add to right side

          $hs_lastStrLeft = mb_substr($ps_pad_string, 0, $hs_lastStrLeftLength, $ps_encoding);
          $hs_lastStrRight = mb_substr($ps_pad_string, 0, $hs_lastStrRightLength, $ps_encoding);

          $ret = str_repeat($ps_pad_string, $hn_repeatCountLeft) . $hs_lastStrLeft;
          $ret .= $ps_input;
          $ret .= str_repeat($ps_pad_string, $hn_repeatCountRight) . $hs_lastStrRight;
          }
        else {
          $hs_lastStr = mb_substr($ps_pad_string, 0, $hn_length_of_padding % $hn_psLength, $ps_encoding); // last part of pad string

          if ($pn_pad_type == STR_PAD_LEFT)
            $ret = str_repeat($ps_pad_string, $hn_repeatCount) . $hs_lastStr . $ps_input;
          else
            $ret = $ps_input . str_repeat($ps_pad_string, $hn_repeatCount) . $hs_lastStr;
          }
        }

      return $ret;
      }
}

?>