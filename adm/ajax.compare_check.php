<?php
    include_once ("./_common.php");

    try {
        $version = isset($_POST['version']) ? $_POST['version'] : false;

        if($version == false) throw new Exception("현재버전 정보를 가져오는데 실패했습니다.");

        $g5_update->setTargetVersion($version);
        $version_list = $g5_update->getVersionList();
        if($version_list == false) return false;
        if(!in_array($version, $version_list)) throw new Exception("해당 버전이 존재하지 않습니다.");

        $list = $g5_update->getVersionCompareList($latest_version);
        $result = $g5_update->checkSameVersionComparison($list);

        if($result == false) throw new Exception("비교에 실패했습니다.");
        if(!is_array($result)) throw new Exception("비정상적인 결과값입니다.");
        if($result['type'] == 'Y') {
            $item = array();
            $message = "업데이트 가능";
        } else if($result['type'] == 'N') {
            $message = "변경된 파일 내역이 존재합니다.";
            $item = $result['item'];
        }

        $data = array();
        $data['error']    = 0;
        $data['item']     = $item;
        $data['message']  = $message;

    } catch (Exception $e) {
        $data = array();
        $data['code']    = $e->getCode();
        $data['message'] = $e->getMessage();

        $g5_update->clearUpdatedir();
    }

    die(json_encode($data));
?>