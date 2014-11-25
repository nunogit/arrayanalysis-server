<?php
require __DIR__ . '/index2.php';


/*
type: raw, affy, xxx
*/

function submitData($filename, $type){
  //CREATE FILE REF NAME (dataname_date)
  $date = date('Y-m-d_H-i_s');
  $REF_NAME = $dataname."_". $data;
  $handler = $REF_NAME . "_data";

if (isset($_FILES[$cellfilename])) {
  if ($_FILES[$cellfilename]['error']) {
      switch($_FILES[$cellfilename]['error']){
       case 1: // UPLOAD_ERR_INI_SIZE
       $err.="The file size is over the authorized limit (php.ini limit)\n";
       throw new Exception("", 400);
       break;
       case 2: // UPLOAD_ERR_FORM_SIZE
       $err.="The file size is over the authorized limit (MAX_FILE_SIZE = $MAX_FILE_SIZE)\n";
       break;
       case 3: // UPLOAD_ERR_PARTIAL
       $err.="The file is corrupted (file transfer was interrupted)\n";
       break;
//       case 4: // UPLOAD_ERR_NO_FILE
//       $err.="A zip file containing the .CEL files is required !\n";
//       break;
     }
  } else {
    $datafile=$_FILES[$cellfilename]['name'];
    $datafile=preg_replace("` `", "_", $datafile);
    $datafile=preg_replace("`\)`", "", $datafile);
    $datafile=preg_replace("`\(`", "", $datafile);
    if(preg_match("`\.zip$`i",$datafile)){
      $dataname=preg_replace("`\.zip$`i", "", $datafile);
      $datafileTmp=$_FILES[$cellfilename]['tmp_name'];
    }else{
      $err.="The archive file you browsed ($datafile) was not recognized as a zip file.\n";
    }
  }
}

  if($type!='cleandata'){
	$subdatafolder = "";
  } else $subdatafolder = "";

  //also copy the archive in the RES dir for later usage
  $TMP_DIR = $WEB_RES_DIR.$REF_NAME."_data/";
  @mkdir($TMP_DIR.$subdatafolder, "0777", true);
  logit("MKDIR $TMP_DIR".$subdatafolder);

  $temp_name = $REF_NAME.".zip";
  $COPYcommand="cp ".$datafileTmp." ".$TMP_DIR.$temp_name;
  exec($COPYcommand);
  logit("EXEC $COPYcommand");

  //SET REF_NAME TO DISK - improve solution // TMP_DIR is not temporry, name induces in error
  file_put_contents($TMP_DIR."/refname.meta", $REF_NAME);

  $timestamp = date(DATE_RFC2822);
  aa_setMeta($handler, "resources/rawdata/$REF_NAME/file", $tempname);
  aa_setMeta($handler, "resources/rawdata/$REF_NAME/systemdescription", "$type (system time stamp $timestamp)");

  //replace datafileTmp with the name including the new path
  $olddatafileTmp = $datafileTmp;
  $datafileTmp=$TMP_DIR.$temp_name;

  $err = aa_unzipContent($handler, $olddatafileTmp, $TMP_DIR, $REF_NAME);
  //var_dump($err);

  if($err!=""){
	  //TODO clean up file
	  $errorList = explode("\n",$err);
	  return array("error"=>$errorList);
  }

}

function submitData_affy($cellfilename){
// INPUT ZIP FILE

/*initializations - avoid notice warnings*/
$err = '';
$dataname = '';
$WEB_RES_DIR = getConfig('DATA_DIR');

logit("DUMPING VAR " .$WEB_RES_DIR);

$datafileTmp = '';

//var_dump($_FILES[$cellfilename]);



return array("success"=>$handler);
}


function getFileNamesFromRawData($handler){
	$res = aa_checkHandler($handler);	
	if(sizeof($res)==0){
		 return array( "error:  handler does not exist");
	}

	/*TODO: check if file exists, open in exclusive lock for concurrency*/
	$data = file_get_contents(getConfig("DATA_DIR").$handler."/filenames.meta");
	$ret = explode("\n",$data);

	//remove last element which is empty due to the explode
	array_pop($ret);
	return $ret;	 
}

function getArrayInformation($handler){
	$res = aa_checkHandler($handler);	
	if(sizeof($res)==0) return array( "error");

	// returns basic information about the array
	/*TODO: check if file exists, open in exclusive lock for concurrency*/
//	$data = file_get_contents(getConfig("DATA_DIR").$handler."/chipinformation.meta");
//	$ret = explode("\n",$data);

  	$atype = aa_getMeta($handler, "chipinformation/infered/atype");
	$chiptype = aa_getMeta($handler, "chipinformation/infered/chiptype");
	$species = aa_getMeta($handler, "chipinformation/infered/species");

// 	return array("atype"=>$ret[0], "chiptype"=> $ret[1], "species"=>$ret[2]);
 	return array("atype" => $atype, "chiptype" => $chiptype, "species" => $species);
}

