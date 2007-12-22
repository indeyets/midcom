<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:application.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * URL name parsing interface class
 *
 * @package midcom
 */
interface midcom_core_service_urlparser
{
    /*
    protected $argc;
    protected $argv;
    */
    public function __construct();

    /**
     * Tokenize URL path to an argument vector array
     */
    public function tokenize($url);

    /**
     * Set the argument vector array to be parsed
     */
    public function parse($argv);

    /**
     * Return next object in URL path
     */
    public function get_object();

    /**
     * Return current object pointed to by the parse URL
     */
    public function get_current_object();

    /**
     * Return array of found URL-based variables (of format namespace-key-value)
     */
    public function get_variable($namespace);

    /**
     * Return full URL that was given to the parser
     */
    public function get_url();
}
?>