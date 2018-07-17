@extends('mockup.watwutaram.layouts.master') @section('title', 'วัดวุฒาราม') @push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/text.jpg'),
  'image_alt' => '',
  'title' => 'หัวข้อใหญ่'
]
?>
  @include('mockup.watwutaram.templates.banner') @include('mockup.watwutaram.templates.text') @endsection