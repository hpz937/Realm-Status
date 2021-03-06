<?php
/**
 * @package Realm Status Module for joomla 1.5
 * @author Brad Cimbura
 * @copyright (C) 2010- Brad Cimbura
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link http://soylentcode.com/
**/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class modRealmStatus
{
/* The Following Code is Copyright (c) 2010, Nick Schaffner
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
 are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this
      list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, 
      this list of conditions and the following disclaimer in the documentation 
      and/or other materials provided with the distribution.
    * Neither the name of the nor the names of its contributors may be used to 
      endorse or promote products derived from this software without specific prior 
      written permission. 

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS 
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 */

##
## WoW Server Status
## Version 4.1	
## Copyright 2008 Nick Schaffner
## http://53x11.com
##

protected function wow_ss_global() {

/*
##		Here you can preset all of the default variables for the script.
##		These variables can also be set when calling the script.
*/

	$wowss['realm']				= "Whisperwind";	// Your full Realm (server) name
	$wowss['display']			= 'full';				// (full | half | text | none) displays full or half image, text or set to none to return an array
	$wowss['region']			= 'us';					// (us | eu) set your server location
	$wowss['update_timer']		= 10;					// Minutes between status update refresh
	//$wowss['data_path']			= './wowss';				// Path to your 'wowss' folder (you may need to prepend this with your root path, 'root/public_html' etc)
	$wowss['data_path']			= 'modules/mod_realm_status/wowss';				// Path to your 'wowss' folder (you may need to prepend this with your root path, 'root/public_html' etc)
	$wowss['image_type']		= 'png';				// (png | gif) image type output
	
/*
##		These are the default messages outputed by the text version of the script.
*/

	$wowss['up'] 				= 'Realm Up';			// Set your "Realm Up" message
	$wowss['down'] 				= 'Realm Down';			// Set your "Realm Down" message
	$wowss['max']				= 'Max (Queued)';		// Set Maxed (Queued)
	$wowss['high']				= 'High';				// Set High Population
	$wowss['medium']			= 'Medium';				// Set Medium Population
	$wowss['low']				= 'Low';				// Set Low Population
	$wowss['pve']				= 'PvE';				// Name for PvE servers
	$wowss['pvp']				= 'PvP';				// Name for PvP servers
	$wowss['rp']				= 'RP';					// Name for RP servers
	$wowss['rppvp']				= 'RPPvP';				// Name for RpPVP servers
	$wowss['offline']			= 'Offline';			// Set Offline Message (for when status is unavailable)
	$wowss['error']				= 'Sever Error';		// Set Error Message

/*
##		Uncomment these variables at YOUR OWN RISK.  These overide default script settings		
*/
	
	## $wowss['show_language'] 	= 'yes';				// (yes | no) Force script to display language type	(EU realms display language by default)
	## $wowss['xml_url'] 		= 'http://www.worldofwarcraft.com/realmstatus/status.xml';	// URL to XML status page
	## $wowss['server_font'] 	= 'silkscreen.ttf'; 	// Font for Server names
	## $wowss['type_font'] 		= 'silkscreenb.ttf'; 	// Font for all other type

################################	
################################ PHP Magic Below, Avoid editing if you don't know what you are doing :)
################################

	$wowss['get_array'] = array('realm','update_timer','display','region','data_path','image_type');
	foreach ($wowss['get_array'] as $value) {
		if(isset($_GET[$value]))
			$wowss[$value] = trim(stripslashes($_GET[$value]));
	}
	$wowss['realm'] = str_replace('�','e',$wowss['realm']);
	
	$wowss['us_xml'] = 'http://www.worldofwarcraft.com/realmstatus/status.xml';
	$wowss['eu_xml'] = 'http://www.wow-europe.com/realmstatus/index.xml';

	$wowss['us_codes'] = array(
		'type' => array(1 => 'pve', 2 => 'pvp', 3 => 'rp', 4 => 'rppvp'),
		'status' => array(1 => 'up', 2 => 'down'),
		'population' => array(0 => 'offline', 1 => 'low', 2 => 'medium', 3 => 'high', 4 => 'max')	
	);
	
	$wowss['eu_codes'] = array(
		'type' => array('pve' => 'pve', 'pvp' => 'pvp', 'rp-pve' => 'rp', 'rp-pvp' => 'rppvp'),
		'status' => array('realm down' => 'down', 'realm up' => 'up'),
		'population' => array('recommended' => 'low', 'medium' => 'medium', 'full' => 'high')
	);
	
	if(substr($wowss['data_path'],-1) != '/')
		$wowss['data_path'] .= '/';
	
	return $wowss;

}

public function wow_ss($realm = 0,$display = 0, $region = 0, $update_timer = 0,$data_path = 0, $image_type = 0) {
	$wowss =  modRealmStatus::wow_ss_global();
	$realm_status = array();
	if($realm)
		$realm_status['realm'] = $realm;
	else
		$realm_status['realm'] = $wowss['realm'];
	if($region)
		$realm_status['region'] = $region;
	else
		$realm_status['region'] = $wowss['region'];
		
	## Overide default values from script call
	foreach ($wowss['get_array'] as $value) {
		if($$value)
			$wowss[$value] = $$value;
	}
		
	$realm_status['script_errors'] = array();
	if(strtolower($wowss['region']) == 'us')
		$realm_status['language'] = 'us';
	
	## Verify data path
	/* if(is_dir($wowss['data_path'])) {

		if(!isset($wowss['xml_url']))
			$wowss['xml_url'] = $wowss[strtolower($wowss['region']).'_xml'];
		$xml_file = 'wowss-'. modRealmStatus::wow_ss_sfn($wowss['region']) .'-'. substr(md5(''),0,16) .'.xml';
		
		## Check if we need to update XML cache
		clearstatcache();
		if(file_exists($wowss['data_path'].$xml_file)) {
			if(time()-($wowss['update_timer']*60) > filemtime($wowss['data_path'].$xml_file))
				$update = true;
		}
		else
			$update = true;
		
		## Fetch XML
		if(isset($update) && $update) {
			$data = @file_get_contents($wowss['xml_url']);
			if(strlen($data) > 300) {
				## Don't write data unless it is there
				$handle = fopen($wowss['data_path'].$xml_file,"w");
				fwrite($handle,$data);
				fclose($handle);
			} else
				$realm_status['script_errors'][] = 'Unable to access remote XML file.';
		}
		$xml = @strtolower(@file_get_contents($wowss['data_path'].$xml_file));
		
		## Parse XML
		if($xml) {
			$xml = str_replace('�','e',$xml);

			## Parse US XML
			if(strtolower($wowss['region']) == 'us') {
				preg_match('/<r n="'. strtolower(str_replace("'",'&#039;',$wowss['realm'])) .'" t="([0-9])" s="([0-9])" l="([0-9])"\/>/',$xml,$status_array);
				## [1] = type, [2] = status, [3] = population
				if(count($status_array) == 4) {
					$realm_status['type'] = $wowss['us_codes']['type'][$status_array[1]];
					$realm_status['status'] = $wowss['us_codes']['status'][$status_array[2]];
					$realm_status['population'] = $wowss['us_codes']['population'][$status_array[3]];
				}
			}
			## Parse EU XML
			if(strtolower($wowss['region']) == 'eu') {
				preg_match('/<title>\s*'. strtolower($wowss['realm']). '\s*<\/title>.*<\/item>/msU',$xml,$match);
				preg_match('/domain="population">(.*)</',$match[0],$status_array);
				if(!$status_array[1])
					$realm_status['population'] = 'medium';
				else
					$realm_status['population'] = $wowss['eu_codes']['population'][$status_array[1]];
				
				preg_match('/domain="status">(.*)<.*domain="language">(.*)<.*domain="type">(.*)<.*domain="queue">(.*)</msU',$match[0],$status_array);
				$realm_status['status'] = $wowss['eu_codes']['status'][$status_array[1]];
				$realm_status['language'] = $status_array[2];
				$realm_status['type'] = $wowss['eu_codes']['type'][$status_array[3]];
				if($status_array[4] != 'false')
					$realm_status['population'] = 'max';
			}
			if(!$realm_status['status'])
				$realm_status['status'] = 'error';
			if(!$realm_status['population'])
				$realm_status['population'] = 'error';
			if(!$realm_status['type'])
				$realm_status['type'] = 'error';
			if($realm_status['status'] == 'down')
				$realm_status['population'] = 'offline';
		} else
			$realm_status['script_errors'][] = 'Unable to access XML file.';	
	} else  
		$realm_status['script_errors'][] = 'Data Path Error.';	*/
		
		##New Status Code
	if(is_dir($wowss['data_path'])) {
		if(!isset($wowss['xml_url']))
			$wowss['xml_url'] = $wowss[strtolower($wowss['region']).'_xml'];
		$xml_file = 'wowss-'. modRealmStatus::wow_ss_sfn($wowss['region']) .'-'. substr(md5(''),0,16) .'.xml';
		
		## Check if we need to update XML cache
		clearstatcache();
		if(file_exists($wowss['data_path'].$xml_file)) {
			if(time()-($wowss['update_timer']*60) > filemtime($wowss['data_path'].$xml_file))
				$update = true;
		}
		else
			$update = true;
			
		if(isset($update) && $update) {
			$realmname=$realm_status['realm'];
			if($realm_status['region']=='eu')
			  $region="http://eu.battle.net/wow/en/status";
			else
			  $region="http://us.battle.net/wow/en/status";
			$fp = fopen($region,"r");

			$inrealm=0;
			$output="";
			while(!feof($fp))
			{
				$line=fgets($fp);
				if(preg_match("/$realmname/i",$line))
					$inrealm=1;
				if($inrealm && preg_match("/<\/tr>/i",$line))
					$inrealm=0;
				if($inrealm)
				{
					$output .= trim($line);
				}
			}
			fclose($fp);
			$output=preg_replace("/<span class=\"filter-text\">[^<]*<\/span>/","",$output);
			$output=preg_replace("/<span[^>]*>/i","",$output);
			$output=preg_replace("/<\/span>/i","",$output);
			$output=preg_replace("/<\/td><td[^>]*>/i","|",$output);
			$output=preg_replace("/<\/td>/i","",$output);
			$rs=explode("|",$output);
			if($rs[2]!="N/A")
				$realm_status['status']='up';
			else
				$realm_status['status']='down';
			if($rs[1]=='(PvE)')
				$realm_status['type']='pve';
			elseif($rs[1]=="(PvP)")
				$realm_status['type']='pvp';
			elseif($rs[1]=="(RP)")
				$realm_status['type']='rp';
			else
				$realm_status['type']='error';
			if($rs[2]=='Low')
				$realm_status['population']='low';
			elseif($rs[2]=='Medium')
				$realm_status['population']='medium';
			elseif($rs[2]=='High')
				$realm_status['population']='high';
			elseif($rs[2]=='N/A')
				$realm_status['population']='offline';
			else
				$realm_status['population']='error';
		}
	} else  
		$realm_status['script_errors'][] = 'Data Path Error.';	
		
		
			
	if($wowss['display'] == 'full') {
		
		unset($update);
		clearstatcache();
		if(file_exists($wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type'])) {
			if(time()-($wowss['update_timer']*60) > filemtime($wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type']))
				$update = true;
		}
		else
			$update = true;
		
		if(isset($update) && $update) {
			## Write image
			if(isset($wowss['show_language']) && $wowss['show_language'] == 'yes')
				$wowss[$realm_status['type']] .= ' '. trim(strtoupper($realm_status['language']));
			if(strtolower($wowss['region']) == 'eu' and !$realm_status['show_language'])
				$wowss[$realm_status['type']] .= ' '. trim(strtoupper($realm_status['language']));
			modRealmStatus::wow_ss_image($realm_status,$wowss);
		}
	
		/* if(!headers_sent()) {
			## Write headers and image if script is called from img tag
			if($wowss['image_type'] == 'png') {
				header("Content-type: image/png");
				$image = imagecreatefrompng($wowss['data_path'].strtolower(wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type']);
				imagepng($image);
			}
			elseif($wowss['image_type'] == 'gif') {
				header("Content-type: image/gif");
				$image = imagecreatefromgif($wowss['data_path'].strtolower(wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type']);
				imagegif($image);
			}
		} else */
			//echo '<img alt="WoW Server Status for '. $realm_status['realm'] .'" src="'. $wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type'] .'" />';
			return array('realm'=>$realm_status['realm'],'imageurl'=>$wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type']);
	}
	if($wowss['display'] == 'half') {
		
		if(count($realm_status['script_errors']) or $realm_status['status'] == 'error')
			$realm_status['status'] = 'unknown';
		/* if(!headers_sent()) {
			## Write headers and image if script is called from img tag
			if($wowss['image_type'] == 'png') {
				header("Content-type: image/png");
				$image = imagecreatefrompng($wowss['data_path'].$realm_status['status'].'.'.$wowss['image_type']);
				imagepng($image);
			}
			elseif($wowss['image_type'] == 'gif') {
				header("Content-type: image/gif");
				$image = imagecreatefromgif($wowss['data_path'].$realm_status['status'].'.'.$wowss['image_type']);
				imagegif($image);
			}
		} else */
			//echo '<img alt="WoW Server Status for '. $realm_status['realm'] .'" src="'. $wowss['data_path'].$realm_status['status'].'.'.$wowss['image_type'] .'" />';
			return array('realm'=>$realm_status['realm'],'imageurl'=>$wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.'.$wowss['image_type']);
			
	}
	if($wowss['display'] == 'text') {
		
		if(isset($wowss['show_language']) && $wowss['show_language'] == 'yes')
			$wowss[$realm_status['type']] .= ' ('. trim(strtoupper($realm_status['language'])) .')';
		if(strtolower($wowss['region']) == 'eu' and !$realm_status['show_language'])
			$wowss[$realm_status['type']] .= ' ('. trim(strtoupper($realm_status['language'])) .')';
		if(count($realm_status['script_errors'])) {
			echo '<u>'. $realm_status['realm'] .'</u>';
			foreach ($realm_status['script_errors'] as $value)
				echo ' '.$value;
		} else {
			if($realm_status['status'] != 'error')
				echo '<u>'. $realm_status['realm'] .'</u> '. $wowss[$realm_status['type']] .': <b>'. $wowss[$realm_status['status']] .' | '. $wowss[$realm_status['population']] .'</b>';
			else
				echo '<u>'. $realm_status['realm'] .'</u> Error contacting server.';
		}
		
	}
	if($wowss['display'] == 'none') {
		return $realm_status;
	}	
}

function wow_ss_image ($realm_status,$wowss) {
	
	## Error control
	if($realm_status['status'] == 'down')
		$realm_status['population'] = 'offline';
	if($realm_status['status'] == 'error' or count($realm_status['script_errors'])) {
		$realm_status['status'] = 'unknown';
		$realm_status['population'] = 'error';
	}
	if($realm_status['type'] == 'error')
		unset($realm_status['type']);
	
	## Set Default Fonts
	if(!isset($wowss['server_font']))
		$wowss['server_font'] = 'silkscreen.ttf';
	if(!isset($wowss['type_font']))
		$wowss['type_font'] = 'silkscreenb.ttf';
	$server_font = $wowss['data_path'].$wowss['server_font'];
	$type_font = $wowss['data_path'].$wowss['type_font'];
	
	## Get and combine base images, set colors
	if($wowss['image_type'] == 'png')
		$back = imagecreatefrompng($wowss['data_path'].$realm_status['status'].'.png');
	if($wowss['image_type'] == 'gif')
		$back = imagecreatefromgif($wowss['data_path'].$realm_status['status'].'.gif');
		
	$backwidth = imagesx($back);		
	if($wowss['image_type'] == 'png')	
		$bottom = imagecreatefrompng($wowss['data_path'].strtolower($realm_status['status']).'2.png');
	if($wowss['image_type'] == 'gif')
		$bottom = imagecreatefromgif($wowss['data_path'].strtolower($realm_status['status']).'2.gif');
	if($wowss['image_type'] == 'png')
		$realm_status['population'] = imagecreatefrompng($wowss['data_path'].strtolower($realm_status['population']).'.png');
	if($wowss['image_type'] == 'gif')
		$realm_status['population'] = imagecreatefromgif($wowss['data_path'].strtolower($realm_status['population']).'.gif');
	$full = imagecreate($backwidth,(imagesy($back)+imagesy($bottom)));
	$bg = imagecolorallocate($full, 0, 255, 255);
	$red = imagecolorallocate($full,204,0,0); // HIGH Red color
	imagecolortransparent($full,$bg); 
	imagecopy($full,$back,0,0,0,0,$backwidth,imagesy($back));
	imagecopy($full,$bottom,0,imagesy($back),0,0,imagesx($bottom),imagesy($bottom));
	$back = $full;
		
	$textcolor = imagecolorallocate($back, 51, 51, 51);
	$shadow = imagecolorclosest($back, 255, 204, 0);
		
	imagecopy($back,$realm_status['population'],round(($backwidth-imagesx($realm_status['population']))/2),62,0,0,imagesx($realm_status['population']),imagesy($realm_status['population']));

	## Ouput centered $server name
	$vadj=0;
	$maxw = 62;
	$box = imagettfbbox(6,0,$server_font,$realm_status['realm']);
	$w = abs($box[0]) + abs($box[2]);
		
	if ($w > $maxw) {
		
		$i = $w;
		$t = strlen($realm_status['realm']);
	
		while ($i > $maxw) {
			$t--;
			$box = imagettfbbox (6, 0,$server_font,substr($realm_status['realm'],0,$t));
		  	$i = abs($box[0]) + abs($box[2]);	
		}
			
		$t = strrpos(substr($realm_status['realm'], 0, $t), " ");
		
		$output[0] = substr($realm_status['realm'], 0, $t);
		$output[1] = ltrim(substr($realm_status['realm'], $t));
		$vadj = -6;
		
	} else
		$output[0] = $realm_status['realm'];
		
	$i = 0;
	foreach($output as $value) {
		$box = imagettfbbox(6,0,$server_font,$value);
		$w = abs($box[0]) + abs($box[2]);
			
		imagettftext($back, 6, 0, round(($backwidth-$w)/2)+1, 58+($i*8)+$vadj, $shadow, $server_font, $value);
		imagettftext($back, 6, 0, round(($backwidth-$w)/2), 57+($i*8)+$vadj, -$textcolor, $server_font, $value);
		$i++;		
	}	
	
	## Ouput centered $realm_status['type']	
	if ($realm_status['type'] and !isset($err)) {
		$realm_status['type'] = $wowss[$realm_status['type']];
		$box = imagettfbbox(6,0,$type_font,$realm_status['type']);
		$w = abs($box[0]) + abs($box[2]);
		imagettftext($back, 6, 0, round(($backwidth-$w)/2)+1, 85, $shadow, $type_font, $realm_status['type']);
		imagettftext($back, 6, 0, round(($backwidth-$w)/2), 84, -$textcolor, $type_font, $realm_status['type']);	
	}	
	
	if($wowss['image_type'] == 'png')
		imagepng($back,$wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.png');
	if($wowss['image_type'] == 'gif')
		imagegif($back,$wowss['data_path'].strtolower(modRealmStatus::wow_ss_sfn($realm_status['realm'].' '.$wowss['region'])).'.gif');
	imagedestroy($back);
}

protected function wow_ss_sfn($text) {
	## Returns safe text for inserting into file name
	return str_replace(' ','_',preg_replace('/[^a-zA-Z0-9- ]/','',$text));
}
}

?>