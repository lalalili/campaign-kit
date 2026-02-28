@extends('layouts.index')

@section('content')
    <link href="{{ asset('css/campaigns/type{{TYPE}}.css') }}" rel="stylesheet" type="text/css">

    <div class="campaign-type{{TYPE}} campaign-type{{TYPE}}--desktop">
        <div class="campaign-type{{TYPE}}__inner">
            <h1>{{ $campaign->campaign_title ?? 'Campaign Type {{TYPE}}' }}</h1>
        </div>
    </div>
@endsection
