<?php		
	
	include("class/class_DomDocumentParser.php");
	include("class/class_SearchNews.php");

	$url = "https://trends24.in/brazil/";
	$apikey = 'c58e9513e20f491999713c324f32fdd4'; //'8fa76b2a89bb426780fdcfc1e129dac4';

	function setSearchNews($apikey, $query){

		$searchNews = new searchNews();
		$result_ok = $searchNews->getSearchNews($apikey, $query);
		if ($result_ok) {
			printf("Total count: ".$searchNews->results." para ".$query."\n");

			try {
			    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
			    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
			    $sql = "CREATE TABLE IF NOT EXISTS consultasnews (
			     	Id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
			     	keyword TEXT, 
			    	results LONGTEXT, 
			    	query_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci" ;
			    $pdo->exec($sql);
			    $sql = "INSERT INTO consultasnews (keyword, results) VALUES (:keyword, :results)" ;
			    $stmt = $pdo->prepare($sql);    
			    $consulta = $searchNews->generateJSON(); 
			    $stmt->bindParam(':keyword', $query, PDO::PARAM_STR);                          
				$stmt->bindParam(':results', $consulta, PDO::PARAM_STR);                                  
				$stmt->execute(); 
			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		} else {
			printf("Nada encontrado para a query '".$query."'\n");
		}

		return(true);
	}

	// WebCrawler nas tendencias
	$parser = new DomDocumentParser($url);
	$keywords = $parser->getKeywords();

	printf("Iniciando a sessao de busca...\n");
	for ($i = 0; $i < sizeof($keywords); $i++){
		if (strpos($keywords[$i], '#') === false){
			setSearchNews($apikey, $keywords[$i]);
		}
	}

?>