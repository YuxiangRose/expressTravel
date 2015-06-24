<?php
	class Handler {

        private $name;

        public function __construct($name){
            $this->name = $this->haha($name);
        }

        public function getName(){
            return $this->name;
        }

        public function haha($name){
            return $name."this is it";
        }

        public function hehe() {
            echo 'myFunction is OK';
        }

        public function parseFileName($fileName){
        	
            $dateString = substr($fileName, 0,8);
        	$orderOfDay = substr($fileName,-2);

        	echo $dateString;
        	echo $orderOfDay;
        }

        public function getContent($fileName){
            
            $fileLines = array();

        	$myfile = fopen($fileName, "r") or die("Unable to open file!");
			while(!feof($myfile))
            {
                $fileLines[] = fgets($myfile);
            }
			fclose($myfile);

            return $fileLines;
        }

        public function getFileDetails($fileLines){
            $typeNeedle = "1 OF 1";
            $numberNeedle = "PRI FF";

            $typeLine   = array();
            $numberLine = array();
            $parsedLine = array();


            foreach ($fileLines as $key => $line) {
                $posType = strpos($line, $typeNeedle);
                $posNumber = strpos($line, $numberNeedle);

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
                    $parsedLine['type'] = $type;
                    switch ($type) {
                        case '1A':
                            $parsedLine['systemName'] = "AMADEUS";
                            break;
                        case '1V':
                            $parsedLine['systemName'] = "GALILEO";
                            break;
                        case 'AA':
                            $parsedLine['systemName'] = "SABRE";
                            break;
                    }
                }
            }
            $parsedLine['airlineName'] = $typeLine[0];

            foreach ($numberLine as $key => $value) {
                if(strlen($value) == 10){
                    $parsedLine['tickeNumebr'] = $value;
                }
            }
            var_dump($parsedLine);
        }
    }
?>