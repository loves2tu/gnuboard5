<?php
$sub_menu = '100500';
include_once('./_common.php');

$g5['title'] = '그누보드 업데이트';
include_once ('./admin.head.php');

$latest_version = "v5.5.4";
// $latest_version = latest_version_check();
if($latest_version == false) $message = "정보조회에 실패했습니다.";

$this_version = G5_GNUBOARD_VER;

?>

<?php if($latest_version != false) { ?>
<div class="version_box">
    <p>현재 그누보드 버전 : v<?php echo $this_version; ?></p>
    <p>최신 그누보드 버전 : <?php echo $latest_version; ?></p>

    <div>
        <span>ftp 주소</span><input>
        <span>계정</span><input>
        <span>비밀번호</span><input>
    </div>
    
    <input>

</div>
<?php } else { ?>
<div class="version_box">
    <p>정보 조회에 실패했습니다. 1시간 후 다시 시도해주세요.</p>
</div>
<?php } ?>



<?php
include_once ('./admin.tail.php');
?>