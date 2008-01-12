<?php
/**
 * @package org.openpsa.httplib
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * HTTP content fetching library
 *
 * @package org.openpsa.httplib
 */
class org_openpsa_httplib extends midcom_baseclasses_components_purecode
{
    var $_client = null;
    var $error = '';
    var $basicauth = array
    (
        'user' => false,
        'password' => false,
    );
    var $files = array();

    /**
     * Initializes the class
     */
    function org_openpsa_httplib()
    {
         $this->_component = 'org.openpsa.httplib';
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Check whether a HTTP response code is a "successfull" one
     *
     * @param int $response_code HTTP response code to check
     * @return boolean Whether HTTP response code is successfull
     * @access private
     */
    function _is_success($response_code)
    {
        if (   $response_code >= 200
            && $response_code < 300)
        {
            return true;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Got HTTP response code {$response_code}, reporting failure", MIDCOM_LOG_DEBUG);
        debug_pop();
        return false;
    }

    /**
     * Get contents of given URL
     *
     * @param string $url Fully qualified URL
     * @param array $headers Additional HTTP headers
     * @return string Contents
     * @todo rewrite to work like the post method (and use http_client)
     */
    function get($url, $headers = null, $username = null, $password = null)
    {
        // Snoopy is an HTTP client in PHP
        $this->_client = new Snoopy();
        $client =& $this->_client;
        $client->agent = $this->_user_agent();
        $client->read_timeout = $this->_config->get('http_timeout');
        $client->use_gzip = $this->_config->get('enable_gzip');
        if (is_array($headers))
        {
            $client->rawheaders = $headers;
        }

        if (!is_null($username))
        {
            $client->user = $username;
        }

        if (!is_null($password))
        {
            $client->pass = $password;
        }

        // Do the HTTP query
        @$client->fetch($url);

        if (!$this->_is_success($client->status))
        {
            // Handle errors
            $this->error = $client->results;
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to fetch URL {$url}, got response: {$client->status}", MIDCOM_LOG_WARN);
            debug_add($client->error, MIDCOM_LOG_DEBUG);
            debug_pop();
            return '';
        }

        return $client->results;
    }

    /**
     * Post variables and get the resulting page
     *
     * @param string $url Fully qualified URL
     * @param array &$variables The data to POST (key => value pairs)
     * @param array $headers Additional HTTP headers
     * @return string Contents
     */
    function post($uri, &$variables, $headers = null)
    {
        require_once('HTTP/Request.php');
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_client =& new HTTP_Request($uri);
        $c =& $this->_client;
        $c->setMethod(HTTP_REQUEST_METHOD_POST);
        $c->addHeader('User-Agent', $this->_user_agent());

        // Handle basic auth
        if (   isset($this->basicauth['user'])
            && $this->basicauth['user'] !== false
            && isset($this->basicauth['password'])
            && $this->basicauth['password'] !== false)
        {
            // Set basic auth
            $c->setBasicAuth($this->basicauth['user'], $this->basicauth['password']);
        }

        // Handle the variables to POST
        if (   !is_array($variables)
            || empty($variables))
        {
            $this->error = '$variables is not array or is empty';
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($variables as $name => $value)
        {
            $c->addPostData($name, $value);
        }
        // add custom headers
        if (!empty($headers))
        {
            foreach ($headers as $key => $value)
            {
                $c->addHeader($key, $value);
            }
        }

        $response = $c->sendRequest();
        if (PEAR::isError($response))
        {
            $this->error = $response->getMessage();
            debug_add("Got error '{$this->error}' from HTTP_Request", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        $code = $c->getResponseCode();
        $headers = $c->getResponseHeader();
        debug_add("Response code is {$code}, headers\n===\n" .  sprint_r($headers) . "===\n", MIDCOM_LOG_DEBUG);
        if (!$this->_is_success((int)$code))
        {
            $this->error = $this->_http_code2error($code);
            debug_add("Got error '{$this->error}' from '{$uri}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        debug_pop();
        return $c->getResponseBody();
    }

    function _http_code2error($code)
    {
        switch((int)$code)
        {
            case 200:
                return false;
            case 404:
                return 'Page not found';
            case 401:
                return 'Unauthorized';
            case 403:
                return 'Forbidden';
            // TODO: rest of them
            default:
                return 'Unknown error: ' . $code;
                break;
        }
    }

    function _user_agent()
    {
        return 'Midgard/' . mgd_version();
    }
}
?>