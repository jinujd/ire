<!DOCTYPE HTML>
<html>
    <head>
        
	    <title>
		    Rail schedules
		</title>
       <meta name = "txtweb-appkey" content = "your service app key here" />
    </head>
    <body>
<?php
/*Prepocesses the request.Initiates sources,sets flags and parameters*/
$appUrl = $_SERVER[ 'HTTP_HOST' ] . '/ire/smsApp/'; //use of  $_SERVER['HTTP_HOST'] will allow app to work on different hosts including localhost
require_once( "engine.php" ); //include app engine
smsApp::$appUrl = $appUrl;
smsApp::init( array(
     'http://erail.in/rail/getTrains.aspx',
    'http://' . $appUrl . 'getStationDetails.php',
    'http://www.indianrail.gov.in/cgi_bin/inet_pnrstat_cgi.cgi',
    'http://www.trainenquiry.com/o/RunningIslTrSt.aspx',
    'http://www.indianrail.gov.in/cgi_bin/inet_frenq_cgi.cgi',
    'http://www.indianrail.gov.in/cgi_bin/inet_accavl_cgi.cgi' 
), 'Asia/Kolkata' );
$_GET[ 'txtweb-message' ] = str_replace( array(
     '"',
    "'" ,
    '<' ,
    '>' ,
    '['	,
	']'
), '', strtolower( $_GET[ 'txtweb-message' ] ) ); //filter the message to avoid sql injection and accidental insertion of < ,> and [ ,] which are used to indicate parameters in help
$tMsg                     = $_GET[ 'txtweb-message' ];
$params1                  = explode( ' ', $tMsg ); //seperate the parameters sent,space is used as separator
$i                        = 0;
$seatSet                  = false; //indicates whether option seat availability is choosed
$fareSet                  = false; //ndicates whether option fare is choosed
$fareAgeSet               = false;
$len                      = sizeof( $params1 );
$time                     = array( );
$params                   = array( );
$flags                    = array( );
$date                     = false;
$fareClassSet             = false;
$hiddSet                  = array( );
while ( $i < $len ) {
    $val = $params1[ $i ];
    if ( strpos( $val, '$' ) !== false ) {//station details are requested
        $val                         = str_replace( '$', '', $val );
        $params[ 'station_details' ] = $val;
        $flags[ 'station_details' ]  = 1;
    } //strpos( $val, '$' ) !== false
    else if ( strpos( $val, 'station:' ) !== false ) {//station details are requested
        $val                         = str_replace( 'station:', '', $val );
        $params[ 'station_details' ] = $val;
        $flags[ 'station_details' ]  = 1;
    } //strpos( $val, 'station:' ) !== false
    else if ( strpos( $val, 'seat:' ) !== false ) {//seat availability request
        $val                    = str_replace( 'seat:', '', $val );
        $flags[ 'seat' ]        = 1;
        $seatSet                = true;
        $params[ 'fare_tr_no' ] = $val;
    } //strpos( $val, 'seat:' ) !== false
    else if ( strpos( $val, 'fare:' ) !== false ) {//fare enquiry
        $val                    = str_replace( 'fare:', '', $val );
        $flags[ 'fare' ]        = 1;
        $fareSet                = true;
        $params[ 'fare_tr_no' ] = $val;
    } //strpos( $val, 'fare:' ) !== false
    else if ( strpos( $val, 'pnr:' ) !== false ) {//pnr status
        $val                = str_replace( 'pnr:', '', $val );
        $params[ 'pnr_no' ] = $val;
        $flags[ 'pnr' ]     = 1;
    } //strpos( $val, 'pnr:' ) !== false
    else if ( strpos( $val, 'status:' ) !== false ) {//train running status
        $val                      = str_replace( 'status:', '', $val );
        $flags[ 'status' ]        = 1;
        $params[ 'status_tr_no' ] = $val;
    } //strpos( $val, 'status:' ) !== false
    else if ( strpos( $val, 'legend:' ) !== false ) {//legends
	   /*to get legend user will send query as legend:<legend name> ,ex:legend:pnr*/
        $flags[ 'legend' ] = 1;
        $leg_ops           = array(
             'pnr' => 0,
            'seat' => 1,
            'quota' => 1,
            'class' => 2 
        );//this array is used to identify the legend option set
        $val               = str_replace( 'legend:', '', $val );
        if ( isset( $leg_ops[ $val ] ) ) {
            $flags[ 'leg_op' ] = $leg_ops[ $val ];
        } //isset( $leg_ops[ $val ] )
        else {
            echo "Invalid option";
            exit( 0 );
        }
    } //strpos( $val, 'legend:' ) !== false
    else if ( strpos( $val, '.' ) !== false || strpos( $val, ':' ) !== false ) { //time, can be hh:mm,or hh.mm
        $val = str_replace( '.', ':', $val );
        array_push( $time, $val );
    } //strpos( $val, '.' ) !== false || strpos( $val, ':' ) !== false
    else if ( strpos( $val, '-' ) !== false || strpos( $val, '/' ) !== false ) {//date,can be dd/mm/yyyy or dd-mm-yyyy
        $val              = str_replace( '/', '-', $val );
        $params[ 'date' ] = $val;
    } //strpos( $val, '-' ) !== false || strpos( $val, '/' ) !== false
    else if ( strpos( $val, '?' ) !== false ) {//detailed info is needed
        $params[ 'all_details' ] = str_replace( '?', '', $val );
    } //strpos( $val, '?' ) !== false
    else if ( $val != '' ) {
        if ( $val == 'tomorrow' ) {//date =  tomorrow's date
            $dt               = new DateTime();
            $dt               = ( $dt->format( 'd' ) + 1 ) . '-' . $dt->format( 'm-Y' );
            $params[ 'date' ] = $dt;
        } //$val == 'tomorrow'
        else if ( isset( $flags[ 'station_details' ] ) ) {
            $params[ 'limit' ] = $val;
        } //isset( $flags[ 'station_details' ] )
        else if ( isset( $params[ 'to' ] ) ) {//destination station is already set =>val  info other than source station and destination station
            if ( !isset( $params[ 'fare_class' ] ) ) {//class option for fare enquiry
                $params[ 'fare_class' ] = $val;
                $fareClassSet           = true;
            } //!isset( $params[ 'fare_class' ] )
            else {
                if ( !isset( $params[ 'fare_age' ] ) ) {//age option for fare enquiry
                    $params[ 'fare_age' ] = $val;
                    $fareAgeSet           = true;
                } //!isset( $params[ 'fare_age' ] )
                else {
                    $params[ 'fare_conc' ] = $val;//concession for fare enquiry
                }
            }
        } //isset( $params[ 'to' ] )
        else if ( isset( $params[ 'from' ] ) ) {//source station set => val is destination station
            $params[ 'to' ] = $val;
        } //isset( $params[ 'from' ] )
        else {
            if ( !isset( $params[ 'status_tr_no' ] ) ) {//train number for running status enquiry not set =>set destination station as val
                $params[ 'from' ] = $val;
            } //!isset( $params[ 'status_tr_no' ] )
            else {
                $params[ 'status_station' ] = $val;//station for running status enquiry 
            }
        }
    } //$val != ''
    $i++;
} //$i < $len
if ( isset( $params[ 'from' ] ) ) {
    if ( isset( $params[ 'to' ] ) ) {
        if ( $seatSet || $fareSet ) {
            $params[ 'fare_st_from' ] = $params[ 'from' ];
            $params[ 'fare_st_to' ]   = $params[ 'to' ];
            $params[ 'fare_day' ]     = isset( $params[ 'date' ] ) ? new DateTime( $params[ 'date' ] ) : new DateTime();
            $params[ 'fare_month' ]   = $params[ 'fare_day' ]->format( 'm' );
            $params[ 'fare_day' ]     = $params[ 'fare_day' ]->format( 'd' );
            unset( $params[ 'from' ] );
            unset( $params[ 'to' ] );
            unset( $params[ 'date' ] );
            if ( $fareSet ) {
			/*
			hiddSet is an array used to set all hidden parameters(both dummy parameters and actual parameters)
			hiddSet = array(<dummyParameterNamePrefix>,<actualParameterNamePrefix>,<range>,<difference of dummyparameterSuffixNumber from actualParametersuffixNumber>)
			<range> is an array storing range of suffix numbers for actual parameters as(<start>,<stop>)
			name of actual parameters will end in numbers from <start> to <stop>
			*/
                array_push( $hiddSet, array(
                     'dummyPrefix' => 'fare_hidden',
                    'actPrefix' => 'lccp_frclass',
                    'actSuffRange' => array(
                         1,
                        7 
                    ),
                    'dummySuffOverHead' => 2 
                ) );
                $opName                    = 'fare';
                $params[ 'fare_hidden1' ]  = 'NONE';
                $params[ 'fare_hidden2' ]  = 'NONE';
                $params[ 'fare_hidden10' ] = 1;
                $i                         = 3;
                $till                      = 10;
            } //$fareSet
            else {
                $opName = 'seat';
                array_push( $hiddSet, array(
                     'dummyPrefix' => 'seat_hidden',
                    'actPrefix' => 'lccp_class',
                    'actSuffRange' => array(
                         2,
                        7 
                    ),
                    'dummySuffOverHead' => 0 
                ) );
                $i    = 2;
                $till = 8;
                if ( $fareClassSet ) {
                    $tmp                    = $params[ 'fare_class' ];
                    $params[ 'seat_class' ] = $tmp;
                } //$fareClassSet
                if ( $fareAgeSet ) {
                    $tmp = $params[ 'fare_age' ];
                    unset( $params[ 'fare_age' ] );
                    $params[ 'seat_quota' ] = $tmp;
                } //$fareAgeSet
                $params[ 'fare_hidden1' ] = 'ZZ';
                $params[ 'fare_class' ]   = 'ZZ';
            }
            while ( $i < $till ) {//sets hidden fields
                $params[ $opName . '_hidden' . $i++ ] = 'ZZ';
            } //$i < $till
        } //$seatSet || $fareSet
        else {
            $flags[ "travel_details" ] = 1;
        }
    } //isset( $params[ 'to' ] )
    else {//destination station not given
        echo ( "Destination station not sent.Try again with the destination station" );
        exit( 0 );
    }
} //isset( $params[ 'from' ] )
if ( isset( $params[ 'status_tr_no' ] ) ) {//status or fare enquiry
    $params[ 'status_date' ] = isset( $params[ 'date' ] ) ? new DateTime( $params[ 'date' ] ) : new DateTime();
    $params[ 'status_date' ] = $params[ 'status_date' ]->format( 'd/m/Y' );
} //isset( $params[ 'status_tr_no' ] )
if ( sizeof( $time ) > 0 ) {//time is set
    $i = 0;
    array_push( $time, "24:00:00" );//if time2 is not set ,it will be set as 24:00:00,time interval will be (time[0],time[1])
    while ( $i < 2 ) {
        if ( isset( $time[ $i ] ) ) {
            $params[ 'time' . ( $i + 1 ) ] = $time[ $i ];
        } //isset( $time[ $i ] )
        ++$i;
    } //$i < 2
} //sizeof( $time ) > 0
smsApp::setSources( array( //sets the sources for each functionality , = array(<function name> => <source index in initiation array passed to smsApp::init>)
     'travel_details' => 0,
    'train_details' => 0,
    'station_details' => 1,
    'pnr' => 2,
    'status' => 3,
    'fare' => 4,
    'seat' => 5 
) );
$mapping = array(
    /*maps app defined parameters to actual parameters that are to be sent to sources in HTTP GET OR POST
    Helps to recognise which parameters are sent actually .Change of source website requires change of actual parameters only
    */
     "from" => "Station_From",
    "to" => "Station_To",
    "no" => "TrainNo",
    "pnr_no" => "lccp_pnrno1",
    "status_tr_no" => "tr",
    "status_station" => "st",
    "status_date" => "dt",
    "fare_st_from" => "lccp_srccode",
    "fare_st_to" => "lccp_dstncode",
    "fare_tr_no" => "lccp_trnno",
    "fare_day" => "lccp_day",
    "fare_month" => "lccp_month",
    "fare_class" => "lccp_classopt",
    "fare_age" => "lccp_age",
    "fare_conc" => "lccp_conc",
    "fare_hidden1" => "lccp_enrtcode",
    "fare_hidden2" => "lccp_viacode",
    "fare_hidden10" => "lccp_disp_avl_flg",
    "seat_quota" => "lccp_quota",
    "seat_class" => "lccp_class1" 
);
$lenM    = sizeof( $hiddSet ); //hiddSet is the array containing the hidden fields to be passed to a page
$i1      = 0;
while ( $i1 < $lenM ) {
    $filler = $hiddSet[ $i1++ ];
    $start  = $filler[ 'actSuffRange' ];
    $dPre   = $filler[ 'dummyPrefix' ];
    $aPre   = $filler[ 'actPrefix' ];
    $oH     = $filler[ 'dummySuffOverHead' ];
    $stop   = $start[ 1 ];
    $start  = $start[ 0 ];
    while ( $start <= $stop ) {
        $dSufNum                     = $start + $oH;
        $mapping[ $dPre . $dSufNum ] = $aPre . $start;
        $start++;
    } //$start <= $stop
    
} //$i1 < $lenM
smsApp::setParams( $params ); //sets parameters
smsApp::mapParams( $mapping ); //map parametersto actual parameters
smsApp::setFlags( $flags ); //set engine flags
smsApp::processRequest(); //process the request
function txtweb_reply( $msg ) //replies a message to a number
{
    $mob_hash = $_GET[ 'txtweb-mobile' ];
    $pub_key  = 'cacccf20-01c6-4f2c-a50d-c44b6dfca6db';
    $msg      = '<html><head><title>railschedule</title>
		<meta name = "txtweb-appkey" content = "cd30ea49-7e0c-48a5-9662-55881501c228" /></head><body>' . $msg . '</body></html>';
    send_post( 'http://api.txtweb.com/v1/push', array(
         'txtweb-mobile' => $mob_hash,
        'txtweb-pubkey' => $pub_key,
        'txtweb-message' => urlencode( $msg ) 
    ) );
}
function txtweb_lnk( $value, $url, $show = 1, $normal = 0 ) //shows a txtweb-link with url and value,normal = 1=>shows a normal link
{
    $normal = $normal == 0 ? 'class ="txtweb-menu-for"' : '';
    $ret    = '<a href="' . $url . '" ' . $normal . ' >' . $value . '</a><br />';
    if ( $show == 1 ) {
        echo ( $ret );
    } //$show == 1
    else {
        return $ret;
    }
}
?>
</body>
</html>