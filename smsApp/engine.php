<?php
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
    } //$word_list as $key => &$val
    arsort( $similar );
    $i   = 0;
    $ret = array( );
    foreach ( $similar as $key => &$word ) {
        $ret[ $i++ ] = $key;
        if ( $i == $count ) {
            break;
        } //$i == $count
    } //$similar as $key => &$word
    return $ret;
}
function send_post( $url, $data ) //sends data array(param=>val,...) to the page $url in post method and returns the reply string
{
    $post    = http_build_query( $data );
    $context = stream_context_create( array(
         "http" => array(
             "method" => "POST",
            "header" => "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen( $post ) . "\r\nUser-agent:Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)r\n",
            "content" => $post 
        ) 
    ) );
    $page    = file_get_contents( $url, false, $context );
    return $page;
}
function time_sort( $a, $b, $depTime = 1 ) //sorts results based on time(dep time or arr time)
{
    if ( $depTime == 1 ) { //sort based on departure time
        $i = 0;
    } //$depTime == 1
    else {
        $i = 1;
    }
    $ta = $a->getTime();
    $tb = $b->getTime();
    return time_cmp( $ta[ $i ], $tb[ $i ] );
}
function time_cmp( $time1, $time2 ) //compares two times in format hour:min:sec(hour => 0=>24) returns 0 if both equal ,1 if time1>time2,-1if time2>time1  
{
    $time1 = explode( ':', $time1 );
    $time2 = explode( ':', $time2 );
    $i     = 0;
    while ( $i++ < 3 ) {
        array_push( $time1, 0 );
        array_push( $time2, 0 );
    } //$i++ < 3
    $ret = 0;
    if ( $time1[ 0 ] > $time2[ 0 ] ) {
        $ret = 1;
    } //$time1[ 0 ] > $time2[ 0 ]
    else if ( $time1[ 0 ] < $time2[ 0 ] ) {
        $ret = -1;
    } //$time1[ 0 ] < $time2[ 0 ]
    else {
        if ( $time1[ 1 ] > $time2[ 1 ] ) {
            $ret = 1;
        } //$time1[ 1 ] > $time2[ 1 ]
        else if ( $time1[ 1 ] < $time2[ 1 ] ) {
            $ret = -1;
        } //$time1[ 1 ] < $time2[ 1 ]
        else {
            if ( $time1[ 2 ] > $time2[ 2 ] ) {
                $ret = 1;
            } //$time1[ 2 ] > $time2[ 2 ]
            else if ( $time1[ 2 ] < $time2[ 2 ] ) {
                $ret = -1;
            } //$time1[ 2 ] < $time2[ 2 ]
        }
    }
    return $ret;
    
}
function generate_arr( $look_up, $base )
{
    /*
    generates an  array.
    $lookup => array storing lookup values,$base => array storing decision values,
    base[i] = 1 => look_up[i] will be in the return array,
    base[i] = 0 => look_up[i] will not be in the return array,
    */
    $i   = 0;
    $len = sizeof( $base );
    $ret = array( );
    while ( $i < 7 ) {
        if ( $base[ $i++ ] == 1 ) {
            array_push( $ret, $look_up[ $i - 1 ] );
        } //$base[ $i++ ] == 1
    } //$i < 7
    return $ret;
}
class Train
{
    private $no, //train no
        $name, // train name
        $startStCode, //journey start station code
        $endStCode, //journey end station code
        $daysPresent, //days present at from station
        $depTime, //departure time from from station
        $arrTime, //arrival time at to station
        $distance, //distance from from station to to station
        $duration, //duration of journey  from from station to to station
        $classesAvail, //classes available
        $fareType, //fare type
        $runDateF, //start running date at from station
        $depDate, //date from which train reaches destinationn station
        $runDateT; //end running date at from station
    
    static $days = array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ), $classes = array( "1A", "2A", "3A", "CC", "FC", "SL", "2S", "3E" );
    
    function __construct( $detailArr ) //constructor
    {
        //detailArr => string storing train details => array(classVarName => value,....)
        $this->daysPresent  = $detailArr[ 'daysPresent' ];
        $this->classesAvail = $detailArr[ 'classesAvail' ];
        $this->name         = $detailArr[ 'name' ];
        $this->no           = $detailArr[ 'no' ];
        $this->distance     = $detailArr[ 'distance' ];
        $this->depTime      = $detailArr[ 'depTime' ];
        $this->arrTime      = $detailArr[ 'arrTime' ];
        $this->duration     = $detailArr[ 'duration' ];
        $this->startStCode  = $detailArr[ 'startStCode' ];
        $this->endStCode    = $detailArr[ 'endStCode' ];
        $this->runDateF     = $detailArr[ 'runDateF' ];
        $this->runDateT     = $detailArr[ 'runDateT' ];
        $this->fareType     = $detailArr[ 'fareType' ];
    }
    public function getName( ) //returns name of the train
    {
        return $this->name;
    }
    public function getNo( ) //returns number of the train
    {
        return $this->no;
    }
    public function getDistance( ) //returns distance of the journey
    {
        return $this->distance;
    }
    public function getTime( ) //returns array(departureTime,arrivalTime)
    {
        return array(
             $this->depTime,
            $this->arrTime 
        );
    }
    public function getArrDate( ) //returns arrival date
    {
        $depDate = new DateTime( $this->depDate );
        $secs    = $this->duration * 60 * 60;
        $depDate->add( new DateInterval( 'PT' . $secs . 'S' ) );
        $ret = $depDate->format( 'd-m-Y' );
        return $ret;
    }
    public function getStations( ) //returns array(<journey statting station's code>,<journey ending station's code>)
    {
        return array(
             $this->startStCode,
            $this->endStCode 
        );
    }
    public function getFareType( ) //returns fare type
    {
        return $this->fareType;
    }
    public function setDepDate( $date )
    {
        $this->depDate = $date;
    }
    /* public function getRunDates() //returns array(runDateF ,runDateT)
    {
    return array(
    new DateTime($this->runDateF),
    new DateTime($this->runDateT)
    );
    }*/
    public function getClasses( ) //returns array(); containing available classes
    {
        return generate_arr( self::$classes, $this->classesAvail );
    }
    public function getDays( ) //returns array(); containing days present at from station
    {
        return generate_arr( self::$days, $this->daysPresent );
    }
    public function isAvailableOn( $t_date = false, $t_time_range = false ) //returns true if train is available on the given date and time range(time range in 24 hor format,as array(hour:min,hour1:min)) date in format dd-mm-yyyy else return false
    {
        $date = $t_date ? new DateTime( $t_date ) : new DateTime();
        //$dateRange = $this->getRunDates();
        if ( !$t_time_range ) {
            $t_time_range = array(
                 $date->format( "H:i" ),
                "24:00" 
            );
        } //!$t_time_range
        //if($date >= $dateRange[0] && $date < $dateRange[1] ) {
        
        if ( in_array( $date->format( 'l' ), $this->getDays() ) ) {
            $ret = true;
            
            $time = $this->getTime();
            if ( time_cmp( $time[ 0 ], $t_time_range[ 0 ] ) == -1 || time_cmp( $time[ 0 ], $t_time_range[ 1 ] ) == 1 ) {
                $ret = false;
            } //time_cmp( $time[ 0 ], $t_time_range[ 0 ] ) == -1 || time_cmp( $time[ 0 ], $t_time_range[ 1 ] ) == 1
            
        } //in_array( $date->format( 'l' ), $this->getDays() )
        else {
            $ret = false;
        }
        /*	} else {
        $ret = false;
        }*/
        return $ret;
    }
}
class smsApp //application
{
    private static $sources, //url sof data source stored as an array
        $params, $paramsMap, $flags, $data, //data extracted
        $sourceMap, $errCodes //stores app error codes
        ; //associative array storing all the parameters that are to be passed ,params stored as $key =>$value
    public static $appUrl, $classes = array( "1A" => "1st AC", "2A" => "2nd AC", "3A" => "3rd AC", "CC" => "AC Chair Car", "FC" => "First class ", "SL" => "Sleeper Class", "2S" => "Second Seating", "3E" => "AC Economy" ), 
	
	$age = array( 'child' => array( 'CHILD AGE [5-11]', 8 ), 'adult' => array( 'ADULT AGE [12 and above]', 30 ), 'scf' => array( 'SENIOR CITIZEN FEMALE AGE [58 and above]', 61 ), 'scm' => array( 'SENIOR CITIZEN MALE AGE [60 and above]', 60 ) ), 
	