function cleanData($handler, $method){
		
}
	
function getCleanData($handler){
	 // returns the clean data
}

function submitCleanData($data){
	// returns handler (pointer)
}

function setGroups($handler, $filenames, $samplenames, $groupnames){
	/*the nr of groups has to be the same as samples? validate!*/ 
	$dataFile = json_decode($filenames); // explode(",", $dataFile);
	$sourceName = json_decode($samplenames); //explode(",", $sourceName);
	$factorValue = json_decode($groupnames); //explode(",", $factorValue);

	if(sizeof($dataFile) != sizeof($sourceName) || sizeof($dataFile) != sizeof($factorValue)){
		logit("Error setting groups: file is too small?");
		return "Error: file is too small";
	}
	
	$output = "ArrayDataFile\tSourceName\tFactorValue\n";
	for($i = 0; $i < sizeof($dataFile); $i++){
		$output .= $dataFile[$i] . "\t" . $sourceName[$i] . "\t" . $factorValue[$i] . "\n";
	}
	logit("Setting groups: ". $output . " -- " . $filenames );
	file_put_contents(getConfig("DATA_DIR").$handler."/groups.meta", $output);

	return "ok";
}


function setGroupsFromFile($handler, $columns, $file){
	
}

function listArrayResources($handler){

	

}

function getArrayQCReport($handler, $sampleqc="", $signalqc="", $spatialqc="", $correlqc="", $normalization=""){
	$arrayDescFile = "groups.meta";
	$datadir = getConfig("DATA_DIR");
	$REF_NAME = aa_getRefName($handler);
	$zipfile =  $REF_NAME.".zip";
	$R_SCRIPT_DIR = getConfig("R_SCRIPT_DIR")."/affy/";


	$aSampleqc = json_decode($sampleqc);
	$aSignalqc = json_decode($signalqc);
	$aSpatialqc = json_decode($spatialqc);
	$aCorrelqc = json_decode($correlqc);
	$aNormalization = json_decode($normalization);

	//$resultsLocation = getConfig("R_SCRIPT_DIR")."/".aa_getRefName($handler);
	$resultsLocation = $datadir."/".$handler."/".$REF_NAME;

	//aa_getPDF($handler, $resultsLocation);
	//return;
	
	//echo "a";	//var_dump($aSampleqc);	//echo "b";	//var_dump($aSignalqc);	//echo "c";	//var_dump($aSpatialqc);	//echo "d";	//var_dump($aCorrelqc);	//echo "e";	//var_dump($aNormalization);

	if(strlen(aa_checkHandler($handler))>0) return array("error"=>"handler is not valid");
	if(strlen(aa_checkDescFile($handler, $arrayDescFile))>0) return array("error"=>"description file is not valid. Did you set one?");
	
	$switchlist = "";

	/*generate switches*/
	foreach($aSampleqc as $elem)
		$switchlist .=  ", \\\"".aa_convertparameter($elem)."\\\"";
	foreach($aSignalqc as $elem)
		$switchlist .=  ", \\\"".aa_convertparameter($elem)."\\\"";
	foreach($aSpatialqc as $elem)
		$switchlist .=  ", \\\"".aa_convertparameter($elem)."\\\"";
	foreach($aCorrelqc as $elem)
		$switchlist .=  ", \\\"".aa_convertparameter($elem)."\\\"";
	foreach($aNormalization as $elem)
		$swicthlist .=  ", \\\"".aa_convertparameter($elem)."\\\"";
	
	logit("switch list generated: ".$switchlist);	
	logit("copying ". "$datadir/$handler/$arrayDescFile TO " .$R_SCRIPT_DIR."/".$arrayDescFile);
        
	copy("$datadir/$handler/$arrayDescFile", $R_SCRIPT_DIR."/".$arrayDescFile);
	//echo "copying -> $datadir/$handler/$arrayDescFile <- " . $R_SCRIPT_DIR."/".$arrayDescFile . "<- ";
	//$Rcommand="affyAnalysisQC(\\\"-d$datadir/$handler/$zipfile\\\", \\\"-g$desc\\\" $switchlist, \\\"-A$REF_NAME\\\")";
        $Rcommand="affyAnalysisQC(\\\"-d$datadir/$handler/$zipfile\\\", \\\"-g$arrayDescFile\\\" $switchlist, \\\"-A$resultsLocation\\\")";

	

	//$Rcommand="affyAnalysisQC(\\\"-d$datadir/$handler/$zipfile\\\", $desc $ord $sample $hybrid $signal $maplot 
        //        $spatial $nuserle $correl $pannotation \\\"-z$normMeth\\\", \\\"-J$normOption1\\\", 
        //        \\\"-A$REF_NAME\\\")";


        logit("Generated Rcommand = $Rcommand");
	//echo $Rcommand . "<br><br>";

        $launchfile="launchFile_".$REF_NAME.".R";

        $command="cd $R_SCRIPT_DIR;\n echo \"source(\\\"affyAnalysisQC_web.R\\\")\n$Rcommand\n\" > $launchfile";

	//echo "<br>".$command . "<br><br>";
        logit("Running $command");
        $result = array();
        exec($command, $result);
        //var_dump($result);

        $command="cd $R_SCRIPT_DIR; \n R --slave --no-restore --file=\"$launchfile\"";
        $result = array();
        $stream = exec($command, $result);
        logit("EXEC ".$command, __FILE__);
	echo $command . "<br><br>";


	zipData(aa_getRefName($handler).".zip", aa_getRefName($handler), getConfig("R_SCRIPT_DIR"), $arrayDescFile, $resultsLocation );

	$format = $_REQUEST["format"];
	$format = "application/pdf";	
	
	//echo $resultsLocation;
	
	aa_getPDF($handler, $resultsLocation);


	if($format=="application/pdf"){
	}

        /* notes: $desc or $descfilem, and should it have the full path? */

        //aa_runQCReport($launchfile, $REF_NAME, $desc, '','');
        // * * _ _ _
        //zipData(aa_getRefName($handler).".zip", aa_getRefName($handler), getConfig("R_SCRIPT_DIR"), $arrayDescFile, getConfig("R_SCRIPT_DIR")."/".aa_getRefName($handler) );

}

