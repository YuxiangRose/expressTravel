<?php
	class Handler {

        const AMADEUSREFOUND = "*****REFUNDED TICKETS";
        const GALILEOREFOUND = "***REFUNDED TICKETS";
        const SABREREFOUND = "REFUND NOTICE";
        const ticketFileNeedle = "**ITINERARY**";

        private $path;
        private $fileName;
        private $fileType;
        private $systemName;
        private $airlineName;
        private $ticketNumber;
        private $dateString;
        private $orderOfDay;
        private $dateOfFile;
        private $fileContent;
        private $rloc;
        private $paxName;
        private $ticketsType;

        /**
         * @param $path         //file directory path
         * @param $fileName     //name of the file
         */
        public function __construct($path,$fileName){

            $this->path     = $path;
            $this->fileName = $fileName; 
            //$this->parseFile($path, $fileName);
            $this->convertFile($path, $fileName);
        }

        //getters
        /**
         * @return mixed
         */
        public function getPath(){
            return $this->path;
        }

        /**
         * @return mixed
         */
        public function getFileName(){
            return $this->fileName;
        }

        /**
         * @return mixed
         */
        public function getFileType(){
            return $this->fileType;
        }

        /**
         * @return mixed
         */
        public function getSystemName(){
            return $this->systemName;
        }

        /**
         * @return mixed
         */
        public function getAirlineName(){
            return $this->airlineName;
        }

        /**
         * @return mixed
         */
        public function getTicketNumber(){
            return $this->ticketNumber;
        }

        /**
         * @return mixed
         */
        public function getDateString(){
            return $this->dateString;
        }

        /**
         * @return mixed
         */
        public function getOrderOfDay(){
            return $this->orderOfDay;
        }

        /**
         * @return mixed
         */
        public function getDateOfFile(){
            return $this->dateOfFile;
        }

        /**
         * @return mixed
         */
        public function getFileContent(){
            return $this->fileContent;
        }

        /**
         * @return mixed
         */
        public function getRloc(){
            return $this->rloc;
        }

        /**
         * @return mixed
         */
        public function getPaxName(){
            return $this->paxName;
        }

        /**
         * @return mixed
         */
        public function getTicketsType(){
            return $this->ticketsType;
        }

        //setters
        /**
         * @param $fileName
         */
        public function setFileName($fileName){
            $this->fileName = $fileName;
        }

        /**
         * @param $fileType
         */
        public function setFileType($fileType){
            $this->fileType = $fileType;
        }

        /**
         * @param $systemName
         */
        public function setSystemName($systemName){
            $this->systemName = $systemName;
        }

        /**
         * @param $airlineName
         */
        public function setAirlineName($airlineName){
            $this->airlineName = $airlineName;
        }

        /**
         * @param $ticketNumber
         */
        public function setTicketNumber($ticketNumber){
            $this->ticketNumber = $ticketNumber;
        }

        /**
         * @param $dateString
         */
        public function setDateString($dateString){
            $this->dateString = $dateString;
        }

        /**
         * @param $orderOfDay
         */
        public function setOrderOfDay($orderOfDay){
            $this->orderOfDay = $orderOfDay;
        }

        /**
         * @param $dateOfFile
         */
        public function setDateOfFile($dateOfFile){
            $this->dateOfFile = $dateOfFile;
        }

        /**
         * @param $fileContent
         */
        public function setFileContent($fileContent){
            $this->fileContent = $fileContent;
        }

        /**
         * @param $rloc
         */
        public function setRloc($rloc){
            $this->rloc = $rloc;
        }

        /**
         * @param $paxName
         */
        public function setPaxName($paxName){
            $this->paxName = $paxName;
        }

        /**
         * @param $ticketsType
         */
        public function setTicketsType($ticketsType){
            $this->ticketsType = $ticketsType;
        }


        /**
         * 1.  Check if the content of the text file has "**ITINERARY**"
         * 2.  If yes, parse the ticket using parseTicket()
         * 3.  If not, check if the content of the text contains one of the system's refund string
         * 4.  If yes, parse the ticket using parseRefunded()
         * @param $path         //path of file directory
         * @param $fileName     //name of the file
         */
        public function convertFile($path,$fileName){
            //$ticketFileNeedle = "**ITINERARY**";
            //$AMADEUSREFOUND = "*****REFUNDED TICKETS";
            //$GALILEOREFOUND = "***REFUNDED TICKETS";
            //$SABREREFOUND = "REFUND NOTICE";
            //
            $refundType = array();
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            
            $fileString = file_get_contents($path.$fileName);

            $isTicket = strpos($fileString, Handler::ticketFileNeedle);
            if($isTicket > 0 ){
                $this->parseTicket($path,$fileName);   
            }
            
            $refundType['1A'] = strpos($fileString, Handler::AMADEUSREFOUND);
            $refundType['1V'] = strpos($fileString, Handler::GALILEOREFOUND);
            $refundType['AA'] = strpos($fileString, Handler::SABREREFOUND);

            $isRefund = strpos($fileString, Handler::AMADEUSREFOUND) + strpos($fileString,Handler::GALILEOREFOUND)+ strpos($fileString, Handler::SABREREFOUND);
            if($isRefund > 0){
                $this->parseRefunded($path,$fileName,$refundType);
            }
        }


        /**
         * Parse the ticket as a regular ticket
         * Gets all the info needed from the text file and store them into the handler class variables
         * @param $path         //path of file directory
         * @param $fileName     //name of the file
         */
        public function parseTicket($path, $fileName){

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

            //parse file to get all the details
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
            fclose($myfile);

            foreach ($fileLines as $key => $line) {
                $posType = strpos($line, $typeNeedle);
                $posNumber = stripos($line, "PRI FF") + stripos($line, "FFFF") + stripos($line,"FFVV") + stripos($line, "PRI ") ;

                if($posType > 0){
                    $typeLine = explode("  ",trim($line));
                    $nameLineIndex = $key + 1;
                }
                if($posNumber > 0){
                    $numberLine = explode(" ",trim($line));
                }
            }

            $nameLineArray = explode("  ", $fileLines[$nameLineIndex]);
            $paxName = str_replace("/", " ", trim($nameLineArray[0]));
            $this->setPaxName($paxName);

            foreach ($typeLine as $key => $value) {
                if(strpos($value, "/") > 0){
                    $value = explode("/",trim($value));
                    $rloc  = $value[0];
                    $type  = $value[1];
                    $this->setRloc($rloc);
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
                            $this->setSystemName("SABRE");
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
                    $this->setTicketNumber($value);
                }
            }


            //parse file content into whole string;
            $fileContent = "<pre class='pre-text'>"; 
            $fileContent .= file_get_contents($path.$fileName);
            $fileContent .= "</pre>";

            $fileContent = str_replace($this->getTicketNumber(), "<span class='ticket-highlight'>".$this->getTicketNumber()."</span>", $fileContent);
            $this->setFileContent($fileContent);
           
            $this->setTicketsType("Ticket");

        }

        public function parseRefunded($path,$fileName,$refundType){

            $fileLines = array();
            $numberLine = array();
            $airLineArray = array();
            $nameLineArray = array();

            //parse file name;
            $temp = preg_replace("/[^0-9]/", "", $fileName);
            
            $dateString = substr($temp, 0,8);
            $orderOfDay = substr($temp,8,2);

            $this->setDateString($dateString);
            $this->setOrderOfDay($orderOfDay);

            $date = new DateTime($dateString);
            $this->setDateOfFile($date->format('Y-m-d'));

            //parse file to get all the details
            $myfile = fopen($path.$fileName, "r") or die("Unable to open file!");
            while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
            fclose($myfile);

            foreach($refundType as $key => $value){
                if($value > 0){
                     $type  = $key;
                     $this->setFileType($type);
                     switch ($type) {
                        case '1A':
                            $this->setSystemName("AMADEUS");
                            foreach ($fileLines as $key => $line) {

                            }                            
                            break;
                        case '1V':
                            $this->setSystemName("GALILEO");
                            foreach ($fileLines as $key => $line) {
                                $posNeedle = strpos($line,Handler::GALILEOREFOUND);
                                if($posNeedle>0){
                                    $nameLineIndex = $key - 3;
                                    $ticketNumerLineIndex = $key + 1;
                                    $airLineIndex = $key - 1;
                                    break;
                                }
                            }
                            $nameLineArray = explode("  ", $fileLines[$nameLineIndex]);
                            $paxName = str_replace("/", " ", trim($nameLineArray[0]));
                            $this->setPaxName($paxName);

                            $airLineArray = explode("   ", $fileLines[$airLineIndex]);
                            $airLineName = str_replace("/", " ", trim($airLineArray[0]));
                            $this->setAirlineName($airLineName);

                            $numberLine = trim($fileLines[$ticketNumerLineIndex]);
                            $numberLineArray = explode(" ",$numberLine);
                        
                            foreach ($numberLineArray as $key => $value) {
                                echo $value;
                                echo "<br>";
                                if(strlen($value) == 10){
                                    //$parsedLine['tickeNumebr'] = $value;
                                    $this->setTicketNumber($value);
                                    echo $value;
                                    echo $this->ticketNumber; die;
                                }
                            }

                            break;
                        case 'AA':
                            $this->setSystemName("SABRE");
                            foreach ($fileLines as $key => $line) {    
                            }
                            break;
                    }
                }
            }
            die;
            foreach ($fileLines as $key => $line) {    
            }

            //parse file content into whole string;
            $fileContent = "<pre>"; 
            $fileContent .= file_get_contents($path.$fileName);
            $fileContent .= "</pre>";

            $this->setFileContent($fileContent);

            $this->setRloc("Unknown");
            $this->setTicketsType("Refund");
        }

        public function parseExchange($path,$fileName){

        }

    }
?>