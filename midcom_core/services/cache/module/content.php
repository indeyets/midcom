<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Content caching module
 *
 * Provides a way to cache a page produced by MidCOM.
 *
 *
 * @package midcom_core
 */
class midcom_core_services_cache_module_content
{
    private $configuration = array();
    private $cache_directory = '';

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function check($identifier)
    {
        if (!$_MIDCOM->cache->exists('content_metadata', $identifier))
        {
            // Nothing in meta cache about the identifier
            return false;
        }
        
        // Check the data for validity
        $meta = $_MIDCOM->cache->get('content_metadata', $identifier);
        
        if (   isset($data['expires'])
            && $data['expires'] < time())
        {
            // The contents in cache have expired
            return false;
        }
        
        // TODO: Check "not modified" and etag sent by browser
        
        // Check that we have the content
        if (!$_MIDCOM->cache->exists('content', $identifier))
        {
            // Nothing in meta cache about the identifier
            return false;
        }
        
        // TODO: Send the headers the original page sent
        
        // Serve the contents and exit
        echo $_MIDCOM->cache->get('content', $identifier);
        exit();
    }
    
    public function put($identifier, $content)
    {
        if (!isset($_MIDCOM->context->etag))
        {
            // Generate eTag from content
            $_MIDCOM->context->etag = md5($content);
        }

        // Store metadata
        $this->put_metadata($identifier);

        // Store the contents
        $_MIDCOM->cache->put('content', $identifier, $content);
    }
    
    private function put_metadata($identifier)
    {
        $metadata = array();
        
        // Store the expiry time
        $metadata['expires'] = time() + $_MIDCOM->context->cache_expiry;
        
        $metadata['etag'] = $_MIDCOM->context->etag;
        
        $_MIDCOM->cache->put('content_metadata', $identifier, $metadata);
    }

    /**
     * Associate tags with content
     */
    public function register($identifier, array $tags)
    {
        // Associate the tags with the template ID
        foreach ($tags as $tag)
        {
            $identifiers = $_MIDCOM->cache->get('content_tags', $tag);
            if (!is_array($identifiers))
            {
                $identifiers = array();
            }
            $identifiers[] = $identifier;

            $_MIDCOM->cache->put('content_tags', $tag, $identifiers);
        }
    }

    /**
     * Invalidate all cached template files associated with given tags
     */
    public function invalidate(array $tags)
    {
        $invalidate = array();
        foreach ($tags as $tag)
        {
            $identifiers = $_MIDCOM->cache->get('content_tags', $tag);
            if ($identifiers)
            {
                foreach ($identifiers as $identifier)
                {
                    if (!in_array($identifier, $invalidate))
                    {
                        $invalidate[] = $identifier;
                    }
                }
            }
        }

        foreach ($invalidate as $identifier)
        {
            $_MIDCOM->cache->delete('content', $identifier);
            $_MIDCOM->cache->delete('content_metadata', $identifier);
            $_MIDCOM->cache->delete('content_tags', $identifier);
        }
    }

    /**
     * Remove all cached template files
     */
    public function invalidate_all()
    {
        // Delete all entries of both content, meta and tag cache
        $_MIDCOM->cache->delete_all('content');
        $_MIDCOM->cache->delete_all('content_metadata');
        $_MIDCOM->cache->delete_all('content_tags');
    }
}
?>