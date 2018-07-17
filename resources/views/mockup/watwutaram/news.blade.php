@extends('mockup.watwutaram.layouts.master') @section('title', 'ข่าวสาร — วัดวุฒาราม') @push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/news.jpg'),
  'image_alt' => '',
  'title' => 'ข่าวสาร'
]
?>
  @include('mockup.watwutaram.templates.banner')@include('mockup.watwutaram.templates.news') @endsection