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

	public function getDate(){
		return View::make('date');
	}


	public function fileConvertAndUpdate(){

		$dir = "../files/";
		$num = 0;
		$documents_group = scandir($dir);

		foreach ($documents_group as $key => $value) {
			if($value != "." && $value != ".."){
				$handler = new handler($dir,$value);
				//var_dump($handler);
				if($handler->getFileType() != NULL){
					try {
			            $document = Document::create(array(
			                'path' 			=> $handler->getPath(),
			                'fileName'  	=> $handler->getFileName(),
			                'fileType'  	=> $handler->getFileType(),
			                'systemName'    => $handler->getSystemName(),
			                'airlineName'   => $handler->getAirlineName(),
			                'ticketNumber'  => $handler->getTicketNumber(),
			                'dateString'    => $handler->getDateString(),
			                'orderOfDay'    => $handler->getOrderOfDay(),
			                'fileContent'   => $handler->getFileContent(),
			                'dateOfFile'    => $handler->getDateOfFile(),
			                'paxName'		=> $handler->getPaxName(),
			                'rloc'			=> $handler->getRloc(),
			                'ticketsType'	=> $handler->getTicketsType(),
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


	/**
	 * function search()
	 * Using either ticketNumber, passengerName or rloc to search up the tickets
     */
	public function search(){
		$data = array();
		$dataProcess = new DataProcess($_POST['ticketNumber'],
			$_POST['passengerName'],
			$_POST['rloc'],
			$_POST['fromDate'],
			$_POST['toDate']);

		$query = Document::query();
		$dataProcess->getQuery($query);
		$model = $query->get();

		$index = 0;
		/* If model only has one record, check if it's the first or last record within the same systemName to determine which prev/next button to enable or disable
		 * If more than one model, next-record/prev-record button will be enabled
		 */
		if(sizeof($model) == 1){
			$systemName = $model[0]->systemName;  //Gets the systemName

			if(($dataProcess->getNewFromDate() != null) && ($dataProcess->getNewToDate() != null)){
				// If
				$getAllModel = Document::whereBetween('dateString', array($dataProcess->getNewFromDate(), $dataProcess->getNewToDate()))->where('systemName', '=', $systemName)->orderBy('ticketNumber', 'asc')->get();
			}else{
				//Getting all the same system number and stores the tickets in an array to find the max ticketNumber
				$getAllModel = Document::where('systemName', '=', $systemName)->orderBy('ticketNumber', 'asc')->get();
			}

			// $index variable to store the location of the current ticketNumber
			// Using this variable to locate the next ticketNumber in row
			$index = 0;
			$allIndex = [];
			if(sizeof($getAllModel) > 0){
				foreach($getAllModel as $key => $value){
					if(($value->ticketNumber) == $dataProcess->getTicketNumber()){
						$index = $key;
					}
					$allIndex[] = $key;
				}
			}

			$maxIndex = max($allIndex);  //Check the max index
			$minIndex = min($allIndex);  //Check the min index usually 0

			if($minIndex == $maxIndex){
				$data[$index]['disable-both'] = 'disable-both';
			}else if($index == $maxIndex){
				$data[$index]['disable-next'] = 'disable-next';
			}else if($index == $minIndex){
				$data[$index]['disable-prev'] = 'disable-prev';
			}

			$data[$index]['content'] = $model[0]['fileContent'];
			$data[$index]['dateOfFile'] = $model[0]['dateOfFile'];
			$data[$index]['paxName'] = $model[0]['paxName'];
			$data[$index]['airlineName'] = $model[0]['airlineName'];
			$data[$index]['systemName'] = $model[0]['systemName'];
			$data[$index]['ticketNumber'] = $model[0]['ticketNumber'];

			$comments = array();
			$notes = Note::where('ticketNumber','=',$model[0]['ticketNumber'])->get();
			foreach ($notes as $key => $value) {
				$comments[$key] = $value->note;
			}
			$data[$index]['comments'] = $comments;
		}else if(sizeof($model)>1){

			foreach ($model as $key => $value) {
				$data[$index]['content'] = $value->fileContent;
				$data[$index]['dateOfFile'] = $value->dateOfFile;
				$data[$index]['paxName'] = $value->paxName;
				$data[$index]['airlineName'] = $value->airlineName;
				$data[$index]['systemName'] = $value->systemName;
				$data[$index]['ticketNumber'] =$value->ticketNumber;

				$comments = array();
				$notes = Note::where('ticketNumber','=',$value->ticketNumber)->get();
				foreach ($notes as $key => $value) {
					$comments[$key] = $value->note;
				}
				$data[$index]['comments'] = $comments;

				//}else{
					//$data['content'][]="Sorry the document does not exist, or hasn't been update yet, please click update and try again.";					
				//}
				$index++;
			}
			//$document = $model[0]->getAttributes();
			//$data['content'] = $document['fileContent']; 	
		}else{
			$data['error'] = "Sorry the document does not exist, or hasn't been update yet, please click update and try again.";
		}

		echo json_encode($data);
	}  //End search function

	/**
	 * function next()
	 * Uses the nextRow()
	 * 1st parameter is 1 because it's used as increment of 1 to the index to find the next row
	 *
	 */
	public function next(){
		$this->nextRow(1,'max');
	}

	/**
	 * function prev()
	 * uses the nextRow()
	 * 1st parameter is -1 because it's used as decrement of -1 to the index to find the prev row
	 */
	public function prev(){
		$this->nextRow(-1,'min');
	}

	/**
	 * function nextRow()
	 * Used by both next() and prev()
	 * Search database to find the same systemName
	 * Sort the search in ticketNumber order which gives the index a sequence
	 * Use the index to find the next number in row
	 * Passes content, ticketNumber and systemName to the view (ticket.blade.php)
	 * @param $nextRow		- increments to find the next / prev row
	 * @param $checkIndex	- not in use
	 */
	public function nextRow($nextRow,$checkIndex){
		$data = array();
		$systemName = $_POST['systemName'];
		$ticketNumber = $_POST['ticketNumber'];

		//Getting all the same system number and stores the tickets in an array to find the max ticketNumber
		$getAllModel = Document::where('systemName', '=', $systemName)->orderBy('ticketNumber', 'asc')->get();

		// $index variable to store the location of the current ticketNumber
		// Using this variable to locate the next ticketNumber in row
		$index = 0;

		$allIndex = [];  //Store all index in an array

		if(sizeof($getAllModel) > 0){
			foreach($getAllModel as $key => $value){
				if(($value->ticketNumber) == $ticketNumber){
					$index = $key;
				}
				$allIndex[] = $key;
			}
		}

		$maxIndex = max($allIndex);  //Check the max index
		$minIndex = min($allIndex);  //Check the min index usually 0
		$nextIndex = $index + $nextRow;  //Next index = next row or previous row
		
		if($nextIndex == $maxIndex || $nextIndex == $minIndex){
			$data['disable'] = 'disable';
		}
		$nextModel = $getAllModel[$nextIndex];
		$data['content'] = $nextModel->fileContent;
		$data['ticketNumber'] = $nextModel->ticketNumber;
		$data['systemName'] = $nextModel->systemName;
		$data['dateOfFile'] = $nextModel->dateOfFile;
		$data['paxName'] = $nextModel->paxName;
		$data['airlineName'] = $nextModel->airlineName;

		//Use next ticketNumber to find the notes in the notes table and put them into an array
		$comments = array();
		$notes = Note::where('ticketNumber','=',$nextModel->ticketNumber)->get();
		foreach ($notes as $key => $value) {
			$comments[$key] = $value->note;
		}
		$data['comments'] = $comments;

		echo json_encode($data);
	}






	/**
	 * function report()
	 * A function that is similar to search()
	 * But this function will return in a new page for print and report purpose.
	 * @return string		string of the contents
     */
	public function report(){
		$dataProcess = new DataProcess(trim($_POST['ticketNumber']),
			trim($_POST['passengerName']),
			trim($_POST['rloc']),
			trim($_POST['date-from-field']),
			trim($_POST['date-to-field']));

		$query = Document::query();
		$dataProcess->getQuery($query);
		$model = $query->get();

		if(sizeof($model) > 0) {
			$longString = null;
			foreach ($model as $key => $value) {
				$document = $value->getAttributes();
				$content = "<div>" . $document['fileContent'] . "</div><hr>";
				$longString .= $content;
			}
		}else{
			$longString = 'Sorry the document does not exist, or hasn not been update yet, please click update and try again.';
		}

		return View::make('date', array('long' => $longString));
	}

	public function saveComment(){
		$data = array();
		if($_POST['comment']){
			$data['comment'] = $_POST['comment'];
			try {
	            $note = Note::create(array(
	                'ticketNumber'  => $_POST['ticketNumber'],
	                'note'    => $_POST['comment'],
	            ));
	            $note->save();
		        } catch (Exception $e) {
		            $response['info'] = "fail";
		            $boolean = false;
		            echo $e;
		        }			
		}
		echo json_encode($data);
	}
}