	$conc = array( "ZZZZZZ" => array( "None", "" ), "ARTCLK" => array( "Article Clerk ", 50 ), "ARTISF" => array( "Artist Lower Class ", 75 ), "ARTIUF" => array( "Artist Upper Class ", 50 ), "AWD50" => array( "Award 50 ", 50 ), "AWD75" => array( "Award 75 ", 75 ), "AWD100" => array( "Award 100 ", 100 ), "BSGUDF" => array( "Bharat Scouts/Guides ", 50 ), "BSDALF" => array( "Bharat Seva Dal ", 25 ), "BLIND" => array( "Blind Concession ", 75 ), "BLNDRS" => array( "Blind Concession in Rajdhani/Shatabdi ", 25 ), "BLESRS" => array( "Blind Free Escort ", 100 ), "CAN100" => array( "Cancer Patient  (For 3A, SL) ", 100 ), "CANCER" => array( "Cancer Patient  (For 2S, CC, FC) ", 75 ), "CNESC" => array( "Cancer Patient Escort (For 3A, SL class) ", 75 ), "CANCEU" => array( "Cancer Patient  (For 1A, 2A class) ", 50 ), "CNESCU" => array( "Cancer Patient Escort (For 1A, 2A class) ", 50 ), "DOCTOR" => array( "Doctor ", 10 ), "FLMTCL" => array( "Film Technician For Lower Class ", 75 ), "FLMTCU" => array( "Film Technician For Upper Class ", 50 ), "PUBEXA" => array( "Public Exam(6-12yrs) ", 50 ), "CIRCLF" => array( "Circus Artist Lower Class ", 75 ), "CIRCUF" => array( "Circus Artist Upper Class ", 50 ), "CVINTF" => array( "Civil International ", 25 ), "DFDM" => array( "Deaf and Dumb ", 50 ), "DFDESC" => array( "Deaf and Dumb Escort ", 50 ), "HNDCAP" => array( "Handicap Lower Class ", 75 ), "HNDESC" => array( "Handicap Escort Lower Class ", 75 ), "HNDCUP" => array( "Handicap 1AC 2AC ", 50 ), "HNDEUP" => array( "Handicap Escort 1AC 2AC ", 50 ), "HNDCRS" => array( "Handicap in Rajdhani/Shatabdi ", 25 ), "HNDERS" => array( "Handicap Escort in Rajdhani/Shatabdi ", 25 ), "HEART" => array( "Heart Patient (for 3A, SL, 2S class)", 75 ), "HRTESC" => array( "Heart Patient Escort (for 3A, SL, 2S class)", 75 ), "HEARTU" => array( "Heart Patient (for 1A, 2A class)", 50 ), "HRTESU" => array( "Heart Patient Escort (for 1A, 2A class)", 50 ), "HMPHL" => array( "Heamophilia Patient (for 3A, CC, SL, 2S)", 75 ), "HMPESC" => array( "Heamophilia Patient Escort (for 3A, CC, SL, 2S)", 75 ), "I1709A" => array( "IAFT 1709 Form D ", 40 ), "I1720U" => array( "IAFT 1720 Upper Class ", 50 ), "MNTERS" => array( "Mental Escort Patient in Rajdhani/Shatabdi ", 25 ), "MNTLRS" => array( "MentalPatient in Rajdhani/Shatabdi ", 25 ), "WORKER" => array( "Industrial Worker ", 25 ), "KIDNEY " => array( "Kidney Patients (for 3A, SL, 2S Class) ", 75 ), "KIESC" => array( "Kidney Patient Escort (for 3A, SL, 2S Class) ", 75 ), "KIDNEU" => array( "Kidney Patients (for 1A, 2A Class) ", 50 ), "KIESCU" => array( "Kidney Patients Escort (for 1A, 2A Class) ", 50 ), "KISANF" => array( "Kisan Concession ", 25 ), "LPROSY" => array( "Leprosy Patient ", 75 ), "MNTLPT" => array( "Mental Patient ", 75 ), "NURSE" => array( "Nurse ", 25 ), "POLO" => array( "Polo Team ", 50 ), "PARTLF" => array( "Proffessional Artist lower class ", 75 ), "PARTUF" => array( "Proffessional Artist Upper class ", 50 ), "PTOFOR" => array( "PTO Common Wealth Country ", 50 ), "PTORDR" => array( "PTO Indian Railway ", 66.6 ), "PUBESC" => array( "Public Examination ", 50 ), "SEARCH" => array( "Research Scholar ", 50 ), "RRECOM" => array( "Retired Railway Emplyoee/Widow above 70 years ", '' ), "SCOUTF" => array( "Scout Concession ", 50 ), "SRCTZN" => array( "Senior Male Citizen ", 40 ), "SRCTNW" => array( "Senior Female Citizen ", 50 ), "SCINTL" => array( "Service Civil Inter ", 25 ), "SPORT" => array( "Sports Lower class ", 75 ), "SPORTI" => array( "Sports Inter N Level FC ", 75 ), "SPORTN" => array( "Sports National Level FC ", 50 ), "STJONF" => array( "ST JOHN'S Ambulance ", 25 ), "STDNT" => array( "Student Concession ", 50 ), "STDSPS" => array( "Student SC/ST ", 75 ), "TBPAT" => array( "TB Patient ", 75 ), "TEACHR" => array( "Teacher ", 25 ), "TLSMIA" => array( "Thalassemia Patient (for 3A, SL, 2S class)", 75 ), "THLESC" => array( "Thalassemia Patient Escort (for 3A, SL, 2S class)", 75 ), "TLSMIU" => array( "Thalassemia Patient (for 1A, 2A class)", 50 ), "THLESU" => array( "Thalassemia Patient Escort (for 1A, 2A class)", 50 ), "WIDOW" => array( "War Widow ", 75 ), "YOUTH" => array( "Youth Concession ", 25 ), "YTH2SR" => array( "Unemployed Youth for Interview (2S) ", 100 ), "YUVA" => array( "Yuva Concession", '' ) ), 
	