function aa_getPDF($handler, $resultsLocation){
		//return;
		
		//header('Content-type: application/pdf');
		//header("Content-type: text/plain");
		$filename = $resultsLocation."/".aa_getRefName($handler).".pdf";
		echo "reading".$filename;
		return;

		$fh = fopen($filename,'rb');
		while (!feof($fh)) {
		  // read one segment of 52 bytes
		  //$s = fread($fh, 1);
		  echo fgetc($fh);
		  //$stream_meta_data = stream_get_meta_data($fh); //Added line
                  //if($stream_meta_data['unread_bytes'] <= 0) break; //Added line
		  //print($s);		 		  
		}
		fclose($fh);
}


function getQCReport($handler, $arrayDescFile, $SampleQuality="", $HybridQuality="", $SignalDistribution="", $IDbias="", $MAOption1="", $spatialBias="", $PShomegeneity="", 
	$clustoption1="", $clustoption2="",  $normMeth="", $normOPtion1="", $Annotation="", $CDFtype="", $species="", 
	$ArrayCorrelation="", $reOrder=""){

	if(strlen(aa_checkHandler($handler))>0) return array("error"=>"handler is not valid");
	if(strlen(aa_checkDescFile($handler, $arrayDescFile))>0) return array("error"=>"description file is not valid. Did you set one?");
	

	$datadir = getConfig("DATA_DIR");

	$zipfile = ''; //get from handler
	$dev = true;

	/*maoption1*/

	//zipfile: Input;
	$REF_NAME = aa_getRefName($handler); // legacy variable
	$desc = aa_getDesc($arrayDescFile, $datadir, $handler);
	$ord  = aa_getOrd($reOrder);
	$sample = aa_getSample($SampleQuality);
	$hybrid = aa_getHybrid($HybridQuality);
	$signal = aa_getSignal($SignalDistribution);
	$maplot = aa_getMaplot($IDbias);
	$spatial = aa_getSpatial($spatialBias);
	$nuserle = aa_getNuserle($PShomogeneity);
	$correl = aa_getCorrel($ArrayCorrelation);
	$pannotation = aa_getAnnotation($Annotation);

	$zipfile =  $REF_NAME.".zip";

	$R_SCRIPT_DIR = getConfig("R_SCRIPT_DIR");


	$err = aa_validateData($Annotation, $CDFtype, $species, $normMeth, $PMAcalls);

	if($err!=""){
		echo "--------------------------------- ".$err;
		//TODO treat the error
		return;
	}

//	$Rcommand="affyAnalysisQC(\\\"-d$zipfile\\\", $desc $ord $sample $hybrid $signal $maplot 
//                $spatial $nuserle $correl $Annotation \\\"-z$normMeth\\\", \\\"-J$normOption1\\\", 
//                \\\"-A$REF_NAME\\\")";
	
//	$Rcommand="affyAnalysisQC(\\\"-d$datadir/$handler/$zipfile\\\", A$desc B$ord C$sample D$hybrid E$signal F$maplot 
//                G$spatial $nuserle $correl $Annotation \\\"-z$normMeth\\\", \\\"-J$normOption1\\\", 
//                \\\"-A$REF_NAME\\\")";

	$Rcommand="affyAnalysisQC(\\\"-d$datadir/$handler/$zipfile\\\", $desc $ord $sample $hybrid $signal $maplot 
                $spatial $nuserle $correl $pannotation \\\"-z$normMeth\\\", \\\"-J$normOption1\\\", 
                \\\"-A$REF_NAME\\\")";


        if ($usage == "dev" || true) print "<p>$Rcommand</p>";

        $launchfile="launchFile_".$REF_NAME.".R";

        $command="cd $R_SCRIPT_DIR;\n echo \"source(\\\"affyAnalysisQC_web.R\\\")\n$Rcommand\n\" > $launchfile";


	echo "<p>$command</p>";

	$result = array();
	exec($command, $result);
	//var_dump($result);

        $settings.= "\n\nR command:\n". preg_replace("`\\\\\"`", "'",$Rcommand)."\n\n";
        $settings = urlencode($settings);

        $command="cd $R_SCRIPT_DIR; \n R --slave --no-restore --file=\"$launchfile\"";
	echo $command;
	$result = array();
	$stream = exec($command, $result);
	logit("EXEC ".$command, __FILE__);

	/* notes: $desc or $descfilem, and should it have the full path? */

	aa_runQCReport($launchfile, $REF_NAME, $desc, '','');
	// * * _ _ _
	zipData(aa_getRefName($handler).".zip", aa_getRefName($handler), getConfig("R_SCRIPT_DIR"), $arrayDescFile, getConfig("R_SCRIPT_DIR")."/".aa_getRefName($handler) );
	


	//$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        //Why so many streams and what to do with them

        //$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        // Enable blocking for both streams
        //stream_set_blocking($errorStream, true);
        //stream_set_blocking($stream, true);

        // Streams content
        //$stdio = stream_get_contents($stream);
        //$stderr = stream_get_contents($errorStream);

        // Close the streams       
        //fclose($errorStream);
        //fclose($stream);


}


