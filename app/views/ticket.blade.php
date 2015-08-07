@extends('layouts.master')
@section('css')
<style>

</style>
<script></script>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>Ticket search</h1>
  </div>
  <div class="update-info">
    <h3>{{$num}} files have been convert and updated.</h3>
  </div>
  <div class="sub-container">
    <div class="form-field">
      <label>Ticket Number : </label>
      <input class="ticket-field" type="text" name="ticketNumber" value="" placeholder="Please enter ticket number">
    </div>
    <div class="form-field">
      <label>Passenger Name : </label>
      <input class="name-field" type = "text" name="passengerName" value="" placeholder="Please enter passenger name">
    </div>
    <div class="form-field">
      <label>RLOC : </label>
      <input class="rloc-field" type = "text" name="rloc" value="" placeholder="Please enter RLOC">
    </div>
    <div class="form-field">
      <label for="from">Date From</label>
      <input class="date-from-field" type="text" id="date-from-field" name="date-from-field" placeholder="Pick a from Date">
    </div>
    <div class="form-field">
      <label for="to">Date To</label>
      <input class="date-to-field" type="text" id="date-to-field" name="date-to-field" placeholder="Pick a to Date">
    </div>

    <div class="button-field">
      <input type="submit" class="btn-search"value="Search">
      <button class="btn-update">Update</button>
      <button class="btn-prev" name="previous">PREV</button>
      <button class="btn-next" name="next">NEXT</button>
      <button class="btn-next-record" name="nextRecord">Next Record</button>
      <button class="btn-prev-record" name="prevRecord">Prev Record</button>
      <button class="btn-report" name="report">Report</button>
    </div> <!---end button-field -->

    <input type="hidden" name="ticketHolder" value="">
  </div><!--end sub-container -->

  <div id="text-field">
  </div>

</div><!--end container -->
@stop

