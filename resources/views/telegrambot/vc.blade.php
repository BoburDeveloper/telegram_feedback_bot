@extends('layouts.telegrambot')
@section('content')
 @if(!request()->get('messaging'))
                    <div class="text-start">
                        <a href="{{env('APP_URL')}}" title="{{__('messages.return_back')}}" class="btn btn-primary"><i class="fas fa-home"></i> <i class="fas fa-long-arrow-alt-left"></i></a>
                    </div>
                @endif
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>{{__('messages.action')}}</th>
                <th>{{__('messages.code')}}</th>
                <th>{{__('messages.expires_at')}}</th>
                <th>{{__('messages.chat_name')}}</th>
                <th>{{__('messages.verified')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vc as $i => $item)
            <tr>
                <td>{{ $vc->firstItem() + $i }}</td>
                <td><a href="javascript:void(0);" onclick="myVC({{$item->chat_id}})" class="btn btn-success vc-btn" id="vc-btn-{{$item->chat_id}}" data-chat_id="{{$item->chat_id}}">{{__('messages.send')}}</a></td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->expires_at }}</td>
                <td>{{ $item->chat_name ?? '-' }}</td>
                <td> {!! $item->verified 
                ? 
                '<i class="text-success fas fa-check"></i>' 
                : 
                '<h4 class="text-danger bold">&times;</h4>' !!} 
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Sahifalash tugmalari --}}
<div class="row col-12 justify-content-center">
   {{ $vc->links('pagination::bootstrap-5') }}
</div>
@endsection