function deleteData($handler){

}



/*support functions*/

function aa_unzipContent($handler, $olddatafileTmp, $tmp_dir, $ref_name){
	// initialize variables
	$R_SCRIPT_DIR = getConfig("R_SCRIPT_DIR") . "/affy/" ;
	$TMP_DIR = $tmp_dir;
	$REF_NAME = $ref_name;
	$err = ''; $warning= '';
	$filenames = array();
	$datafile = $ref_name; //TODO - improve... this is just an euristic for the filename

        // DEFINE ARRAY TYPE, ARRAY NB, CDF and SPECIES
        //List the names of the files in the zip file
        $UNZIPcommand = "unzip -l ".$olddatafileTmp." | grep \" ..:.. \" | sed 's/.*..:.. *//'";
        exec($UNZIPcommand, $FILELIST);
        logit("EXEC $UNZIPcommand", __FILE__);

        // Verify the list (keep only CEL files and check the list of CEL file is not empty):   
        $i=0;
        $r=1;
        $repo="";
        $UNZIP=array();
        foreach($FILELIST as $value) {
                if(preg_match("`\.CEL(.gz)*`i",$value)){    // For now, this test is not case sensitive...
			logit("CHECKING FILE: $value");
                        $UNZIP[$i]=$value;
                        if(preg_match("`\.GZ`",$value)){ 
				$err .= "File $value cannot be recognized as a CEL file.\n";
				logit("File $value cannot be recognized as a CEL file");
				$err="Please check the file name (replace \".GZ\" by \".gz\")\n";

			}
                        if(preg_match("`(.*)[/\\\]`",$value,$match_repo)) {
                                if($repo==$match_repo[1])$r++;
                                $repo=$match_repo[1];
                        }
                }else{
                        $warning .= "Element $value found in $datafile not recognized as a CEL file. <br/>";    
                }
                $i++;
        }
    if(count($UNZIP) == 0){
        $err.="No .CEL file was found in the zip file $datafile\n";
    }

        if($repo!=""){
                if($r == count($UNZIP)){
                        $err.="CEL files should be zipped directly in $datafile (extra folder \"$repo\" was found)\n";
                }else{
                        $err.="CEL files should be zipped directly in $datafile (extra folders were found)\n";
                }
        }       

	/*NO ERRORS, let's write the file names*/
	if($err==""){
		$filenames='';
		foreach($FILELIST as $file) $filenames .= $file. "\n";
		file_put_contents($TMP_DIR."/"."filenames.meta", $filenames);
	}
	/*NO ERRORS, lets go for deep inspection*/
        if($err=="") {  

                // First file
                $first_file = $UNZIP[0];
                $tmp_file =  preg_replace("/\(/","\(",$first_file); 
                $tmp_file =  preg_replace("/\)/","\)",$tmp_file); 

                // Unzip the 1st file in the RES folder ($TMP_DIR, created in POST_zip_file.php):
                $UNZIPcommand = "unzip $olddatafileTmp $tmp_file -d $TMP_DIR 2>&1";

                $out = array();         
                exec($UNZIPcommand, $out);
                logit("EXEC $UNZIPcommand", __FILE__);
                $out = $out[1];

                if(preg_match("`skipping(.*)`",$out,$match)){// Ex: Array ( [0] => Archive: /tmp/phpWCiBBc [1] => skipping: 101641-009.CEL unsupported compression method 98 ) 
                        $err.="Could not open the zip file (skipping".$match[1].").
                        You may try to update your compression program.\n";
                }
        }
	/*Still no errors*/
        if($err=="") {

                // Prepare name for the files to be stored on the calc server
                $first_fileServ = "firstarray_".$REF_NAME."_".$first_file; // end by first_file to keep the extension
                $launchServ = "launch1starray_".$REF_NAME.".R";

                // Connection to Calculation Server
                ////include("connect_server.inc.php"); // already connected by POST_zip_file.php...

                // Copy the first array on the Calculation Server
                exec("cp ".$TMP_DIR.$first_file." ".$R_SCRIPT_DIR.$first_fileServ);
		logit("CP: "."cp ".$TMP_DIR.$first_file." ".$R_SCRIPT_DIR.$first_fileServ);
                ////logit("SSH2SCP ".  $TMP_DIR.$first_file  . " " . $R_SCRIPT_DIR.$first_fileServ);

                //Remove the single extracted array from the webserver
                unlink($TMP_DIR.$first_file);

                // Launch check_chiptype analysis 
                $Rcommand = "cd $R_SCRIPT_DIR \n echo \"source(\\\"chiptype.R\\\")\n";
                $Rcommand.= "check_chiptype(\\\"".$first_fileServ."\\\")\n\" > \"".$launchServ."\" \n";
                $Rcommand.= "R --slave --no-restore --file=\"".$launchServ."\"";

		$settings.= "\n\nR command:\n". preg_replace("`\\\\\"`", "'",$Rcommand)."\n\n";
        	$settings = urlencode($settings);


                //echo $Rcommand;
                logit("EXEC $Rcommand", __FILE__);
		$results = array();
                $result = exec($Rcommand, $results);
//		var_dump($result);
		//var_dump($results);
                //Get the information on array type and cdf name
                //$results=explode('[1]',$results);
                $aType=$results[1];
                $chipName=$results[2];
                $species=$results[3];
		
	//		var_dump($results);                

		$nArrays = count($UNZIP);

                //Remove the temporary files
                $CLEANcommand = "cd $R_SCRIPT_DIR \n rm \"".$first_fileServ."\" \n rm \"".$launchServ."\" \n";
                logit("EXEC $CLEANcommand");
                $CLEANstream =exec($CLEANcommand);

                $example = "FALSE";

		/*fill array metadata*/
		$chipInformation=substr($aType, 5, -1)."\n".substr($chipName,5, -1)."\n".substr($species,5, -1);
		//logit("Result " . $chipInformation);
		file_put_contents($TMP_DIR."/"."chipinformation.meta", $chipInformation);

		aa_setMeta($handler, "chipinformation/infered/atype",    substr($aType, 5, -1));
		aa_setMeta($handler, "chipinformation/infered/chiptype", substr($chipName,5, -1));
		aa_setMeta($handler, "chipinformation/infered/species",  substr($species,5, -1));
		aa_setMeta($handler, "chipinformation/atype",    substr($aType, 5, -1));
		aa_setMeta($handler, "chipinformation/chiptype", substr($chipName,5, -1));
		aa_setMeta($handler, "chipinformation/species",  substr($species,5, -1));

//		return $err;
	}

	logit("ERRORS: ".$err);
	logit("WARNING: ".$warning);
	return $err;

}

