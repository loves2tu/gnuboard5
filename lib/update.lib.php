<?php
if (!defined('_GNUBOARD_')) exit;

class G5Update {
    private $g5_update;

    public $target_version = false;
    public $now_version = false;
    
    // token값 입력 필요
    private $token = null;
    
    private $url = "https://api.github.com";
    private $version_list = array();
    private $compare_list = array();

    function __construct() {  }

    public function setNowVersion($now_version = null) {
        $this->now_version = $now_version;
    }

    public function setTargetVersion($target_version = null) {
        $this->target_version = $target_version;
    }

    public function getToken() {
        return $this->token;
    }

    public function getVersionList() {
        if(empty($this->version_list)) {
            $result = $this->getApiCurlResult('version');
            if($result == false) return false;

            foreach($result as $key => $var) {
                if(!isset($var->tag_name)) continue;
                if($var->tag_name)
        
                $this->version_list[] = $var->tag_name;
            }
        }

        return $this->version_list;
    }

    public function downloadVersion($version = null) {
        if($version == null) return false;
    }

    public function checkCompareModFile($version) {
        if($version == null) return false;
    }

    public function compareFiles($version1 = null) {
        if($version1 == null || $version2 == null) return false;
    }

    public function getLatestVersion() {
        $result = $this->getVersionList();

        if($result == false) return false;

        return $result[0];
    }

    public function getVersionCompareList() {
        if($this->now_version == false || $this->target_version == false) return false;
        $result = $this->getApiCurlResult("compare", $this->now_version, $this->target_version);

        if($result == false) return false;

        foreach($result->files as $key => $var) {
            $this->compare_list[] = $var->filename;
        }

        return $this->compare_list;
    }

    public function getApiCurlResult($option, $param1 = null, $param2 = null) {
        if($this->token == null) return false;
        $url = "https://api.github.com";
        switch($option) {
            case "version": 
                $url .= "/repos/gnuboard/gnuboard5/releases";
                break;
            case "compare":
                if($param1 == null || $param2 == null) return false;
                $url .= "/repos/gnuboard/gnuboard5/compare/".$param1."...".$param2;
                break;
            case "zip":
                if($param1 == null) return false;
                $url .= "/gnuboard/gnuboard5/zipball/".$param1;
                break;
            default:
                $url = false;
                break;
        }

        if($url == false) return false;
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERAGENT => 'gnuboard',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 3600,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_FAILONERROR => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: token  ' . $this->token
            ),
        ));
    
        $cinfo = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = json_decode(curl_exec($curl));

        if(curl_errno($curl)) {
            return false;
        }
    
        return $response;
    }
}