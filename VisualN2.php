<?php
/**
 * Network Visualization extenstion
 * 
 * @file
 * @ingrop Extensions
 *
 * @author Abhishek Kumar <abhishek.iitd16@gmail.com>
 */


$vnScriptPath = $wgScriptPath . '/extensions/VisualN';
$vnStyle = 'large';
$vnAutomaticNamespaces = array();

$dir = dirname(__FILE__);


$wgHooks['ParserFirstCallInit'][] = 'visualnParserFunctions';
$wgHooks['BeforePageDisplay'][] = 'VisualN::addHTMLHeader';
//$wgHooks['ParserAfterTidy'][] = 'VisualN::replaceFirstLevelHeaders';

$wgAutoloadClasses['VisualN'] = "$dir/VisualN_body.php";

$wgResourceModules['ext.visualn'] = array(
	// JavaScript and CSS styles. To combine multiple files, just list them as an array.
	'scripts' => array(
		'skins/ext.visualn.d3.v3.min.js',
		'skins/ext.visualn.core.js',
		),
	// 'styles' => // the style is added in HeaderTabs::addHTMLHeader

	// If your scripts need code from other modules, list their identifiers as dependencies
	// and ResourceLoader will make sure they're loaded before you.
	// You don't need to manually list 'mediawiki' or 'jquery', which are always loaded.
	// 'dependencies' => array( 'jquery.ui.tabs' ),

	// ResourceLoader needs to know where your files are; specify your
	// subdir relative to "/extensions" (or $wgExtensionAssetsPath)
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'VisualN',
);

# Parser function to insert a link changing a tab.
function visualnParserFunctions( $parser ) {
	$parser->setHook( 'visualn', array( 'VisualN', 'tag' ) );
	// $parser->setHook( 'infobox', array('VisualN','info'));
	// $parser->setFunctionHook( 'switchtablink', array( 'HeaderTabs', 'renderSwitchTabLink' ) );
	// $parser->setFunctionHook( 'infobox', array( 'VisualN', 'renderSwitchTabLink' ) );
	return true;
}

