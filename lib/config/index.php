<?php

function getConfig($varName){
	global ${'CONFIG_'.$varName};
	return ${'CONFIG_'.$varName};
}


