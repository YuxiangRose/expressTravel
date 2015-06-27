<?php
	class Handler {

        private $path;
        private $fileName;
        private $fileType;
        private $systemName;
        private $airlineName;
        private $tickeNumebr;
        private $dateString;
        private $orderOfDay;
        private $dateOfFile;
        private $fileContent;

        public function __construct($path,$fileName){

            $this->path     = $path;
            $this->fileName = $fileName; 
            $this->parseFile($path, $fileName);
        }

        public function getPath(){
            return $this->path;
        }

        public function getFileName(){
            return $this->fileName;
        }

        public function getFileType(){
            return $this->fileType;
        }

        public function getSystemName(){
            return $this->systemName;
        }

        public function getAirlineName(){
            return $this->airlineName;
        }

        public function getTickeNumebr(){
            return $this->tickeNumebr;
        }

        public function getDateString(){
            return $this->dateString;
        }

        public function getOrderOfDay(){
            return $this->orderOfDay;
        }

        public function getDateOfFile(){
            return $this->dateOfFile;
        }

        public function getFileContent(){
            return $this->fileContent;
        }


        public function parseFile($path, $fileName){

            $fileLines = array();
            $typeLine   = array();
            $numberLine = array();
            $parsedLine = array();

            $typeNeedle = "1 OF 1";
            $numberNeedle = "PRI FF";

            //parse file name;
            $temp = preg_replace("/[^0-9]/", "", $fileName);
            
            $dateString = substr($temp, 0,8);
            $orderOfDay = substr($temp,8,2);

            $this->setDateString($dateString);
            $this->setOrderOfDay($orderOfDay);

            $date = new DateTime($dateString);
            $this->setDateOfFile($date->format('Y-m-d'));

            //parse file content into whole string;
            $fileContent = "<pre>"; 
            $fileContent .= file_get_contents($path.$fileName);
            $fileContent .= "</pre>";

            $this->setFileContent($fileContent);

            //parse file to get all the details
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
            fclose($myfile);

            foreach ($fileLines as $key => $line) {
                $posType = strpos($line, $typeNeedle);
                $posNumber = stripos($line, "PRI FF") + stripos($line, "FFFF") + stripos($line,"FFVV") ;

                if($posType > 0){
                    $typeLine = explode("  ",trim($line));
                }
                if($posNumber > 0){
                    $numberLine = explode(" ",trim($line));
                }
            }

            foreach ($typeLine as $key => $value) {
                if(strpos($value, "/") > 0){
                    $type = substr($value, -2);
                    $this->setFileType($type);
                    //$parsedLine['type'] = $type;
                    switch ($type) {
                        case '1A':
                            $this->setSystemName("AMADEUS");
                            //$parsedLine['systemName'] = "AMADEUS";
                            break;
                        case '1V':
                            $this->setSystemName("GALILEO");
                            //$parsedLine['systemName'] = "GALILEO";
                            break;
                        case 'AA':
                            $this->setSystemName("SABRE");
                            //$parsedLine['systemName'] = "SABRE";
                            break;
                        case '1S':
                            $this->setSystemName("Unknown");
                            //$parsedLine['systemName'] = "Unknown";
                            break;
                    }
                }
            }
            $this->setAirlineName($typeLine[0]);
            //$parsedLine['airlineName'] = $typeLine[0];

            foreach ($numberLine as $key => $value) {
                if(strlen($value) == 10){
                    //$parsedLine['tickeNumebr'] = $value;
                    $this->setTickeNumebr($value);
                }
            }

        }

        

        public function setFileName($fileName){
            $this->fileName = $fileName;
        }

        public function setFileType($fileType){
            $this->fileType = $fileType;
        }

        public function setSystemName($systemName){
            $this->systemName = $systemName;
        }

        public function setAirlineName($airlineName){
            $this->airlineName = $airlineName;
        }

        public function setTickeNumebr($tickeNumebr){
            $this->tickeNumebr = $tickeNumebr;
        }

        public function setDateString($dateString){
            $this->dateString = $dateString;
        }

        public function setOrderOfDay($orderOfDay){
            $this->orderOfDay = $orderOfDay;
        }

        public function setDateOfFile($dateOfFile){
            $this->dateOfFile = $dateOfFile;
        }

        public function setFileContent($fileContent){
            $this->fileContent = $fileContent;
        }

    }
?>