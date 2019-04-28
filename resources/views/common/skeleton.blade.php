@extends("$parent.master")

@section('title', $title)

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ $title }}</h1>
    </section>
    <section class="content"></section>
</div>

{{ $bottom ?? '' }}
@endsection
