@extends('admin.index')

@section('content')
    <livewire:show-user :user="$user" />
@endsection
