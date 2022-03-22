<?php
    include_once ("./_common.php");

    try {
        $version = !empty(G5_GNUBOARD_VER) ? 'v'.G5_GNUBOARD_VER : false;

        if($version == false) throw new Exception("현재버전 정보를 가져오는데 실패했습니다.");

        $latest_version = latest_version_check();
        if($latest_version == false) throw new Exception("정보조회에 실패했습니다.");

        if($latest_version != $version) {
            $message = "새로운 버전이 존재합니다.";
            $item = 1;
        } else {
            $message = "최신 버전입니다.";
            $item = 0;
        }

        $data = array();
        $data['error']    = 0;
        $data['item']     = $item;
        $data['message']  = $message;

    } catch (Exception $e) {
        $data = array();
        $data['code']    = $e->getCode();
        $data['message'] = $e->getMessage();
    }

    die(json_encode($data));


?>