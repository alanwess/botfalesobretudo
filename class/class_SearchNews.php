<?php
	
	class SearchNews{

		public $title = array();
		public $author = array();
		public $description = array();
		public $url = array();
		public $urlToImage = array();
		public $publishedAt = array();
		public $content = array();
		public $results;
		public $limit;

		function getSearchNews($apikey, $query, $language = 'pt', $pagesize = 100, $sortBy = 'relevance', $page = 1, $end = -1, $contador = 0){
			
			if (($contador + $pagesize) <= 100){
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, "https://newsapi.org/v2/everything?q=".urlencode($query)."&apiKey=".$apikey."&language=".$language."&pageSize=".$pagesize."&sortBy=".$sortBy."&page=".$page); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$output = curl_exec($ch); 
				curl_close($ch);  

				$noticias_json = json_decode($output);
				if ($noticias_json->status == "error") {
				   return(false);
				} else {
					if ($noticias_json->totalResults > 0){
						$this->results = (int) $noticias_json->totalResults;
						$this->limit = ceil($this->results / $pagesize);
						$noticias = $noticias_json->articles;  

						foreach($noticias as $noticia){
							$this->title[] = $noticia->title;
							$this->author[] = $noticia->author;
							$this->description[] = $noticia->description;
							$this->url[] = $noticia->url;
							$this->urlToImage[] = $noticia->urlToImage;
							$this->publishedAt[] = $noticia->publishedAt;
							$this->content[] = $noticia->content;
							$contador++;
						} 

						$end = $this->limit;
						if ($page < $end) $this->getSearchNews($apikey, $query, $language, $pagesize, $sortBy, $page+1, $end, $contador);

						return(true);
					} else {
						return(false);
					}
				}
			}
		}

		function generateJSON(){
			return(json_encode(array($this->title, $this->author, $this->description, $this->url, $this->urlToImage, $this->publishedAt, $this->content),JSON_UNESCAPED_UNICODE));
		}
	}

?>
