<?php
if (!defined('_GNUBOARD_')) exit;

$s3config_file = G5_DATA_PATH.'/'.G5_S3CONFIG_FILE;
if (file_exists($s3config_file)) {
    include_once($s3config_file);
    include_once(G5_LIB_PATH.'/common.lib.php');    // 공통 라이브러리
    require_once(G5_LIB_PATH.'/s3.lib.php');

    // s3 $g5 배열에 저장
    $g5['s3'] = new S3(G5_S3_ACCESS_KEY, G5_S3_SECRET_KEY, G5_S3_BUCKET_NAME);

    add_event('s3_extend_uploaded_file', 's3_uploaded_file', 10, 2);
    add_event('s3_extend_delete_file', 's3_delete_file', 10, 1);
    add_event('s3_extend_delete_thumbnail', 's3_delete_thumbnail', 10, 2);
    add_event('s3_extend_delete_qa_thumbnail', 's3_delete_qa_thumbnail', 10, 1);
    add_event('s3_extend_delete_editor_thumbnail', 's3_delete_editor_thumbnail', 10, 1);

    function s3_uploaded_file($tmp_file, $dest_file) {
        global $g5;

        if(isset($g5['s3']) == false) return false;

        $result = $g5['s3']->uploadFile($tmp_file, $dest_file);

        return $result;
    }

    function s3_delete_file($delete_file) {
        global $g5;

        if(isset($g5['s3']) == false) return false;

        if( file_exists($delete_file) ){
            unlink($delete_file);
        }
    }

    function s3_delete_thumbnail($bo_table, $file) {
        global $g5;

        if(isset($g5['s3']) == false) return false;
        if(!$bo_table || !$file) return false;

        $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
        $files = $g5['s3']->glob($g5['s3']->getPath().'/'.G5_DATA_DIR.'/file/'.$bo_table.'/thumb-'.$fn.'*');

        if (is_array($files)) {
            foreach ($files as $filename)
                unlink($filename);
        }
    }

    function s3_delete_qa_thumbnail($file) {
        global $g5;

        if(!$file)
            return;

        $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
        $files = $g5['s3']->glob($g5['s3']->getPath().'/'.G5_DATA_DIR.'/qa/thumb-'.$fn.'*');
        
        if (is_array($files)) {
            foreach ($files as $filename)
                unlink($filename);
        }
    }

    function s3_delete_editor_thumbnail($contents) {
        global $g5;
        
        if(isset($g5['s3']) == false) return false;
        if(!$contents) return false;
    
        // run_event('delete_editor_thumbnail_before', $contents);

        // $contents 중 img 태그 추출
        $matchs = get_editor_image($contents, false);

        if(!$matchs) return;

        for($i=0; $i<count($matchs[1]); $i++) {
            // 이미지 path 구함
            $imgurl = @parse_url($matchs[1][$i]);
            $srcfile = $g5['s3']->getPath().$imgurl['path'];
            if(!preg_match('/(\.jpe?g|\.gif|\.png|\.webp)$/i', $srcfile)) continue;
            $filename = preg_replace("/\.[^\.]+$/i", "", basename($srcfile));
            $filepath = dirname($srcfile);
            // s3에서 glob이 적용안되는 관계로 s3 class 내에 glob 관련 함수 작성으로 적용
            $files = $g5['s3']->glob($filepath.'/thumb-'.$filename.'*');

            // 기존에 에디터로 올라간 파일의 삭제코드가 존재하지 않음으로 인해 해당코드 추가
            @chmod($srcfile, G5_FILE_PERMISSION);
            @unlink($srcfile);

            if (is_array($files)) {
                foreach($files as $filename)
                    unlink($filename);
            }
        }

        // run_event('delete_editor_thumbnail_after', $contents, $matchs);
    }
}
