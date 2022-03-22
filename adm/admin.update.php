<?php
    if (!defined('_GNUBOARD_')) exit;
?>
<div class="update_box">
    <button class="btn_check_update">업데이트 확인</button>
</div>

<script>
    var isAjax = false;
    $(function() {
        $(".btn_check_update").click(function() {
            if(isAjax == false) {
                isAjax = true;
            } else {
                alert("현재 통신중입니다.");
                return false;
            }

            $.ajax({
                url: "./ajax.check_update.php",
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
                        $tag = "<button class=\"btn_update\">지금 업데이트</button>";
                        $(".update_box").append($tag);
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

</script>