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
		$num =	$this->fileConvertAndUpdate();
		return View::make('ticket',array('num'=>$num));
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

		//echo $num." files have been converted."; die;
		return $num;
	}

	public function update(){
		$data = array();
		$num = $this->fileConvertAndUpdate();
		$data['num'] = $num;
		echo json_encode($data);
	}

	public function search(){
		$data = array();
		if(is_numeric($_POST['ticketNumber'])){
			$ticketNumber = $_POST['ticketNumber'];
		}
		$model = Document::where('tickeNumebr', '=', $ticketNumber)->first();
		if($model){
			$document = $model->getAttributes();
			$data['content'] = $document['fileContent']; 	
		}else{
			$data['content'] = "Sorry the document does not exist, or hasn't been update yet, please click update and try again."; 	
		}
		
		echo json_encode($data);
	}
}
