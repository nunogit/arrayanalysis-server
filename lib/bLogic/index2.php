<?php

/*switch array*/

$switch = array(
	"boxplot" => "H",	//boolean
	"sampleprep" => "s", 	//boolean
	"ratio" => "r",		//boolean
	"degplot" => "e",  	//boolean
	"hybrid" => "h",  	//boolean
	"bgplot" => "b",  	//boolean
	"percpres" => "p",  	//boolean
	"pmacalls" => "P",  	//boolean
	"posnegdistrib" => "n", //??
	"controlplot" => "H", 	//boolean
	"scalefact" => "f", 	//boolean
	"boxplotraw" => "x", 	//boolean
	"boxplotnorm" => "X", 	//boolean
	"densityraw" => "y", 	//boolean
	"densitynorm" => "Y", 	//boolean
	"maraw" => "k", 	//boolean
	"manorm" => "K", 	//boolean
	"maoption1" => "j",  	// "group or dataset"
	"layoutplot" => "F", 	//boolean
	"posnegcoi" => "N", 	//boolean
	"spatialimage" => "R",  //boolean
	"plmimage" => "W", 	//boolean
	"nuse"     => "u",	//boolean
	"rle"	   => "a",	//boolean
	"correlraw"=> "c",	//boolean
	"correlnorm"=> "C",	//boolean
	"pcaraw" => "t",	//boolean
	"pcanorm" => "T", 	//boolean
	"clusterraw" => "o",	//boolean
	"customcdf" => "l",	//boolean
	"clusternorm" => "O",	//boolean
	"clusteroption1" => "v", //spearmanm, pearson, euclidean
	"clusteroption2" => "w", //ward, single complete, average, mcquitty, median, centroid
	"normmeth" => "z", //RMA GCRMA  PLIER NONE
	"normoption1" => "J", //group, dataset
	"cdftype" => "L", //default is ENGS, others ENTREZG,  (check doc)
	"species" => "S" // check list and dependency
);


function aa_convertparameter($elem){
	global $switch;
	

	if(is_object($elem)){
		$ret = "";
		foreach($elem as $id => $val)
			$ret =  "-".$switch[$id]. $val;
		return $ret;
	}

	return "-".$switch[$elem];
}
