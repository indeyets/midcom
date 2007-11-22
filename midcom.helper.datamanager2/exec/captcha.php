<?php
/**
 * Captcha display handler. Takes the session data and renders the captcha PNG.
 *
 * No caching is done so far.
 *
 * If the session is not valid, a 500 will be triggered.
 *
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once 'Text/CAPTCHA.php';
global $argc, $argv;

$session = new midcom_service_session('midcom_helper_datamanager2_widget_captcha');

if (   $argc == 0
    || ! $session->exists($argv[0]))
{
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
        'Failed to generate Captcha, the session key passed is invalid.');
    // This will exit.
}

$passphrase = $session->get($argv[0]);

// Set Captcha options (font must exist!)
$width = 200;
$height = 80;
$options = Array
(
    'font_path' => MIDCOM_ROOT . '/midcom/helper/datamanager2/widget/',
    'font_file' => 'captcha-font.ttf',
    'background_color' => '#000000',
);

/* Generate a new Text_CAPTCHA object, Image driver
 * This is working at least with Text_CAPTCHAS latest alpha version. Older versions
 * give missing font error
 */

   $captcha = Text_CAPTCHA::factory('Image');
   $result = $captcha->init($width, $height, $passphrase, $options);
// $result = $captcha->init($height, $width, $passphrase, $options);

if (PEAR::isError($result))
{
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
        'Failed to generate Captcha, captcha class init call failed: ' . $result->getMessage());
    // This will exit.
}

// Get Captcha GD Handle
$image = $captcha->getCAPTCHA();
if (! is_resource($image))
{
    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to generate Captcha, captcha rendering failed. <br /><strong>Reason</strong>: '.$image->getMessage());
    // This will exit.
}

// Render the Captcha
header('Content-Type: image/png');
imagepng($image);
?>