function listResources($handler){
	$resources = aa_getMeta($handler, "resources");
	
	return $resources;
}

function zipData($zipname, $dataname, $R_SCRIPT_DIR, $descfile, $WORK_DIR){

	$reportname = $dataname.".pdf";

	$ZIPcommand = "cp $R_SCRIPT_DIR"."$descfile $WORK_DIR"."description_".$dataname.".txt \n ";
	$ZIPcommand .= "cd $WORK_DIR \n zip -R ".$zipname." *.png description_".$dataname.".txt PMAtable.txt";
	exec($ZIPcommand);
	logit("EXEC: zipping report content: ".$ZIPcommand, __FILE__);

	$PDFcommand = "cd $WORK_DIR \n convert -page A4 `ls --time-style=full-iso --sort=time -r *.p*` ".$reportname." \n";
	$output = array();
	echo $PDFcommand;
	exec($PDFcommand, $output);
	var_dump($output);
	logit("EXEC: concatening PDF: ".$PDFcommand, __FILE__);

	$REScommand = "ls $WORK_DIR \n";
	$filelist = exec($REScommand); //check how to get it
	logit("EXEC: list files: ".$REScommand, __FILE__);



	/*foreach ($filelist as $temp){
        foreach ($temp as $val){
                //if(preg_match("`\.png$`i",$val)){
                //      $val = preg_replace("/(\s|\n)/","",$val); 
                //      ssh2_scp_recv ($connection, "$WORK_DIR"."$val", "results/$LOCALworkdir/$val");
                //}
                if(preg_match("`\.zip$`i",$val)){
                        $zip = preg_replace("/(\s|\n)/","",$val); 
                        ssh2_scp_recv ($connection, "$WORK_DIR"."$val", "$RES_DIR"."$zip");
                        logit("EXEC $WORK_DIR"."$val ". $RES_DIR . " " . $zip , __FILE__);
                }
                if(preg_match("`\.pdf$`i",$val)){
                        $report = preg_replace("/(\s|\n)/","",$val); 
                        ssh2_scp_recv ($connection, "$WORK_DIR"."$val", "$RES_DIR"."$report");
                        logit("EXEC $WORK_DIR"."$val ". $RES_DIR . " " . $report , __FILE__);
                }
                if(strpos($val,$normDataname)){
                        $normData = preg_replace("/(\s|\n)/","",$val); 
                        //print "TXT: $normData <br/>";
                        ssh2_scp_recv ($connection, "$WORK_DIR"."$val", "$RES_DIR"."$normData");
                        logit("EXEC $WORK_DIR"."$val ". $RES_DIR . " " . $normData , __FILE__);
                }
        	}
	}*/


	/*clean up files - locate that on run.php under lien 147 */

	

}

