@extends('admin.index')

@section('content')
    <livewire:edit-user :user="$user" />
@endsection
