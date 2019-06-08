<?php
	class DomDocumentParser {

		private $doc;

		public function __construct($url) {

			$options = array(
				'http'=>array('method'=>"GET", 'header'=>"User-Agent: viewBoot/0.1\n")
				);
			$context = stream_context_create($options);

			$this->doc = new DomDocument();
			@$this->doc->loadHTML(file_get_contents($url, false, $context));
		}

		private function getHashtags() {
			$finder = new DomXPath($this->doc);
			$classname="trend-card";
			$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
			return $nodes;
		}

		public function getKeywords(){

			$elementos = $this->getHashtags();

			$keywords = array();
			$tmp_dom = new DOMDocument(); 
			foreach ($elementos as $node) 
			{
			    $tmp_node = $tmp_dom->importNode($node,true);
			    foreach ($tmp_node->childNodes as $el){
			    	if ($el->nodeName == "ol"){
						foreach ($el->childNodes as $el2){
							foreach ($el2->childNodes as $el3){
								if ($el3->nodeName == "a"){
									if (!in_array($el3->nodeValue,$keywords)){
										array_push($keywords,$el3->nodeValue);
									}
								}
							}
						}
					}
			    }
			}

			return($keywords);
		}

	}
?>