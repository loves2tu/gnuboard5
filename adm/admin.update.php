<?php
    if (!defined('_GNUBOARD_')) exit;
?>
<div class="upgrade_box">
    <button class="btn_check_upgrade">업데이트 확인</button>
</div>

<script>
    var isAjax = false;
    $(function() {
        $(".btn_check_upgrade").click(function() {
            if(isAjax == false) {
                isAjax = true;
            } else {
                alert("현재 통신중입니다.");
                return false;
            }

            $.ajax({
                url: "./ajax.check_upgrade.php",
                type: "POST",
                dataType: "json",
                success: function(data) {
                    inAjax = false;
                    if(data.error != 0) {
                        alert(data.message);
                        return false;
                    }

                    alert(data.message);
                    if(data.item == 1) {
                        $tag = "<button class=\"btn_upgrade\">지금 업데이트</button>";
                        $(".upgrade_box").append($tag);
                    }
                },
                error:function(request,status,error){
                    inAjax = false;
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

            return false;
        });
    });

    $(".btn_upgrade").click(function() {
        location.href = "<?php echo G5_ADMIN_URL; ?>"+'/upgrade.php';
    })

</script>