/*
*  $reOrder: boolean
*/

function aa_getOrd($reOrder){
	if($reOrder)
		return $ord="\\\"-G\\\",";
	else
		"";
}


function aa_getAnnotation($Annotation){
      if($Annotation=="-l" && (($normMeth!="none" && $normMeth!="") || $PMAcalls=="-P")){
                print "<br />You choose to use a <u>custom annotation file (CDF):</u> $CDFtype applied to $species.<br/>";
                $settings.= "\nYou choose to use a custom annotation file (CDF): $CDFtype applied to $species.";
                $Annotation ="\\\"$Annotation\\\", \\\"-L$CDFtype\\\", \\\"-S$species\\\",";
        } else {
                $Annotation ="";
        }
	return $Annotation;
}

/*
*  $arrayDescFile: string
*/

function aa_getDesc($arrayDescFile, $datadir, $handler){

	 if(sizeof($arrayDescFile) > 0){
	 echo "copying ". "$datadir/$handler/$arrayDescFile TO " .getConfig("R_SCRIPT_DIR")."/".$arrayDescFile;
	 copy("$datadir/$handler/$arrayDescFile", getConfig("R_SCRIPT_DIR")."/".$arrayDescFile);
		 return "\\\"-g".$arrayDescFile."\\\",";
//		 return "\\\"-g".$datadir."/".$handler."/".$arrayDescFile."\\\",";
	} else 
		return "";
}

function aa_getSample($SampleQuality){
	$SampleQuality = explode(" ", $SampleQuality);

       for ($i = 0; $i < count($SampleQuality); $i++){
                $temp=$SampleQuality[$i];
                $sample.="\\\"".$temp."\\\", ";
        }
	return $sample;
}


function aa_getHybrid($HybridQuality){
	$HybridQuality = explode(" ", $HybridQuality);
        $hybrid="";
        for ($i = 0; $i < count($HybridQuality); $i++){
                $temp=$HybridQuality[$i];
                $hybrid.="\\\"".$temp."\\\", ";
        }

	return $hybrid;
}


