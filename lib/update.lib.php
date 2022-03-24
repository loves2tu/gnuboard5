<?php
if (!defined('_GNUBOARD_')) exit;

class G5Update {
    private $g5_update;

    public $path = null;
    public $latest_version = null;
    public $target_version = null;
    public $now_version = null;
    
    // token값 입력 필요
    private $token = null;
    
    private $url = "https://api.github.com";
    private $version_list = array();
    private $compare_list = array();

    public function __construct() {  }

    public function clearUpdatedir() {
        rm_rf(G5_DATA_PATH.'/update'); 
        @mkdir(G5_DATA_PATH.'/update', G5_DIR_PERMISSION, true);
        @chmod(G5_DATA_PATH.'/update', G5_DIR_PERMISSION, true);
    }

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
        $this->clearUpdatedir();

        // 테스트용 코드
        // $version = $this->target_version;

        $save = G5_DATA_PATH.'/update/gnuboard.zip';

        $zip = @fopen($save, 'w+');
        if($zip == false) return false;

        $result = $this->getApiCurlResult('zip', $version);
        if($result == false) return false;

        $file_result = @fwrite($zip, $result);
        if($file_result == false) return false;

        exec('unzip '.$save.' -d '.G5_DATA_PATH.'/update/'.$version);
        exec('mv '.G5_DATA_PATH.'/update/'.$version.'/gnuboard-*/* '.G5_DATA_PATH.'/update/'.$version);
        exec('rm -rf '.G5_DATA_PATH.'/update/'.$version.'/gnuboard-*/');
        exec('rm -rf '.$save);

        return true;
    }

    public function checkSameVersionComparison($list = null) {
        if($this->now_version == null) return false;
        if($list == null) return false;

        $result = $this->downloadVersion($this->now_version);
        if($result == false) return false;

        $check = array();
        $check['type'] = 'Y';
        foreach($list as $key => $var) {
            $now_file_path = G5_PATH.'/'.$var;
            $release_file_path = G5_DATA_PATH.'/update/'.$this->target_version.'/'.$var;
            // 테스트용 코드
            // $release_file_path = G5_DATA_PATH.'/update/'.$this->now_version.'/'.$var;

            if(!file_exists($now_file_path)) continue;
            if(!file_exists($release_file_path)) continue;
            
            $now_fp = @fopen($now_file_path, 'r');
            $release_fp = @fopen($release_file_path, 'r');

            $now_content = @fread($now_fp, filesize($now_file_path));
            $release_content = @fread($release_fp, filesize($release_file_path));

            if($now_content != $release_content) {
                $check['type'] = 'N';
                $check['item'][] = $var;
            }
        }

        return $check;
    }

    public function getLatestVersion() {
        if($this->latest_version == null) {
            $result = $this->getVersionList();
            
            if($result == false) return false;

            $this->latest_version = $result[0];
        }

        return $this->latest_version;
    }

    public function getVersionCompareList() {
        if($this->now_version == null || $this->target_version == null) return false;
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
                $url .= "/repos/gnuboard/gnuboard5/zipball/".$param1;
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
        if($option == 'zip') {
            $response = curl_exec($curl);
        } else {
            $response = json_decode(curl_exec($curl));
        }

        if(curl_errno($curl)) {
            return false;
        }
    
        return $response;
    }
}