@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header 
        title="Dashboard" 
        subtitle="Welcome back, {{ Auth::user()->name }}! Here's an overview of your RSS feeds."
    />

  
@endsection 