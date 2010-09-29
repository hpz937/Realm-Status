<?php
//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

echo '<div><img src="modules/mod_realm_status/wow_ss.php?realm=' . $params->get('realm') . '&region=' . $params->get('region') . '&display=' . $params->get('display') . '&update_timer=' . $params->get('updatetimer') . '&image_type=' . $params->get('imagetype') . '" alt="' . $params->get('realm') . ' Server Status" /></div>';
?>