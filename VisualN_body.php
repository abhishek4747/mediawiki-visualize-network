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
	public $title;
	public $priority;
	public $start_time;
	public $end_time;
	
	function __construct($title=NULL,$priority=NULL,$time=NULL,$type_of_edge=NULL,$arguments=NULL)
	{
		$this->title = $title;
		$this->priority = $priority;
		if ($time!=NULL) {
			$t = explode("-", $time);
			$this->start_time=$t[0];
			if (count($t)>1) {
				$this->end_time=$t[1];
			}else{
				$this->end_time="present";
			}
		}
		$this->type_of_edge = $type_of_edge;
		$this->arguments = $arguments;
	}
}


/**
* 
*/
class VisualNode 
{
	public $title; // string: name of node
	public $edge; // VisualEdge: edge connecting this node from parent
	public $neighbors; // List of VisualNodes
	public $parent_title;
	public $depth;

	function getParent()
	{

	}

	public function addNeighbour($name, $edge = NULL)
	{
		if (!array_key_exists($name, $this->neighbors)) {
			$this->neighbors[$name] = $edge;
		}
	}

	public function getNeighbors(){
		return $this->neighbors;
	}
	
	function __construct($title, $depth = 0)
	{
		$this->title = $title;
		if ($edge == NULL){
			$this->edge = new VisualEdge();
		}else{
			$this->edge = $edge;
		}
		$this->depth = $depth;
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

	public static function requestWikiPages($titles=NULL)
	{
		if ($titles!=NULL and strlen(trim($titles))!=0){
			global $wgRequest;
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
		$ret["node"] = "Unnamed node";
		$ret["edge"] = "Unnamed edge";
		$ret["priority"] = 0;
		$ret["time"] = "no time";
		$ret["type_of_edge"] = "default";
		$ret["edge_attributes"] = "no edge attributes";
		if ($count_strs>0){
			$node = explode("=", $strs[0]);
			if (count($node)>1){
				$ret["edge"] = NULL;
				if (strlen(trim($node[0]))!=0) {
					$ret["edge"] = trim($node[0]);
				}
				$ret["node"] = trim($node[1]);
			}else{
				$ret["node"] = trim($node[0]);
			}
		}
		if ($count_strs>1){
			$ret["priority"] = intval($strs[1]);
		}
		if ($count_strs>2){
			$ret["time"] = trim($strs[2]);
		}
		if ($count_strs>3){
			$ret["type_of_edge"] = trim($strs[3]);
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
		$ret = '';
		if (array_key_exists("url", $args)) {
			$ret = '
<script>
graph = "";
$( window ).load(function(){d3.json("'.$args["url"].'",function(error,graph){data = graph;});});			
</script>
';
		}else{

		$adjList = array(); // Over all graph
		$parent_children = array(); // Current level of graph: mapping from parent to children
		$edgeList = array();
		
		
		$max_depth = 5;
		if (array_key_exists("depth", $args)){
			$max_depth = $args["depth"];
		}
		$depth = 0;

		
		// Intialize with current pagetitle => text of visualN
		$blocks = array($wgRequest->getVal('title') => $input); 
		while ( $depth < $max_depth and count($blocks)>0) {
			MWDebug::log("Depth : {$depth}  Length of graph : ".serialize($adjList));    
			MWDebug::log('Blocks: '.serialize($blocks));
			foreach ($blocks as $parent_key => $text) {
				if (!array_key_exists($parent_key, $adjList)){
					$adjList[$parent_key] = new VisualNode($parent_key, $depth);
				}
				$children = explode("\n", $text);
				for($x = 0; $x < count($children); $x++) {
					if (strlen(trim($children[$x])) != 0){
						MWDebug::log("parent:".$parent_key." children:".$children[$x]);
						// $finalChildren[] = 'Level '.$depth.': '.'<a href="/mediawiki/index.php/'.$children[$x].'" title="'.$children[$x].'">'.$children[$x]."</a>";
						$node_params = VisualN::parseNode($children[$x]);
						// MWDebug::log("children:".serialize($node_params));
						
						$node_name = $node_params["node"];
						if (!array_key_exists($node_name, $adjList)){
							$adjList[$node_name] = new VisualNode($node_name, $depth+1);
							$parent_children[] = $node_name;
						}
						$edge = new VisualEdge($node_params["edge"],$node_params["priority"],$node_params["time"],$node_params["type_of_edge"],$node_params["edge_attributes"]);
						$adjList[$parent_key]->addNeighbour($node_name,$edge); 
					}
				}
			}
			// $all_children = array_values($parent_children);
			ksort($parent_children);
			$titles = implode("|",$parent_children);
			$result = VisualN::requestWikiPages($titles);
			
			$visualNtext = "";
			$blocks = array();
			$parent_children = array();
			// var_dump($result['query']['pages']);
			foreach($result['query']['pages'] as $pageid=>$page) {
			
			    $wikitext = $result['query']['pages'][$pageid]["revisions"][0]["*"];
			    $visualTagText = VisualN::extractHTML($wikitext,'<visualn','</visualn>');
			    $result_title = str_replace(" ", "_", $result['query']['pages'][$pageid]["title"]);
			    $tagDom = str_get_html($visualTagText, true, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT, DEFAULT_SPAN_TEXT);
			    if ($tagDom!=NULL){
			    	$visualTagText = $tagDom->find('visualn')[0]->innertext;
			    	MWDebug::log($result_title." : ".serialize(explode("\n", $visualTagText)));

				    if (strlen(trim($visualTagText)) != 0){
						// MWDebug::log('New VisualN:'.$visualTagText);    
				    	// $visualNtext .= "\n".$visualTagText;
				    	$blocks[$result_title] = $visualTagText;	
				    }
			    }else{
			    	// visualn tag not found in the page
			    }
			}
			$depth++;
		}
		
		$graph = VisualN::graphExplode($adjList);
		$nodes_array = implode("," , $graph["nodes"]);
		$edges_array = implode("," , $graph["edges"]);
		$ret = '
<script >
var data = {
	"nodes":['.$nodes_array.'],
	"links":['.$edges_array.']
};
</script>
';
		}
		return $ret.'<div id="visualn">&nbsp;<div id="VN_slider"></div><div id="visualnsvg"></div></div>';
	}

	public static function graphExplode($adjacencyList){
		$graph = array();	
		$graph["nodes"] = array();
		$graph["edges"] = array();
		$keys = array_keys($adjacencyList); // One optimization store the back mapping also. no need to search
		foreach ($keys as $index => $key) {
			$d = $adjacencyList[$key]->depth;
			$d1 = $d+1;
			$graph["nodes"][] = "{\"name\":\"{$key}\",\"group\":1,\"depth\":{$d}}";
			foreach ($adjacencyList[$key]->getNeighbors() as $neighbour=>$edge) {
				$index2 = array_search($neighbour, $keys);
				if ($edge->priority==NULL) {
					$edge->priority = 0;
				}
				$graph["edges"][] = "{\"source\":{$index}, \"target\":{$index2}, \"value\":1 ,\"depth\":{$d1},\"type\":\"{$edge->type_of_edge}\",\"priority\":{$edge->priority},\"start_time\":\"{$edge->start_time}\", \"end_time\":\"{$edge->end_time}\",\"arguments\":\"{$edge->arguments}\" }";
			}
		}
		return $graph;
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
		$out->addScriptFile( $vnScriptPath . '/d3.slider/d3.slider.js' );
		$out->addExtensionStyle( $vnScriptPath . '/d3.slider/d3.slider.css');
		// return true;
	}
}
