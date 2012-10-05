 <!DOCTYPE HTML>
<html>
    <head>
   <script src = './stationCodes.js'>
   </script>
    <script>
</script>
        
       <meta name = "txtweb-appkey" content = "e2e4dd4e-4899-4065-a9a8-9c5b0b8ca08e" />
    </head>
    <body>
    <?php
/*Index page which shows the main menu and associated links*/
$appUrl = $_SERVER[ 'HTTP_HOST' ] . '/ire/smsApp/'; //app url
$lnk    = "http://" . $appUrl;
$tMsg   = isset( $_GET[ 'txtweb-message' ] ) ? $_GET[ 'txtweb-message' ] : '';

if ( ctype_space( $tMsg ) || $tMsg == '' ) {
    if ( isset( $_GET[ 'station_name' ] ) ) { //know station details 
        echo file_get_contents( $lnk . "preprocessor.php?txtweb-message=".urlencode('$' . $_GET[ 'station_name' ] ));
    } else if ( isset( $_GET[ 'option' ] ) ) { //receive inputs(options)
        $op  = $_GET[ 'option' ];
        $add = ''; //additional data to be shown
        //$field = >array(<Option name to be shown> =>  <actual parameter corresponding to the option name>)
        if ( $op == 'station' ) {
            $field = array(
                 "Station name/Station Code" => "station_name" 
            );
        } else if ( $op == 'train' ) {
            $field = array(
                 "query" => "txtweb-message" 
            );
            $add   = "<br />query format&lt;sourceStation&gt; &lt;destinationStation&gt; &lt;date&gt; &lt;time1&gt; &lt;time2&gt;
sourceStation & destinationStation can be station name or code.
Other fields are optional.Put a space between each fields";
        } else if ( $op == 'pnr' ) {
            $field = array(
                 "PNR number" => "pnrNo" 
            );
        } else if ( $op == 'status' ) {
            $field = array(
                 "TrainNumber StationName/Code Date" => "status" 
            );
            $add   = 'Train number,station name/code and Date should be separated with a space.If Date is\'nt sent ,status of today\'s train will be returned<br/>ex:16332 Pune';
        } else if ( $op == 'fare' ) {
            $field = array(
                 "TrainNumber SourceStationName/Code DestinationStationName/Code Date Class" => "fare" 
            );
            $add   = 'TrainNumber,SourceStationName/Code,DestinationStationName/Code,Date and Class should be separated with a space.Class and Date are optional<br />ex:12626 kottayam trivandrum';
        } else if ( $op == 'seat' ) {
            $field = array(
                 "TrainNumber SourceStationName/Code DestinationStationName/Code Date Class Quota" => "seat" 
            );
            $add   = 'Train number,station name/code,Date,Class,Quota  should be separated with a space.If Date is\'nt sent ,status of today\'s train will be returned<br/>By default quota = GN,class =SL<br />ex:16332 kottayam tvc';
        } else if ( $op == 'shortcut' ) { //shortcut
            $lnk = $lnk . "index.php?option=";
            echo "Indian Railway Enquiries<br />--------<br />Shortcut for which option?<br />Reply the letter corresponding to your option<br />";
            txtweb_lnk( "Train Times", $lnk . "shortcut:train" );
            txtweb_lnk( "Running Status", $lnk . "shortcut:status" );
            txtweb_lnk( "Know Fare", $lnk . "shortcut:fare" );
            txtweb_lnk( "Station Codes/Names", $lnk . "shortcut:station" );
            txtweb_lnk( "PNR Status", $lnk . "shortcut:pnr" );
            txtweb_lnk( "Seat Availability", $lnk . "shortcut:seat" );
            txtweb_lnk( "Shortcuts", $lnk . "shortcut:shortcut" );
            exit;
        } else if ( $op == 'term' ) { //railway terminology
            $lnk = $lnk . "index.php?option=";
            echo "Indian Railway Enquiries<br />--------<br />Terminology regarding which option<br />Reply the letter corresponding to your option<br />";
            txtweb_lnk( "PNR Status", $lnk . "term:pnr" );
            txtweb_lnk( "Class Codes", $lnk . "term:class" );
            txtweb_lnk( "Quota Codes", $lnk . "term:quota" );
            txtweb_lnk( "Seat Availability", $lnk . "term:quota" );
            exit;
            
        } else if ( $op == 'about' ) { //about
            $str = "All data are fetched from various Indian railway websites<br />This app is developed as a social service.Commercial use of this app is not allowed<br />This app is an an opensource app and the source code can be found at https://github.com/jinujd/ire<br />For further information contact:helpline.ire@gmail.com";
            echo $str;
            exit;
        } else if ( strpos( $op, 'shortcut:' ) === 0 ) { //show shortcuts
            $op = trim( str_replace( 'shortcut:', '', $op ) ); //option 
            if ( $op == 'train' ) {
                $opName = 'Train Times';
                $str    = '&lt;sourceStation&gt; &lt;destinationStation&gt; &lt;date&gt; &lt;time1&gt; &lt;time2&gt;<br />
sourceStation & destinationStation can be station name or code.
Other fields are optional.Put a space between each fields";';
                $str    = $str . "ex:<br />@ire kottayam changanacheri <br />-gives list of all remaining trains from  kottayam to changanacheri  today<br />@ire ktym cgy <br />-same result<br />";
                $str    = $str . "@ire ktym cgy 8:30<br /> -gives list of all remaining trains from  kottayam to changanacheri  today after 8:30<br />@ire ktym cgy 8.30<br />-same result<br />";
                $str    = $str . "@ire ktym cgy 8:30-15:00<br /> -gives list of all remaining trains from  kottayam to changanacheri  today between 8:30 and  15:00<br /";
                $str    = $str . "@ire ktym cgy 12-8-2012<br /> -gives list of all remaining trains from  kottayam to changanacheri  on 12-8-2012<br />@ire ktym changanacheri   12/8/2012<br />-same result<br />";
                $str    = $str . "Slly date time1,time2 can be included in a single query and the order of appearence of date and time has no importance.ie.-<br />@ire ktym changanacheri   12/8/2012 8.30 14.30<br />and<br />@ire ktym changanacheri  8.30 14.30 12/8/2012 <br />gives same result<br />";
            } else if ( $op == 'status' ) {
                $opName = 'Running Status';
                $str    = 'status:&lt;Train Number&gt;&lt;station name/code&gt;&lt;Date&gt;<br />This query returns when train will arrive or depart from a station,what is the delay in arrival or departure etc.<br />Train number,station name/code and Date should be separated with a space.If Date is\'nt sent ,status of today\'s train will be returned ex:<br/>';
                $str    = $str . "@ire status:12626 kottayam<br />- returns running status of train having train number 12626 at station  kottayam on today<br >@ire status:12626 ktym<br />-same result<br />";
                $str    = $str . "@ire status:12626 kottayam 12-8-2012<br />- returns running status of train having train number 12626 at station  kottayam on 12-8-2012<br />@ire status:12626 ktym 12-8-2012<br />-same result<br />@ire status:12626 ktym 12/8/2012<br />-same result";
                
            } else if ( $op == 'seat' ) {
                $opName = 'Seat Availability';
                $str    = 'seat:&lt;Train Number&gt;&lt;Source station name/code&gt;&lt;Destination station name/code&gt;&lt;Date&gt;&lt;Class Code&gt;&lt;Quota Code&gt;';
                $str    = $str . "<br />If no date is given ,date will be set to today's date.<br />If no class code is given,class code will be set to 'sl'.<br />If no quota code is given ,quota code will be set to GN<br />";
                $str    = $str . "The order of appearence of &lt;Date&gt;&lt;Class Code&gt;&lt;Quota Code&gt; are not important.<br />ie. they can be put in any order .<br />";
                $str    = $str . "ex:<br />@ire seat:12626 kottayam tvc<br />@ire seat:12626 kottayam tvc 12-8-2012<br />@ire seat:12626 kottayam tvc 3a<br/>@ire seat:12626 kottayam tvc GN<br/>@ire seat:12626 kottayam tvc GN 2a<br/>@ire seat:12626 kottayam tvc 2a GN";
            } else if ( $op == 'fare' ) {
                $opName = 'Know Fare';
                $str    = 'fare:&lt;Train Number&gt;&lt;Source station name/code&gt;&lt;Destination station name/code&gt;&lt;Date&gt;&lt;Class Code&gt;&lt;Age Code&gt;&lt;Concession Code&gt;<br />';
                $str    = $str . "If no date is given ,date will be set to today's date.<br />If no class code is given,class code will be set to 'sl'.<br />If no age code is given ,age code will be set to 'adult'<br />If no concession code is given ,fare with no concession will be the result<br />";
                $str    = $str . "the order of appearence of &lt;Date&gt;&lt;Class Code&gt;&lt;Age Code&gt;&lt;Concession Code&gt; are not important.<br />ie. they can be put in any order .<br />";
                $str    = $str . "&lt;Class Code&gt;&lt;Date&gt;&lt;Age Code&gt;&lt;Concession Code&gt;<br />";
                $str    = $str . "&lt;Class Code&gt;&lt;Date&gt;&lt;Concession Code&gt;&lt;Age Code&gt;<br />etc.are valid<br />";
                $str    = $str . "If you dont know the exact concession code ,enter the name of the concession or part of the name of the concession.App will find the concession<br />";
                $str    = $str . "Valid age codes are: <br />'child' - age 5-11<br />'adult' -age 12 and above<br />scm - senior citizen male with age 60 and above<br />scf - senior citizen femle with age 58 and above<br />";
                $str    = $str . "Valid class codes are: <br />sl - sleeper class<br />2s - Second Seating<br />1a -1st AC<br />2a - 2nd AC<br />3a - 3rd AC<br />cc - AC Chair Car<br />fc - First class<br />3e -AC Economy<br />";
                $str    = $str . "Sample concession codes: <br />";
                $str    = $str . "artclk - Article Clerk<br />blind - Blind Concession<br />can100 - Cancer Patient ,class 3a and sl<br />canceu - Cancer Patient ,class 1a and 2a";
                $str    = $str . "@ire fare:12626 kottayam tvc<br />@ire fare:ktym tvc 12-8-2012<br />@ire fare:ktym tvc 12-8-2012 2a<br />@ire fare:ktym tvc 12-8-2012 child 2a<br />@ire fare:ktym tvc 2a child<br />@ire fare:ktym tvc artclk";
            } else if ( $op == 'station' ) {
                $opName = 'Station Codes/Name';
                $str    = '$&lt;Station Name/Code&gt;&lt;Number of suggestions&gt;<br />or <br />@ire station:&lt;Station Name/Code&gt;&lt;Number of suggestions&gt;<br />';
                $str    = $str . "ex: @ire \$kottayam<br/>-returns details for station kottayam<br />@ire \$ktym<br />-same result<br />@ire station:ktym<br />-same result<br />";
                $str    = $str . "If you dont know the exact staion name or code ,give part of the station name.<br/>App will suggest you a list of similar stations.<br />&lt;Number of suggestions&gt; is the number of suggestions.<br />If not provided 5 similar stations will be returned.";
                $str    = $str . "ex:<br />@ire \$kottym<br/>will return list of 5 stations having name similar to kottym<br /> @ire \$kottym 10<br />will return list of 10 stations having name similar to kottym";
            } else if ( $op == 'pnr' ) {
                $opName = 'PNR Status';
                $str    = 'pnr:&lt;PNR Number&gt;';
            } else if ( $op == 'shortcut' ) {
                $opName = 'Application ShortCuts';
                $str    = 'shortcut:&lt;option&gt;';
                $str    = $str . "<br />If no &lt;option&gt; is given,shortcuts page will be returned<br />Otherwise shorcut for the option will be returned.<br />&lt;option&gt; can be <br />train<br />status<br />fare<br />station<br />pnr<br />shortcut";
            }
            $str = "Shorcuts for " . $opName . "<br />@ire " . $str;
            $str = $str . '<br />no need to enter &lt; and &gt;';
            echo ( $str );
            exit( 0 );
        } else if ( strpos( $op, 'term:' ) === 0 ) {
            $op = str_replace( 'term:', '', $op );
            echo ( file_get_contents( $lnk . 'preprocessor.php?txtweb-message=legend:' . $op ) );
            exit( 0 );
        }
        $str = "<form class = 'txtweb-form' action = 'http://" . $appUrl . "index.php' method = 'get'>" . txtweb_input( $field ) . "<input type = 'submit'  value = 'Submit'></form>Note:No need to enter &gt; and &lt;" . $add;
        echo ( $str );
    } else if ( isset( $_GET[ 'status' ] ) ) { //get running status
        echo ( file_get_contents( $lnk . "preprocessor.php?txtweb-message=" . urlencode( 'status:' . $_GET[ 'status' ] ) ) );
    } else if ( isset( $_GET[ 'pnrNo' ] ) ) { //get pnr status
        echo ( file_get_contents( $lnk . "preprocessor.php?txtweb-message=" . urlencode( 'pnr:' . $_GET[ 'pnrNo' ] ) ) );
    } else if ( isset( $_GET[ 'fare' ] ) ) { //get fare
        echo ( file_get_contents( $lnk . "preprocessor.php?txtweb-message=" . urlencode( 'fare:' . $_GET[ 'fare' ] ) ) );
    } else if ( isset( $_GET[ 'seat' ] ) ) { //get seat availability
        echo ( file_get_contents( $lnk . "preprocessor.php?txtweb-message=" . urlencode( 'seat:' . $_GET[ 'seat' ] ) ) );
    } else { //show home page
        $lnk = $lnk . "index.php?option=";
        echo "Indian Railway Enquiry<br />--------<br />Reply the letter/number given in ( ) corresponding to your option<br />";
        txtweb_lnk( "Train Times", $lnk . "train" );
        txtweb_lnk( "Running Status", $lnk . "status" );
        txtweb_lnk( "Seat Availability", $lnk . "seat" );
        txtweb_lnk( "PNR Status", $lnk . "pnr" );
        txtweb_lnk( "Know Fare", $lnk . "fare" );
        txtweb_lnk( "Station Codes/Names", $lnk . "station" );
        txtweb_lnk( "Shortcuts", $lnk . "shortcut" );
        txtweb_lnk( "Railway Terminology", $lnk . "term" );
        txtweb_lnk( "About", $lnk . "about" );
    }
} else {
    if ( $tMsg != 'shortcut:' ) {
        echo ( file_get_contents( $lnk . "preprocessor.php?txtweb-message=" . urlencode( $tMsg ) ) ); //direct request without the use of home page
    } else {
        echo ( file_get_contents( $lnk . "index.php?option=shortcut" ) );
    }
}

function txtweb_lnk( $value, $url, $show = 1 )
//shows a txtweb-link with url and value
{
    $ret = '<a href="' . $url . '" >' . $value . '</a><br />';
    if ( $show == 1 ) {
        echo ( $ret );
    } else {
        return $ret;
    }
}
function txtweb_input( $names ) /*shows a txtweb input names =array(<Name of the input to be shown> => <actual name of the input>,<Name of the input to be shown> => <actual name of the input>,.... )*/ 
{
    $ret = '';
    foreach ( $names as $item => $name ) {
        $ret = $ret . $item . "<input type = 'text' size = '50' name='" . $name . "' />";
    }
    
    return $ret;
}

?>
</body>
</html>