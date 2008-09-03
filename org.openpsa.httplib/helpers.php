<?php
/**
 * @package org.openpsa.httplib
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Helpers for HTTP content fetching and handling
 *
 * @package org.openpsa.httplib
 */
class org_openpsa_httplib_helpers extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class
     */
    function __construct()
    {
         $this->_component = 'org.openpsa.httplib';
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Validates given URL string format
     *
     * @param string $url Uniform Resource Locator
     * @return boolean Whether URL is valid
     */
    function validate_url($url)
    {
        // TODO: Implement
        return true;
    }

    function _quotes()
    {
        return '"\'';
    }

    /**
     * Get value of a meta tag in HTML page.
     *
     * @param string $html HTML to parse
     * @param string $name Name of the meta tag to fetch
     * @param array Content of the meta tag
     */
    function get_meta_value($html, $name)
    {
        $quotes = org_openpsa_httplib_helpers::_quotes();
        $regex_metatag = "/<html.*?>.*?<head.*?>.*?(<meta[^>]*?name=([{$quotes}]){$name}\\2[^>]*?>).*?<\/head>/msi";
        $regex_value = "/content=([{$quotes}])(.*?)\\1/i";
        if (!preg_match($regex_metatag, $html, $tag_matches))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to find meta tag for name \"{$name}\"", MIDCOM_LOG_DEBUG);
            debug_pop();
            return '';
        }
        if (!preg_match($regex_value, $tag_matches[1], $value_matches))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to find meta tag value for tag name \"{$name}\"", MIDCOM_LOG_DEBUG);
            debug_pop();
            return '';
        }
        return $value_matches[2];
    }

    /**
     * Get value(s) of a link tag(s) in HTML page.
     *
     * @param string $html HTML to parse
     * @param string $relation Relation (rel) or reverse relation (rev) of the link tag to fetch
     * @param string $type Type (type) of the link tag to fetch (defaults to null, meaning all types of the link relation)
     * @return array Links matching given criteria as arrays containing keys title, href and optionally hreflang
     */
    function get_link_values($html, $relation, $type = null)
    {
        $quotes = org_openpsa_httplib_helpers::_quotes();
        $values = array();
        $regex_linktags = "/<html.*?>.*?<head.*?>.*?(<link[^>]*?rel=([{$quotes}]){$relation}\\2[^>]*?>).*?<\/head>/msi";
        if (!preg_match_all($regex_linktags, $html, $tag_matches))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to find link tag values for relation \"{$relation}\"", MIDCOM_LOG_DEBUG);
            debug_pop();
            return $values;
        }
        if (!is_null($type))
        {
            $regex_type_filter = "/type=([{$quotes}]){$type}\\1/i";
            foreach ($tag_matches[1] as $k => $tagcode)
            {
                // Type filter not matches, clear from resultset
                if (!preg_match($regex_type_filter, $tagcode))
                {
                    unset($tag_matches[0][$k]);
                    unset($tag_matches[1][$k]);
                    unset($tag_matches[2][$k]);
                }
            }
        }
        $regex_properties = "/(title|href|hreflang)=([{$quotes}])(.*?)\\2/i";
        foreach ($tag_matches[1] as $tagcode)
        {
            $tag = array
            (
                'title' => false,
                'href' => false,
                'hreflang' => false,
            );
            if (!preg_match_all($regex_properties, $tagcode, $property_matches))
            {
                // Could not read properties
                continue;
            }
            foreach ($property_matches[1] as $k => $property)
            {
                $value =& $property_matches[3][$k];
                if (!array_key_exists($property, $tag))
                {
                    // WTF unsupported property ??
                    continue;
                }
                $tag[$property] = $value;
            }
            $values[] = $tag;
        }

        return $values;
    }

    /**
     * Get value(s) of an anchor tag(s) in HTML page.
     *
     * @param string $html HTML to parse
     * @param string $relation Relation (rel) of the anchor to fetch
     * @return array Links matching given criteria as arrays containing keys title, href and value
     */
    function get_anchor_values($html, $relation)
    {
        $quotes = org_openpsa_httplib_helpers::_quotes();
        $values = array();
        $regex_atags = "/(<a[^>]*?rel=([{$quotes}]){$relation}\\2[^>]*?>)((.*?)<\/a>)?/msi";
        if (!preg_match_all($regex_atags, $html, $tag_matches))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to find anchor tag values for relation \"{$relation}\"", MIDCOM_LOG_DEBUG);
            debug_pop();
            return $values;
        }
        $regex_properties = "/(title|href)=([{$quotes}])(.*?)\\2/i";
        foreach ($tag_matches[1] as $key => $tagcode)
        {
            $tag = array
            (
                'title' => false,
                'href' => false,
                'value' => false,
            );
            if (!preg_match_all($regex_properties, $tagcode, $property_matches))
            {
                // Could not read properties
                continue;
            }
            if (isset($tag_matches[4][$key]))
            {
                $tag['value'] = $tag_matches[4][$key];
            }
            foreach ($property_matches[1] as $k => $property)
            {
                $value =& $property_matches[3][$k];
                if (!array_key_exists($property, $tag))
                {
                    // WTF unsupported property ??
                    continue;
                }
                $tag[$property] = $value;
            }
            $values[] = $tag;
        }

        return $values;
    }
}
?>