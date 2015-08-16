<?php

    /**
     * Created by PhpStorm.
     * User: ET
     * Date: 8/15/2015
     * Time: 2:02 PM
     */
    class DataProcess {
        private $ticketNumber;

        private $passengerName;
        private $first;         // first of passgenerName after parsed
        private $mid;           // mid of passgenerName after parsed
        private $last;          // last of passgenerName after parsed

        private $rloc;
        private $newFromDate;
        private $newToDate;

        /**
         * Data constructor.
         * Using the POST data passed by the input and pass them into the setters to process the POST data
         *
         * @param $ticketNumber
         * @param $passengerName
         * @param $rloc
         * @param $fromDate
         * @param $toDate
         */
        public function __construct($ticketNumber, $passengerName, $rloc, $fromDate, $toDate) {
            $this->setTicketNumber($ticketNumber);
            $this->setPassengerName($passengerName);
            $this->setRloc($rloc);
            $this->setNewFromDate($fromDate);
            $this->setNewToDate($toDate);
        }


        /**
         * @return mixed
         */
        public function getTicketNumber() {
            return $this->ticketNumber;
        }

        /**
         * @return mixed
         */
        public function getPassengerName() {
            return $this->passengerName;
        }

        /**
         * @return mixed
         */
        public function getFirst() {
            return $this->first;
        }

        /**
         * @return mixed
         */
        public function getMid() {
            return $this->mid;
        }

        /**
         * @return mixed
         */
        public function getLast() {
            return $this->last;
        }

        /**
         * @return mixed
         */
        public function getRloc() {
            return $this->rloc;
        }

        /**
         * @return mixed
         */
        public function getNewFromDate() {
            return $this->newFromDate;
        }

        /**
         * @return mixed
         */
        public function getNewToDate() {
            return $this->newToDate;
        }

        /**
         * @param mixed $ticketNumber
         */
        public function setTicketNumber($ticketNumber) {
            /* Check if ticket number entered is not null and is number */
            if(($ticketNumber != null) && (is_numeric($ticketNumber))){
                $this->ticketNumber = trim($ticketNumber);
            }else{
                $this->ticketNumber = "";
            }
        }

        /**
         * @param mixed $passengerName
         */
        public function setPassengerName($passengerName) {
            /* Parse the passenger name by space or slash */
            if (($passengerName != null) || empty(($passengerName))) {
                $this->passengerName = strtoupper(trim($passengerName));
                if(strpos($this->passengerName,'/')){
                    $parsePassengerName = explode("/", $this->passengerName);
                }else{
                    $parsePassengerName = explode(" ", $this->passengerName);
                }
                $this->setFirst(array_key_exists(0, $parsePassengerName) ? $parsePassengerName[0] : "");
                $this->setMid(array_key_exists(1, $parsePassengerName) ? $parsePassengerName[1] : "");
                $this->setLast(array_key_exists(2, $parsePassengerName) ? $parsePassengerName[2] : "");
            }else{
                $this->passengerName = null;
            }
        }

        /**
         * @param mixed $first
         */
        public function setFirst($first) {
            $this->first = $first;
        }

        /**
         * @param mixed $mid
         */
        public function setMid($mid) {
            $this->mid = $mid;
        }

        /**
         * @param mixed $last
         */
        public function setLast($last) {
            $this->last = $last;
        }

        /**
         * @param mixed $rloc
         */
        public function setRloc($rloc) {
            /* Check if RLOC is entered */
            if (($rloc != null)) {
                $this->rloc = strtoupper(trim($rloc));
            }else{
                $this->rloc = "";
            }
        }

        /**
         * @param mixed $fromDate
         */
        public function setNewFromDate($fromDate) {
            if($fromDate != null){
                $parseFromDate = explode("/", trim($fromDate));
                $this->newFromDate = $parseFromDate[2].$parseFromDate[0].$parseFromDate[1];
            }else{
                $this->newFromDate = null;
            }
        }

        /**
         * @param mixed $toDate
         */
        public function setNewToDate($toDate) {
            if($toDate != null){
                $parseToDate = explode("/", trim($toDate));
                $this->newToDate = $parseToDate[2].$parseToDate[0].$parseToDate[1];
            }else{
                $this->newToDate = null;  //set as today if input left null
            }
        }


        /**
         * function getQuery()
         * Used in search() and report()
         * If the variables aren't null a query condition will be added into the final query when it runs
         * @param $query		stores all the conditions
         */
        public function getQuery($query){
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

            // Query dates bigger than selected dates
            if($this->newFromDate != null){
                $query = $query->where('dateString', '>=', $this->newFromDate);
            }

            // Query dates smaller than selected dates
            if($this->newToDate != null){
                $query = $query->where('dateString', '<=', $this->newToDate);
            }
        }


    }