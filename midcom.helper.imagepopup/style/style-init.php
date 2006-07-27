<?
echo '<?xml version="1.0" encoding="UTF-8"?>';

$request_data =& $_MIDCOM->get_custom_context_data('request_data');
/*
 * Created on Sep 17, 2005
 * @author tarjei huse
 * @package 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
?><html>
    <head>
      <title><?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title>
      <?php echo    $_MIDCOM->print_head_elements(); ?>
    </head>
    <body <?php $_MIDCOM->print_jsonload(); ?>>

