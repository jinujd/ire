<?php
/*
return format:
^<stationName1>~<stationCode1>~<divisionName1>~<zonName1>^<stationName2>~<stationCode2>~<divisionName2>~<zonName2>..
It first checks whether the argument is a valid station code,otherwise checks in station names and returns a maximum of  5 similar station by default names
*/
function stripper( $elm )
{
    return trim( $elm );
}
function find_similar_words( $inp_word, $word_list, $count = 1, $compare_keys = 0 ) //finds words that that have closest match from a list of words ,$inp_word => the input word.word_list => list of words stored as an array,$count =>maximum number of similar words to be found
{
    //if compare_keys != 0,array keys are compared instead of values
    $similar     = array( ); //array storing similar  words .format :array(word1=>)
    $word_cnt    = sizeof( $word_list );
    $inp_soundex = soundex( $inp_word );
    foreach ( $word_list as $key => &$val ) {
        $word             = $compare_keys ? $key : $val;
        $sim_percent      = similar_text( $inp_word, $word );
        $similar[ $word ] = $sim_percent;
    }
    arsort( $similar );
    $i   = 0;
    $ret = array( );
    foreach ( $similar as $key => &$word ) {
        $ret[ $i++ ] = $key;
        if ( $i == $count ) {
            break;
        }
    }
    return $ret;
}
$ret = '';
if ( isset( $_GET[ 'station_details' ] ) ) { //indicates that the value passed may be either station name or station code
    // return format stationCode1~stationName1~divisionName1~zoneName1~stationCode2~stationName2~divisionName2~zoneName2~....
    $station     = strtoupper( $_GET[ 'station_details' ] );
    $search_path = "stations/stations" . strtolower( $station[ 0 ] ) . "/";
    require_once( $search_path . "stFromCode.php" );
    if ( isset( $stFromCode[ $station ] ) ) { //check whether it is a valid stationcode
        $details = $stFromCode[ $station ];
        $ret     = "^" . $details[ "name" ] . "~" . $station . "~" . $details[ "division" ] . "~" . $details[ "zone" ];
    } else { //check in station names
        require_once( $search_path . "stFromName.php" );
        if ( isset( $stFromName[ $station ] ) ) {
            $ret = $ret . "^" . $station . "~" . implode( "~", $stFromName[ $station ] );
            
        } else {
            $i     = isset( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
            $limit = isset( $_GET[ 'limit' ] ) ? $_GET[ 'limit' ] : 5;
            $limit += $i;
            $similar_stations = find_similar_words( $station, $stFromName, $limit, 1 );
            $len              = sizeof( $similar_stations );
            $limit            = $limit <= $len ? $limit : $len;
            while ( $i < $limit ) {
                $st_name = $similar_stations[ $i ];
                $ret     = $ret . "^" . $st_name . "~" . implode( "~", $stFromName[ $st_name ] );
                ++$i;
            }
        }
    }
}
$ret = strtolower( $ret );
echo $ret;
?>