@extends('layouts.master')
@section('css')
<style>
  div.container{width:620px;}
    div#text-field{float:none; width: initial; min-height: 400px;}
    #text-field pre{float:none;}
</style>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>REPORT</h1>
  </div>
  <div id="text-field">
    <div class='text-block-single'>{{ $long }}</div>
  </div>
</div>
@stop

@section('js')

{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
