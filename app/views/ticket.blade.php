@extends('layouts.master')
@section('css')
<style>

</style>
<script></script>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>E-Ticket Search</h1>
  </div>
  <div class="update-info">
    <h3>{{$num}} file(s) have been converted and updated.</h3>
  </div>
  <div class="sub-container">
      <form action="/report" method="POST" target="_blank">
        <div class="input-group">
          <div class="form-field">
            <label>Ticket Number without Airline Code :</label>
            <input class="ticket-field" type="text" name="ticketNumber" value="" placeholder="The 10 Digitals Tkt number ">
          </div>
          <div class="form-field">
            <label>Passenger Name : </label>
            <input class="name-field" type = "text" name="passengerName" value="" placeholder="Please enter Pax name">
          </div>
          <div class="form-field">
            <label>Record Locator : </label>
            <input class="rloc-field" type = "text" name="rloc" value="" placeholder="Please enter RLOC">
          </div>
          <div class="form-field">
            <label for="from">Date of Issue From</label>
            <input class="date-from-field" type="text" id="date-from-field" name="date-from-field" placeholder="Pick a from Date">
          </div>
          <div class="form-field">
            <label for="to">Date of Issue To</label>
            <input class="date-to-field" type="text" id="date-to-field" name="date-to-field" placeholder="Pick a to Date">
          </div>
        </div>
        <div class="button-field">
          <input type="submit" class="btn-search"value="Search">
          <button class="btn-update">Update</button>
          <button class="btn-report" type="submit">Report</button>
          <button class="btn-reset">Reset</button>
          <button class="btn-prev" name="previous"> << PREV</button>
          <button class="btn-next" name="next">NEXT >> </button>
          <button class="btn-next-record" name="nextRecord"> << Next Record</button>
          <button class="btn-prev-record" name="prevRecord">Prev Record>></button>
        </div> <!---end button-field -->
      </form>

    <input type="hidden" name="ticketHolder" value="">
  </div><!--end sub-container -->

  <div id="text-field">
  </div>

</div><!--end container -->
@stop

