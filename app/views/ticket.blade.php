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
    <button>Update</button>
    <input type="text" name="ticketNumber" value="" placeholder="Please enter ticket number">
    <button>Search</button>
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
    }, 2000);

    $( "button" ).button()
     
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
