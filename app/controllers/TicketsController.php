<?php

class TicketsController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function getIndex()
	{		
		return View::make('ticket');
		//$this->fileConvertAndUpdate();
	}

	public function fileConvertAndUpdate(){
		
		$dir = "../files/";
		$num = 0;
		$documents_group = scandir($dir);

		foreach ($documents_group as $key => $value) {
			if($value != "." && $value != ".."){
				$handler = new handler($dir,$value);
				
				try {
		            $document = Document::create(array(
		                'path' 			=> $handler->getPath(),
		                'fileName'  	=> $handler->getFileName(),
		                'fileType'  	=> $handler->getFileType(),
		                'systemName'    => $handler->getSystemName(),
		                'airlineName'   => $handler->getAirlineName(),
		                'tickeNumebr'   => $handler->getTickeNumebr(),
		                'dateString'    => $handler->getDateString(),
		                'orderOfDay'    => $handler->getOrderOfDay(),
		                'fileContent'   => $handler->getFileContent(),
		                'dateOfFile'    => $handler->getDateOfFile(),
		            ));
		            $document->save();
		            $num++;
		            rename($dir.$value, "../done/".$value);
		        } catch (Exception $e) {
		            $response['info'] = "fail";
		            $boolean = false;
		            echo $e;
		        }				
			}
		}

		//echo $num." files have been converted.";
		return $num;
	}

	public function update(){
		$num = $this->fileConvertAndUpdate();
		echo $num;
	}

	public function search(){
		$model = Document::where('tickeNumebr', '=', 4683038938)->first();
		$document = $model->getAttributes(); 
		//echo (string)$document["fileContent"];
		echo "1017PZ BAINS TRAVEL LTD                     AN01TARN        IN0001179042
 ETKT     **AGENT COUPON**              61552481       **ITINERARY**
 CHINA SOUTHERN AIRLINES      1 OF 1   6DO9N9/1A   OCZ0330E YVRCAN 20APR
 PARMAR/HARBINDER S MR A         TC                   ELESS46
                                          06MAR14  XCZ0359E CANDEL 21APR
 NONEND PENALTY NOSHOW APPLIES                        ELESS46
 
 
 
 DEL CZ X/CAN CZ YVR27500.00CZ X/CAN CZ DEL18500.0
 0INR46000.00END PD XT1.00XG18.16YQ291.46YR20.00SQ
 52.14JN28.47IN12.26YM23.50IN4.14WO32.80CN
 
 
 INR 46000.00  CAD   835.00 EXCH/7844682885321
 CAD    92.00 CP FCI 1
        25.91 CA CCVI A/C CHECK CAD182.00
       483.93 XT ORIG/7844682885321YVR14FEB1461552481
 CAD   182.00
          784 4683038938 4 PRI FFVV
 7906/                         7840EX     90.00      0.00          92.00
                                                         5.40A        72";	
	}

}
