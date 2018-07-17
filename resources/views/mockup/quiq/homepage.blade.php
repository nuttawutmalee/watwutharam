@extends('mockup.quiq.layouts.master')

@push('styles')
    <style type="text/css">
        .welcome {
            position: absolute;
            top: 50%;
            left: 50%;
            font-size: 3rem;
            text-transform: uppercase;
            color: #888;
            font-weight: 300;
            transform: translate3d(-50%, -50%, 0);
            margin: 0;
            width: auto;
            text-align: center;
        }
    </style>
@endpush

@section('content')
    <h1 class="welcome">Homepage of mockup</h1>
@endsection