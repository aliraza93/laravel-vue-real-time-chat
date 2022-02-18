@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
@endsection

@section('content')

<div class="container mt-3">
    <div class="row clearfix">
        <div class="col-lg-12">
            <private-chat :user="{{ Auth::user() }}"></private-chat>
        </div>
    </div>
</div>
@endsection
