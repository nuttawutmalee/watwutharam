@extends('mockup.watwutaram.layouts.master') @section('title', 'บทความ — วัดวุฒาราม') @push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/articles.jpg'),
  'image_alt' => '',
  'title' => 'บทความ'
]
?>
  @include('mockup.watwutaram.templates.banner') @include('mockup.watwutaram.templates.articles') @endsection