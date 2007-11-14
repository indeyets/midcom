<?php

/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:_i18n_l10n.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Global string table cache, it stores the string tables
 * loaded during runtime.
 * 
 * @global Array $GLOBALS["midcom_services_i18n__l10n_localedb"]
 */
$GLOBALS["midcom_services_i18n__l10n_localedb"] = Array();

/**
 * This is the L10n main interface class, used by the components. It
 * allows you to get entries from the l10n string tables in the current
 * language with an automatic conversion to the destination character 
 * set.
 * 
 * <b>Note:</b> With MidCOM 2.0.0 the backwards compatibility to NemeinLocalization
 * has been removed.
 * 
 * <b>L10n language database file format specification:</b>
 * 
 * Lines starting with --- are considered command lines and treated specially,
 * unless they occure within string data. All commands are separated with at 
 * least a single space from their content, unless they don't have an argument.
 * 
 * Empty lines are ignored, unless within string data.
 * 
 * All keys and values will be trim'ed when encountered, so leading and trailing
 * whitespace will be eliminated completely.
 * 
 * Windows-style line endings (\r\n) will be silently converted to the UNIX
 * \n style.
 *
 * Commented example:
 * 
 * <pre>
 * ---# Lines starting with a # command are ignored.
 * 
 * ---# The CVS lines are ignored too, but they mark a line as CVS variable,
 * ---# this is reserved for later usage.
 * ---CVS $Id:_i18n_l10n.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * 
 * ---# File format version
 * ---VERSION 2.1.0
 * 
 * ---# Language of the table
 * ---LANGUAGE en
 * 
 * ---STRING string identifier
 * TRANSLATED STRING taken literally until ---STRINGEND, which is the 
 * only reserved value at the beginning of the line, everything else is
 * fine. Linebreaks within the translation are preserved.
 * \r\n sequeces are translated into to \n
 * ---STRINGEND
 * </pre>
 *  
 * File naming scheme: {$component_directory}/locale/{$database_name}.{$lang}.txt
 * 
 * @package midcom.services
 */
class midcom_services__i18n_l10n {
    
    /**
     * The name of the locale library we use, this is usually
     * a component's name.
     * 
     * @var string
     * @access private
     */
    var $_library;
    
    /**
     * The full path basename to the active library files. The individual
     * files are ending with .$lang.txt.
     * 
     * @var string
     * @access private
     */
    var $_library_filename;
    
    /**
     * A copy of the language DB from i18n.
     * 
     * @var Array
     * @access private
     */
    var $_language_db;
    
    /**
     * Fallback language, in case the selected language is not available.
     * 
     * @var string
     * @access private
     */
    var $_fallback_language;
    
    /**
     * Current character set
     * 
     * @var string
     * @access private
     */
    var $_charset;
    
    /**
     * Current language.
     * 
     * @var string
     * @access private
     */
    var $_language;
    
    /**
     * The language database, loaded from /lib/midcom/services/_i18n_language-db.dat
     * 
     * @var Array
     * @access private
     */
    var $_localedb;
    
    /**
     * The string database, a reference into the global cache.
     * 
     * @var Array
     * @access private
     */
    var $_stringdb;
    
    /**
     * The current L10n DB file format number (corresponds to the MidCOM versions).
     * 
     * @var string
     * @access private
     */
    var $_version = '2.1.0';
    
    /**
     * The constructor loads the translation library indicated by the snippetdir
     * path $library and initializes the system completely. The output character 
     * set will be initizialized to the language's default.
     * 
     * @param string $library	Name of the locale library to use.
     * @param string $database	Name of the database in the library to load.
     */
    function midcom_services__i18n_l10n ($library = null, $database) {
        global $midcom_errstr;
        global $midcom;
        
        if (is_null($library))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Default constructor for midcom_services__i18n_l10n forbidden, library path must be present.", MIDCOM_LOG_ERROR);
            debug_pop();
            $midcom->generate_error(MIDCOM_ERRCRIT, 
            	"Default constructor for midcom_services__i18n_l10n forbidden, library path must be present.");
            // This will exit();
        }
        
