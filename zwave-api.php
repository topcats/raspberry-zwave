<?php

class ZWaveAPI {
    private $login_cookie = '';

    public $login_username = '';
    public $login_password = '';

    const apibaseURL = 'http://localhost:8083/ZAutomation/api/v1/';

    private function zway_dologin() {
        $post_login = '{"form":true,"login":"'.$this->login_username.'","password":"'.$this->login_password.'","keepme":false,"default_ui":1}';

        $ch_login = curl_init();

        curl_setopt($ch_login, CURLOPT_URL, self::apibaseURL."login");
        curl_setopt($ch_login, CURLOPT_POST, 1);
        curl_setopt($ch_login, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch_login, CURLOPT_POSTFIELDS, $post_login);

        // receive server response ...
        curl_setopt($ch_login, CURLOPT_HEADER, 1);
        curl_setopt($ch_login, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch_login);
        curl_close ($ch_login);

        // get cookie
        preg_match('/^Set-Cookie:\s*([^;]*)/mi', $server_output, $m);
        parse_str($m[1], $cookies);

        return $cookies['ZWAYSession'];
    }


    public function getDevices() {
        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();

        #Do Lookup
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::apibaseURL."devices?since=0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        $ch_response = curl_exec($ch);
        curl_close($ch);

        $funcjson_a = json_decode($ch_response, true);
        return $funcjson_a['data']['devices'];
    }


    public function getLocations() {
        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();
        
        #Do Lookup
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::apibaseURL."locations");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        $ch_response = curl_exec($ch);
        curl_close($ch);

        $funcjson_a = json_decode($ch_response, true);
        return $funcjson_a['data'];
    }


    public function getModules() {
        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();
        
        #Do Lookup
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::apibaseURL."modules");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        $ch_response = curl_exec($ch);
        curl_close($ch);

        $funcjson_a = json_decode($ch_response, true);
        return $funcjson_a['data'];
    }


    public function getModuleInstances($moduleid = "") {
        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();
        
        #Do Lookup
        $ch = curl_init();
        if ($moduleid == '')
            curl_setopt($ch, CURLOPT_URL, self::apibaseURL."instances");
        else
            curl_setopt($ch, CURLOPT_URL, self::apibaseURL."instances/".$moduleid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        $ch_response = curl_exec($ch);
        curl_close($ch);

        $funcjson_a = json_decode($ch_response, true);
        return $funcjson_a['data'];
    }



    public function setDeviceCommand($deviceid, $newcommand) {
        #switchMultilevel on / off / min / max / exact?level=40 / increase / decrease / update
        #switchBinary on / off / update
        #toggleButton on

        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();

        #Check inputs
        if ($deviceid == '')
            return false;
        if ($newcommand == '')
            return false;

        #Do Command
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::apibaseURL."devices/".$deviceid.'/command/'.$newcommand);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        $ch_response = curl_exec($ch);
        curl_close($ch);

        #Check Response
        $funcjson_a = json_decode($ch_response, true);
        if ($funcjson_a['code'] == 200)
            return true;
        else
            return false;
     }



    public function setInstanceCommand($instanceid, $newcactive, $newparamtime, $newparamdays) {
        #http://docs.docszwayhomeautomation.apiary.io/#reference/instances/instance-model/update

        #Double check login
        if ($this->login_cookie == '')
            $this->login_cookie = $this->zway_dologin();

        #Check inputs
        if ($instanceid == '')
            return false;
        #Time must be HH:mm
        if (strlen($newparamtime) != 5)
            return false;

        #Do Command
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::apibaseURL."instances/".$instanceid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Accept: application/json","Cookie: ZWAYSession=".$this->login_cookie));
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"active":'.$newcactive.',"params":{"time":"'.$newparamtime.'","weekdays":['.$newparamdays.']}}');
        $ch_response = curl_exec($ch);
        curl_close($ch);

        #Check Response
        $funcjson_a = json_decode($ch_response, true);
        if ($funcjson_a['code'] == 200)
            return true;
        else
            return false;
     }

}

?>
