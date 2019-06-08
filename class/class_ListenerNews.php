<?php
	class ListenerNews {

		private $db = "consultas_news";
		private $host = "localhost";
		private $user = "root";
		private $pass = "";
		
		function getHeadlines($date_start, $date_end){
			try {
				$pdo = new PDO("mysql:dbname=".$this->db.";host=".$this->host, $this->user, $this->pass);
			    $stmt = $pdo->query("SELECT results FROM headlinesnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $global = 1;
				while ($row = $stmt->fetch()) {
				    printf("Inicio da nova linha do banco\n");
				    $noticias = json_decode($row['results']);
				    for ($j = 0; $j < sizeof($noticias[0]); $j++){
				    	printf("Noticia de numero: ".$global++."\n");
				    	printf("Titulo: ".$noticias[0][$j]."\n");
						printf("Autor: ".$noticias[1][$j]."\n");
						printf("Descrição: ".$noticias[2][$j]."\n");
						printf("Url: ".$noticias[3][$j]."\n");
						printf("Url da Imagem: ".$noticias[4][$j]."\n");		
						printf("Publicado em: ".$noticias[5][$j]."\n");
						printf("Conteúdo: ".$noticias[6][$j]."\n");
						printf("\n\n");
					}
				}
			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}

		function getSearchNews($date_start, $date_end){
			try {
				$pdo = new PDO("mysql:dbname=".$this->db.";host=".$this->host, $this->user, $this->pass);
			    $stmt = $pdo->query("SELECT results FROM consultasnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $i = 0;
			    $global = 1;
				while ($row = $stmt->fetch()) {
				    printf("Inicio da nova linha do banco\n");
				    $noticias = json_decode($row['results']);
				    for ($j = 0; $j < sizeof($noticias[$i]); $j++){
				    	printf("Noticia de numero: ".$global++."\n");
				    	printf("Titulo: ".$noticias[0][$j]);
						printf("Autor: ".$noticias[1][$j]);
						printf("Descrição: ".$noticias[2][$j]);
						printf("Url: ".$noticias[3][$j]);
						printf("Url da Imagem: ".$noticias[4][$j]);
						printf("Publicado em: ".$noticias[5][$j]);
						printf("Conteúdo: ".$noticias[6][$j]);
						printf("\n\n");
					}
				}
			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}

	}
?>