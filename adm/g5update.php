<?php
$sub_menu = '100500';
include_once('./_common.php');

$g5['title'] = '그누보드 업데이트';
include_once ('./admin.head.php');
$g5_update = new G5Update();
$version_list = $g5_update->getVersionList();
$latest_version = $g5_update->getLatestVersion();
if($latest_version == false) $message = "정보조회에 실패했습니다.";

$this_version = G5_GNUBOARD_VER;
?>

<?php if($latest_version != false) { ?>
<div class="version_box">
    <form action="./g5update_update.php">
        <p>현재 그누보드 버전 : v<?php echo $this_version; ?></p>
        <p>최신 그누보드 버전 : <?php echo $latest_version; ?></p>

        <span>목표 버전</span>
        <select class="version_list" name="version_list">
            <?php foreach($version_list as $key => $var) { ?>
                <option value="<?php echo $var; ?>"><?php echo $var; ?></option>
            <?php } ?>
        </select>

        <button type="button" class="btn_dup_check">업데이트 가능여부</button>
        <button type="submit" class="btn_update">지금 업데이트</button>
    </form>

</div>
<?php } else { ?>
<div class="version_box">
    <p>정보 조회에 실패했습니다. 1시간 후 다시 시도해주세요.</p>
</div>
<?php } ?>

<script>
    $(function() {
        var inAjax = false;
        $(".btn_dup_check").click(function() {
            var version = $(".version_list").val();
            
            if(inAjax == false) {
                inAjax = true;
            } else {
                alert("현재 통신중입니다.");
                return false;
            }

            $.ajax({
                url: "./ajax.compare_check.php",
                type: "POST",
                data: {
                    'version' : version
                },
                dataType: "json",
                success: function(data) {
                    inAjax = false;
                    if(data.error != 0) {
                        alert(data.message);
                        return false;
                    }
                    
                    $(".version_box").append("<p>"+data.message+"</p>");
                    
                    for(var i = 0; i < data.item.length; i++ ) {
                        $(".version_box").append("<p>"+data.item[i]+"</p>");
                    }
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        });
    })

</script>

<?php
include_once ('./admin.tail.php');
?>