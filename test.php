<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once('inclibs.php');

aa_setMeta("test","nuno/nun", Array("cxb","xxx"));

print_r(aa_getMeta("test","nuno/nun"));

die();
