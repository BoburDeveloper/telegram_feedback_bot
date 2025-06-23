@extends('layouts.telegrambot')

@section('content')

<h2 class="text-center">{{__('messages.'.$action.'_tbot')}}</h2>

<div class="container-fluid">
    
    <form action="{{$_SERVER['REQUEST_URI']}}" method="post">
        {{csrf_field()}}
        
        <div class="form-group">
            <label for="tbot_name">{{__('messages.tbot_name')}}</label>
            <input type="text" class="form-control" name="tbot[name]" value="{{isset($bot->name) ? $bot->name : null}}" />
        </div>
        <div class="form-group">
            <label for="tbot_username">{{__('messages.tbot_username')}}</label>
            <input type="text" class="form-control" name="tbot[username]" value="{{isset($bot->username) ? $bot->username : null}}" />
        </div>
        <div class="form-group">
            <label for="tbot_token">{{__('messages.tbot_token')}}</label>
            <input type="text" class="form-control" name="tbot[token]" value="{{isset($bot->token) ? $bot->token : null}}" />
        </div>
        <div class="form-group text-end">
            <button type="submit" class="btn btn-{{$action=='add' ? 'success' : 'primary'}}">{{__('messages.save')}}</button>
        </div>
    </form>
</div>

@endsection