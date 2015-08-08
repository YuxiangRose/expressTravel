@extends('layouts.master')
@section('css')
<style>
</style>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>Date Range From <i>2015-07-09</i> to <i>2015-01-01</i></h1>
  </div>
  <div id="text-field">
    <div class='text-block-single'>asdldfl;gajlfjasdjfklas;fdljaskjdf
      asdkflajsdf;lkajsdf
      sajkldfjk;alsjfa'f</div>
  </div>
</div>
@stop

@section('js')

<script>
  $(document).ready(function() {
    console.log(window.opener.data);
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
