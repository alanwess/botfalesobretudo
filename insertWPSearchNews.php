<?php		
	
	include("class/class_insertWPAPI.php");

	printf("Inserindo resultados de buscas no wordpress...\n");
	date_default_timezone_set('America/Sao_Paulo');
	$wpapi = new insertWPAPI();
	$wpapi->insertSearchNews(date('Y-m-d 00:00:00', time()),date('Y-m-d 23:59:59', time()));

?>