	$quotas = array( "CK" => "Tatkal Quota", "SS" => "Lower Berth Quota", "GN" => "General Quota", "PH" => "Parliament House Quota", "HP" => "Handicaped Quota", "DP" => "Duty Pass Quota", "FT" => "Foreign Tourist", "DF" => "Defence Quota", "LD" => "Ladies Quota", "YU" => "Yuva Quota" );
    /*
    classes => array storing railway class info as array(<classCode> => <className>,...)
    ages => array storing age info as array(<ageCode> => <ageDescription>),this is used for fare enquiry
    conc => array string concession info as array(<concessionCode> =>array(<concessionName>,<concessionPercentage>),...)
    quotas => array storing railway quota info as array(<quotaCode> => <quotaDescription>,....)
    */
    public static function init( $sources, $timeZone ) //initialisation
    {
        /*
        sources => array storing list of source websites
        timezone => timezone of the region
        */
        self::$sources  = $sources;
        self::$errCodes = array(
             'busy' => 'Server is busy.Please try after sometime' 
        );
        date_default_timezone_set( $timeZone );
    }
    public static function setSources( $sourceMap ) //sets sources for each detail of processRequest(),$sourceMap => array(function=>sourceIndex,....)
    {
        self::$sourceMap = $sourceMap;
    }
    public static function setParam( $param, $value ) //sets a parameter's value to $value , whose name is stored in $param 
    {
        if ( !isset( self::$paramsMap[ $param ] ) ) {
            self::$paramsMap[ $param ] = $param;
        } //!isset( self::$paramsMap[ $param ] )
        self::$params[ self::$paramsMap[ $param ] ] = $value;
    }
    public static function setFlag( $flag, $value ) //sets a flag's value to $value , whose name is stored in $flag 
    {
        self::$flags[ $flag ] = $value;
    }
    public static function unsetFlag( $flag ) //unsets a flag named <flag>
    {
        unset( self::$flags[ $flag ] );
    }
    public static function getParam( $param ) //returns value of a parameter,whose name is stored in $param,returns false if parameter not set
    {
        if ( isset( self::$paramsMap[ $param ] ) && isset( self::$params[ self::$paramsMap[ $param ] ] ) ) {
            $ret = self::$params[ self::$paramsMap[ $param ] ];
        } //isset( self::$paramsMap[ $param ] ) && isset( self::$params[ self::$paramsMap[ $param ] ] )
        else {
            $ret = false;
        }
        return $ret;
    }
    public static function unsetParam( $param ) //unsets a parameter named <param>
    {
        unset( self::$params[ self::$paramsMap[ $param ] ] );
        unset( self::$paramsMap[ $param ] );
    }
    public static function setParams( $params ) //set parameters from array(option1=>value,option2=>value,...)
    {
        foreach ( $params as $key => &$val ) {
            if ( isset( self::$paramsMap[ $key ] ) ) {
                self::$params[ self::$paramsMap[ $key ] ] = $val;
            } //isset( self::$paramsMap[ $key ] )
            else {
                self::$params[ $key ]    = $val;
                self::$paramsMap[ $key ] = $key;
            }
        } //$params as $key => &$val
    }
    public static function isValidTrNo( $trNo ) // returns true if train number is valid ,otherwise returns false
    {
        $ret = false;
        if ( strlen( $trNo ) == 5 ) {
            if ( is_numeric( $trNo ) ) {
                $ret = true;
            } //is_numeric( $trNo )
        } //strlen( $trNo ) == 5
        return $ret;
    }
    public static function setFlags( $flags ) //set flags from array(flag1=>value1,flag2=>value2 ..)
    {
        self::$flags = $flags;
    }
    public static function mapParams( $mapping ) //maps a user set parameter to a parameter that is to be passed ,$mapping => array(userParamName=>passParamName,userParamName=>passParamName,...)
    {
        if ( isset( self::$params ) ) {
            foreach ( $mapping as $dummyParam => $actualParam ) {
                if ( $dummyParam !== $actualParam ) {
                    if ( isset( self::$params[ $dummyParam ] ) ) {
                        self::$params[ $actualParam ] = self::$params[ $dummyParam ];
                        unset( self::$params[ $dummyParam ] );
                    } //isset( self::$params[ $dummyParam ] )
                    self::$paramsMap[ $dummyParam ] = $actualParam;
                } //$dummyParam !== $actualParam
                
            } //$mapping as $dummyParam => $actualParam
        } //isset( self::$params )
    }
    public static function destroy( $code ) //exits the application and shows an error message according to the code passed
    {
        echo ( self::$errCodes[ $code ] );
        exit( 0 );
    }
    public static function getStation( $stationHint ) //returns the corresponding station or list of station suggestions from the string <stationHint>
    {
        self::setParam( 'station_details', $stationHint );
        self::setFlag( 'station_details', 1 );
        self::getInfoFromSource( self::$sourceMap[ 'station_details' ] );
        $data = self::$data;
        if ( sizeof( $data ) > 2 ) { // self::data = array(stationName1=>stationCode1,stationName2=>stationCode2...)
            smsApp::handleTypo( $stationHint, $data );//exact station name not found ,shows list of suggestions
        } //sizeof( $data ) > 2
        self::unsetFlag( 'station_details' );
        self::unsetParam( 'station_details' );
        return $data[ 1 ];//station code corresponding to the station name
    }
    public static function isValidClass( $class ) //returns true if entered railway class code is valid ,otherwise returns false
    {
        $class = strtoupper( $class );
        $ret   = false;
        if ( isset( self::$classes[ $class ] ) ) {
            $ret = true;
        } //isset( self::$classes[ $class ] )
        return $ret;
    }
    public static function isValidQuota( $quota ) //returns true if entered railway quota code is valid ,otherwise returns false
    {
        $quota = strtoupper( $quota );
        $ret   = false;
        if ( isset( self::$quotas[ $quota ] ) ) {
            $ret = true;
        } //isset( self::$quotas[ $quota ] )
        return $ret;
    }
    public static function isValidAge( $age ) //returns true if entered railway age code is valid ,otherwise returns false
    {
        $age = strtolower( $age );
        $ret = false;
        if ( isset( self::$age[ $age ] ) ) {
            $ret = true;
        } //isset( self::$age[ $age ] )
        return $ret;
    }
    public static function isValidConc( $concCode ) //returns true if entered railway concession code is valid ,otherwise returns false
    {
        $concCode = strtoupper( $concCode );
        $ret      = false;
        if ( isset( self::$conc[ $concCode ] ) ) {
            $ret = true;
        } //isset( self::$conc[ $concCode ] )
        return $ret;
    }
    public static function getInfoFromSource( $sourceIndex, $method = 'get' ) //gets info  from  source url self::sources[sourceIndex]
    {
        $url    = self::$sources[ $sourceIndex ] . '?';
        $params = array( );
        foreach ( self::$params as $paramName => $paramVal ) {
            array_push( $params, $paramName . '=' . $paramVal );
        } //self::$params as $paramName => $paramVal
        $url        = $url . implode( '&', $params );
        $data       = $method == 'get' ? file_get_contents( $url ) : send_post( $url, self::$params );
        $detailArr  = array(
             'detail' => $data,
            'params' => self::$params,
            'flags' => self::$flags 
        );
        self::$data = extract_data( $detailArr );
        if ( !self::$data ) {
            self::destroy( 'busy' );
        } //!self::$data
    }
    public static function processRequest( ) //processes the user request
    {
        $params = self::$params;
        $flags  = self::$flags;
        $str    = ''; //result
		$extraInfo ='';//extra information
        if ( isset( $flags[ 'travel_details' ] ) ) { //gets details related with ,travel from one place to another
            $sourceIndex    = self::$sourceMap[ 'travel_details' ];
            $i              = 0;
            $stationDetails = array( );
            foreach ( $params as $key => &$val ) {
                if ( $key == self::$paramsMap[ 'from' ] || $key == self::$paramsMap[ 'to' ] ) {
                    $data = self::getStation( $val );
                    array_push( $stationDetails, $data[ 1 ] );
                } //$key == self::$paramsMap[ 'from' ] || $key == self::$paramsMap[ 'to' ]
            } //$params as $key => &$val
            self::setParam( 'from', $stationDetails[ 0 ] ); //set the source staion code
            self::setParam( 'to', $stationDetails[ 1 ] ); //set the destination station code
            self::getInfoFromSource( $sourceIndex ); //get information
            $data = self::$data;
            $d    = new DateTime;
            $str  = $str . $data[ 0 ][ 1 ] . " -  " . $data[ 1 ][ 1 ];//source station name - destination station name
            $time = $date = false;
            if ( isset( $params[ 'date' ] ) ) {
                $date    = $params[ 'date' ];
                $curDate = new DateTime();
                $d       = new DateTime( $date );
                if ( $curDate->diff( $d )->format( "%a" ) > 120 ) {//details of trains before or after 120 days from current day cannot be fetched
                    echo ( "<br />Oops..The date " . $date . " is too far" );
                    return false;
                } //$curDate->diff( $d )->format( "%a" ) > 120
                $time = array(
                     "00:00:00",
                    "24:00:00" 
                );
                $str  = !isset( $params[ 'all_details' ] ) ? $str . " on " . $date : $str . '';
            } //isset( $params[ 'date' ] )
            else {//by default todays trains will be returned ,if no date is given
                $str = $str . " on today";
				$extraInfo ='<br />'.txtweb_lnk("tomorrow's train", 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode($stationDetails[ 0 ] . " " . $stationDetails[ 1 ] . " tomorrow" ), 0 );//show link for tomorrow's train 
            }
            if ( isset( $params[ 'time1' ] ) && isset( $params[ 'time2' ] ) ) {//trains at a specified  time interval
                $time1 = $params[ 'time1' ];
                $time2 = $params[ 'time2' ];
                $time  = array(
                     $time1,
                    $time2 
                );
                $str   = !isset( $params[ 'all_details' ] ) ? $str . ( $time2 == '24:00:00' ? " after " . $time1 : " between " . $time1 . " & " . $time2 . "<br />" ) : $str . '';
            } //isset( $params[ 'time1' ] ) && isset( $params[ 'time2' ] )
            $len = sizeof( $data );
            $i   = 2;
            $ret = array( );
            if ( $len > 0 ) {
                while ( $i < $len ) {
                    if ( $data[ $i ]->isAvailableOn( $date, $time ) ) {//filters out the trains which are not available on the specified date and time ,from the entire list
                        array_push( $ret, $data[ $i ] );
                        $data[ $i ]->setDepDate( $date );
                    } //$data[ $i ]->isAvailableOn( $date, $time )
                    $i++;
                } //$i < $len
                $i   = 0;
                $len = sizeof( $ret );
                $str = $str . "<br />";
                if ( $len > 0 ) {
                    usort( $ret, "time_sort" );//sort trains based on arrival time on source station
                    if ( !isset( $params[ 'all_details' ] ) ) {
					/*
					if all details is set, then details of the specific train will be returned,
					all details should be set to the index of the train in the return list of normal request
					*/
                        $str = $str . "Reply letter/number given in ( ) for details<br />";
                        while ( $i < $len ) {
                            $time = $ret[ $i ]->getTime();
                            $str1 = $ret[ $i ]->getName() . " at " . $time[ 0 ];
                            $str  = $str . txtweb_lnk( $str1, 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( $_GET[ 'txtweb-message' ] . " ?" . $i ), 0, 1 );
                            
                            $i++;
                        } //$i < $len
						$str = $str.$extraInfo;
                    } //!isset( $params[ 'all_details' ] )
                    else {
                        $train    = $ret[ $params[ 'all_details' ] ];//all details is the index of the train in the return list of normal request,details for that train will be shown
                        $time     = $train->getTime();//train time
                        $stations = $train->getStations();//array(<journey statting station's code>,<journey ending station's code>)
                        $trNo     = $train->getNo();//get train number
                        $str      = $str . $train->getName() . " ( No: " . $trNo . " ) at " . $time[ 0 ] . "<br />Arrives  destination at " . $time[ 1 ] . " on " . $train->getArrDate() . "
							<br />Distance:" . $train->getDistance() . "kms , classes available - " . implode( ",", $train->getClasses() ) . " Fare type - " . $train->getFareType() . "<br />This train is from " . $stations[ 0 ] . "
							to " . $stations[ 1 ] . "<br />Reply the letter or number in ( ) for following options<br />" . txtweb_lnk( 'running status of this train at ' . $data[ 0 ][ 1 ], 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=status:' . urlencode( $trNo . " " . str_replace( self::getParam( 'to' ), '', $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                        $str      = $str . txtweb_lnk( 'running status of this train at ' . $data[ 1 ][ 1 ], 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=status:' . urlencode( $trNo . " " . str_replace( self::getParam( 'from' ), '', $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                        $str      = $str . txtweb_lnk( 'fare', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=fare:' . urlencode( $trNo . " " . $stationDetails[ 0 ] . " " . $stationDetails[ 1 ] . " " . $date ), 0, 1 );
                        $str      = $str . txtweb_lnk( 'seat availability in this train', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=seat:' . urlencode( $trNo . " " . $stationDetails[ 0 ] . " " . $stationDetails[ 1 ] . " " . $date ), 0, 1 );
                        
                    }
                } //$len > 0
                else {//no trains found
                    $str = $str . "<br />No direct trains found.You may use an intermediate station";
                }
            } //$len > 0
            else {
                $str = $str . "<br />No direct trains found.You may use an intermediate station";
            }
        } //isset( $flags[ 'travel_details' ] )
        else if ( isset( $flags[ 'station_details' ] ) ) { //gets details related with ,a station ,like station code,name etc
            //station code,name details
            self::getInfoFromSource( self::$sourceMap[ 'station_details' ] );
            $data = self::$data;
            $i    = 1;
            $len  = sizeof( $data );
            if ( $len > 2 ) {
                $str = !isset( $params[ 'all_details' ] ) ? "Stations similar to " . self::$params[ 'station_details' ] . "<br />Reply the letter/number  given in ( ) for details<br />" : "Station Details";
            } //$len > 2
            else {
                $str                     = "Details for station " . self::$params[ 'station_details' ];
                $params[ 'all_details' ] = 1;
            }
            if ( !isset( $params[ 'all_details' ] ) ) {
                while ( $i < $len ) {
                    $stationDetail = $data[ $i ];
                    if ( sizeof( $stationDetail ) == 4 ) {
                        $str = $str . txtweb_lnk( "Station name : " . $stationDetail[ 0 ] . " , Station Code : " . $stationDetail[ 1 ], 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( $_GET[ 'txtweb-message' ] . " ?" . $i ), 0, 1 );
                    } //sizeof( $stationDetail ) == 4
                    ++$i;
                } //$i < $len
            } //!isset( $params[ 'all_details' ] )
            else {
                $stationDetail = $data[ $params[ 'all_details' ] ];
                if ( sizeof( $stationDetail ) == 4 ) {
                    $str = $str . "<br />Station name : " . $stationDetail[ 0 ] . " , Station Code : " . $stationDetail[ 1 ] . " , Division  : " . $stationDetail[ 2 ] . " , Zone : " . $stationDetail[ 3 ] . "<br />*";
                } //sizeof( $stationDetail ) == 4
            }
            
        } //isset( $flags[ 'station_details' ] )
        else if ( isset( $flags[ 'status' ] ) ) { //gets train status
            if ( self::isValidTrNo( self::getParam( 'status_tr_no' ) ) ) {
                $station = self::getParam( 'status_station' );
                if ( $station != false ) {
                    $data = self::getStation( $station );
                    self::setParam( 'status_station', $data[ 1 ] ); //data[0] => null array
                    $station = $data[ 0 ];
                    self::getInfoFromSource( self::$sourceMap[ 'status' ] );
                    $data = self::$data;
                    if ( sizeof( $data ) > 1 ) {
                        $str1                 = 'Journey starts here';
                        $str2                 = '';
                        $str3                 = 'Journey ends here';
                        $str4                 = '';
                        $dateArr              = self::getParam( 'status_date' );
                        $dateDep              = '';//departure date
                        $eArDt                = '';//expected arrival date
                        $eDpDt                = '';//expected departure date
                        $eDpDtO               = '';
                        $data[ 'expArrDate' ] = trim( str_replace( '(Expected)', '', $data[ 'expArrDate' ] ) );
                        $data[ 'expDepDate' ] = trim( str_replace( '(Expected)', '', $data[ 'expDepDate' ] ) );
                        $dName                = array(
                             'yesterday',
                            'today',
                            'tomorrow' 
                        );
                        $today                = new DateTime();
                        if ( $data[ 'schArrDate' ] != 'Starting Station' ) {
                            $str1    = 'Scheduled Arrival: ' . $data[ 'schArrTime' ];
                            $str2    = 'Expected Arrival: ' . $data[ 'expArrTime' ];
                            $arrDate = new DateTime( $data[ 'schArrDate' ] );
                            if ( $arrDate->format( 'm' ) == $today->format( 'm' ) && $arrDate->format( 'y' ) == $today->format( 'y' ) ) {
                                $diff    = $arrDate->format( 'd' ) - $today->format( 'd' );
                                $dateArr = isset( $dName[ $diff + 1 ] ) ? $dName[ $diff + 1 ] : $dateArr;
                            } //$arrDate->format( 'm' ) == $today->format( 'm' ) && $arrDate->format( 'y' ) == $today->format( 'y' )
                            $dateArr = $dateArr != '' ? $dateArr : $arrDate->format( 'd-m-Y' );
                            $str1    = $str1 . ' , ' . $dateArr;
                            if ( strpos( $data[ 'expArrDate' ], '(Arrived)' ) ) {
                                $arrived              = 1;
                                $data[ 'expArrDate' ] = trim( str_replace( '(Arrived)', '', $data[ 'expArrDate' ] ) );
                            } //strpos( $data[ 'expArrDate' ], '(Arrived)' )
                            $eArDt = $data[ 'expArrDate' ] == $data[ 'schArrDate' ] ? '' : $data[ 'expArrDate' ];
                        } //$data[ 'schArrDate' ] != 'Starting Station'
                        if ( $data[ 'schDepDate' ] != 'Destination Station' ) {
                            $str3    = 'Scheduled Departure: ' . $data[ 'schDepTime' ];
                            $str4    = 'Expected Departure: ' . $data[ 'expDepTime' ];
                            $depDate = new DateTime( $data[ 'schDepDate' ] );
                            if ( $depDate->format( 'm' ) == $today->format( 'm' ) && $depDate->format( 'y' ) == $today->format( 'y' ) ) {
                                $diff    = $depDate->format( 'd' ) - $today->format( 'd' );
                                $dateDep = isset( $dName[ $diff + 1 ] ) ? $dName[ $diff + 1 ] : $dateDep;
                            } //$depDate->format( 'm' ) == $today->format( 'm' ) && $depDate->format( 'y' ) == $today->format( 'y' )
                            $dateDep = $dateDep != '' ? $dateDep : $depDate->format( 'd-m-Y' );
                            $dateDep = $dateDep == $dateArr ? '' : $dateDep;
                            $str3    = $str3 . ' , ' . $dateDep;
                            if ( strpos( $data[ 'expDepDate' ], '(Departed)' ) ) {
                                $departed             = 1;
                                $data[ 'expDepDate' ] = trim( str_replace( '(Departed)', '', $data[ 'expDepDate' ] ) );
                            } //strpos( $data[ 'expDepDate' ], '(Departed)' )
                            $eDpDt = $data[ 'expDepDate' ] == $data[ 'schDepDate' ] ? '' : $data[ 'expDepDate' ];
                        } //$data[ 'schDepDate' ] != 'Destination Station'
                        $eDpDtO = $dateArr == $dateDep ? '' : $eDpDt;
                        
                        /*  if(!isset($params['all_details'])) */ {
                            $str2 = isset( $arrived ) ? str_replace( 'Expected Arrival:', 'Arrived at', $str2 ) : $str2;
                            $str2 = $str2 != '' ? '<br />' . $str2 : '';
                            $str  = $data[ 'trainName' ] . " at " . $station . " on " . $dateArr . "<br />" . $str1 . $str2 . ' ' . $eArDt;
                            $str4 = isset( $departed ) ? str_replace( 'Expected Departure:', 'Departed at', $str4 ) : $str4;
                            $str4 = $str4 != '' ? '<br />' . $str4 : '';
                            $str  = $str . "<br />" . $str3 . $str4 . ' ' . $eDpDtO;
                        }
                        /*else {
                        }*/
                    } //sizeof( $data ) > 1
                    else {
                        $str = $data[ 0 ];
                    }
                } //$station != false
                else {
                    $str = "Please try again with the stop station";
                }
            } //self::isValidTrNo( self::getParam( 'status_tr_no' ) )
            else {
                $str = "Invalid train number";
            }
        } //isset( $flags[ 'status' ] )
        else if ( isset( $flags[ 'pnr' ] ) ) { //returns pnr details
            $pnr = str_replace( '-', '', self::getParam( 'pnr_no' ) );
            self::setParam( 'pnr_no', $pnr );
            if ( strlen( $pnr ) == 10 && is_numeric( $pnr ) ) {
                self::getInfoFromSource( self::$sourceMap[ 'pnr' ], 1 );
                $data = self::$data;
                $i    = 1;
                if ( sizeof( $data ) > 1 ) {
                    $pCnt = $data[ 'passengersCnt' ];//number of passengers
                    if ( isset( $params[ 'all_details' ] ) ) {
                        $str = "PNR:" . $pnr . "<br />" . $data[ 'trainName' ] . '(' . $data[ 'trainNo' ] . ')' . "<br />" . $data[ 'from' ] . "-" . $data[ 'to' ] . " on " . $data[ 'boardingDate' ];
                        $str = $str . "<br />Passenger)-Booking Status(Coach no.,Berth no.,Quota)-Current Status(Coach no.,Berth no.)";
                        while ( $i <= $pCnt ) {
                            $pass = $data[ 'passenger' . $i ];
                            $str  = $str . '<br />' . $i++ . ')' . $pass[ 'bookingStatus' ] . '-' . $pass[ 'currentStatus' ];
                        } //$i <= $pCnt
                        $str = $str . "<br />Charting Status - " . strtolower( $data[ 'chartingStatus' ] );
                        $str = $str . "<br />Reserved Upto " . $data[ 'reservedUpTo' ] . "<br />Boarding Point : " . $data[ 'boardingPoint' ] . '<br />Class : ' . $data[ 'class' ];
                    } //isset( $params[ 'all_details' ] )
                    else {
                        $str = $data[ 'trainName' ] . '(' . $data[ 'trainNo' ] . ')' . "<br />" . $data[ 'from' ] . "-" . $data[ 'to' ] . " on " . $data[ 'boardingDate' ];
                        while ( $i <= $pCnt ) {
                            $pass = $data[ 'passenger' . $i++ ];
                            $str  = $str . '<br />' . $pass[ 'bookingStatus' ] . '-' . $pass[ 'currentStatus' ];
                        } //$i <= $pCnt
                        $str = $str . "<br />" . strtolower( $data[ 'chartingStatus' ] ) . "<br />";
                        $str = $str . 'Reply ' . txtweb_lnk( 'detailed Info', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( $_GET[ 'txtweb-message' ] . " ?" . $i ), 0, 1 );
                        $str = $str . 'Reply ' . txtweb_lnk( 'for legends', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( 'legend:pnr' ), 0, 1 );
                        
                        
                    }
                } //sizeof( $data ) > 1
                else {
                    $str = $data[ 0 ];
                }
            } //strlen( $pnr ) == 10 && is_numeric( $pnr )
            else {
                $str = '<br />Invalid PNR Number<br />';
            }
        } //isset( $flags[ 'pnr' ] )
        else if ( isset( $flags[ 'fare' ] ) ) { //fare enquiry
            $trNo         = self::getParam( "fare_tr_no" );
			/*Block  A starts
			   This block is used to make the order of appearence of inputs unimportant.ie 2a child,child 2a will result in same output
			*/
			
            $dParams      = array(
                 'fare_class' => false,
                'fare_age' => false,
                'fare_conc' => false 
            );
            $flag         = $j = 0;
            $keysToIgNore = array( );/*array storing which keys of the  array dParams are to be ignored.ex:when a valid class code is identified,then 'fare_class' will be ignored in further checks,as it is valid*/
            foreach ( $dParams as $param_ => &$val_ ) {
                $ky = false;
                if ( ( $dP = self::getParam( $param_ ) ) ) {
                    if ( !isset( $keysToIgnore[ 'fare_class' ] ) && self::isValidClass( $dP ) ) {
                        $ky = 'fare_class';
                    } //!isset( $keysToIgnore[ 'fare_class' ] ) && self::isValidClass( $dP )
                    else if ( !isset( $keysToIgnore[ 'fare_age' ] ) && self::isValidAge( $dP ) ) {
                        $ky = 'fare_age';
                    } //!isset( $keysToIgnore[ 'fare_age' ] ) && self::isValidAge( $dP )
                    else if ( !isset( $keysToIgnore[ 'fare_conc' ] ) && self::isValidConc( $dP ) ) {
                        $ky = 'fare_conc';
                    } //!isset( $keysToIgnore[ 'fare_conc' ] ) && self::isValidConc( $dP )
                    else {
                        foreach ( $dParams as $ki => $vl ) {
                            if ( !isset( $keysToIgnore[ $ki ] ) ) {
                                $ky = $ki;
                                break;
                            } //!isset( $keysToIgnore[ $ki ] )
                        } //$dParams as $ki => $vl
                    }
                    $flag           = 1;
                    $tmp            = $dParams[ $ky ];
                    $dParams[ $ky ] = $dP;
                    if ( !$dParams[ $param_ ] )
                        $dParams[ $param_ ] = $tmp;
                    $keysToIgnore[ $ky ] = 1;
                } //( $dP = self::getParam( $param_ ) )
                
            } //$dParams as $param_ => &$val_
            if ( $flag ) {
                foreach ( $dParams as $ky => &$val_ ) {
                    $dParams[ $ky ] ? self::setParam( $ky, $val_ ) : self::setParam( $ky, false );
                } //$dParams as $ky => &$val_
            } //$flag
			
			/*Block A ends*/
            if ( $trNo ) {
                if ( self::isValidTrNo( $trNo ) ) {
                    if ( ( $from = self::getParam( 'fare_st_from' ) ) ) {
                        $from = self::getStation( $from );
                        $to   = self::getStation( self::getParam( 'fare_st_to' ) );
                        self::setParam( 'fare_st_from', $from[ 1 ] ); //sets parameter as station code 
                        self::setParam( 'fare_st_to', $to[ 1 ] ); //sets parameter as station code 
                        if ( !( $class = self::getParam( 'fare_class' ) ) ) {
                            self::setParam( 'fare_class', 'SL' );
                            $class = 'SL';
                        } //!( $class = self::getParam( 'fare_class' ) )
                        if ( self::isValidClass( $class ) ) {
                            if ( !( $age = self::getParam( 'fare_age' ) ) ) {
                                $age = 'adult';
                                self::setParam( 'fare_age', $age );
                            } //!( $age = self::getParam( 'fare_age' ) )
                            
                            if ( self::isValidAge( $age ) ) {
                                self::setParam( 'fare_age', self::$age[ $age ][ 1 ] );
                                if ( !( $conc = self::getParam( 'fare_conc' ) ) ) {
                                    $conc = 'ZZZZZZ';
                                    self::setParam( 'fare_conc', $conc );
                                } //!( $conc = self::getParam( 'fare_conc' ) )
                                else {
                                    if ( !self::isValidConc( $conc ) ) {
                                        $concs = array( );
                                        $rev   = array( );
                                        foreach ( self::$conc as $key => &$val ) {
                                            $name          = $val[ 0 ];
                                            $concs[ $key ] = $name;
                                            $rev[ $name ]  = $key;
                                        } //self::$conc as $key => &$val
                                        $wrd_cnt     = 10;
                                        $i_          = 0;
                                        $sim_words   = find_similar_words( $conc, $concs, $wrd_cnt );
                                        $suggestions = array( );
                                        while ( $i_ < $wrd_cnt ) {
                                            $name                 = $sim_words[ $i_++ ];
                                            $suggestions[ $name ] = $rev[ $name ];
                                        } //$i_ < $wrd_cnt
                                        
                                        self::handleTypo( $conc, $suggestions, 1 );
                                    } //!self::isValidConc( $conc )
                                }
                                self::getInfoFromSource( self::$sourceMap[ 'fare' ], 1 );
                                $date = self::getParam( 'date' );
                                $data = self::$data;
                                $size = sizeof( $data );
                                if ( $size > 2 ) {
                                    $i = 8;
                                    
                                    $str = $from[ 0 ] . "-" . $to[ 0 ] . " on " . $data[ 2 ] . " in " . strtolower( $data[ 7 ] ) . " class for  ";
                                    if ( !isset( $params[ 'all_details' ] ) ) {//all details are not shown
                                        $str     = $data[ 1 ] . " " . $str . $age;
                                        $str     = $conc != 'ZZZZZZ' ? $str . ' with concession ' . $conc : $str;
                                        $details = '';
                                        while ( $i < $size ) {
                                            $detail = $data[ $i ];
                                            if ( strpos( $detail, 'Total Amount' ) === 0 ) {
                                                $details = $details . '<br />' . $detail . " = " . $data[ $i + 1 ];
                                                $i++;
                                            } //strpos( $detail, 'Total Amount' ) === 0
                                            else if ( strpos( $detail, 'Net Amount' ) === 0 ) { //concession
                                                $details = '<br />' . $detail . " = " . $data[ $i + 1 ];
                                                $i++;
                                            } //strpos( $detail, 'Net Amount' ) === 0
                                            $i++;
                                        } //$i < $size
                                        $str = $str . $details . '<br/>' . txtweb_lnk( 'Details', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( $_GET[ 'txtweb-message' ] . " ?" ), 0 );
                                        $str = $str . "Reply the number in () for knowing fare for corresponding age<br />";
                                        foreach ( self::$age as $ageCode => &$ageDet ) {
                                            $str = $age != $ageCode ? $str . txtweb_lnk( $ageCode, 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=fare:' . urlencode( $trNo . " " . $from[ 1 ] . " " . $to[ 1 ] . " " . $date . " " . $class . " " . $ageCode ), 0, 1 ) : $str;
                                        } //self::$age as $ageCode => &$ageDet
                                    } //!isset( $params[ 'all_details' ] )
                                    else {
                                        $concName = self::$conc[ strtoupper( $conc ) ][ 0 ];
                                        $str      = $data[ 1 ] . "(" . $data[ 0 ] . ") " . $str . strtolower( self::$age[ $age ][ 0 ] );
                                        $str      = $concName != 'None' ? $str . ' with concession for ' . $concName : $str;
                                        $remarks  = '';
                                        while ( $i < $size ) {
                                            $detail = $data[ $i ];
                                            $value  = $data[ $i + 1 ];
                                            $isNum  = is_numeric( $value );
                                            if ( ( $isNum && $value != 0 ) || !$isNum ) {
                                                if ( ( $pos1 = strpos( $detail, '*' ) ) === false ) {
                                                    $str = $str . '<br />' . $detail . " = " . $value;
                                                } //( $pos1 = strpos( $detail, '*' ) ) === false
                                                else {
                                                    $remarks = $remarks . '<br/>' . substr( $detail, $pos1 );
                                                    $i--;
                                                }
                                            } //( $isNum && $value != 0 ) || !$isNum
                                            $i += 2;
                                        } //$i < $size
                                        $str = $str . $remarks;
                                    }
                                } //$size > 2
                                else {
                                    if ( sizeof( $data ) == 1 ) {
                                        $str = $data[ 0 ] . '<br />' . txtweb_lnk( 'try again', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( $_GET[ 'txtweb-message' ] ), 0 );
                                        ;
                                    } //sizeof( $data ) == 1
                                    else {
                                        $str          = $data[ 0 ] . "<br />Reply letter corresp. to your class given in ()<br />";
                                        $len          = sizeof( $data[ 1 ] );
                                        $k            = 0;
                                        $classes      = self::$classes;
                                        $validClasses = $data[ 1 ];
                                        while ( $k < $len ) {
                                            $classCode = $validClasses[ $k++ ];
                                            $className = $classes[ $classCode ];
                                            $str       = $str . txtweb_lnk( $className, 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=fare:' . urlencode( $trNo . " " . $from[ 1 ] . " " . $to[ 1 ] . " " . $date . " " . $classCode . " adult" ), 0, 1 );
                                        } //$k < $len
                                    }
                                }
                            } //self::isValidAge( $age )
                            else {
                                $str = 'Invalid age.Reply the letter given in ( ) correponding to your age<br />';
                                foreach ( self::$age as $code => &$detail ) {
                                    $str = $str . txtweb_lnk( $detail[ 0 ], 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( str_replace( $age, $code, $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                                } //self::$age as $code => &$detail
                            }
                            
                        } //self::isValidClass( $class )
                        else {
                            $str = 'Invalid class code.Reply the letter given in ( ) correponding to your class code<br />';
                            foreach ( self::$classes as $code => &$name ) {
                                $str = $str . txtweb_lnk( $name . "(" . $code . ")", 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( str_replace( $class, $code, $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                            } //self::$classes as $code => &$name
                        }
                    } //( $from = self::getParam( 'fare_st_from' ) )
                    else {
                        $str = "Source station not sent.Try again with the source station";
                    }
                } //self::isValidTrNo( $trNo )
                else {
                    $str = "Invalid train number";
                }
            } //$trNo
            else {
                $str = "Train number not sent.Try again with the train number";
            }
        } //isset( $flags[ 'fare' ] )
        else if ( isset( $flags[ 'seat' ] ) ) { //seat availability
            if ( $from = self::getParam( 'fare_st_from' ) ) {
                $to   = self::getParam( 'fare_st_to' );
                $from = self::getStation( $from );
                $to   = self::getStation( $to );
                self::setParam( 'fare_st_from', $from[ 1 ] ); //sets parameter as station code 
                self::setParam( 'fare_st_to', $to[ 1 ] ); //sets parameter as station code 
                $trNo         = self::getParam( "fare_tr_no" );
                $class        = self::getParam( 'seat_class' );
                $quota        = self::getParam( 'seat_quota' );
                $i            = 0;
                $flag         = false;
                $classIsValid = false;//true if classcode sent is valid
                $quotaIsValid = false;//true if quota sent is valid
                $paramsA      = array(
                     $class,
                    $quota 
                );
                while ( $i < 2 ) {
                    $paramA = $paramsA[ $i ];
                    $j      = $i == 0 ? 1 : 0;
                    $paramB = $paramsA[ $j ];
                    if ( self::isValidClass( $paramA ) ) {
                        $classIsValid = true;
                        $quota        = $paramB;
                        $class        = $paramA;
                        $flag         = true;
                        if ( self::isValidQuota( $quota ) ) {
                            $quotaIsValid = true;
                        } //self::isValidQuota( $quota )
                    } //self::isValidClass( $paramA )
                    else if ( self::isValidQuota( $paramA ) ) {
                        $quotaIsValid = true;
                        $class        = $paramB;
                        $quota        = $paramA;
                        $flag         = true;
                        if ( self::isValidClass( $class ) ) {
                            $classIsValid = true;
                        } //self::isValidClass( $class )
                    } //self::isValidQuota( $paramA )
                    if ( $flag ) {
                        break;
                    } //$flag
                    $i++;
                } //$i < 2
                if ( $trNo ) {
                    if ( self::isValidTrNo( $trNo ) ) {//train number is valid
                        if ( !$class ) {
                            $class        = 'SL';
                            $classIsValid = true;
                        } //!$class
                        if ( !$quota ) {
                            $quota        = 'GN';
                            $quotaIsValid = true;
                        } //!$quota
                        if ( $classIsValid ) {
                            if ( $quotaIsValid ) {
                                self::setParam( 'seat_class', $class );
                                self::setParam( 'seat_quota', $quota );
                                self::getInfoFromSource( self::$sourceMap[ 'seat' ], 1 );
                                $data = self::$data;
                                if ( sizeof( $data ) > 1 ) {
                                    $str     = 'Seat Availability on ' . $data[ 'date' ] . ' in ' . $data[ 'trainName' ] . ' (' . $data[ 'trainNo' ] . ') from ' . $data[ 'source' ] . ' to ' . $data[ 'destination' ] . ' in ' . $data[ 'quota' ] . ' quota<br />';
                                    $str     = $str . "<br />Reply " . txtweb_lnk( 'for legends', 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( 'legend:pnr' ), 0, 1 );
                                    $seats   = $data[ 'seats' ];
                                    $len     = sizeof( $seats );
                                    $i       = 0;
                                    $classes = $data[ 'classes' ];
                                    $cLen    = sizeof( $classes );
                                    while ( $i < $len ) {
                                        $seat = $seats[ $i++ ];
                                        $str  = $str . "--<br />" . $seat[ 'date' ] . '<br />Class - Availability';
                                        $j    = 0;
                                        while ( $j < $cLen ) {
                                            $class = $classes[ $j++ ];
                                            $str   = $str . '<br />' . $class . ' - ' . $seat[ $class ];
                                        } //$j < $cLen
                                        $str = $str . "<br />";
                                    } //$i < $len
                                    
                                } //sizeof( $data ) > 1
                                else {
                                    $str = str_replace( array(
                                         'Destn',
                                        'destn' 
                                    ), 'destination', $data[ 0 ] );
                                    $str = str_replace( array(
                                         'Src',
                                        'src' 
                                    ), 'source', $str );
                                }
                                
                            } //$quotaIsValid
                            else {
                                $suggestions = array( );
                                $quotas      = self::$quotas;
                                foreach ( $quotas as $code => &$desr ) {
                                    $suggestions[ $desr ] = $code;
                                } //$quotas as $code => &$desr
                                self::handleTypo( $quota, $suggestions, 2 );
                            }
                        } //$classIsValid
                        else {
                            $str = 'Invalid class code.Reply the letter given in ( ) correponding to your class code<br />';
                            foreach ( self::$classes as $code => &$name ) {
                                $str = $str . txtweb_lnk( $name . "(" . $code . ")", 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( str_replace( $class, $code, $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                            } //self::$classes as $code => &$name
                        }
                    } //self::isValidTrNo( $trNo )
                    else {
                        $str = "Invalid train number";
                    }
                } //$trNo
                else {
                    $str = "Train number not sent.Try again with the train number";
                }
            } //$from = self::getParam( 'fare_st_from' )
            else {
                $str = "Source station not sent.Try again with the source station";
            }
        } //isset( $flags[ 'seat' ] )
        else if ( isset( $flags[ 'legend' ] ) ) { //show legends
            $leg_ops = array(
                 array(
                     "Symbol - Description",
                    array(
                         'CAN / MOD' => 'Cancelled or Modified Passenger',
                        'CNF / Confirmed' => 'Confirmed (Coach/Berth number will be available after chart preparation)',
                        'RAC' => 'Reservation Against Cancellation',
                        'WL #' => 'Waiting List Number',
                        'RLWL' => 'Remote Location Wait List',
                        'GNWL' => 'General Wait List',
                        'PQWL' => 'Pooled Quota Wait List',
                        'REGRET/WL' => 'No More Booking Permitted',
                        'RELEASED' => 'Ticket Not Cancelled but Alternative Accommodation Provided',
                        'R# #' => 'RAC Coach Number Berth Number' 
                    ) 
                    
                ),
                array(
                     "Quota Code - Description",
                    array(
                         'GN' => 'General Quota',
                        'LD' => 'Ladies Quota',
                        'HO' => 'Head quarters/high official Quota',
                        'DF' => 'Defence Quota',
                        'PH' => 'Parliament house Quota',
                        'FT' => 'Foreign Tourist Quota',
                        'DP' => 'Duty Pass Quota',
                        'CK' => 'Tatkal Quota',
                        'SS' => 'Female(above 45 Year)/Senior Citizen/Travelling alone',
                        'HP' => 'Physically Handicapped Quota',
                        'RE' => 'Railway Employee Staff on Duty for the train' 
                    ) 
                ),
                array(
                     "Class Code - Description",
                    array(
                         '1A' => 'First class Air-Conditioned (AC).The Executive class in Shatabdi type trains is also treated as Ist AC',
                        '2A' => 'AC 2-tier sleeper',
                        '3A' => 'AC 3 Tier',
                        '3E' => 'AC 3 Tier Economy',
                        'CC' => 'AC chair Car',
                        'SL' => 'Sleeper Class',
                        '2S' => 'Second Sitting',
                        'Accomodation Types<br />----' => '<br />Berths , Seats , Chair Car' 
                    ) 
                ) 
                
            );
            $leg_ops = $leg_ops[ $flags[ 'leg_op' ] ];
            $str     = $str . $leg_ops[ 0 ] . '<br />--------<br />';
            $legends = $leg_ops[ 1 ];
            foreach ( $legends as $key => &$val ) {
                $str = $str . $key . " - " . $val . "<br />";
            } //$legends as $key => &$val
        } //isset( $flags[ 'legend' ] )
        echo $str;
    }
    public static function handleTypo( $error, $solutions, $type = 0 ) /*handles a typographical error ,<error> => word which is misspelled,<solutions> => suggested solutions that match the word <type>  => typographical error  type*/ 
    {
        if ( !$type ) { //station name misspelled
            $ret  = "No station named " . $error . ".If the station name is in the following list , reply with the letter given in ( ) corresponding to the station<br />";
            $i    = 0;
            $len1 = sizeof( $solutions );
            while ( $i < $len1 ) {
                $suggestion = $solutions[ $i++ ];
                if ( sizeof( $suggestion ) == 4 ) {
                    $code = $suggestion[ 1 ];
                    $ret  = $ret . txtweb_lnk( $suggestion[ 0 ] . "( code: " . $code . ")", 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( str_replace( $error, $code, $_GET[ 'txtweb-message' ] ) ), 0, 1 );
                } //sizeof( $suggestion ) == 4
            } //$i < $len1
        } //!$type
        else {
            if ( $type == 1 ) { //invalid concession code for fare
                $ret = "concession";
            } //$type == 1
            else if ( $type == 2 ) {
                $ret = "quota";
            } //$type == 2
            $ret = 'Invalid ' . $ret . ' code.Reply with the letter/number given in ( ) corresponding to the ' . $ret . '<br />';
            foreach ( $solutions as $suggestion => &$code ) {
                $ret = $ret . txtweb_lnk( $suggestion . "( code: " . $code . ")", 'http://' . self::$appUrl . 'preprocessor.php?txtweb-message=' . urlencode( str_replace( $error, $code, $_GET[ 'txtweb-message' ] ) ), 0, 1 );
            } //$solutions as $suggestion => &$code
            
        }
        echo $ret;
        exit( 0 );
    }
    
}
function extract_data( $detailArr ) //extracts required  data from the array 'detail' and returns a data array array()
{
    //dataArray => extracted data is put in to the array dataArr
    $detail = $detailArr[ 'detail' ];
    $params = $detailArr[ 'params' ];
    //echo $detail;
    //  print_r($params);
    $flags  = $detailArr[ 'flags' ];
    if ( $detail ) {
        if ( isset( $flags[ 'pnr' ] ) ) { //extracting pnr data
            $nl     = '<br/>';
            $detail = strip_tags( $detail );
            $detail = preg_replace( "/[\n\r]/", "~", $detail );
            if ( !strpos( $detail, 'Facility Not Avbl due to Network Connectivity Failure.' ) ) {
                if ( !strpos( $detail, 'Invalid PNR NO.' ) ) {
                    if ( !strpos( $detail, 'FLUSHED PNR / PNR NOT YET GENERATED' ) ) {
                        $pos1        = strpos( $detail, '~~*' );
                        $pos2        = strpos( $detail, 'LEGENDS' );
                        $detail      = str_replace( array(
                             'Get Schedule',
                            'Train Running Status' 
                        ), '', $detail );
                        $detail      = substr( $detail, $pos1, $pos2 - $pos1 );
                        $detail      = str_replace( array(
                             '~~~~~~~~~S. No.~Booking Status  (Coach No , Berth No., Quota)~* Current Status (Coach No , Berth No.)~~~',
                            '*' 
                        ), '', $detail );
                        $detail      = explode( '~', $detail );
                        $detailDummy = array( );
                        $dlen        = sizeof( $detail );
                        $kI          = 0;
                        while ( $kI < $dlen ) {
                            $elm = trim( $detail[ $kI++ ] );
                            if ( $elm != '' ) {
                                array_push( $detailDummy, $elm );
                            } //$elm != ''
                        } //$kI < $dlen
                        $len     = sizeof( $detailDummy ) - 3;
                        //	print_r($detailDummy);
                        $dataArr = array(
                             'trainNo' => $detailDummy[ 0 ],
                            'trainName' => $detailDummy[ 1 ],
                            'boardingDate' => $detailDummy[ 2 ],
                            'from' => $detailDummy[ 3 ],
                            'to' => $detailDummy[ 4 ],
                            'reservedUpTo' => $detailDummy[ 5 ],
                            'boardingPoint' => $detailDummy[ 6 ],
                            'class' => $detailDummy[ 7 ],
                            'chartingStatus' => $detailDummy[ $len + 1 ] 
                        );
                        $i       = 8;
                        $j       = 1;
                        while ( $i < $len ) {
                            $dataArr[ 'passenger' . $j++ ] = array(
                                 'bookingStatus' => str_replace( ' ', '', $detailDummy[ $i + 1 ] ),
                                'currentStatus' => str_replace( ' ', '', $detailDummy[ $i + 2 ] ) 
                            );
                            $i += 3;
                        } //$i < $len
                        $dataArr[ 'passengersCnt' ] = --$j;
                        //print_r($dataArr);
                    } //!strpos( $detail, 'FLUSHED PNR / PNR NOT YET GENERATED' )
                    else {
                        $dataArr = array(
                             'Either the PNR number is not generated yet or the PNR number is too old' 
                        );
                    }
                } //!strpos( $detail, 'Invalid PNR NO.' )
                else {
                    $dataArr = array(
                         '<br />Invalid PNR Number<br />' 
                    );
                }
            } //!strpos( $detail, 'Facility Not Avbl due to Network Connectivity Failure.' )
            else {
                $dataArr = array(
                     '<br />Facility not available due to network connectivity problem<br />' 
                );
            }
        } //isset( $flags[ 'pnr' ] )
        else if ( !isset( $flags[ 'station_details' ] ) ) {
            /*extractiong train data
            dataArray => array(fromStationDetails,toStationDetails,trainDetail1,trainDetail2...)
            fromStationDetails => array(fromStationName,fromStationCode);
            toStationDetails => array(toStationName,toStationCode);
            */
            if ( isset( $flags[ 'travel_details' ] ) ) {
                $dataArr = array( );
                $detail  = explode( '^', $detail );
                foreach ( $detail as &$value ) {
                    $value = explode( '~', $value );
                } //$detail as &$value
                unset( $value );
                $value        = array_shift( $detail );
                $dataArr[ 0 ] = array(
                     $value[ 1 ],
                    $value[ 2 ] 
                );
                $dataArr[ 1 ] = array(
                     $value[ 3 ],
                    $value[ 4 ] 
                );
                foreach ( $detail as $trainDetail ) {
                    $details = array(
                         'daysPresent' => $trainDetail[ 13 ],
                        'classesAvail' => $trainDetail[ 21 ],
                        'name' => $trainDetail[ 1 ],
                        'no' => $trainDetail[ 0 ],
                        'distance' => $trainDetail[ 39 ],
                        'depTime' => $trainDetail[ 10 ],
                        'arrTime' => $trainDetail[ 11 ],
                        'duration' => $trainDetail[ 12 ],
                        'startStCode' => $trainDetail[ 3 ],
                        'endStCode' => $trainDetail[ 5 ],
                        'runDateF' => $trainDetail[ 37 ],
                        'runDateT' => $trainDetail[ 38 ],
                        'fareType' => $trainDetail[ 32 ] 
                    );
                    array_push( $dataArr, new Train( $details ) );
                } //$detail as $trainDetail
            } //isset( $flags[ 'travel_details' ] )
            else if ( isset( $flags[ 'status' ] ) ) { //extracting train running status data
                $detail = strip_tags( $detail );
                $detail = preg_replace( "/([\n\r\t]+)/", "~", $detail );
                $pos1   = strpos( $detail, '(DD/MM/YYYY)' );
                $pos2   = strpos( $detail, '~*' );
                $detail = substr( $detail, $pos1, $pos2 - $pos1 );
                $detail = explode( '~', $detail );
                if ( sizeof( $detail ) > 6 ) {
                    $dataArr[ 'trainNo' ]     = $detail[ 1 ];
                    $dataArr[ 'trainName' ]   = $detail[ 5 ];
                    $dataArr[ 'stationName' ] = $detail[ 7 ];
                    $dataArr[ 'stationCode' ] = $detail[ 2 ];
                    $tmp                      = explode( ',', $detail[ 9 ] );
                    $dataArr[ 'schArrTime' ]  = trim( $tmp[ 0 ] ); //scheduled arrival time
                    $dataArr[ 'schArrDate' ]  = isset( $tmp[ 1 ] ) ? trim( $tmp[ 1 ] ) : $dataArr[ 'schArrTime' ]; //scheduled arrival date
                    $dataArr[ 'delArr' ]      = $detail[ 11 ]; //delay in arrival time
                    $tmp                      = explode( ',', $detail[ 13 ] );
                    $dataArr[ 'expArrTime' ]  = trim( $tmp[ 0 ] ); //expected arrival time;
                    $dataArr[ 'expArrDate' ]  = isset( $tmp[ 1 ] ) ? trim( $tmp[ 1 ] ) : $dataArr[ 'expArrTime' ]; //expected arrival date
                    $tmp                      = explode( ',', $detail[ 15 ] );
                    $dataArr[ 'schDepTime' ]  = trim( $tmp[ 0 ] ); //scheduled departure time
                    $dataArr[ 'schDepDate' ]  = isset( $tmp[ 1 ] ) ? trim( $tmp[ 1 ] ) : $dataArr[ 'schDepTime' ];
                    ; //scheduled departure date
                    $dataArr[ 'delDep' ]     = $detail[ 17 ]; //delay in departure time
                    $tmp                     = explode( ',', $detail[ 19 ] );
                    $dataArr[ 'expDepTime' ] = $tmp[ 0 ]; //expected departure time];
                    $dataArr[ 'expDepDate' ] = isset( $tmp[ 1 ] ) ? trim( $tmp[ 1 ] ) : $dataArr[ 'expDepTime' ];
                    ; //expected departure date
                    $dataArr[ 'updated' ] = $detail[ 21 ]; //last updated on
                } //sizeof( $detail ) > 6
                else {
                    $dataArr = array(
                         "No information found for running status of the specified train.<br />Possible reasons<br />1)Train may not have a stop at the specified station<br/>2)Train may not be running through  the specified station<br />3)Train may not be running on the specified date<br />4)Status not updated yet!<br />To confirm reason 1)Do fare enquiry.If it results in  \"Train Does Not Touch This Station\" ,then you can confirm reason 1:(" 
                    );
                }
            } //isset( $flags[ 'status' ] )
            else if ( isset( $flags[ 'fare' ] ) ) { //extracting fare data
                $detail = strip_tags( $detail );//remove tags
                $detail = preg_replace( "/([\n\r\t]+)|&nbsp;/", "~", $detail );//replace all whitespace characters with ~
                if ( !strpos( $detail, 'Sorry, This particular service is unavailable at this time!!!' ) ) {
                    if ( !strpos( $detail, 'Facility Not Avbl due to Network Connectivity' ) ) {
                        if ( !( $pos1 = strpos( $detail, 'Following ERROR was encountered in your Query Processing' ) ) ) {
                            if ( !( $pos1 = strpos( $detail, 'SORRY !!! No Classes Available For This Enquiry On The Given Train' ) ) ) {
                                if ( strpos( $detail, 'SORRY !!! This is not a validClass For the Given Train' ) == false ) {
                                    $pos1    = strpos( $detail, 'Destination Station~' );
                                    $pos2    = strpos( $detail, 'No. of Queries' );
                                    $detail  = substr( $detail, $pos1, $pos2 - $pos1 );//extract the needed portion
                                    $detail  = str_replace( array(
                                         '~Train Type~Distance (kms)',
                                        'Destination Station~',
                                        '~Fare/Charges~Class -- ',
                                        'Concession Code~' 
                                    ), '', $detail );
                                    $detail  = preg_replace( "/([~]*\s*[~]\s*[~]*)/", "~", $detail );//convert multiple ~ to single ~
                                    $detail  = preg_replace( "/~$/", "", $detail );//remove the last ~
                                    $dataArr = explode( '~', $detail );//explode 
                                    if ( !is_numeric( $dataArr[ 6 ] ) ) { //to avoid the concession codein caseof concession
                                        array_splice( $dataArr, 5, 1 ); //remove concession code
                                    } //!is_numeric( $dataArr[ 6 ] )
                                } //strpos( $detail, 'SORRY !!! This is not a validClass For the Given Train' ) == false
                                else {
                                    $pos1        = strpos( $detail, ': ~' ) + 3;
                                    $pos2        = strpos( $detail, ' ~~~~~ ~' );
                                    $detail      = substr( $detail, $pos1, $pos2 - $pos1 );
                                    $detail      = explode( '~', $detail );
                                    $k           = 0;
                                    $len1        = sizeof( $detail );
                                    $detailDummy = array( );
                                    while ( $k < $len1 ) {
                                        $detailDummy[ $k ] = trim( $detail[ $k ] );
                                        $k++;
                                    } //$k < $len1
                                    $detail  = $detailDummy;
                                    $dataArr = array(
                                         'Class not available in this train',
                                        $detail 
                                    );
                                }
                            } //!( $pos1 = strpos( $detail, 'SORRY !!! No Classes Available For This Enquiry On The Given Train' ) )
                            else {
                                $dataArr = array(
                                     'No Classes Available For This Enquiry On The Given Train' 
                                );
                            }
                            
                        } //!( $pos1 = strpos( $detail, 'Following ERROR was encountered in your Query Processing' ) )
                        else {
                            $pos2    = strpos( $detail, 'Please Try Again' );
                            $detail  = substr( $detail, $pos1, $pos2 - $pos1 );
                            $detail  = explode( '~', $detail );
                            $dataArr = array(
                                 ( !strpos( $detail[ 1 ], 'Invalid Train Number' ) ) ? $detail[ 1 ] : 'Fare enquiry not available for this train' 
                            );
                        }
                    } //!strpos( $detail, 'Facility Not Avbl due to Network Connectivity' )
                    else {
                        $dataArr = array(
                             'Network connectivity failure!.Please try again.<br />Sometimes you may need to try 2 - 3 times :(' 
                        );
                    }
                } //!strpos( $detail, 'Sorry, This particular service is unavailable at this time!!!' )
                else {
                    $dataArr = array(
                         'Service is unavailable this time!.Please try again.<br />Sometimes you may need to try 2 - 3 times :(' 
                    );
                }
            } //isset( $flags[ 'fare' ] )
            else if ( isset( $flags[ 'seat' ] ) ) { //extracting seat availability data
                $detail = strip_tags( $detail );//remove tags
                $detail = preg_replace( "/([\n\r\t]+)|&nbsp;/", "~", $detail );//replace all whitespace characters with ~
                if ( !( $pos1 = strpos( $detail, 'Facility Not Avbl due to Network Connectivity Failure' ) ) ) {
                    if ( !( $pos1 = strpos( $detail, 'Following ERROR was encountered in your Query Processing' ) ) ) {
                        $pos1                     = strpos( $detail, ' ~Train Number~Train Name~Date (DD-MM-YYYY)~Source Station~Destination Station~Quota Code~' );
                        $pos2                     = strpos( $detail, 'No. of Queries' );
                        $detail                   = substr( $detail, $pos1, $pos2 - $pos1 );
                        $detail                   = str_replace( array(
                             '~Train Number~Train Name~Date (DD-MM-YYYY)~Source Station~Destination Station~Quota Code~',
                            'S.No.~Date (DD-MM-YYYY)' 
                        ), '', $detail );
                        $detail                   = preg_replace( "/([~]*\s*[~]\s*[~]*)/", "~", $detail );//convert multiple ~ to single ~
                        $detail                   = preg_replace( "/[~]+$/", "", $detail );
                        $detail                   = explode( '~', $detail );//explode
                        $dataArr[ 'classes' ]     = array( );
                        $len                      = sizeof( $detail );
                        $dataArr[ 'trainNo' ]     = $detail[ 0 ];
                        $dataArr[ 'trainName' ]   = $detail[ 1 ];
                        $dataArr[ 'date' ]        = $detail[ 2 ];
                        $dataArr[ 'source' ]      = $detail[ 3 ];
                        $dataArr[ 'destination' ] = $detail[ 4 ];
                        $dataArr[ 'quota' ]       = $detail[ 5 ];
                        $dataArr[ 'classes' ]     = array( );
                        $dataArr[ 'seats' ]       = array( );
                        $i                        = 6;
                        $classes                  = 0;
                        while ( ( $det = $detail[ $i++ ] ) != 1 ) {
                            $classes++;
                            array_push( $dataArr[ 'classes' ], trim( str_replace( array(
                                 'Class',
                                '-' 
                            ), '', $det ) ) );
                        } //( $det = $detail[ $i++ ] ) != 1
                        $classArr = $dataArr[ 'classes' ];
                        while ( $i < $len ) {
                            $j   = 0;
                            $tmp = array( );
                            while ( $j < $classes ) {
                                $tmp[ 'date' ]          = $detail[ $i ];
                                $tmp[ $classArr[ $j ] ] = $detail[ $i + $j + 1 ];
                                $j++;
                            } //$j < $classes
                            $i += $classes + 2;
                            array_push( $dataArr[ 'seats' ], $tmp );
                        } //$i < $len
                    } //!( $pos1 = strpos( $detail, 'Following ERROR was encountered in your Query Processing' ) )
                    else {
                        $detail       = substr( $detail, $pos1 );
                        $pos2         = strpos( $detail, '~Please Try Again~' );
                        $detail       = explode( '~', $detail );
                        $detail       = $detail[ 1 ];
                            $dataArr = array(
                                 ( !strpos( $detail, 'Invalid Train Number' ) ) ? $detail: 'Seat availability enquiry not available for this train' 
                            );
                    }
                    
                } //!( $pos1 = strpos( $detail, 'Facility Not Avbl due to Network Connectivity Failure' ) )
                else {
                    $dataArr = array(
                         'Network connectivity failure!.Please try again.<br />' 
                    );
                }
            } //isset( $flags[ 'seat' ] )
        } //!isset( $flags[ 'station_details' ] )
        else { //=>extracting station data
            $dataArr = explode( '^', $detail );
            $i       = 0;
            $len     = sizeof( $dataArr );
            while ( $i < $len ) {
                $dataArr[ $i ] = explode( "~", $dataArr[ $i ] );
                $i++;
            } //$i < $len
            
        }
    } //$detail
    else {
        $dataArr = false;
    }
    return $dataArr;
}
?>