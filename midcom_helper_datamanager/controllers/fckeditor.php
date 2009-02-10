<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * FCKeditor connector controller
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_controllers_fckeditor
{
    

    public function action_connector($route_id, &$data, $args)
    {
        // Only users should have business utilizing these APIs
        $_MIDCOM->authorization->require_user();
        
        if (!isset($_MIDCOM->dispatcher->get['Command']))
        {
            throw new midcom_exception_notfound('no command defined');
        }
        $data['command'] = $_MIDCOM->dispatcher->get['Command'];

        if (!isset($_MIDCOM->dispatcher->get['Type']))
        {
            throw new midcom_exception_notfound('no type defined');
        }
        $data['type'] = $_MIDCOM->dispatcher->get['Type'];
        
        switch ($data['command'])
        {
            case 'GetFolders':
            case 'GetFoldersAndFiles':
                $this->action_get_folders_and_files($route_id, $data, $args);
                break;
            default:
                throw new midcom_exception_notfound('unknown command');                
        }
    }

    public function action_get_folders_and_files($route_id, &$data, $args)
    {
        if (!isset($_MIDCOM->dispatcher->get['CurrentFolder']))
        {
            throw new midcom_exception_notfound('no folder defined');
        }
        $data['current_folder'] = $_MIDCOM->dispatcher->get['CurrentFolder'];
    
        // Get root page
        $page = new midgard_page($_MIDCOM->context->host->root);
        if (!$page->guid)
        {
            throw new midcom_exception_notfound('failed to load root folder');
        }
        
        // Load by path starting from root page
        $path_parts = explode('/', $data['current_folder']);
        foreach ($path_parts as $part)
        {
            if (empty($part))
            {
                continue;
            }
            
            $qb = new midgard_query_builder('midgard_page');
            $qb->add_constraint('up', '=', $page->id);
            $qb->add_constraint('name', '=', $part);
            $pages = $qb->execute();
            if (!$pages)
            {
                throw new midcom_exception_notfound('failed to load folder');
            }
            $page = $pages[0];
        }

        // Load subfolders
        $qb = new midgard_query_builder('midgard_page');
        $qb->add_constraint('up', '=', $page->id);
        $qb->add_constraint('name', '<>', '');
        $data['folders'] = $qb->execute();
                
        if ($data['command'] != 'GetFoldersAndFiles')
        {
            // That's all, folks
            return;
        }
        
        // Add page itself to documents list
        $data['files'] = array();
        $data['files'][] = array
        (
            'name' => '',
            'size' => round($page->metadata->size / 1024, 1),
        );
        
        $attachments = $page->list_attachments();
        foreach ($attachments as $attachment)
        {
            $data['files'][] = array
            (
                'name' => $attachment->name,
                'size' => round($attachment->metadata->size / 1024, 1),
            );
        }

        // TODO: Query NAP for more
    }
}
?>