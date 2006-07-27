<?php
/**
 * OpenPSA Interface helper.
 * 
 * @package net.nemein.hourview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

// We must tune down error reporting since the OpenPSA is not E_ALL compatible
error_reporting(E_ALL ^ E_NOTICE);

// Load the libraries
mgd_include_snippet_php("/NemeinProjects/Config/Init");

?>
