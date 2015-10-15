@extends('layouts.master')
@section('css')
<style>
  div.container{width:620px;}
    div#text-field{float:none; width: initial; min-height: 400px; margin-top: 0px;}
    button.btn-print, button.btn-back{float: right; position: relative; top: 7px;}
    #text-field pre{float:none;}
</style>
@stop

@section('contents')
<div class="container">
  <!-- <div class="title">
    <h1>REPORT</h1>
    @if($back)
      <a href="/ticket"><button class='btn-back ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'>Back</button></a>
    @else
      <button class='btn-print ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'>Print</button>
    @endif
  </div> -->
  <div id="text-field">
    <div class='text-block-single'>{{ $long }}</div>
  </div>
  @if(!$back)
    <button class='btn-print ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'>Print</button>
  @endif

</div>
@stop

@section('js')
  <script>
    $(document).ready(function() {
      $('.btn-print').button();
      $('.btn-back').button();
    });
  </script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
