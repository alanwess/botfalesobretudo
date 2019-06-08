<?php		

	include("class/class_ListenerNews.php");

	printf("Recuperando resultados da busca...\n");
	date_default_timezone_set('America/Sao_Paulo');
	$listener = New ListenerNews();
	$listener->getSearchNews(date('Y-m-d 00:00:00', time()),date('Y-m-d 23:59:59', time()));

?>