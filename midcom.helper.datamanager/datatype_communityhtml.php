<?php

class midcom_helper_datamanager_datatype_communityhtml extends midcom_helper_datamanager_datatype
{

    var $_smileys;
    var $_colors;
    var $_sizes;

    function midcom_helper_datamanager_datatype_communityhtml (&$datamanager, &$storage, $field)
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "attachment";
        }
        if (   ! array_key_exists("datatype_communityhtml_smileys", $field)
            || ! is_array($field["datatype_communityhtml_smileys"]))
        {
            $field["datatype_communityhtml_smileys"] = $this->_get_default_smileys();
        }
        if (   !array_key_exists("datatype_communityhtml_colors", $field)
            || ! is_array($field["datatype_communityhtml_colors"]))
        {
            $field["datatype_communityhtml_colors"] = $this->_get_default_colors();
        }
        if (   !array_key_exists("datatype_communityhtml_sizes", $field)
            || ! is_array($field["datatype_communityhtml_sizes"]))
        {
            $field["datatype_communityhtml_sizes"] = $this->_get_default_sizes();
        }

        $field["widget"] = "communityhtml";

        parent::_constructor ($datamanager, $storage, $field);

        $this->_smileys = $this->_field["datatype_communityhtml_smileys"];
        $this->_colors= $this->_field["datatype_communityhtml_colors"];
        $this->_sizes= $this->_field["datatype_communityhtml_sizes"];
    }

    function get_value()
    {
        $result = Array();
        $result["raw"] = $this->_value;
        $result["formatted"] = $this->_format_string($this->_value);
        return $result;
    }

    function _get_widget_default_value ()
    {
        return $this->_value;
    }

    function _format_string ($raw)
    {
        $result = strip_tags(trim($this->_value));

        $midgard = $_MIDCOM->get_midgard();

        foreach ($this->_smileys as $tag => $url)
        {
            $result = str_replace("[$tag]", "<img src='$url' style='border: none;' />", $result);
        }

        foreach ($this->_colors as $tag => $color)
        {
            $result = str_replace(Array ("[$tag]", "[/$tag]"), Array ("<span style='color: $color;'>", "</span>"), $result);
        }

        foreach ($this->_sizes as $tag => $size)
        {
            $result = str_replace(Array ("[$tag]", "[/$tag]"), Array ("<span style='font-size: $size;'>", "</span>"), $result);
        }

        $result = str_replace(Array ("[u]", "[/u]"), Array ("<span style='text-decoration: underline;'>", "</span>"), $result);
        $result = str_replace(Array ("[b]", "[/b]"), Array ("<span style='font-weight: bold;'>", "</span>"), $result);
        $result = str_replace(Array ("[i]", "[/i]"), Array ("<span style='font-style: italic;'>", "</span>"), $result);
        $result = str_replace(Array ("[quote]", "[/quote]"), Array ("</p><blockquote>", "</blockquote><p>"), $result);
        $result = str_replace("\n", "<br />\n", $result);

        $href_search = Array (
            "/([^\@]+)\@([\w-]+(\.[\w-]+){1,})/", /* E-Mail Adresse */
            "|(https?://[\w/.:%-]*)|i", /* URL */
        );
        $href_replace = Array (
            "$1 at $2",
            "<a href='$1' rel='nofollow'>$1</a>",
        );

        $result = preg_replace($href_search, $href_replace, $result);

        return "<p>$result</p>";
    }

    // This maps Tag-Codes to Smiley-Image-URLs
    function _get_default_smileys()
    {
        return Array (
            'smiley-eek' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-eek.gif',
            'smiley-grin' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-grin.gif',
            'smiley-lol2' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-lol2.gif',
            'smiley-looney' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-looney.gif',
            'smiley-mad' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-mad.gif',
            'smiley-rolleyes' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-rolleyes.gif',
            'smiley-sad' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-sad.gif',
            'smiley-smiley' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager/communityhtml/smiley-smiley.gif',
        );
    }

    // This maps Menu-Entries/Tag-Codes to valid HTML Colors
    function _get_default_colors()
    {
        return Array (
            'red' => 'red',
            'blue' => 'blue',
            'green' => 'green',
            'orange' => 'orange',
            'purple' => 'purple',
            'brown' => 'brown',
        );
    }

    // This maps Menu-Entries/Tag-Codes to CSS font sizes
    function _get_default_sizes()
    {
        return Array (
            'small' => 'small',
            'large' => 'large',
            'x-large' => 'x-large',
        );
    }
}

?>