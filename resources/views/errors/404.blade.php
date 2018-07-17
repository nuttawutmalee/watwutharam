@extends('mockup.watwutaram.layouts.master') 
@section('title', 'ไม่พบหน้าที่คุณต้องการ — วัดวุฒาราม')
@push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/error/error.jpg'),
  'image_alt' => '',
  'title' => '๔๐๔ ไม่พบหน้าที่คุณต้องการ'
]
?>
    @include('mockup.watwutaram.errors.404') @endsection