        if (substr($library, -1) != "/") 
        {
            $library = "/{$library}/locale/{$database}";
        } 
        else 
        {
            $library = "/{$library}locale/{$database}";
        }
        
        $this->_localedb =& $GLOBALS["midcom_services_i18n__l10n_localedb"];
        $this->_library_filename = MIDCOM_ROOT . $library;
        $this->_library = $library;
        
        $this->_language_db = $_MIDCOM->i18n->get_language_db();
        $this->_fallback_language = $_MIDCOM->i18n->get_fallback_language();
        
        if (! array_key_exists($this->_library, $this->_localedb)) 
        {
            $GLOBALS["midcom_services_i18n__l10n_localedb"][$this->_library] = Array();
        }
        
        $this->_stringdb =& $GLOBALS["midcom_services_i18n__l10n_localedb"][$this->_library];
        
        $this->set_language($_MIDCOM->i18n->get_current_language());
        $this->set_charset($_MIDCOM->i18n->get_current_charset());
    }
    
    /** 
     * This will flush the complete string table to the filesystem.
     * No locking code is in place, so check that there are no concurrent
     * accesses to the file have to be done on a social level.
     * 
     * It will write all loaded languages to disk, regardless of changes.
     */
    function flush() {
        foreach ($this->_stringdb as $lang => $table)
        {
            $file = fopen("{$this->_library_filename}.{$lang}.txt", 'w');
            if (! $file)
            {
                $_MIDCOM->uimessages->add("L10N Error", "Failed to open the file '{$this->_library_filename}.{$lang}.txt' for writing.", 'error');
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to open the file '{$this->_library_filename}.{$lang}.txt' for writing.",MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            
            fwrite($file, "---# MidCOM String Database\n");
            fwrite($file, "---VERSION 2.1.0\n");
            fwrite($file, "---CVS \$Id\$\n");
            fwrite($file, "---LANGUAGE {$lang}\n\n");
            
            foreach  ($table as $key => $translation)
            {
                $key = trim($key);
                $translation = str_replace("\r\n", "\n", trim($translation));
                fwrite($file, "---STRING {$key}\n");
                fwrite($file, "{$translation}\n");
                fwrite($file, "---STRINGEND\n\n");
            }
            
            fclose($file);
        }
    }
    
    /**
     * Load a language database
     * 
     * - Leading and trailing whitespace will be eliminated
     */
    function _load_language ($lang)
    {
        $filename = "{$this->_library_filename}.{$lang}.txt";
        
        if (! file_exists($filename))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("{$filename} does not exist, creating an empty language array therefore.");
            $this->_stringdb[$lang] = Array();
            debug_pop();
            return;
        }
        
        $data = file($filename); 
        
        // Parse the Array
        $stringtable = Array();
        $version = '';
        $language = '';
        $instring = false;
        $string_data = '';
        $string_key = '';
        
        foreach ($data as $line => $string)
        {
            // Kill any excess whitespace first.
            $string = trim($string);
            
            if (! $instring)
            {
                // outside of a string value
                
                if ($string == '')
                {
                    // Do nothing
                }
                else if (substr($string, 0, 3) == '---')
                {
                    // this is a command
                    if (strlen($string) < 4)
                    {
                        $line++; // Array is 0-indexed
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            "L10n DB SYNTAX ERROR: An incorrect command was detected at {$filename}:{$line}");
                        // This will exit
                    }
                    
                    $pos = strpos($string, ' ');
                    if ($pos === false)
                    {
                        $command = substr($string, 3);
                    }
                    {
                        $command = substr($string, 3, $pos - 3);
                    }
                    
                    switch ($command)
                    {
                        case '#':
                        case 'CVS':
                            // Skip
                            break;
                        
                        case 'VERSION':
                            if ($version != '')
                            {
                                $line++; // Array is 0-indexed
                                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                                    "L10n DB SYNTAX ERROR: A second VERSION tag has been detected at {$filename}:{$line}");
                                // This will exit
                            }
                            $version = substr($string, 11);
                            break;
                        
                        case 'LANGUAGE':
                            if ($language != '')
                            {
                                $line++; // Array is 0-indexed
                                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                                    "L10n DB SYNTAX ERROR: A second LANGUAGE tag has been detected at {$filename}:{$line}");
                                // This will exit
                            }
                            $language = substr($string, 12);
                            break;
                            
                        case 'STRING':
                            $string_data = '';
                            $string_key = substr($string, 10);
                            $instring = true;
                            break;
                            
                        default:
                            $line++; // Array is 0-indexed
                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                                "L10n DB SYNTAX ERROR: Unknown command '{$command}' at {$filename}:{$line}");
                            // This will exit
                    }
                }
                else
                {
                    $line++; // Array is 0-indexed
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        "L10n DB SYNTAX ERROR: Invalid line at {$filename}:{$line}");
                    // This will exit
                }
            }
            else
            {
                // Within a string value
                if ($string == '---STRINGEND')
                {
                    $instring = false;
                    $stringtable[$string_key] = $string_data;
                }
                else
                {
                    if ($string_data == '')
                    {
                        $string_data .= $string;
                    }
                    else
                    {
                        $string_data .= "\n{$string}";
                    }
                }
            }
        }
        
        if ($instring)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "L10n DB SYNTAX ERROR: String constant exceeds end of file.");
            // This will exit
        }
        if (version_compare($version, $this->_version, "<"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "L10n DB ERROR: File format version of $filename is too old, no update available at the moment.");
            // This will exit
        }
        if ($lang != $language)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "L10n DB ERROR: The DB language version {$language} did not match the requested {$lang}.");
            // This will exit
        }
        
        ksort($stringtable, SORT_STRING);
        $this->_stringdb[$lang] = $stringtable;
    }

    /**
     * Checks, wether the referenced language is already loaded. If not,
     * it is automatically made available. Any errors will trigger
     * generate_error.
     * 
     * @param string $lang The language to check for.
     * @see midcom_services__i18n_l10n::_load_language()
     * @access private
     */    
    function _check_for_language($lang)
    {
        if (! array_key_exists($lang, $this->_stringdb))
        {
            $this->_load_language($lang);
        }
    }
    
    /**
     * This tries to load the language files for all languages defined
     * in the i18n's language database.
     * 
     * @access private
     */
    function _load_all_languages()
    {
        foreach ($this->_language_db as $lang => $data)
        {
            $this->_check_for_language($lang);
        }
    }
    
    /**
     * Set output character set.
     * 
     * This is usually set through midcom_services_i18n.
     * 
     * @param string $charset	Charset name.
     * @see midcom_services_18n::set_charset()
     */
    function set_charset ($encoding) 
    {
        $this->_charset = strtolower($encoding);
    }
    
    /**
     * Set output language.
     * 
     * This will set the character encoding to the language's default
     * encoding and will also set the system locale to the one 
     * specified in the language database.
     * 
     * If you want another character encoding as the default one, you
     * have to override it manually using midcom_services__i18n_l10n::set_charset()
     * after calling this method.
     * 
     * This is usually set through midcom_services_i18n.
     * 
     * @param string $lang	Language code.
     * @see midcom_services_i18n::set_language()
     */
    function set_language($lang) 
    {
        if (!array_key_exists($lang, $this->_language_db))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Language {$lang} not found in the language database.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
    
        $this->_language = $lang;
        $this->_charset = $this->_language_db[$lang]["encoding"];
    }
    
    /**
     * Set the fallback language.
     * 
     * This is usually set through midcom_services_i18n.
     * 
     * @param string $lang	Language name.
     * @see midcom_services_i18n::set_fallback_language()
     */
    function set_fallback_language ($lang) {
        $this->_fallback_language = $lang;
    }
    
    /**
     * Checks if a localized string for $string exists. If $language is unset,
	 * the current language is used.
	 * 
	 * @param string $string The string-ID to search for.
	 * @param string $language The language to search in.
	 * @return bool Indicating availability.
     */
    function string_exists($string, $language = null) {
        if (is_null($language)) 
        {
            $language = $this->_language;
        }
        
        $this->_check_for_language($language);
        
        if (! array_key_exists($language, $this->_stringdb)) 
        {
            // debug_add("L10N: {$language} does not exist in {$this->_library}, not searching for {$string}.");
            return false;
        }
        
        if (! array_key_exists($string, $this->_stringdb[$language])) 
        {
            // debug_add("L10N: {$string} not found in {$this->_library} for language {$language}.");
            return false;
        }
        return true;
    }
    
    /**
     * Checks wether the given string is available in either the current
     * or the fallback language. Use this to determine if an actually processed
     * result is returned by get. This is helpful especially if you want to
     * "catch" cases where a string might translate to itself in some languages.
     * 
     * @param string $string The string-ID to search for
     * @return bool Indicating availability.
     */
    function string_available($string)
    {
        return
        (
               $this->string_exists($string, $this->_language)
            || $this->string_exists($string, $this->_fallback_language)
        );
    }
    
    /**
     * Retrieves a localized string from the database using $language as 
     * destination. If $language is unset, the currently set default language is 
     * used. If the string is not found in the selected language, the fallback
     * is checked. If even the fallback cannot be found, then $string is
     * returned and the event is logged to MidCOMs Debugging system.
     * 
     * L10n DB loads are done through string_exists.
     * 
	 * @param string $string The string-ID to search for.
	 * @param string $language The language to search in, uses the current language as default.
	 * @return string The translated string if available, the fallback string otherwise.
     */
    function get ($string, $language = null) {
        if (is_null($language)) 
        {
            $language = $this->_language;
        }
        
        if (! $this->string_exists($string, $language)) 
        {
            // Go for Fallback
            $language = $this->_fallback_language;
            
            if (! $this->string_exists($string, $language)) 
            {
                // Nothing found, log is produced by string_exists.
                return $string;
            }
        }
        
        return $_MIDCOM->i18n->convert_from_utf8($this->_stringdb[$language][$string]);
    }
    
    /**
     * This is a shortcut for "echo $this->get(...);", useful in style code.
     * 
     * Note, that due to the stupidity of the Zend engine, it is not possible to call
     * this function echo, like it should have been called.
     * 
	 * @param string $string The string-ID to search for.
	 * @param string $language The language to search in, uses the current language as default.
	 * @see get()
     */
    function show ($string, $language = null)
    {
        echo $this->get($string, $language);
    }
    
    /**
     * Creates a new entry in the localization library. For the string $string in
     * the language $language the translation $translation will be added. The 
     * function assumes that $translation is in UTF-8. It will create a language
     * subdir if neccessary. Returns true on success, false on failure.
     *
     * @param string $string		The string-ID to edit.
     * @param string $laguage		The language to edit.
     * @param string $translation	The UTF-8 encoded string to add to the translation table.
     * @deprecated This method is deprecated and will be dismissed in 2.2.0, the update method replaces this one completely.
     */
    function create ($string, $language, $translation) {
        trigger_error ('Use of deprecated function midcom_services__i18n_l10n::create($string, $language, $translation);', E_USER_NOTICE);
        $this->update($string, $language, $translation);
    }
    
    /**
     * Updates a string in the database. If it does not exist, it will be created
     * automatically. 
     * 
     * @param string $string		The string-ID to edit.
     * @param string $laguage		The language to edit.
     * @param string $translation	The UTF-8 encoded string to add/update.
     */
    function update ($string, $language, $translation) {
        $this->_check_for_language($language);
        $this->_stringdb[$language][$string] = $translation;
    }

    /**
     * Deletes a string from the database. If the string is not present, it
     * will fail silently.
     * 
     * @param string $string		The string-ID to edit.
     * @param string $laguage		The language to edit.
     */
    function delete ($string, $language) {
        // This is error-resilent, deleting a non-existant string will
        // just do nothing.
        if ($this->string_exists($string, $language)) 
        {
            unset ($this->_stringdb[$language][$string]);
        }
    }
    
    /**
     * Scans the current library and delivers all string ids that are in use.
     * 
     * @return Array A list of all string-IDs
     */
    function get_all_string_ids() {
        $this->_load_all_languages();
        
        $found_strings = Array();
        foreach ($this->_stringdb as $language => $stringtable) 
        {
            foreach ($stringtable as $string => $translation) 
            {
                if (! array_key_exists($string, $found_strings)) 
                {
                    $found_strings[] = $string;
                }
            }
        }
        sort($found_strings, SORT_STRING);
        return $found_strings;
    }
    
}

?>