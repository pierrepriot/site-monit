<?php
$yaml =  'conf.yml';

/* ------------------------------------------------ */
	
$conf = yaml_parse_file ($yaml);

foreach($conf['urls'] as $k => $val){
	if (!preg_match('/^.+\/$/msi', $val)){
		$val .= '/';
	}
	
	$cmd = 'ab -f TLS1.2 -n 1 -v 3 '.$val;
	$res = exec ($cmd, $aRes);

	$code='';
	$time='';
	$critical=false;

	foreach($aRes as $idx => $line){			
		if (preg_match('/LOG: Response code = ([0-9]{3})/msi', $line, $aMatches)){
			$code=$aMatches[1];
		}
		elseif (preg_match('/Time per request:       ([0-9\.]+) \[ms\] \(mean, across all concurrent requests\)/msi', $line, $aMatches)){
			$time=(float)$aMatches[1];
		}
	}

	echo '<strong>'.$val.'</strong><br>';

	if($code!='200'){
		$mess = 'ALERT! SITE '.$val.' IS DOWN!';	
		$critical=true;
	}
	else{
		echo 'SITE IS UP<br>';
		if($time<100){
			$mess = 'SITE '.$val.' IS AAA FAST: '.$time.' ms';
		}
		elseif($time<1000){
			$mess = 'SITE '.$val.' IS OK FAST: '.$time.' ms';
		}
		elseif($time<10000){
			$mess =  'ALERT! SITE '.$val.' GETTING SLOW: '.$time.' ms';
			$critical=true;
		}
		else{
			$mess = 'ALERT! SITE '.$val.' ALMOST DEAD: '.$time.' ms';	
			$critical=true;
		}		
	}	
	echo $mess.'<br>';
	if (isset($conf['email'])	&&	$critical==true){
		mail($conf['email'], $mess, $mess);
	}
}
