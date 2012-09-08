<?php
    /*updates all necessary details like ,station names,codes etc.*/
	
	set_time_limit(3000);
	/*This script will take about 2-3 minutes to finish*/
	require_once('stationNames.php');
	/*names and codes available at erail.in are stored in the file stationNames.php*/
	function stripper($elm) {//strips whitespace from the input <elm>
        return trim($elm);
    }
	$missing = array(
	    "AVT" =>"Avatihalli H" ,
		"AXM" =>"AGASTISWARAM" ,
		 "ANQ" => "Amnapur" ,
		 "ARL" => "Araul Makanpur" ,
		 "BHHT" => "Barhara" ,
		 "DHL" => "Devanahalli" ,
		 "DJL" => "Dodjala H" ,
		 "HSP" =>"Hasanparti Road" ,
		 "KSP" => "Kishanpur" ,
		 "NLO" => "Not Known Yet" ,
		 "OMLF" => "Old Malda" ,
		 "SXS" => "Shobhasan"
	) ;//stations whose name is missing in the source site(s)
	$alpha = range('a','z');//alphabet list
	$i = 0;
	$len = 26;
	$path = 'stations';
	mkdir($path);
	$path = $path."/".$path;
	$file_start = '<?php ';
	$file_end = ';
    ?>';
	$names = array();// array(station name => stationdetails,...)
	while($i < $len ) {
	     $char = $alpha[$i++];
		 $file_path = $path.$char;//details for stations starting with alphabet alpha[i] is stored in the directory stations/stations<alpha[i]>
		 mkdir($file_path);
		 $file_path = $file_path."/";
		 $station_list =  file_get_contents("http://www.trainenquiry.com/o/station_name/noticea.asp?seldoc=".$char);
		   /*
		       Now retreive the required values
		   */
			$station_list = strip_tags($station_list);//remove tags
			$pos =strpos($station_list,"ZONE")+5;
			$pos1 = strpos($station_list," Home 
                        | Ministry of Railways ");
			$station_list = substr($station_list,$pos,$pos1-$pos);//strip the portion containing station details
			$station_list = explode("&nbsp;",$station_list);//store details into an array
			$station_list = array_filter($station_list,"stripper");//strip unnecessary values off the array
			/*Now the array contains the following details
			    index1,stationCode1,stationName1,divisionName1,zoneName1,index2,stationCode2,stationName2,divisionName2,zoneName2,....
			*/
			$list_len = sizeof($station_list);
			$j = 0;
			$file1_contents  = '$stFromCode = array(
			';
			$file2_contents  = '$stFromName = array(
			';
			while($j < $list_len) {
			    $s_code = trim($station_list[$j+1]);
				if(isset($stationNames[trim(strtolower($s_code))])) {//=>available at erail.in
				
				$s_name = str_replace(array("'",'"'),"",trim($station_list[$j + 2]));
			    $station_code = '"'.$s_code.'"';
				$other_name = '"'.strtoupper(str_replace(array("'",'"'),"",$stationNames[trim(strtolower($s_code))])).'"';
			    $station_name = "'Not Known Yet'";
				if(preg_match("/^XXXXXXXXXX/",$s_name) == 1) {
				    if(isset($missing[$s_code])) {
				        $station_name = '"'.strtoupper(str_replace(array("'",'"'),"'",$missing[$s_code])).'"';
					} 
				} else {
				     $station_name = '"'.strtoupper($s_name).'"';
				}
				$division_name = '"'.trim($station_list[$j+3]).'"';
				$zone_name = '"'.trim($station_list[$j+4]).'"';
				$station_name = strtoupper($station_name);
				if(str_replace(" ","",$station_name) != str_replace(" ","",$other_name)) {//if a station has two names.ex:kollam at erail.in and Quilion at  indianrail.gov.in
				    $names[$other_name] = array($station_code,$division_name,$zone_name);
				}
				$names[$station_name] = array($station_code,$division_name,$zone_name);
				$file1_contents = $file1_contents.$station_code.'=>array("name"=>'.$station_name.' , '.' "division" => '.$division_name.' , '.' "zone" =>'.$zone_name.') ,
				';
				}
				$j += 5;
			}
			$file1_contents = $file_start.$file1_contents."''\n)".$file_end;
			file_put_contents($file_path."stFromCode.php",$file1_contents);
			
	}
	ksort($names);
	$ch = 'A';
	foreach($names as $st_name => &$details) {
	   if($st_name[1] != $ch) {
			$file2_contents = $file_start.$file2_contents."''\n)".$file_end;
			file_put_contents($path.$ch."/stFromName.php",$file2_contents);	
	        $ch = $st_name[1];
			$file2_contents  = '$stFromName = array(
			';
	   }
	    $file2_contents = $file2_contents.$st_name.'=>array("code" => '.$details[0].' ,"division" => '.$details[1].' , "zone" => '.$details[2].') ,
		';
	}
	
			
    echo("Station details successfully updated");
?>