<?php



$string="{\"auth\":{\"aid\":\"n91dB8Z77MLdoR0K\",\"type\":\"AID\"},\"client\":{\"id\":\"DB\",\"name\":\"DB Navigator\",\"os\":\"Android 4.4.4\",\"res\":\"1080x1920\",\"type\":\"AND\",\"ua\":\"Dalvik/1.6.0 (Linux; U; Android 4.4.4; Samsung Galaxy S4 - 4.4.4 - API 19 - 1080x1920_1 Build/KTU84P)\",\"v\":16040000},\"ext\":\"DB.R15.12.a\",\"formatted\":false,\"lang\":\"de\",\"svcReqL\":[{\"cfg\":{\"polyEnc\":\"GPA\"},\"meth\":\"TripSearch\",\"req\":{\"outDate\":\"20160712\",\"outTime\":\"181534\",\"arrLocL\":[{\"lid\":\"A=1@O=Sierksdorf@X=10769104@Y=54068525@U=80@L=008005561@B=1@p=1465942746@\",\"name\":\"Sierksdorf\",\"type\":\"S\"}],\"cMZE\":0,\"depLocL\":[{\"lid\":\"A=1@O=Bad Oldesloe@X=10382829@Y=53805285@U=80@L=008000023@B=1@p=1465942746@\",\"name\":\"Bad Oldesloe\",\"type\":\"S\"}],\"economic\":false,\"extChgTime\":-1,\"frwd\":true,\"getEco\":false,\"getIST\":false,\"getIV\":false,\"getPT\":true,\"getPasslist\":false,\"getPolyline\":false,\"getTariff\":true,\"indoor\":false,\"liveSearch\":false,\"maxChg\":1000,\"maxChgTime\":-1,\"minChgTime\":-1,\"supplChgTime\":-1,\"trfReq\":{\"cType\":\"PK\",\"jnyCl\":2,\"tvlrProf\":[{\"type\":\"E\"}]},\"ushrp\":false}}],\"ver\":\"1.10\"}";





class CheckSum{


    private $preCalcChecksum = "bdI8UVj40K5fvxwf";

    private $expected = "560f2a95d8029efda3cc3f635375edc8";


    public function calc($input){

        $input=$input.$this->preCalcChecksum;

        $md5Input = md5($input,true);

        $test = array_pop(unpack('H*', $md5Input));
        var_dump($test);




        //var_dump($input);


    }

}


$test = new CheckSum();
$test->calc($string);
