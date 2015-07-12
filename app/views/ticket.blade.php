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
      <input class="rloc-field" type = "text" name="rloc" value="" placeholder="Please enter rloc number">
    </div>
    <input type="submit" class="btn-search"value="Search">
    <button class="btn-update">Update</button>
    <button class="btn-prev">PREV</button>
    <button class="btn-next">NEXT</button>
  </div>
  <div class="text-field">
  </div>
</div>
@stop

@section('js')

<script>
  $(document).ready(function() {
    setTimeout(function() {
        $('.update-info').slideUp('slow');
    }, 1000);

    $("button,input[type=submit]").button()

    $(".btn-search").click(function(event) {
      event.preventDefault();
      var ticketNumber = $.trim($("input[name='ticketNumber']").val());
      if($.isNumeric(ticketNumber)){
        $(".text-field").empty();
        $.ajax({
          method: "post",
          url: "/search",
          dataType: "json",
          data: {ticketNumber: ticketNumber},
          success: function(data){
            $(".text-field").append(data['content']);
          }
        });
      }else{
        $("input[name='ticketNumber']").val('');
        alert("please enter a number");
      }
    });


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
    });
     
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
