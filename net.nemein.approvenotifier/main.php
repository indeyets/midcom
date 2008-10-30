<?php
/**
 * @package net.nemein.approvenotifier 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * This class scans for articles that will be expiring and sends notifications
 * to their authors.
 *
 * @package net.nemein.approvenotifier
 */
class net_nemein_approvenotifier extends midcom_baseclasses_components_purecode
{    
    /**
     * Timestamp for the "advance notification"
     */
    var $advance_time = null;
    
    /**
     * Timestamp for "expiring today" notification
     */
    var $today_time = null;
    
    var $debug_mode = false;

    /**
     * Constructor. Figure out the Midgard API in use
     */
    function __construct($debug_mode = false)
    {
        $this->_component = 'net.nemein.approvenotifier';
        parent::__construct();
        
        $this->debug_mode = $debug_mode;
        
        // Generate the timestamps we use for notification checking
        $this->advance_time = mktime(24, 0, 0, date('m', time()), date('d', time()) + $this->_config->get('expiry_advance_days'), date('Y', time()));        
        $this->today_time = mktime(24, 0, 0, date('m', time()), date('d', time()), date('Y', time()));
    }

    function check_topic_articles($topic_id)
    {
        if ($this->debug_mode)
        {
            echo "Checking topic {$topic_id}\n";
        }
        
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic_id);
        
        $qb->add_constraint('metadata.scheduleend', '<=', gmdate('Y-m-d H:i:s', $this->advance_time));
        $qb->add_constraint('metadata.scheduleend', '>=', gmdate('Y-m-d H:i:s', time()));
        $qb->add_constraint('metadata.scheduleend', '<>', '0000-00-00 00:00:00');
        
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            $metadata = midcom_helper_metadata::retrieve($article);
            $expiry = $metadata->get('schedule_end');
            
            if ($expiry == 0)
            {
                // No expiry set, skip
                continue;
            }
            
            if ($expiry < time())
            {
                // Expired already, skip
                continue;
            }
            
            if ($expiry <= $this->today_time)
            {
                $this->_send_notification('expires_today', $article, $metadata);
            }
            elseif ($expiry <= $this->advance_time)
            {
                $this->_send_notification('expire_advance_notification', $article, $metadata);
            }
        }
        
        if ($this->debug_mode)
        {
            echo "\n";
        }
        
        // Recurse the checks to subtopics
        $topic_qb = midcom_db_topic::new_query_builder();
        $topic_qb->add_constraint('up', '=', $topic_id);
        $topics = $topic_qb->execute();
        foreach ($topics as $topic)
        {
            $this->check_topic_articles($topic->id);
        }
    }
    
    /**
     * Constructs the notification message and sends it via org.openpsa.notifications
     *
     * @see org.openpsa.notifications
     */
    function _send_notification($type, $object, $metadata)
    {
        $expiry = $metadata->get('schedule_end');
        $previous_notice_sent = $object->parameter($this->_component, $type);
        if ($previous_notice_sent)
        {
            // We've sent a previous notice. Check if it was before last edit to object
            if (strtotime($previous_notice_sent) > $object->metadata->revised)
            {
                // No changes since previous notice
                return false;
            }
        }
        
        $message = array();
        $message['title'] = sprintf($this->_l10n->get('article %s will expire on %s'), $object->title, strftime('%x %X', $expiry));
        $message['content']  = "{$message['title']}\n\n";
        
        if ($type == 'expire_advance_notification')
        {
            $message['content'] .= sprintf($this->_l10n->get('this is the %s day advance notification'), $this->_config->get('expiry_advance_days')) . "\n\n";
        }
        
        $message['content'] .= $this->_l10n->get('link to page') . ":\n";
        $message['content'] .= $_MIDCOM->permalinks->create_permalink($object->guid);
        
        // TODO: Make smarter choices here
        $author = new midcom_db_person($object->author);
        
        if ($this->debug_mode)
        {
            echo "We would send the following to {$author->name} ({$author->email})\n";
            print_r($message);
            return true;
        }
        
        org_openpsa_notifications::notify("{$this->_component}:{$type}", $author->guid, $message);
        
        $object->parameter($this->_component, $type, gmdate('Y-m-d H:i:s'));
        
        return true;
    }
}
?>