@extends('layouts.telegrambot')

@section('content')
    
<div class="container py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold">
            <i class="fab fa-telegram text-primary"></i>
            {{ __('messages.'.$action.'_tbot') }} 
        </h3>
    </div>

    <form action="{{ request()->url() }}" method="POST" class="border rounded shadow-sm p-4 bg-white" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="formFile" class="form-label"><b>{{__('messages.upload_image')}}</b></label>
            <input class="form-control" type="file" name="image" id="formFile">
            <input type="text" class="form-control" name="tbot_messaging[caption]" placeholder="{{__('messages.image_caption')}}">
        </div>
        <div class="mb-3">
            <label for="tbot_name" class="form-label"><b>{{ __('messages.text') }}</b></label>
            <textarea class="form-control" id="editor" name="tbot_messaging[message]">{{ old('tbot_messaging.message') }}</textarea>
        </div>
        <div class="row">

            <div class="col-md-6">
                @if(!request()->get('messaging'))
                    <div class="text-start">
                        <a href="{{env('APP_URL')}}" title="{{__('messages.return_back')}}" class="btn btn-primary"><i class="fas fa-home"></i> <i class="fas fa-long-arrow-alt-left"></i></a>
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="text-end">
                    <button type="submit" class="btn btn-{{ $action == 'add' ? 'success' : 'primary' }}">
                        <i class="fas fa-paper-plane"></i> {{ __('messages.send') }}
                    </button>
                </div>
            </div>
        </div>

    </form>

        <h3 class="fw-bold mt-4">
            <i class="fas fa-envelope text-primary"></i>
            {{ __('messages.sent_messages') }}
        </h3>
	@if(isset($messagings))
		<table class="table table-hovered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>{{__('messages.image')}}</th>
					<th>{{__('messages.caption')}}</th>
					<th>{{__('messages.text')}}</th>
				</tr>
			</thead>
			<tbody>
				@php
						$i=0;
						@endphp
							@foreach($messagings as $key => $value)
						@php
						$i++;
				@endphp
				<tr>
					<td>{{$i}}</td>
					<td><img src="{{$value->image}}" class="img-fluid img-td" /></td>
					<td>{{$value->caption}}</td>
					<td>{{$value->message}}</td>
				</tr>
				@endforeach
				

			</tbody>
		</table>
        {{ $messagings->links('pagination::bootstrap-5') }}
	@endif
</div>

@endsection