@section('js')
<script>
  $(document).ready(function() {
    /* Enable datepicker widget */
    // Sets both datepicker not able to select any date pass today
    // after date-from-field selected a date, date-to-field cannot select any date before the date date-from-field selected
    // e.g. date-from-field has the value of 08/08/2015 then date-to-field cannot select any date before 08/08/2015, only can select between 08/08/2015 and today
    $( "#date-from-field" ).datepicker({
//      defaultDate: "+1w",
//        changeMonth: true,
//        numberOfMonths: 2,
      onClose: function( selectedDate ) {
        $( "#date-to-field" ).datepicker( "option", "minDate", selectedDate );
      },
      maxDate: "0"
    });

    $( "#date-to-field" ).datepicker({
//      defaultDate: "+1w",
//        changeMonth: true,
//        numberOfMonths: 2,
      onClose: function( selectedDate ) {
        $( "#date-from-field" ).datepicker( "option", "maxDate", selectedDate ? selectedDate: "0");
      },
      maxDate: "0"
    });
    /* End datepicker widget */

    $( "#text-field" ).accordion();
    $('.btn-prev').attr('disabled','disabled');
    $('.btn-next').attr('disabled','disabled');
    $('.btn-prev-record').attr('disabled','disabled');
    $('.btn-next-record').attr('disabled','disabled');

    setTimeout(function() {
        $('.update-info').slideUp('slow');
    }, 1000);

    $("button,input[type=submit]").button();

    /* Two variables created to save the ticketNumber / systemName passed here from PHP when search is clicked */
    var globalTicketNumber;
    var globalSystemName;


    /*****************/
    /* Search Record */
    /*****************/
    $(".btn-search").click(function(event) {
      $('.btn-prev').button( "disable" );
      $('.btn-next').button( "disable" );
      $('.btn-prev-record').button( "disable" );
      $('.btn-next-record').button( "disable" );

      event.preventDefault();
      var noError = true;

      var ticketNumber  = $.trim($("input[name='ticketNumber']").val());
      var passengerName = $.trim($("input[name='passengerName']").val());
      var rloc          = $.trim($("input[name='rloc']").val());
      var fromDate      = $.trim($("input[name='date-from-field']").val());
      var toDate        = $.trim($("input[name='date-to-field']").val());

      if($.isNumeric(ticketNumber) || ticketNumber==""){
        noError = true;
      }else{
        noError = false;
        $("input[name='ticketNumber']").val('');
        alert("please enter a number");
      }

      if(noError){
        $("#text-field").empty();
        $.ajax({
          method: "post",
          url: "/search",
          dataType: "json",
          data: {ticketNumber:ticketNumber,
            passengerName:passengerName,
            rloc:rloc,
            fromDate:fromDate,
            toDate:toDate},
          success: function(data){
            if(data.length>1){
              maxIndexForDoc = data.length -1;
              $.each(data,function(index,item){
                $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"<button class='print-btn'>Print</button></div></div>");
              });

              $( "#text-field" ).accordion( "destroy" );
              $( "#text-field" ).accordion({
                collapsible: true,
                header: "> div > h3"
              });
              $('.btn-prev-record').button( "enable" );
              $('.btn-next-record').button( "enable" );
            }else{
              $.each(data,function(index,item) {
                $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"<button class='print-btn'>Print</button></div></div>");
                $( "#text-field" ).accordion( "destroy" );
                $( "#text-field" ).accordion({
                  collapsible: true,
                  header: "> div > h3"
                });
                globalSystemName = item['systemName'];
                globalTicketNumber = item['ticketNumber'];
                //Enables all buttons first and use the codes below to check which one should be disabled
                $('.btn-prev').button( "enable" );
                $('.btn-next').button( "enable" );

                /* Checks which buttons (prev/next) should be disabled and will override the enabled if needed */
                //Disable-both - Only has ONE ticketNumber within the same systemName which is rare but still possible
                //Disable-next - The record pulled has reached the end of the record
                //Disable-prev - The record pulled has reached the earliest of the record
                if(item['disable-both'] == 'disable-both'){
                  $('.btn-prev').button( "disable" );
                  $('.btn-next').button( "disable" );
                }else if(item['disable-next']  == 'disable-next'){
                  $('.btn-next').button( "disable" );
                }else if(item['disable-prev']  == 'disable-prev'){
                  $('.btn-prev').button( "disable" );
                }
              });

            }

            $("input[name='ticketNumber']").val('');
            $("input[name='passengerName']").val('');
            $("input[name='rloc']").val('');
          }
        });
      }
    });  //end btn-search

    /*
     * Update Button
     * This updates the documents in files directory
     * Converts the documents into database properly and saves the documents into done directory
     * Files cannot be converted will stay in the files directory
     * */
    $(".btn-update").click(function(event) {
      event.preventDefault();
      $.ajax({
        method: "get",
        url: "/update",
        dataType: "json",
        success: function(data){
          $(".update-info h3").text(data['num']+' files have been convert and updated.')
          $(".update-info").show();
          setTimeout(function() {
              $('.update-info').slideUp('slow');
          }, 1000);
        }
      });
    });   //end btn-update


    /*
     * Next Button
     * This will find the next ticketNumber in column.
     * */
    $(".btn-next").click(function(event) {
      event.preventDefault();
      var systemName = globalSystemName;
      var ticketNumber = Number(globalTicketNumber);
      $.ajax({
        method: "post",
        url: "/next",
        dataType: "json",
        data: {systemName: systemName, ticketNumber: ticketNumber},
        success: function (data) {
          console.log(data);
            appendDataPrevNext(data, 'next');
        }
      });
    });  //end btn-next

    
    /*
     * Previous Button
     * This will find the previous ticketNumber in column.
     * */
    $(".btn-prev").click(function(event) {
      event.preventDefault();
      var systemName = globalSystemName;
      var ticketNumber = Number(globalTicketNumber);
      $.ajax({
        method: "post",
        url: "/prev",
        dataType: "json",
        data: {systemName: systemName, ticketNumber: ticketNumber},
        success: function(data){
          appendDataPrevNext(data, 'prev');
        }
      });
    });  //end btn-prev

    /*
     * For the use of next / previous button
     * Both buttons appends the data in the same way using jQuery UI's accordion
     * 1st parameter passes the data which is passed back from PHP
     * 2nd parameter defines if the function is used for next / previous button to know which button to be disabled / enabled when needed
     * */
    function appendDataPrevNext(data, pn){
      $("#text-field").empty();
      $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>" + data['dateOfFile'] + "</span><span>" + data['paxName'] + "</span><span>" + data['airlineName'] + "</span></h3><div class='text-block'>" + data['content'] + "<button class='print-btn'>Print</button></div></div>");
      $("#text-field").accordion("destroy");
      $("#text-field").accordion({
        collapsible: true,
        header: "> div > h3"
      });

      globalTicketNumber = data['ticketNumber'];

      if(pn == 'next'){
        $('.btn-prev').button("enable");
        if ((data['disable']) == 'disable') {
          $('.btn-next').button("disable");
        }
      }

      if(pn == 'prev'){
        $('.btn-next').button( "enable" );
        if((data['disable']) == 'disable'){
          $('.btn-prev').button( "disable" );
        }
      }
    }

    $(".btn-next-record").click(function(event) {
      /* Act on the event */
      event.preventDefault();
      var section = $(".group:first");
      $(".group:first").find('h3').removeClass( "ui-accordion-header-active ui-state-active ui-corner-top" );
      var content = $(".group:first").html();
      $("div").remove(".group:first");
      $("#text-field").append("<div class='group'>"+content+"<div>");
      $("#text-field").accordion("refresh");

    });

    $(".btn-prev-record").click(function(event) {
      /* Act on the event */
      event.preventDefault();
      var section = $(".group:last");
      $('.group:first').find('h3').removeClass( "ui-accordion-header-active ui-state-active ui-corner-top" );
      var content =  $(".group:last").html();
      $("div").remove(".group:last");
      var rest = $("#text-field").html();
      $("#text-field").empty();
      $("#text-field").append("<div class='group'>"+content+"<div>");
      $("#text-field").append(rest);
      $("#text-field").accordion("refresh");

    });

    $('.btn-report').click(function(e){
        e.preventDefault();
        $.ajax({
            method: "post",
            url: "/report",
            dataType: "json",
//            data: {systemName: systemName, ticketNumber: ticketNumber},
            success: function(data){
                console.log(data);
                $("#text-field").append(data);
            }
        });
    });
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