@section('js')
<script>
  $(document).ready(function() {
    var buttonBlock = "<div class='button-block'><button class='print-btn'>Print</button><button class='comment-btn'>Remarks</button></div>";
    /* Enable datepicker widget */
    // Sets both datepicker not able to select any date pass today
    // after date-from-field selected a date, date-to-field cannot select any date before the date date-from-field selected
    // e.g. date-from-field has the value of 08/08/2015 then date-to-field cannot select any date before 08/08/2015, only can select between 08/08/2015 and today
    $( "#date-from-field" ).datepicker({
          defaultDate: "",
//        changeMonth: true,
//        numberOfMonths: 2,
      onClose: function( selectedDate ) {
        $( "#date-to-field" ).datepicker( "option", "minDate", selectedDate );
      },
      maxDate: "0"
    });

/**********************************************************************/
      /* Reference for future use*/
//    /* Enable datepicker widget */
//    // Sets both datepicker not able to select any date pass today
//    // after date-from-field selected a date, date-to-field cannot select any date before the date date-from-field selected
//    // e.g. date-from-field has the value of 08/08/2015 then date-to-field cannot select any date before 08/08/2015, only can select between 08/08/2015 and today
//    $( "#date-from-field" ).datepicker({
////      defaultDate: "+1w",
////        changeMonth: true,
////        numberOfMonths: 2,
//      onClose: function( selectedDate ) {
//        $( "#date-to-field" ).datepicker( "option", "minDate", selectedDate );
//      },
//      maxDate: "0"
//    });
//
//    $( "#date-to-field" ).datepicker({
////      defaultDate: "+1w",
////        changeMonth: true,
////        numberOfMonths: 2,
//      onClose: function( selectedDate ) {
//        $( "#date-from-field" ).datepicker( "option", "maxDate", selectedDate ? selectedDate: "0");
//      },
//      maxDate: "0"
//    });
/**********************************************************************/

      /* Enable datepicker widget */
      // Only restriction is cannot pick any date pass today
      $( "#date-from-field" ).datepicker({
          maxDate: "0"
      });

      $( "#date-to-field" ).datepicker({
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

          if ($.isNumeric(ticketNumber) || ticketNumber == "") {
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
                            var comment = '';
                            $.each(item['comments'],function(index,note){
                              comment += "<div class='single-comment'><p>"+note+"</p></div>"
                            });
                            $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"<div class='comment-area'>"+comment+"</div>"+buttonBlock+"</div></div>");
                            });
                            $(".print-btn").button();
                            $('.comment-btn').button();
                            $( "#text-field" ).accordion( "destroy" );
                            $( "#text-field" ).accordion({
                                collapsible: true,
                                header: "> div > h3",
                                animate: 0,
                            });
                            $('.btn-prev-record').button( "enable" );
                            $('.btn-next-record').button( "enable" );
                      }else{
                        if(data['error'] != null){
                          $("#text-field").append("<div class='group'>"+data['error']+"</div>");
                        }else{
                          $.each(data,function(index,item) {
                            var comment = '';
                            $.each(item['comments'],function(index,note){
                              comment += "<div class='single-comment'><p>"+note+"</p></div>"
                            });
                            $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"<div class='comment-area'>"+comment+"</div>"+buttonBlock+"</div></div>");
                            $(".print-btn").button();
                            $(".comment-btn").button();
                            $( "#text-field" ).accordion( "destroy" );
                            $( "#text-field" ).accordion({
                                collapsible: true,
                                header: "> div > h3",
                                animate: 0,
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
                            }else if(item['disable-next'] == 'disable-next'){
                                $('.btn-next').button( "disable" );
                            }else if(item['disable-prev'] == 'disable-prev'){
                                $('.btn-prev').button( "disable" );
                            }
                          });
                        }
                      }
                    $("input[name='ticketNumber']").val('');
                    $("input[name='passengerName']").val('');
                    $("input[name='rloc']").val('');
                    $("input[name='date-from-field']").val('');
                    $("input[name='date-to-field']").val('');
                  }
              });
          }
      }); //end btn-search

      /* Only used inside search where only one record is found */
      function displaySingleDataFromSearch(data){
          $.each(data,function(index,item) {
              $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"<button class='print-btn'>Print</button></div></div>");
              $( "#text-field" ).accordion( "destroy" );
              $( "#text-field" ).accordion({
                  collapsible: true,
                  header: "> div > h3",
                  animate: 10
              });

              globalSystemName = item['systemName'];
              globalTicketNumber = item['ticketNumber'];

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
              //Enables all buttons first and use the codes below to check which one should be disabled
              $('.btn-prev').button( "enable" );
              $('.btn-next').button( "enable" );


      }

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
//      $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>" + data['dateOfFile'] + "</span><span>" + data['paxName'] + "</span><span>" + data['airlineName'] + "</span></h3><div class='text-block'>" + data['content']+"<div class='comment-area'></div>"+buttonBlock+"</div></div>");
      var comment = '';
      $.each(data['comments'],function(index,note){
          comment += "<div class='single-comment'><p>"+note+"</p></div>"
      });
      $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>"+data['dateOfFile']+"</span><span>"+data['paxName']+"</span><span>"+data['airlineName']+"</span></h3><div class='text-block'>"+data['content']+"<div class='comment-area'>"+comment+"</div>"+buttonBlock+"</div></div>");
      $(".print-btn").button();
      $('.comment-btn').button();
      $("#text-field").accordion("destroy");
      $("#text-field").accordion({
        collapsible: true,
        header: "> div > h3",
        animate: 10
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
      $('.comment-btn').button('destroy');
      $('.print-btn').button('destroy');
      $(".group:first").find('h3').removeClass( "ui-accordion-header-active ui-state-active ui-corner-top" );
      var content = $(".group:first").html();
      $("div").remove(".group:first");
      $("#text-field").append("<div class='group'>"+content+"<div>");
      $("#text-field").accordion("refresh");
    });

    $(".btn-prev-record").click(function(event) {
      /* Act on the event */
      event.preventDefault();
      $('.comment-btn').button('destroy');
      $('.print-btn').button('destroy');
      $('.group:first').find('h3').removeClass( "ui-accordion-header-active ui-state-active ui-corner-top" );
      var content =  $(".group:last").html();
      $("div").remove(".group:last");
      var rest = $("#text-field").html();
      $("#text-field").empty();
      $("#text-field").append("<div class='group'>"+content+"<div>");
      $("#text-field").append(rest);
      $("#text-field").accordion("refresh");
    });

    var inputField = "<div class='inputField'><input class='comment-input' placeholder='Please enter your comment.' name='comment-input' /><button class='comment-save'>SAVE</button><button class='comment-cancel'>CANCEL</button></div>";
    $("#text-field").on('click','.comment-btn',function(e){
      $(this).parents('.text-block').append(inputField);
      $('.comment-btn').button( "disable" );
    })

    $("#text-field").on('click','.comment-save',function(e){
      var comment = $.trim($("input[name='comment-input']").val());
      var ticketNumber = $(this).closest('.group').find('.ticket-highlight').text()
      var comentArea = $(this).closest('.group').find('.comment-area');
      $.ajax({
        method: "post",
        url: "/saveComment",
        dataType: "json",
        data: {ticketNumber:ticketNumber,
          comment:comment
          },
        success: function(data){
          comentArea.append("<div class='single-comment'><p>"+data['comment']+"</p></div>");
          $("#text-field").find('.inputField').remove();
          $('.comment-btn').button("enable");
        }
      })
    })


    $("#text-field").on('click','.comment-cancel',function(e){
      $('.comment-btn').button( "enable" );  
      $(this).parent().remove();
    })

    $( "#text-field" ).on( "accordionactivate", function( event, ui ) {
      $('.comment-btn').button();
      $('.print-btn').button();
      $("#text-field").find('.inputField').remove();
      $('.comment-btn').button("enable");
    });

    $("#text-field").on('click','h3',function(e){
      if($(this).hasClass('ui-accordion-header-active')){
        $('.comment-btn').button('destroy');
        $('.print-btn').button('destroy');
        var content = '';
        var test = $(this).parent().prevAll();
        $.each(test, function(index, val) {
           content = "<div class='group'>"+val.innerHTML+"</div>" + content;
           $("div").remove(".group:first");
        });
        var rest = $("#text-field").html();
        $("#text-field").empty();
        $("#text-field").append(rest);
        $("#text-field").append(content);
        $("#text-field").accordion("refresh");
        $("#text-field").accordion( "option", "active", 0);
      }
    })

      /*****************/
      /* Report Record */
      /*****************/
      $('form').submit(function(event){
          var ticketNumber = $.trim($("input[name='ticketNumber']").val());
//          var passengerName = $.trim($("input[name='passengerName']").val());
//          var rloc = $.trim($("input[name='rloc']").val());
//          var fromDate = $.trim($("input[name='date-from-field']").val());
//          var toDate = $.trim($("input[name='date-to-field']").val());

          var noError = true;

          if($.isNumeric(ticketNumber) || ticketNumber==""){
              noError = true;
          }else{
              noError = false;
              $("input[name='ticketNumber']").val('');
              alert("please enter a number");
              event.preventDefault();
          }
          if(noError){
              return;
          }
      });

      $('.btn-reset').on('click', function(){
         alert('hi');
      });

//      $('.btn-report').click(function(e){
//          $('.btn-prev').button( "disable" );
//          $('.btn-next').button( "disable" );
//          $('.btn-prev-record').button( "disable" );
//          $('.btn-next-record').button( "disable" );
//          e.preventDefault();
//          var noError = true;
//          var ticketNumber = $.trim($("input[name='ticketNumber']").val());
//          var passengerName = $.trim($("input[name='passengerName']").val());
//          var rloc = $.trim($("input[name='rloc']").val());
//          var fromDate = $.trim($("input[name='date-from-field']").val());
//          var toDate = $.trim($("input[name='date-to-field']").val());
//          if($.isNumeric(ticketNumber) || ticketNumber==""){
//              noError = true;
//          }else{
//              noError = false;
//              $("input[name='ticketNumber']").val('');
//              alert("please enter a number");
//          }
//          if(noError){
//              $.ajax({
//                  method: "post",
//                  url: "/report",
//                  dataType: "json",
//                  data: {ticketNumber: ticketNumber, passengerName: passengerName, rloc: rloc, fromDate: fromDate, toDate: toDate},
//                  success: function(data){
//                      if(jQuery.isEmptyObject(data)){
//                          $("#text-field").empty();
//                          $("#text-field").append("<div class='text-block'>Sorry the document does not exist, or has not been update yet, please click update and try again.</div>");
//                      }else {
//                          $("#text-field").empty();
//                          $("#text-field").append("<button class='print-btn'>Print</button>");
//                          $.each(data, function (index, item) {
//                              $("#text-field").append("<div class='group'><h3 class='block-hearder'><span>" + item['dateOfFile'] + "</span><span>" + item['paxName'] + "</span><span>" + item['airlineName'] + "</span></h3><div class='text-block'>" + item['content'] + "</div></div>");
//                          });
//                          $("#text-field").append("<button class='print-btn'>Print</button>");
//                      }
//                  }
//              });
//          }
//          $("input[name='ticketNumber']").val('');
//          $("input[name='passengerName']").val('');
//          $("input[name='rloc']").val('');
//          $("input[name='date-from-field']").val('');
//          $("input[name='date-to-field']").val('');
//      }); // end btn-report
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
