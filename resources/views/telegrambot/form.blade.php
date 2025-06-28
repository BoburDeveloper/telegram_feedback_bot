@extends('layouts.telegrambot')

@section('content')

<div class="container py-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold">
            <i class="fab fa-telegram text-primary"></i>
            {{ __('messages.'.$action.'_tbot') }}
        </h3>
    </div>

    <form action="{{ request()->url() }}" method="POST" class="border rounded shadow-sm p-4 bg-white">
        @csrf

        <div class="mb-3">
            <label for="tbot_name" class="form-label">{{ __('messages.tbot_name') }}</label>
            <input type="text" class="form-control" id="tbot_name" name="tbot[name]" value="{{ old('tbot.name', $bot->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="tbot_username" class="form-label">{{ __('messages.tbot_username') }}</label>
            <input type="text" class="form-control" id="tbot_username" name="tbot[username]" value="{{ old('tbot.username', $bot->username ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="tbot_token" class="form-label">{{ __('messages.tbot_token') }}</label>
            <input type="text" class="form-control" id="tbot_token" name="tbot[token]" value="{{ old('tbot.token', $bot->token ?? '') }}" required>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-{{ $action == 'add' ? 'success' : 'primary' }}">
                <i class="fas fa-save"></i> {{ __('messages.save') }}
            </button>
        </div>
    </form>
</div>

@endsection
