<?php
error_reporting(E_ALL);
class midcom_support_wsnormalizer
{
    var $tab_size = 4;
    var $_tab_strings = array();
    var $operations = array
    (
        'newlines',
        'clearnulls',
        'clear_start_bom',
        'tabs2spaces',
        'open_pre_strip',
        'close_post_strip',
        'line_end_ws',
    );

    /**
     * Calls all the normalization operations defined for given $data
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function normalize($data)
    {
        foreach($this->operations as $op)
        {
            if (!method_exists($this, $op))
            {
                continue;
            }
            $data = $this->$op($data);
        }
        return $data;
    }

    /**
     * Replaces Unicode Byte Order Marks in beginning of the string with empty string
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function clear_start_bom($data)
    {
        return preg_replace('%^((\xEF\xBB\xBF)|(\xFF\xFE)|(\xFE\xFF)|(\xFF\xFE)|(\xFF\xFE\x0\x0)|(\x0\x0\xFE\xFF))+%', '', $data);
    }

    /**
     * Replaces nulls with empty string
     *
     * NOTE: If this messes up your code, think about using the null keyword
     * or "\x00" in stead of the raw binary-null -character.
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function clearnulls($data)
    {
        return preg_replace('%(\x0)%', '', $data);
    }

    /**
     * Normalizes various newline schemes to unix newlines
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function newlines($data)
    {
        return preg_replace("/\n\r|\r\n|\r/", "\n", $data);
    }

    /**
     * Normalizes tabs to given number of spaces
     *
     * @see $this->tab_size
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function tabs2spaces($data)
    {
        if (!isset($this->_tab_strings[$this->tab_size]))
        {
            $this->_tab_strings[$this->tab_size] = str_pad('', $this->tab_size, ' ');
        }
        return str_replace("\t", $this->_tab_strings[$this->tab_size], $data);
    }

    /**
     * Removes whitespace between start of string and first PHP open
     * tag (if said tag is the first nonwhitespace in string)
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function open_pre_strip($data)
    {
        return preg_replace('%^\s+(<\?(php)?)%s', '\\1', $data);
    }

    /**
     * Removes whitespace between last PHP close tag and end of
     * string (if said tag is the last nonwhitespace in the string)
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function close_post_strip($data)
    {
        return preg_replace('%(\?>)\s+$%s', '\\1', $data);
    }

    /**
     * Removes whitespace from ends of lines (if lines are not all whitespace)
     *
     * @param string $data data to normalize (usually file contents)
     * @return string normalized
     */    
    function line_end_ws($data)
    {
        return $data;
        /* Not so easy afterall
        $data_arr = explode("\n", $data);
        //$data_arr = preg_replace('%[\t\f ]+$%m', '', $data_arr);
        $data_arr = preg_replace('%(^\s+$)|([^\t\f ])\s+%', '\\1', $data_arr);
        return implode("\n", $data_arr);
        */
    }

}
?>