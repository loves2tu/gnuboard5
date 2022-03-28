<?php
$sub_menu = '100600';
include_once('./_common.php');

$version_list = isset($_POST['version_list']) ? $_POST['version_list'] : false;
$username = isset($_POST['username']) ? $_POST['username'] : false;
$userpassword = isset($_POST['password']) ? $_POST['password'] : false;
$port = isset($_POST['port']) ? $_POST['port'] : false;
if($version_list == false) die("목표버전 정보가 입력되지 않았습니다.");

$g5_update->setTargetVersion($version_list);

$list = $g5_update->getVersionCompareList();
if($list == null) {
    die("비교파일리스트가 존재하지 않습니다.");
}
$conn_result = $g5_update->connect($_SERVER['HTTP_HOST'], $port, $username, $userpassword);
if($conn_result == false) die("서버연결에 실패했습니다.");

$g5_update->clearUpdatedir();
$result = $g5_update->downloadVersion($version_list);
if($result == false) {
    die("목표버전 다운로드에 실패했습니다.");
}

foreach($list as $key => $var) {
    $result = $g5_update->writeUpdateFile(G5_PATH.'/'.$var, G5_DATA_PATH.'/update/'.$version_list.'/'.$var);
    if($result == false) {
        echo $var." 업데이트 실패<br>";
    }
}

$g5_update->clearUpdatedir();

goto_url("./upgrade.php");

exit;




