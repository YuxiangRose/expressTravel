<?php
	class Handler {

        public function hehe() {
            echo 'myFunction is OK';
        }

        public function parseFileName($fileName){
        	$dateString = substr($fileName, 0,8);
        	$orderOfDay = substr($fileName,-2);

        	echo $dateString;
        	echo $orderOfDay;
        }

        public function getContent(){
        	$myfile = fopen("../files/20150306-1.txt", "r") or die("Unable to open file!");
			echo fread($myfile,filesize("../files/20150306-1.txt"));
			fclose($myfile);
        }
    }
?>