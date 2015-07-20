@extends('layouts.master')
@section('css')
<style>
</style>
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
    <div class="button-field">
      <input type="submit" class="btn-search"value="Search">
      <button class="btn-update">Update</button>
      <button class="btn-prev" name="previous">PREV</button>
      <button class="btn-next" name="next">NEXT</button>
    </div>
    <input type="hidden" name="ticketHolder" value="">
  </div>
  <div id="text-field">
  </div>
</div>
@stop

@section('js')

<script>
  $(document).ready(function() {
    $( "#text-field" ).accordion();
    $('.btn-prev').attr('disabled','disabled');
    $('.btn-next').attr('disabled','disabled');

    setTimeout(function() {
        $('.update-info').slideUp('slow');
    }, 1000);

    $("button,input[type=submit]").button()

    /* Two variables created to save the ticketNumber / systemName passed here from PHP when search is clicked */
    var globalTicketNumber;
    var globalSystemName;

    /* Search Record */
    $(".btn-search").click(function(event) {
      event.preventDefault();
      var noError = true;

      var ticketNumber = $.trim($("input[name='ticketNumber']").val());
      var passengerName = $.trim($("input[name='passengerName']").val());
      var rloc           = $.trim($("input[name='rloc']").val());

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
            rloc:rloc},
          success: function(data){
            if(data.length>1){
              $.each(data,function(index,item){
                $("#text-field").append("<h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"</div>");
//                searchNext(item['orderOfDay'],item['dateOfFile']);
              });

              $( "#text-field" ).accordion( "destroy" );
              $( "#text-field" ).accordion({
                collapsible: true
              });

            }else{
              $.each(data,function(index,item){
                $("#text-field").append("<div class='text-block-single'>"+item['content']+"</div>"+"<script>");
                globalSystemName = item['systemName'];
                globalTicketNumber = item['ticketNumber'];
                if(item['content'].indexOf('S') != 0){
                  $('.btn-prev').button( "enable" );
                  $('.btn-next').button( "enable" );
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
     * This will find the next ticketNumber in row.
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
        success: function(data){
          $("#text-field").empty();
          $("#text-field").append("<div class='text-block-single'>"+data['content']+"</div>");
          globalTicketNumber = data['ticketNumber'];
          $('.btn-prev').button( "enable" );
          if((data['disable']) == 'disable'){
            $('.btn-next').button( "disable" );

          }
        }
      });
    });  //end btn-next

    /*
     * Previous Button
     * This will find the next ticketNumber in row.
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
          $("#text-field").empty();
          $("#text-field").append("<div class='text-block-single'>"+data['content']+"</div>");
          globalTicketNumber = data['ticketNumber'];
          $('.btn-next').button( "enable" );
          if((data['disable']) == 'disable'){
            $('.btn-prev').button( "disable" );

          }
        }
      });
    });  //end btn-prev


  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
