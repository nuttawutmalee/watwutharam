@extends('mockup.watwutaram.layouts.master') @section('title', 'รูปภาพ — วัดวุฒาราม') @push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/gallery.jpg'),
  'image_alt' => '',
  'title' => 'รูปภาพ'
]
?>
  @include('mockup.watwutaram.templates.banner') @include('mockup.watwutaram.templates.gallery') @endsection