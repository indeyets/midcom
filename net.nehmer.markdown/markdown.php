<?php

/**
 * @package net.nehmer.markdown
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Markdown interface class
 *
 * @package net.nehmer.markdown
 */
class net_nehmer_markdown_markdown extends midcom_baseclasses_components_purecode
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_markdown_markdown()
    {
        $this->_component = 'net.nehmer.markdown';

        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Renders the text according to the current configuration of the Markdown
     * Library.
     *
     * Can be called multiple times with the same configuration.
     *
     * @param string $text The unprocessed, markdown'ed text.
     * @return string The processed HTML.
     */
    function render($text)
    {
        return Markdown($text);
    }

}
?>