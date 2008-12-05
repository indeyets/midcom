<?php
/**
 * @package net_nemein_notifications
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Class for sending notices by email.
 *
 * @package net_nemein_notifications
 */
class net_nemein_notifications_api_mail
{
    private $data = array();
    
    public function __construct()
    {
    }
    
    /**
     * Generates and sends the mail
     */
    public function send()
    {
        //var_dump($this->data);
    }
    
    /**
     * Get value of a particular parameter
     *
     * @param string $key Key to get data of
     * @return array Context data
     */
    private function get_item($key)
    {        
        if (! isset($this->data[$key]))
        {
            throw new OutOfBoundsException("key '{$key}' not found.");
        }
        
        return $this->data[$key];
    }
    
    /**
     * Set value of a particular parameter
     *
     * @param string $key Key to set data of
     * @param mixed $value Value to set to the context data array
     */
    private function set_item($key, $value)
    {
        if ($key == 'from')
        {
            if (empty($value))
            {
                $value = '"MidCOM Notifier" <noreply@midgard-project.org>';
            }
        }
        
        $this->data[$key] = $value;
    }

    /**
     * Get value of given parameter
     *
     * @param string $key Key to get data of
     * @return mixed Value
     **/
    public function __get($key)
    {
        return $this->get_item($key);
    }

    /**
     * Set value of a particular context data array item
     *
     * @param string $key Key to set data to
     * @param mixed $value Value to set
     */
    public function __set($key, $value)
    {
        $this->set_item($key, $value);
    }

    /**
     * Check if data array item exists
     *
     * @param string $key Key to check for
     * @return bool
     **/
    public function __isset($key)
    {
        if (isset($this->data[$key]))
        {
            return true;
        }

        return false;
    }
}

?>