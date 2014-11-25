<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


/*        function logit2($content){
                $content = __FILE__ . " " . date('m/d/Y h:i:s a', time()) . " - " .  $_SERVER['REMOTE_ADDR'] . " - " . $content . "\n";
                file_put_contents ( "log/log.txt" , $content, FILE_APPEND);
        }*/


        function logit($content, $file=''){
		if(strlen($file)==0) $file = __FILE__;
                $content = date('m/d/Y h:i:s a', time()) . " - " .  $_SERVER['REMOTE_ADDR'] . " - " . $content . "\n";
    //            $content = "File " . __FILE__ . " " . date('m/d/Y h:i:s a', time()) . " - " .  $_SERVER['REMOTE_ADDR'] . " - " . $content . "\n";

		$file = fopen("log/log.txt","a+");
		fwrite($file, $content);
		fclose($file);		
	}

