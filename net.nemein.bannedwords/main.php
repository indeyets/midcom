<?php
/**
 * Class for handling banned words 
 *
 * @package net.nemein.bannedwords
 */
class net_nemein_bannedwords_handler extends midcom_baseclasses_components_purecode
{
    var $_banned_words = null;

    var $_sitegroup = null;

    function net_nemein_bannedwords_handler()
    {
        $this->_sitegroup = $_MIDGARD['sitegroup'];
        $this->_component = 'net.nemein.bannedwords';
	parent::midcom_baseclasses_components_purecode();
	$this->_populate_word_list();
    }

    function _populate_word_list()
    {
        $qb = net_nemein_bannedwords_word_dba::new_query_builder();
	$qb->add_constraint('sitegroup', '=', $this->_sitegroup);
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
            $regexp = $this->_build_regexp();
	    $censored = "<span class=\"net_nemein_bannedwords_censored\">***{$this->_l10n->get('censored')}***</span>";
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
