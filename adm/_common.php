<?php
define('G5_IS_ADMIN', true);
include_once ('../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_LIB_PATH.'/update.lib.php');

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');