<?php		
	
	include("class/class_newsHeadlines.php");

	$apikey = 'c58e9513e20f491999713c324f32fdd4'; //'8fa76b2a89bb426780fdcfc1e129dac4';

	function setHeadlines($apikey){
		
		$categories = array('business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology');
		foreach ($categories as $category){
			$newsHeadlines = new newsHeadlines();

			$result_ok = false;
			
			if ($category != NULL) $result_ok = $newsHeadlines->getHeadlines($apikey, '', $category);
			else $result_ok = $newsHeadlines->getHeadlines($apikey);

			if ($result_ok) {
				printf("Total count: ".$newsHeadlines->results." em ".$category."\n");

				try {
				    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
				    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
				    $sql = "CREATE TABLE IF NOT EXISTS headlinesnews (
				     	Id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
				     	category TEXT, 
				    	results LONGTEXT, 
				    	query_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci" ;
				    $pdo->exec($sql);
				    $sql = "INSERT INTO headlinesnews (category, results) VALUES (:category, :results)" ;
				    $stmt = $pdo->prepare($sql);    
				    $consulta = $newsHeadlines->generateJSON(); 
				    $stmt->bindParam(':category', $category, PDO::PARAM_STR);                          
					$stmt->bindParam(':results', $consulta, PDO::PARAM_STR);                                  
					$stmt->execute(); 
				} catch(PDOException $e) {
				    printf($e->getMessage()."\n");
				}
			} else {
				printf("Nada encontrado para a categoria ".$category." nas headlines\n");
			}
		}
	}

	printf("Iniciando a sessão de headlines...\n");
	setHeadlines($apikey);

?>