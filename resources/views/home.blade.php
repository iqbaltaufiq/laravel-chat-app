@extends('layouts.app')

@section('content')
<chatroom-component :userprop="{{ Auth::user() }}"></chatroom-component>
@endsection
