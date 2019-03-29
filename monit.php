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
  $fullReport='';

	foreach($aRes as $idx => $line){			
		if (preg_match('/LOG: Response code = ([0-9]{3})/msi', $line, $aMatches)){
			$code=$aMatches[1];
		}
		elseif (preg_match('/Time per request:       ([0-9\.]+) \[ms\] \(mean, across all concurrent requests\)/msi', $line, $aMatches)){
			$time=(float)$aMatches[1];
		}
    elseif ($code==''  &&  preg_match('/WARNING: Response code not 2xx \(([0-9]{3})\)/msi', $line, $aMatches)){
      $code=$aMatches[1];
    }
    $fullReport.=$line."\n";
	}
  
	echo '<br><strong>'.$val.'</strong><br>';
  
  if ($code==''){
		$subj = 'ALERT! SITE '.$val.' IS DOWN! no response from http daemon';
    $mess = $fullReport;
		$critical=true;
  }
	elseif((int)$code>=400){
		$subj = 'ALERT! SITE '.$val.' IS DOWN! code : '.$code;
    $mess = $fullReport;
		$critical=true;
	}
	else{
		echo 'SITE IS UP<br>';
		if($time<100){
			$subj = 'SITE '.$val.' IS AAA FAST: '.$time.' ms';
      $mess = $subj;
		}
		elseif($time<2000){
			$subj = 'SITE '.$val.' IS OK FAST: '.$time.' ms';
      $mess = $subj;
		}
		elseif($time<10000){
			$subj =  'NOTICE! SITE '.$val.' GETTING SLOW: '.$time.' ms';
      $mess =  $subj;
			//$critical=true;
		}
		else{
			$subj = 'WARNING! SITE '.$val.' ALMOST DEAD: '.$time.' ms';
      $mess = $subj;
			$critical=true;
		}		
	}	
	echo $subj.'<br>';
	if (isset($conf['email'])	&&	$critical==true){
		mail($conf['email'], $subj, $mess);
	}
}
