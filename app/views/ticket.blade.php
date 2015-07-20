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
      <button class="btn-next-record" name="nextRecord">Next Record</button>
      <button class="btn-prev-record" name="prevRecord">Prev Record</button>
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
    var maxIndexForDoc;
    $( "#text-field" ).accordion();
    $('.btn-prev').attr('disabled','disabled');
    $('.btn-next').attr('disabled','disabled');
    $('.btn-prev-record').attr('disabled','disabled');
    $('.btn-next-record').attr('disabled','disabled');

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
              maxIndexForDoc = data.length -1;
              $.each(data,function(index,item){
                $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"</div></div>");
              });

              $( "#text-field" ).accordion( "destroy" );
              $( "#text-field" ).accordion({
                collapsible: false,
                header: "> div > h3"
              })
              
              $('.btn-prev-record').button( "enable" );
              $('.btn-next-record').button( "enable" );
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
     * This will find the next ticketNumber with increment of 1.
     * (Still need to try figure out how to do next ticketNumber with the same systemName in case the ticketNumber isn't always increment of 1)
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
          if(data['content'].indexOf('>') > 0){
            if((ticketNumber + 1) == data['ticketNumber']){
              globalTicketNumber++;
              console.log(ticketNumber);
            }
          }else{
            $('.btn-next').button( "disable" );
          }
        }
      });
    });  //end btn-next

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

  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
