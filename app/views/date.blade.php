@extends('layouts.master')
@section('css')
<style>
  div.container{width:620px;}
    div#text-field{float:none; width: initial; min-height: 400px; margin-top: 50px;}
    button.btn-print{float: right; position: relative; top: 7px;}
    #text-field pre{float:none;}
</style>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>REPORT</h1>
    <button class='btn-print ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'>Print</button>
  </div>
  <div id="text-field">
    <div class='text-block-single'>{{ $long }}</div>
  </div>
  <button class='btn-print ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only'>Print</button>
</div>
@stop

@section('js')
  <script>
    $('.btn-print').button();
  </script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
