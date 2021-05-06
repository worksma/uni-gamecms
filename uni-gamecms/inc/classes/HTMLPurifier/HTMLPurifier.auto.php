<?php

/**
 * This is a stub include that automatically configures the include path.
 */

set_include_path( dirname(__FILE__) . PATH_SEPARATOR . get_include_path() );

require_once 'HTMLPurifier/Bootstrap.php';
require_once 'HTMLPurifier.autoload.php';

$Purifier_Config = HTMLPurifier_Config::createDefault();
$Purifier_Config->set('HTML.Trusted', true);
$Purifier_Config->set('Filter.YouTube', true);

$def = $Purifier_Config->getHTMLDefinition(true);
$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
$def->addAttribute('iframe', 'allowfullscreen', 'Enum#allowfullscreen');

$audio = $def->addElement(
	'audio',
	'Block',
	'Flow',
	'Common',
	array( 'src*' => 'URI', 'controls' => 'CDATA' )
);
$audio->excludes = array('audio' => true);

$video = $def->addElement(
	'video',
	'Block',
	'Flow',
	'Common',
	array( 'src*' => 'URI', 'controls' => 'CDATA' )
);
$video->excludes = array('video' => true);

$Purifier = new HTMLPurifier($Purifier_Config);

// vim: et sw=4 sts=4