function aa_getSignal($SignalDistribution){
	$SignalDistribution = explode(" ", $SignalDistribution);
	$signal = "";
	 for ($i = 0; $i < count($SignalDistribution); $i++){
                $temp=$SignalDistribution[$i];
                $signal.="\\\"".$temp."\\\", ";

	 }
	 return $signal;
}

function aa_getMaplot($IDbias){
     $IDbias = explode(" ", $IDbias);
     $maplot = "";
     for ($i = 0; $i < count($IDbias); $i++){
                $temp=$IDbias[$i];
                $maplot.="\\\"".$temp."\\\", ";
     }

     return $maplot;
}


function aa_getSpatial($spatialBias){
	$spatialBias = explode(" ", $spatialBias);
	$spatial="";
        for ($i = 0; $i < count($spatialBias); $i++){
                $temp=$spatialBias[$i];
                $spatial.="\\\"".$temp."\\\", ";
        }  
	return $spatial;
}

function aa_getNuserle($PShomogeneity){
	$PShomogeneity = explode(" ", $PShomogeneity);
	$nuserle = "";
        for ($i = 0; $i < count($PShomogeneity); $i++){
                $temp=$PShomogeneity[$i];
                $nuserle.="\\\"".$temp."\\\", ";
        }
	return $nuserle;
}

function aa_getCorrel($ArrayCorrelation){
	$ArrayCorrelation = explode(" ",$ArrayCorrelation);
	$correl="";
  	$clust=FALSE;
        for ($i = 0; $i < count($ArrayCorrelation); $i++){
                $temp=$ArrayCorrelation[$i];
                $correl.="\\\"".$temp."\\\", ";
		
		/*check this to clean, for now it stays because we need it*/
                switch($temp){
                        case "-c": print "\t Correlation plot (raw),";
                                                $settings.=" Correlation plot (raw),"; break;
                        case "-C": print "\t Correlation plot (norm),";
                                                $settings.=" Correlation plot (norm),"; break;
                        case "-t": print "\t PCA analysis (raw),";
                                                $settings.=" PCA analysis (raw),"; break;
                        case "-T": print "\t PCA analysis (norm),";
                                                $settings.=" PCA analysis (norm),"; break;
                        case "-o": print "\t Hierachical clustering (raw),"; $clust=TRUE;
                                                $settings.=" Hierachical clustering (raw),"; break;
                        case "-O": print "\t Hierachical clustering (norm)."; $clust=TRUE;
                                                $settings.=" Hierachical clustering (norm)."; break;
                } 
        }
        if($clust) {
                print " (Clusters computed $clustoption1 and $clustoption2).";
                $settings.= " (Clusters computed $clustoption1 and $clustoption2).";
                $correl.="\\\"-v$clustoption1\\\", \\\"-w$clustoption2\\\",";
                $testBool=1;
        }

	return $correl;
}



function aa_validateData($Annotation, $species, $CDFtype, $species, $normMeth, $PMAcalls){

	// TESTS FOR ANNOTATION (CDF)
	if($species!=null){
	        if(($Annotation=="-l") && (($species=="NA") || ($species=="/")) && ((($normMeth!="none") && ($normMeth!="")) || ($PMAcalls=="-P"))){
                	$err.="<p style=\"color:red\">You need to define a species when you choose to use a custom annotation</p>";
        	}
	}


	if($Annotation=="-l" && $species!="NA"){
	        if(($CDFtype=="ENSG" || $CDFtype=="ENST")&& ($species=="Ag" || $species=="Os" || $species=="Sp" || $species=="At")){
                	$err.="<p style=\"color:red\">Ensembl annotation does not exist for the species you selected. Please select another annotation type.</p>";
        	}
	        if(($CDFtype=="REFSEQ")&& ($species=="Ag" || $species=="Os" || $species=="Sp" || $species=="Sc" || $species=="Ss")){
        	        $err.="<p style=\"color:red\">RefSeq annotation does not exist for the species you selected. Please select another annotation type.</p>";
	        }
        	if(($CDFtype=="UG")&& ($species=="Ag" || $species=="Os" || $species=="Sp" || $species=="At" || $species=="Ce" || $species=="Sc")){
	                $err.="<p style=\"color:red\">UniGene annotation does not exist for the species you selected. Please select another annotation type.</p>";
        	}
	        if(($CDFtype=="VEGAT")&& !($species=="Cf" || $species=="Dr" || $species=="Hs" || $species=="Mm" || $species=="Sc")){
        	        $err.="<p style=\"color:red\">Vega-Transcript annotation does not exist for the species you selected. Please select another annotation type.</p>";
	        }
        	if(($CDFtype=="VEGAG")&& !($species=="Hs" || $species=="Mm")){
	                $err.="<p style=\"color:red\">Vega-Gene annotation only exists for Homo sapiens and Mus musculus. Please select another annotation type.</p>";
        	}
	        if(($CDFtype=="TAIRT" || $CDFtype=="TAIRG")&& !($species=="At")){
                	$err.="<p style=\"color:red\">The Arabidopsis Information Resource (TAIR) gives annotation only for Arabidopsis. Please select another annotation type.</p>";
        	}
	        if(($CDFtype=="MIRBASEF" || $CDFtype=="MIRBASEG")&& !($species=="Hs" || $species=="Mm" || $species=="Rn")){
                	$err.="<p style=\"color:red\">miRBase annotation only exists for Homo sapiens, Mus musculus and Rattus norvegicus. Please select another annotation type.</p>";
        	}
	}

	return $err;
}


