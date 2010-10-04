<?php
/**
 * @package Realm Status Module for joomla 1.5
 * @author Brad Cimbura
 * @copyright (C) 2010- Brad Cimbura
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link http://soylentcode.com/
**/

//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

require_once( dirname(__FILE__).DS.'helper.php' );

$data = modRealmStatus::wow_ss($params->get('realm'),$params->get('display'),$params->get('region'),$params->get('updatetimer'),'modules/mod_realm_status/images/',$params->get('imagetype'));
require( JModuleHelper::getLayoutPath( 'mod_realm_status' ) );

?>