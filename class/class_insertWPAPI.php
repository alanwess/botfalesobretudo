<?php

	class insertWPAPI{

		private $username = 'admin';
		private $password = 'admin';
		private $baseurl = "http://localhost/wp-generic";
		private $token_now = '';
		private $token_valid = 0;

		private function createSlug($str, $delimiter = '-'){
	    	$slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
	    	return $slug;
	  	} 

		private function generateToken($username, $password){
			$process = curl_init($this->baseurl.'/wp-json/simple-jwt-authentication/v1/token');
			$data = array(
					'username' => $username, 
					'password' => $password 
			);
			$data_string = json_encode($data);
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_POST, 1);
			curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($data_string)));
			$return = curl_exec($process);
			$err = curl_error($process);
			curl_close($process);

			if ($err) {
			  	printf("cURL Error #:" . $err ."\n");
			} else {
				$this->token_now = json_decode($return)->token;
				return($this->token_now);
			}

		}

		private function validateToken($token){
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->baseurl."/wp-json/simple-jwt-authentication/v1/token/validate",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_HTTPHEADER => array("Authorization: Bearer ".$token, "Cache-control: no-cache")
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  	printf("cURL Error #:" . $err ."\n");
			} else {
				if(json_decode($response)->data->status == "403") $this->validateToken($this->generateToken($this->username, $this->password));
				else $this->token_valid = 1;
			}

		}

		private function req_insertArticle($title, $autor, $excerpt, $url, $url_image, $data_publicacao, $content, $count) {

			$this->validateToken($this->token_now);

			if ($this->token_valid == 1){
				$process = curl_init($this->baseurl.'/wp-json/api/createarticle');
				$data = array(
					'title' => $title,
					'autor' => $autor,
					'excerpt' => $excerpt,
					'url' => $url,
					'url_image' => $url_image,
					'data_publicacao' => $data_publicacao,
					'content' => $content
				);
				$data_string = json_encode($data);
				curl_setopt($process, CURLOPT_TIMEOUT, 30);
				curl_setopt($process, CURLOPT_POST, 1);
				curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);	
				curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer '.$this->token_now, 'Content-Length: '.strlen($data_string)));
				$return = curl_exec($process);
				$err = curl_error($process);
				curl_close($process);

				if ($err) {
				  	printf("cURL Error #:" . $err ."\n");
				} else {
					printf("Noticia (".$count.") inserida com sucesso!\n");
				}

			} else {
				printf("Erro ao validar token\n");
				return("error");
			}

		}

		function insertSearchNews($date_start, $date_end){
			try {
			    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
			    $stmt = $pdo->query("SELECT keyword, results FROM consultasnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $global = 1;
			    $session = curl_multi_init();
			    $requests = array();
				while ($row = $stmt->fetch()) {
				    printf("Inserindo noticias para palavra '".$row['keyword']."'...\n");
				    $noticias = json_decode($row['results']);
      				for ($j = 0; $j < sizeof($noticias[0]); $j++){
      					$this->req_insertArticle($noticias[0][$j], $noticias[1][$j], $noticias[2][$j], $noticias[3][$j], $noticias[4][$j], $noticias[5][$j], $noticias[6][$j], $global++);
      				}
      				unset($noticias);
				}

			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}

		function insertArticleHeadlines($date_start, $date_end){
			try {
			    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
			    $stmt = $pdo->query("SELECT category, results FROM headlinesnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $global = 1;
			    $session = curl_multi_init();
			    $requests = array();
				while ($row = $stmt->fetch()) {
					printf("Inserindo noticias para categoria '".$row['category']."'...\n");
					$noticias = json_decode($row['results']);
      				for ($j = 0; $j < sizeof($noticias[0]); $j++){
      					$this->req_insertArticle($noticias[0][$j], $noticias[1][$j], $noticias[2][$j], $noticias[3][$j], $noticias[4][$j], $noticias[5][$j], $noticias[6][$j], $global++);
      				}
      				unset($noticias);
				}
				
			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}

		private function async_req_insertArticle($resultsjson){

			$this->validateToken($this->token_now);

			if ($this->token_valid == 1){
				$process = curl_init($this->baseurl.'/wp-json/api/createarticleasync');
				$data = array(
					'results' => $resultsjson 
				);
				$data_string = json_encode($data);
				curl_setopt($process, CURLOPT_TIMEOUT, 30);
				curl_setopt($process, CURLOPT_POST, 1);
				curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);	
				curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json', 'Authorization: Bearer '.$this->token_now, 'Content-Length: '.strlen($data_string)));

				return($process);
			} else {
				printf("Erro ao validar token\n");
				return("error");
			}

		}

		function async_insertSearchNews($date_start, $date_end){
			try {
			    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
			    $stmt = $pdo->query("SELECT keyword, results FROM consultasnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $global = 1;
			    $session = curl_multi_init();
			    $requests = array();
				while ($row = $stmt->fetch()) {
				    $req = $this->async_req_insertArticle($row['results']);
					$requests[] = $req;
				    curl_multi_add_handle($session, $req);
				    printf("Resultados da consulta (".$global++.") ".$row['keyword']." anexada\n");
				}

				printf("Processando...\n");
				do {
				    curl_multi_exec($session, $running);
				    curl_multi_select($session);
				} while ($running > 0);
				printf("Processamento finalizado...\n");

				foreach ($requests as $request){
					curl_multi_remove_handle($session, $request);
				}
				curl_multi_close($session);
				printf("Recursos liberados...\n");

			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}

		function async_insertArticleHeadlines($date_start, $date_end){
			try {
			    $pdo = new PDO("mysql:dbname=consultas_news;host=localhost", "root", "" );
			    $stmt = $pdo->query("SELECT category, results FROM headlinesnews WHERE query_date BETWEEN '".$date_start."' and '".$date_end."'");
			    $global = 1;
			    $session = curl_multi_init();
			    $requests = array();
				while ($row = $stmt->fetch()) {
					$req = $this->async_req_insertArticle($row['results']);
					$requests[] = $req;
				    curl_multi_add_handle($session, $req);
				    printf("Categoria (".$global++.") ".$row['category']." anexada\n");
				}
				
				printf("Processando...\n");
				do {
				    curl_multi_exec($session, $running);
				    curl_multi_select($session);
				} while ($running > 0);
				printf("Processamento finalizado...\n");
				
				foreach ($requests as $request){
					curl_multi_remove_handle($session, $request);
				}
				curl_multi_close($session);
				printf("Recursos liberados...\n");
				
			} catch(PDOException $e) {
			    printf($e->getMessage()."\n");
			}
		}
	}

?>