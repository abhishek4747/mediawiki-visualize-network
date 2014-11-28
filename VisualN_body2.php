<?php
/**
 * Network Visualization extenstion
 * 
 * @file
 * @ingrop Extensions
 *
 * @author Abhishek Kumar <abhishek.iitd16@gmail.com>
 */

require 'simple_html_dom.php';

/**
* 
*/
class VisualEdge
{
	var $title;
	var $priority;
	var $start_time;
	var $end_time;
	
	function __construct($title)
	{
		$this->title = $title;
		
	}
}


/**
* 
*/
class VisualNode 
{
	var $title; // string: name of node
	var $edge; // VisualEdge: edge connecting this node from parent
	var $neighbours; // List of VisualNodes
	var $parent_title;

	function getParent()
	{

	}
	
	function __construct($title, $edge)
	{
		$this->title = $title;
		if ($edge == NULL){
			$this->edge = new VisualEdge();
		}else{
			$this->edge = $edge;
		}
	}
}


/**
* 
*/
class VisualN{
	public static function extractHTML($string, $start, $end) {
	    $string = " ".$string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return "";
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return $start.substr($string, $ini, $len).$end;
	}

	public static function generateGraph($text, $args, $parser){
		global $wgRequest;


	}

	public static function callAPI($titles=NULL)
	{
		if ($titles!=NULL){
			$api = new ApiMain(
				new DerivativeRequest(
					$wgRequest,
					array(
						'action' => 'query',
						'format' => 'json',
						'prop' => 'revisions',
						'rvprop' => 'content',
						'titles' => $titles),
					false // was posted?
				),
				false // enable write?
			);
			$api->execute();
			return $api->getResult()->getData();
		}
		return NULL;		
	}

	public static function parseNode($str)
	{
		$ret = array();
		$str = trim($str);
		$strs = explode(";", $str);
		$count_strs = count($strs);
		if ($count_strs>0){
			$node = explode("=", $strs[0]);
			if (count($node)>1){
				$ret["edge"] = NULL
				if (strlen(trim($node[0]))!=0) {
					$ret["edge"] = trim($node[0]);
				}
				$ret["node"] = trim($node[1]);
			}
		}
		if ($count_strs>1){
			$ret["priority"] = intval($strs[1]);
		}
		if ($count_strs>2){
			$ret["time"] = trim(strs[2]);
		}
		if ($count_strs>3){
			$ret["type_of_edge"] = trim(strs[3])
		}
		if (count($strs)>4 and strlen(trim($strs[4]))!=0) {
			$edge_attributes = array();
			$atts = explode(",", $strs[4]);
			foreach ($atts as $at) {
				$att = explode("=", trim($at));
				if (count($att)==2) {
					$edge_attributes[trim($att[0])] = trim($att[1]);
				}
			}
			$ret["edge_attributes"] = $edge_attributes;	
		}
		// I know its more comparisons this way but I prefer neat code!! 
		// And that's for you 
		return $ret;
	}

	public static function tag( $input, $args, $parser ) {
		global $wgRequest;
		
		$visualNtext = $input;
		$finalChildren = array();
		$parent_children = array();
		
		$adjList = array();
		$parent_key = 
		
		$max_depth = 5;
		if (array_key_exists("depth", $args ){
			$max_depth = $args["depth"];
		}
		$depth = 0;

		
		// Intialize with current pagetitle => text of visualN
		$blocks = array($wgRequest->getVal('title') => $input); 
		while ( $depth < $max_depth) {
			// MWDebug::log("Tag Text {$depth}:".$visualNtext);    
			foreach ($blocks as $parent_key => $text) {
				if (!array_key_exists($parent_key, $adjList)){
					$adjList[$parent_key] = new VisualNode($parent_key);
				}
				$children = explode("\n",$text);
				for($x = 0; $x < count($children); $x++) {
					if (strlen(trim($children[$x])) != 0){
						// $finalChildren[] = 'Level '.$depth.': '.'<a href="/mediawiki/index.php/'.$children[$x].'" title="'.$children[$x].'">'.$children[$x]."</a>";
						if (!array_key_exists($children[$x], $adjList)){
							$node_params = VisualN::parseNode($children[$x]);
							$adjList[$children[$x]] = new VisualNode($node_params["node"]);
							$parent_children[$parent_key] = $children[$x];

						}
						$adjList[$parent_key]->addNeighbour($children[$x]); 
					}
				}
			}
			$all_children = array_values($parent_children);
			ksort($all_children);
			$titles = implode("|",$all_children);

			
			$result = VisuaN::callAPI($titles);
			
			$visualNtext = "";
			$moreChildren = array();
			// var_dump($result['query']['pages']);
			foreach($result['query']['pages'] as $pageid=>$page) {
			
			    $wikitext = $result['query']['pages'][$pageid]["revisions"][0]["*"];
			    $visualTagText = VisualN::extractHTML($wikitext,'<visualn','</visualn>');
			    $tagDom = str_get_html($visualTagText);
			    $visualTagText = $tagDom->innertext;

			    if (strlen(trim($visualTagText)) != 0){
					// MWDebug::log('New VisualN:'.$visualTagText);    
			    	$visualNtext .= "\n".$visualTagText;
			    	
			    }
			}
			$depth++;
		}
		return '<div id="visualn"></div><div id="visualn2">'.implode("<br><br>",$finalChildren).'</div>';
	}

	public static function info($info, $args, $parser){
		return '<script></script>';
	}

	public static function addHTMLHeader( &$out ) {
		global $vnScriptPath, $vnStyle;

		// Add the CSS file for the specified style.
		if ( !empty( $vnStyle ) && $vnStyle !== 'jquery' ) {
			$styleFile = $vnScriptPath . '/skins/ext.visualn.' . $vnStyle . '.css';
			$out->addExtensionStyle( $styleFile );
		}
		$out->addScriptFile( $vnScriptPath . '/skins/ext.visualn.d3.v3.min.js' );
		$out->addScriptFile( $vnScriptPath . '/skins/ext.visualn.core.js' );
		// return true;
	}
}
