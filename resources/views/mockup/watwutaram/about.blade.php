@extends('mockup.watwutaram.layouts.master') @section('title', 'เกี่ยวกับเรา — วัดวุฒาราม') @push('styles') @endpush @section('content')

<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/about.jpg'),
  'image_alt' => '',
  'title' => 'เกี่ยวกับเรา'
]
?>
  @include('mockup.watwutaram.templates.banner') @include('mockup.watwutaram.templates.about') @endsection