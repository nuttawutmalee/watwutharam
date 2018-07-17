@extends('mockup.watwutaram.layouts.master') @section('title', 'ติดต่อเรา — วัดวุฒาราม') @push('styles') @endpush @section('content')
<?php
$template_item = [
  'template' => 'banner',
  'image_url' => CMSHelper::getAssetPath('assets/images/banner/contact.jpg'),
  'image_alt' => '',
  'title' => 'ติดต่อเรา'
]
?>
  @include('mockup.watwutaram.templates.banner') @include('mockup.watwutaram.templates.contact') @endsection