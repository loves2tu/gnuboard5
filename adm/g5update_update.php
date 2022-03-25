<?php
$sub_menu = '100500';
include_once('./_common.php');

$version_list = isset($_REQUEST['version_list']) ? $_REQUEST['version_list'] : false;
if($version_list == false) die("목표버전 정보가 입력되지 않았습니다.");

$g5_update->setTargetVersion($version_list);

$list = $g5_update->getVersionCompareList();
if($list == null) {
    die("비교파일리스트가 존재하지 않습니다.");
}

$g5_update->clearUpdatedir();
$result = $g5_update->downloadVersion($version_list);
if($result == false) {
    die("목표버전 다운로드에 실패했습니다.");
}

exit;

foreach($list as $key => $var) {
    if(!file_exists(G5_PATH.'/'.$var)) {
        if(!is_dir(pathinfo(G5_PATH.'/'.$var, PATHINFO_DIRNAME))) {
            @mkdir(G5_PATH.'/'.$var, G5_DIR_PERMISSION);
            @chmod(G5_PATH.'/'.$var, G5_DIR_PERMISSION);
        }

        rm_rf(G5_PATH.'/'.$var);
        @copy(G5_DATA_PATH.'/update/'.$var, G5_PATH.'/'.$var);
    }
    $path = realpath(G5_PATH.'/'.$var)."<br>";
    echo $path;
}

exit;




