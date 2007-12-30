<?php
/**
 * Created on 2006-Oct-Thu
 * @package org.routamc.photostream
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */

/**
 *
 * @package org.routamc.photostream
 *
 */
class org_routamc_photostream_handler_index extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * The handler for displaying index to different photo streams
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        // Populate different photostreams here
        $data['photostreams'] = array();

        if ($_MIDCOM->auth->is_valid_user())
        {
            // We have a valid user, show "my" options
            $user = $_MIDCOM->auth->user->get_storage();
            $data['photostreams'][] = array
            (
                'url' => "list/{$user->username}/",
                'title' => $this->_l10n->get('my photos'),
            );

            $data['photostreams'][] = array
            (
                'url' => "tag/{$user->username}/",
                'title' => $this->_l10n->get('my tags'),
            );
        }

        // Show "all" options
        $data['photostreams'][] = array
        (
            'url' => 'list/all/',
            'title' => $this->_l10n->get('all photos'),
        );

        $data['photostreams'][] = array
        (
            'url' => 'tag/all/',
            'title' => $this->_l10n->get('all tags'),
        );

        $data['view_title'] = $this->_topic->extra;
        $_MIDCOM->set_pagetitle($data['view_title']);
        return true;
    }

    /**
     * Display a list of photos. This method is used by several of the request
     * switches.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('show_index_header');

        foreach ($data['photostreams'] as $photostream)
        {
            $data['photostream'] = $photostream;
            midcom_show_style('show_index_item');
        }

        midcom_show_style('show_index_footer');
    }
}
?>