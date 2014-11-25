###############################################################################
# Read and check array data and define repositories					          #
###############################################################################

# This script is called only from the WEBPORTAL 

###############################################################################
# Set directories 		                          				              #
###############################################################################

SCRIPT.DIR <- getwd()
WORK.DIR <- refName # directory where the results are computed
dir.create(WORK.DIR)
DATA.DIR <- "DATA.DIR" # CEL files are 'ReadAffy' then the repository is deleted

correctDIR <- function(d) { 
  lastChar <- substr(d,nchar(d),nchar(d))
  if((lastChar != "/") && (lastChar != "\\")) d <- paste(d,"/",sep="")
  return(d)
}
if(exists("DATA.DIR")) DATA.DIR <- correctDIR(DATA.DIR)
if(exists("SCRIPT.DIR")) SCRIPT.DIR <- correctDIR(SCRIPT.DIR)
if(exists("WORK.DIR")) WORK.DIR <- correctDIR(WORK.DIR)
DESC.DIR <- SCRIPT.DIR

setwd(WORK.DIR)
dir.create(DATA.DIR) # DATA.DIR is a relative path name

###############################################################################
# Unzip CEL files		                          				              #
###############################################################################

setwd(SCRIPT.DIR)
print(paste("Zip file: ",rawdataZip))
print(paste("Workdir: ",WORK.DIR))
unzip(rawdataZip, exdir = WORK.DIR) 

###############################################################################
# Copy CEL files in DATA.DIR and remove files from WORK.DIR					  # 
###############################################################################
# The content of the Zip file is verified:
setwd(WORK.DIR)
# Case 1: the .CEL files are in WORK.DIR : we move them in the DATA.DIR file
celfile <- list.files(pattern = ".CEL",ignore.case = TRUE)
if(length(celfile) > 0 ){
	listfile <- list.files()
	file.copy(listfile, DATA.DIR)
	unlink(listfile)    
	setwd(DATA.DIR)
}else{  
# Case 2: no .CEL files were found
	stop("Execution halted! CANNOT FIND CEL FILES IN THE INPUT ZIP FILE!") 
}

print("Raw data ready to be loaded in R")

###############################################################################
# Load array data in R                          						      #
###############################################################################
#load the data
require("affy", quietly = TRUE)
rawData <- ReadAffy()

###############################################################################
# Clean space if the usage is the webportal        						      #
###############################################################################
  setwd(SCRIPT.DIR)
  setwd(WORK.DIR)
  unlink(DATA.DIR, recursive = TRUE)
  rm(DATA.DIR,rawdataZip,listfile) 

###############################################################################
print("Raw data have been loaded in R")
setwd(SCRIPT.DIR)
