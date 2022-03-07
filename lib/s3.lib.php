<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/aws/aws-autoloader.php');    // aws autoloader 추가

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class S3 {
    private $_s3Client = false;

    private $_accessKey;
    private $_secretKey;
    private $_bucketName = '';

    private $_region = 'ap-northeast-2';
    private $_version = 'latest';

    private $_credentials;

    private $_path;

    public function __construct($accessKey, $secretKey, $bucketName) {
        $this->_accessKey = $accessKey;
        $this->_secretKey = $secretKey;
        $this->_bucketName = $bucketName;

        $this->setCredentials($this->_accessKey, $this->_secretKey);
        $this->setPath("s3://".$bucketName);
        $this->setUrl("https://".$this->_bucketName.'.s3.'.$this->_region.'.amazonaws.com');

        $this->setS3();
    }

    public function getPath() {
        return $this->_path;
    }

    public function setPath($path) {
        $this->_path = $path;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function setCredentials($accessKey, $secretKey) {
        $this->_credentials = new Credentials($accessKey, $secretKey);
    }

    public function getS3() {
        if($this->_s3Client == false) return false;

        return $this->_s3Client;
    }

    private function setS3() {
        if(empty($this->_region)) return false;
        if(empty($this->_version)) return false;
        if(empty($this->_credentials)) return false;

        if(empty($this->_s3Client)) {
            $options = array(
                'region'        => $this->_region,
                'version'       => $this->_version,
                'credentials'   => $this->_credentials,
            );

            $this->_s3Client = new S3Client($options);

            $default_opts = [
                's3' => [
                    'ACL' => 'private',
                    'seekable' => true
                ],
            ];
        
            $default = stream_context_set_default($default_opts);

            $this->_s3Client->registerStreamWrapper();
        }
    }

    public function makeDir($dirname = '') {
        if($dirname == '') return false;
        if($this->_s3Client == false) return false;

        $path = $this->getPath();
        mkdir($path.'/'.$dirname);
    }

    public function uploadFile($tmp_file, $dest_file) {
        if(empty($tmp_file)) return false;
        if($this->_s3Client == false) return false;

        $error_code = move_uploaded_file($tmp_file, $dest_file) or $_FILES['bf_file']['error'][$i];
        chmod($dest_file, G5_FILE_PERMISSION);

        return $error_code;
    }

    public function glob($pattern) {
        if(empty($pattern)) return false;

        $return = [];

        $patternFound = preg_match('(\*|\?|\[.+\])', $pattern, $parentPattern, PREG_OFFSET_CAPTURE);
        if ($patternFound) {
            $parent = dirname(substr($pattern, 0, $parentPattern[0][1] + 1));
            $parentLength = strlen($parent);
            $leftover = substr($pattern, $parentPattern[0][1]);
            if (($index = strpos($leftover, '/')) !== FALSE) {
                $searchPattern = substr($pattern, $parentLength + 1, $parentPattern[0][1] - $parentLength + $index - 1);
            } else {
                $searchPattern = substr($pattern, $parentLength + 1);
            }

            $replacement = [
                '/\*/' => '.*',
                '/\?/' => '.'
            ];
            $searchPattern = preg_replace(array_keys($replacement), array_values($replacement), $searchPattern);

            if (is_dir($parent."/") && ($dh = opendir($parent."/"))) {
                while($dir = readdir($dh)) {
                    if (!in_array($dir, ['.', '..'])) {
                        if (preg_match("/^". $searchPattern ."$/", $dir)) {
                            if ($index === FALSE || strlen($leftover) == $index + 1) {
                                $return[] = $parent . "/" . $dir;
                            } else {
                                if (strlen($leftover) > $index + 1) {
                                    $return = array_merge($return, self::glob("{$parent}/{$dir}" . substr($leftover, $index)));
                                }
                            }
                        }
                    }
                }
            }
        } elseif(is_dir($pattern) || is_file($$pattern)) {
            $return[] = $pattern;
        }

        return $return;
    }
}