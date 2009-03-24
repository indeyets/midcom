<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Variant handling helper for MidCOM 3
 *
 * @package midcom_core
 */
class midcom_core_helpers_variants
{
    public function __construct()
    {
    }

    public function handle($variant, $request_method)
    {
        switch ($request_method)
        {
            case 'GET':
                return $this->handle_get($variant);
                break;
            default:
                throw new midcom_exception_httperror("{$request_method} not allowed", 405);
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM variants::handled');
        }
    }

    private prepare_variant($variant)
    {
        if (!isset($this->datamanager))
        {
            // TODO: non-DM variants
            throw new midcom_exception_notfound("Datamanager not available");
        }
        
        $variant_field = $variant['variant'];
        if (!isset($this->datamanager->types->$variant_field))
        {
            throw new midcom_exception_notfound("{$variant_field} not available");
        }
    }

    private function handle_put($variant)
    {
        $this->prepare_variant($variant);
        
        // TODO: Format conversions
        
        $variant_field = $variant['variant'];
        
        // TODO: Pass via widget
        $this->datamanager->types->$variant_field->value = file_get_contents('php://input');
        
        if (!$this->datamanager->save())
        {
            throw new midcom_exception_httperror("Saving {$variant_field} failed");
        }
        
        // Return original content
        return $this->handle_get($variant);
    }

    private function handle_get($variant)
    {
        $this->prepare_variant($variant);

        $type_field = "as_{$variant['type']}";
        if (!isset($this->datamanager->types->$variant_field->$type_field))
        {
            throw new midcom_exception_notfound("Type {$type_field} of {$variant_field} not available");
        }

        // TODO: Mimetype, other headers
        switch ($variant['type'])
        {
            case 'html':
                $_MIDCOM->context->mimetype = 'text/html';
                break;
            case 'raw':
            case 'csv':
                $_MIDCOM->context->mimetype = 'text/plain';
                break;
        }
        header('Content-Type: ' . $_MIDCOM->context->mimetype);

        return $this->datamanager->types->$variant_field->$type_field;
    }
    
    public function __set($attribute, $value)
    {
        switch ($attribute)
        {
            case 'datamanager':
                $this->datamanager = $value;
                break;
            case 'object':
                $this->object = $value;
                break;
            default:
                throw new OutOfBoundsException("MidCOM variant handler is unable to utilize {$attribute}.");
        }
    }
}
?>