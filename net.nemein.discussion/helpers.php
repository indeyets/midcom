<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum indexing helper class.
 * 
 * This one builds a MidCOM document which stores an entire discussion thread in
 * a single index document.
 * 
 * Internally it is based on a datamanager document of the threads' root article,
 * but the replies are processed directly, appending the title and content fields
 * only.
 * 
 * @param midcom_helper_datamanager $thread_dm The threads' root article in form of a Datamanager instance.
 * @param midcom_services_indexer A reference to the indexer, for performance reasons. 
 * @return midcom_services_indexer_document_midcom The full document.
 */
function net_nemein_discussion_thread2document (&$thread_dm, &$indexer)
{
 	$document = $indexer->new_document($thread_dm);
    
    // Now iterate over all replies and append them to the content.
    // also, update the revised timestamps accordingly
    
	$replies = mgd_list_reply_articles($thread_dm->_storage->id);
    if ($replies)
    {
        while ($replies->fetch())
        {
            $article = new midcom_db_article($replies->id);
            if ($article)
            {
                $document->content .= "\n{$article->title} {$article->content}";
            }
        }
    }
    
    return $document;
}




?>
