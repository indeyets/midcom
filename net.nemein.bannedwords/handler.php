<?php
/**
 * @package net.nemein.bannedwords
 */

/**
 * Class for handling banned words
 *
 * @package net.nemein.bannedwords
 */
class net_nemein_bannedwords_handler extends midcom_baseclasses_components_purecode
{
    var $_banned_words = null;

    var $_sitegroup = null;

    var $_language = "en";

    function __construct()
    {
        $this->_sitegroup = $_MIDGARD['sitegroup'];
        $this->_component = 'net.nemein.bannedwords';
    parent::__construct();
    $this->_populate_word_list();
    }

    function set_language($language)
    {
        $this->_language = $language;
    }

    function _populate_word_list()
    {
        $qb = net_nemein_bannedwords_word_dba::new_query_builder();
    $qb->add_constraint('language', '=', $this->_language);

    if ($this->_config->get('use_global_word_library') && $this->_config->get('use_local_word_library'))
    {
        $qb->add_constraint('sitegroup', 'IN', array(0, $this->_sitegroup));
    }
    elseif ($this->_config->get(use_global_word_library))
    {
            $qb->add_constraint('sitegroup', '=', 0);
    }
    elseif ($this->_config->get('use_local_word_library'))
    {
            $qb->add_constraint('sitegroup', '=', $this->_sitegroup);
    }

        $banned_objects = $qb->execute();

    foreach($banned_objects as $banned)
    {
            $this->_banned_words[] = $banned->bannedWord;
    }
    }

    function _build_regexp()
    {
        $regexp = implode("|", $this->_banned_words);
        return $regexp;
    }

    function search_and_replace_html($content)
    {
        if (!empty($this->_banned_words))
    {
            $censored_start = "";
        $censored_end = "";
        $censored_word_id = "";
        $censored_custom_word = "";

        if ($this->_config->get('censored_start'))
        {
                $censored_start = $this->_config->get('censored_start');
        }
        if ($this->_config->get('censored_end'))
        {
            $censored_end = $this->_config->get('censored_end');
        }
        if ($this->_config->get('censored_word_id'))
        {
                $censored_word_id = $this->_config->get('censored_word_id');
        }
        if ($this->_config->get('censored_custom_word'))
        {
                $censored_custom_word = $this->_config->get('censored_custom_word');
        }

            $regexp = $this->_build_regexp();
        $censored = "<span class=\"net_nemein_bannedwords_censored\">{$censored_start}";
        if (!empty($censored_custom_word))
        {
                $censored .= $censored_custom_word;
        }
        else
        {
            $censored .= $this->_l10n->get($censored_word_id);
        }
        $censored .= "{$censored_end}</span>";

            $processed_content = eregi_replace($regexp, $censored, $content);

        return $processed_content;
    }
    else
    {
            return $content;
    }
    }

    function search_and_replace_plain($content)
    {
        if (!empty($this->_banned_words))
    {
            $regexp = $this->_build_regexp();
        $censored = "***{$this->_l10n->get('censored')}***";
            $processed_content = eregi_replace($regexp, $censored, $content);
        }
    else
    {
            return $processed_content;
    }
    }

    function search_banned_words($content)
    {
        $content = strip_tags($content);

    // double spaces break the indes number
    $order = array("\r\n", "\r", "\n");
    $content = str_replace($order, " ", $content);

        if (!empty($this->_banned_words))
    {
        $matches = array();
            $regexp = $this->_build_regexp();
            $words = explode(" ", $content);
            $cleaned_words = array();

            /**
         * str_replacing \r and \n will create multiple spaces so exploding the content with " " delimiter
         * will create empty values to $words array. These are removed here so that they don't affect
         * array keys
         */
        foreach($words as $word)
        {
                if (!empty($word))
        {
                    $cleaned_words[] = $word;
        }
        }

        foreach($cleaned_words as $key => $word)
        {
            if (eregi($regexp, $word))
        {
                     $matches[$key] = $word;
        }
        }

        return $matches;
    }

    return false;
    }

    function is_banned($word)
    {
        foreach($this->_banned_words as $banned)
    {
            if (eregi($banned, $word))
        {
                return true;
        }
    }

        return false;
    }

    function is_valid_username($username, $custom_regexp = "")
    {
        $regexp = "";

        if (!empty($custom_regexp))
    {
            $regexp = $custom_regexp;
    }
    else
    {
        // The default is alpha numeric
        $regexp = '/^[a-z0-9]*/i';
        }

    if (!preg_match($regexp, $username))
    {
            return false;
    }

        if($this->is_banned($username))
    {
            return false;
        }

        return true;
    }
}

?>