function aa_getRefName($handler){
	$ret = file_get_contents(getConfig("DATA_DIR").$handler."/refname.meta");
	return $ret;
}




function aa_runQCReport($launchfile, $REF_NAME, $descfile, $email, $settings=''){

//$settings needed?
echo "<br><br>RUNNING";
echo  $launchfile." ".$REF_NAME." ".$descfile." ".$email." ".$settings;

$R_SCRIPT_DIR = getConfig("R_SCRIPT_DIR");

$dateVal = preg_replace('#^.*_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}_\d{2})$#','\1',$REF_NAME);
$dataname = preg_replace('#^(.*)_'.$dateVal.'$#','\1',$REF_NAME);

////////////////////////////////////////////////////////////////////////////////////
// Execute the analysis:
$command="cd $R_SCRIPT_DIR; \n R --slave --no-restore --file=\"$launchfile\"";


echo "<br>$command</br>";
$content = array();
$stream = exec($command,$content);	
var_dump($content);

echo "calling". $commnad;
logit("EXEC ".$command, __FILE__);

}





function aa_checkHandler($handler){
	/*sanitize handler and check if exists*/
	/*should be improved*/		
	if(trim(strlen($handler))==0){
		logit("handler is invalid (size 0)");
		return  "handler \"$handler\" does not exist";
	}
	if(is_dir(getConfig("DATA_DIR").$handler)){
		return "";
	}else{
		logit("handler $handler is invalid. Directory ". getConfig("DATA_DIR").$handler  . " does not exist");
		return "handler \"$handler\" does not exist";
	}
}

function aa_checkDescFile($handler, $datafile){
	/*sanitize handler and check if exists*/
	/*should be improved*/		
	if(trim(strlen($handler))==0) return  "description file \"$datafile\" does not exist";
	//echo "checking ".getConfig("DATA_DIR").$handler;
	if(is_file(getConfig("DATA_DIR").$handler."/".$datafile))
		return "";
	else
		return "description file \"$datafile\" does not exist";
}


function aa_setMeta($handler, $path, $data){

	      logit("Setting meta to $handler $path = $data" );

	      $rawmeta = @file_get_contents(getConfig("DATA_DIR").$handler."/meta");
	      if($rawmeta===false){ $rawmeta = ""; }

	      $meta = json_decode($rawmeta);
	      $aPath = explode("/", $path);
		
	      if(!is_object($meta)) $meta = new stdClass();
	      $currentStep = $meta;

	     for($i=0; $i < count($aPath); $i++){
		if(isset( $currentStep->$aPath[$i] )){
			if($i==count($aPath)-1) $currentStep->$aPath[$i] = $data;
				else if(!is_object($currentStep->$aPath[$i])){
						$currentStep->$aPath[$i] = new stdClass(); 
						$currentStep = $currentStep->$aPath[$i];
						}else{
							$currentStep = $currentStep->$aPath[$i];
						}
		} else  {

			if($i==count($aPath)-1){
					$elem = $aPath[$i];
					$currentStep->$elem = $data;
				}else{
					$currentStep->$aPath[$i] = new stdClass(); //just assign an empty object
					$currentStep = $currentStep->$aPath[$i];
				}
		}
	      }



	    $rawmeta = json_encode($meta);

	    file_put_contents(getConfig("DATA_DIR").$handler."/meta", $rawmeta, LOCK_EX);
}

function aa_getMeta($handler, $path){
	      $rawmeta = file_get_contents(getConfig("DATA_DIR").$handler."/meta");
	      $meta = json_decode($rawmeta);
	      $aPath = explode("/", $path);
		
	      $currentStep = $meta;


	      for($i=0; $i < count($aPath); $i++){
	    
		if($i==count($aPath)-1){ return  $currentStep->$aPath[$i];}
			else 
		if(isset($currentStep->$aPath[$i])){
			$currentStep = $currentStep->$aPath[$i];
		} else return null;
	      }
}
