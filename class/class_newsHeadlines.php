<?php

	class newsHeadlines{

		public $title = array();
		public $author = array();
		public $description = array();
		public $url = array();
		public $urlToImage = array();
		public $publishedAt = array();
		public $content = array();
		public $results;
		public $limit;

		function getHeadlines($apikey, $q = '', $category = '', $sources = '&sources=google-news-br', $country = 'br', $pagesize = 100, $page = 1, $end = -1, $contador = 0){
			
			if (($contador + $pagesize) <= 100){
				$ch = curl_init();  

				$curl_url = "https://newsapi.org/v2/top-headlines?apiKey=".$apikey."&country=".$country."&pageSize=".$pagesize."&page=".$page."&sources=".$sources;

				if ($q != '') $curl_url .= "&q=".urlencode($q);
				if ($category != '') $curl_url .= "&category=".$category; 

				curl_setopt($ch, CURLOPT_URL, $curl_url); 
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
						if ($page < $end) $this->getHeadlines($apikey, $q, $category, $sources, $country, $pagesize, $page+1, $end, $contador);

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
