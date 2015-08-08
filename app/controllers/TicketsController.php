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

	/*Used in searchReportNullValidation() to store variables here and so search() and report() can access these variables*/
	private $ticketNumber;

	private $passengerName;
	private $first;         // first of passgenerName after parsed
	private $mid;           // mid of passgenerName after parsed
	private $last;          // last of passgenerName after parsed

	private $rloc;
	private $newFromDate;
	private $newToDate;
	/*******/

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
		$this->searchReportNullValidation($_POST['ticketNumber'],
										   $_POST['passengerName'],
										   $_POST['rloc'],
										   $_POST['fromDate'],
										   $_POST['toDate']);

		$query = Document::query();
		$this->searchReportQuery($query);
		$model = $query->get();

		$index = 0;
		/* If model only has one record, check if it's the first or last record within the same systemName to determine which prev/next button to enable or disable
		 * If more than one model, next-record/prev-record button will be enabled
		 */
		if(sizeof($model) == 1){
			$systemName = $model[0]->systemName;  //Gets the systemName

			if(($this->newFromDate != null) && ($this->newToDate != null)){
				// If
				$getAllModel = Document::whereBetween('dateString', array($this->newFromDate, $this->newToDate))->where('systemName', '=', $systemName)->orderBy('ticketNumber', 'asc')->get();
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
					if(($value->ticketNumber) == $this->ticketNumber){
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

		}else if(sizeof($model)>1){
			foreach ($model as $key => $value) {
				$document = $value->getAttributes();
				//if($document){
				$data[$index]['content'] = $document['fileContent'];
				$data[$index]['dateOfFile'] = $document['dateOfFile'];
				$data[$index]['paxName'] = $document['paxName'];
				$data[$index]['airlineName'] = $document['airlineName'];
				$data[$index]['systemName'] = $document['systemName'];
				$data[$index]['ticketNumber'] = $document['ticketNumber'];
				//}else{
					//$data['content'][]="Sorry the document does not exist, or hasn't been update yet, please click update and try again.";					
				//}
				$index++;
			}
			//$document = $model[0]->getAttributes();
			//$data['content'] = $document['fileContent']; 	
		}else{
			$data[$index]['content'] = "Sorry the document does not exist, or hasn't been update yet, please click update and try again.";
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

		echo json_encode($data);
	}

	/**
	 * function dateQuery()
	 * Used in searchReportQuery() where multi queries that meet the requirement will run
	 * @param $query			when requirement met, this variable will contain the queries needs to run
	 * @param $newFromDate		from date from date picker
	 * @param $newToDate		to date	from date picker
     */
	public function dateQuery($query, $newFromDate, $newToDate){
		return $query->whereBetween('dateString', array($newFromDate, $newToDate));
	}

	/**
	 * function searchReportNullValidation()
	 * Used in search() and report()
	 * Checks what needs to be done when the variables are null or not
	 * The variables will then be stored as global variables
	 * @param $ticketNumber			$_POST['ticketNumber']
	 * @param $passengerName		$_POST['passengerName']
	 * @param $rloc					$_POST['rloc']
	 * @param $fromDate				$_POST['fromDate']
	 * @param $toDate				$_POST['toDate']
     */
	public function searchReportNullValidation($ticketNumber, $passengerName, $rloc , $fromDate, $toDate){
		/* Check if ticket number entered is not null and is number */
		if(($ticketNumber != null) && (is_numeric($ticketNumber))){
			$this->ticketNumber = trim($ticketNumber);
		}else{
			$this->ticketNumber = "";
		}

		/* Parse the passenger name by space or slash */
		if (($passengerName != null) || empty(($passengerName))) {
			$this->passengerName = strtoupper(trim($passengerName));
			if(strpos($this->passengerName,'/')){
				$parsePassengerName = explode("/", $this->passengerName);
			}else{
				$parsePassengerName = explode(" ", $this->passengerName);
			}
			$this->first = (array_key_exists(0, $parsePassengerName) ? $parsePassengerName[0] : "");
			$this->mid   = (array_key_exists(1, $parsePassengerName) ? $parsePassengerName[1] : "");
			$this->last   = (array_key_exists(2, $parsePassengerName) ? $parsePassengerName[2] : "");
		}else{
			$this->passengerName = null;
		}

		/* Check if RLOC is entered */
		if (($rloc != null)) {
			$this->rloc = strtoupper(trim($rloc));
		}else{
			$this->rloc = "";
		}

		if($fromDate != null){
			$parseFromDate = explode("/", trim($fromDate));
			$this->newFromDate = $parseFromDate[2].$parseFromDate[0].$parseFromDate[1];
		}else{
			$this->newFromDate = null;
		}

		if($toDate != null){
			$parseToDate = explode("/", trim($toDate));
			$this->newToDate = $parseToDate[2].$parseToDate[0].$parseToDate[1];
		}else{
			$this->newToDate = null;  //set as today if input left null
		}
	}

	/**
	 * function searchReportQuery()
	 * Used in search() and report()
	 * If the variables aren't null a query condition will be added into the final query when it runs
	 * @param $query		stores all the conditions
     */
	public function searchReportQuery($query){
		// Query ticketNumber input if not null
		if($this->ticketNumber != null){
			$query = $query->where('ticketNumber', 'LIKE', '%'.$this->ticketNumber.'%');
		}
		// Query rloc input if not null
		if($this->rloc != null){
			$query = $query->where('rloc', 'LIKE', '%'.$this->rloc.'%');
		}
		// Query passengerName if not null
		// Query will depend on how many words are in the input (max 3 which are parsed into $first, $mid, $last)
		if($this->passengerName != null){
			$query = $query->where('paxName', 'LIKE', '%'.$this->first.'%')
				->where('paxName','LIKE','%'.$this->mid.'%')
				->where('paxName','LIKE','%'.$this->last.'%');
		}
		/* Query fromDate and toDate if null or not null (combination of possibilities) */
//		// If both date form and to input are empty
//		// Both dates will be set as today
//		if(($newFromDate == null) && ($newToDate == null)){
//			$newFromDate = date('Ymd');
//			$newToDate = date('Ymd');
//			$this->dateQuery($query, $newFromDate, $newToDate);
//		}
//		// If date from is null it'll search the database to find the oldest date
//		// Query will be the oldest date(fromDate) to the toDate input entered
//		else
		if(($this->newFromDate == null) && ($this->newToDate != null)){
			$oldestDate = Document::orderBy('dateString', 'asc')->first();
			$this->newFromDate = $oldestDate->dateString;
			$this->dateQuery($query, $this->newFromDate, $this->newToDate);
		}
		// If date to is null it'll set the toDate to today
		elseif(($this->newToDate == null) && ($this->newFromDate != null)){
			$this->newToDate = date('Ymd');
			$this->dateQuery($query, $this->newFromDate, $this->newToDate);
		}
		// Query the ticket in between the dates if both not null
		elseif(($this->newFromDate != null) && ($this->newToDate != null)){
			$this->dateQuery($query, $this->newFromDate, $this->newToDate);
		}
	}

	
	public function report(){
		$data = array();
		$this->searchReportNullValidation($_POST['ticketNumber'],
			$_POST['passengerName'],
			$_POST['rloc'],
			$_POST['fromDate'],
			$_POST['toDate']);

		$query = Document::query();
		$this->searchReportQuery($query);
		$model = $query->get();

		$index = 0;
		foreach ($model as $key => $value) {
			$document = $value->getAttributes();
			//if($document){
			$data[$index]['content'] = $document['fileContent'];
			$data[$index]['dateOfFile'] = $document['dateOfFile'];
			$data[$index]['paxName'] = $document['paxName'];
			$data[$index]['airlineName'] = $document['airlineName'];
			$data[$index]['systemName'] = $document['systemName'];
			$data[$index]['ticketNumber'] = $document['ticketNumber'];
			$index++;
		}
		return json_encode($data);
	}
}