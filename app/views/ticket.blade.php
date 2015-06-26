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
    <h3>0 files have been convert and updated.</h3>
  </div>
  <div class="sub-container">
    <input class="ticket-field" type="text" name="ticketNumber" value="" placeholder="Please enter ticket number">
    <input type="submit" class="btn-search"value="Search">
    <button class="btn-update">Update</button>
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
      /*//event.preventDefault();
      var ticketNumber = $("input[name='ticketNumber']").val();
      //$(".text-field").empty();
      $.ajax({
        method: "post",
        url: "/search",
        dataType: "json",
        success: function(data){
          //$(".text-field").append(data);
          alert("dfsdf");
        }
      });*/
      event.preventDefault();
      $.ajax({
        method: "post",
        url: "/search",
        dataType: "text",
        success: function(data){
          $(".text-field").append("<pre>"+data+"</pre>");
        }
      });
    });

    $(".btn-update").click(function(event) {
      event.preventDefault();
      $.ajax({
        method: "get",
        url: "/update",
        dataType: "json",
        success: function(data){
          $(".update-info h3").text(data+' files have been convert and updated.')
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
