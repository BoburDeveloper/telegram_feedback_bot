@extends('layouts.telegrambot')

@section('content')

	<link rel="stylesheet" href="{{asset($asset_theme.'libs/fontawesome/css/all.min.css')}}" />

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fab fa-telegram"></i> {{ __('messages.my_tbot') }}</h2>
        <div class="clearfix"></div>
        @if($isCreateTrue)
            <a href="javascript:void(0);" class="btn btn-success btn-sm open-form" data-action="add" data-id="0" data-bs-toggle="modal" data-bs-target="#tgBotModal">
                <i class="fa fa-plus"></i> {{ __('messages.add_tbot') }}
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    <table class="table table-striped table-bordered shadow-sm">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>{{ __('messages.tbot_name') }}</th>
                <th>{{ __('messages.tbot_username') }}</th>
                <th>{{ __('messages.tbot_token') }}</th>
                <th>{{ __('messages.tbot_api_url') }}</th>
                <th>{{ __('messages.edit') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bots as $i => $bot)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><a href="https://t.me/{{strtr($bot->username, ['@'=>''])}}"><i class="fas fa-link"></i> {{ $bot->name }} </a></td>
                    <td>{{ $bot->username }}</td>
                    <td><span class="token-mask">••••••••••••</span></td>
                    <td><a href="{{env('APP_URL')}}/api/telegraph/{{$bot->token}}/handle" class="btn btn-secondary btn-sm"><i class="fas fa-link"></i> Visit</a></span></td>
                    <td>
                        <a href="javascript:void(0);" class="btn btn-outline-primary btn-sm open-form" data-action="edit" data-id="{{ $bot->id }}" data-bs-toggle="modal" data-bs-target="#tgBotModal">
                            <i class="fa fa-edit"></i> {{ __('messages.edit') }}
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $bots->links('pagination::bootstrap-5') }}


        <h5 class="col-12"><i class="fab fa-telegram-plane"></i> Telegram chat for demonstration: <a href="https://t.me/+piYBBPeMv2VkMzYy"><i class="fas fa-link"></i> https://t.me/+piYBBPeMv2VkMzYy</a></h5>
        <h5 class="col-12"><i class="fas fa-envelope"></i> Email for notification will set manually on .env file</a></h5>
        <h5 class="col-12"><i class="fas fa-database"></i> chat_id will append to DB after first messaging with the bot or adding the bot to your group</a></h5>
        <h5 class="col-12"><i class="fas fa-comments"></i> You should get exactly chat_id from DB and set to MY_GROUP_ID on `.env` file which you want to make feedback environment</a></h5>
        <h5 class="col-12"><i class="fas fa-robot"></i> After adding the bot to the group you should give it admin role for working properly</a></h5>
</div>

<!-- Modal -->
<div class="modal fade" id="tgBotModal" tabindex="-1" aria-labelledby="tgBotModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tgBotModalLabel">Bot Form</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function(){
        $('.open-form').on('click', function() {
            const modalBody = $('#tgBotModal .modal-body');
            modalBody.html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            const id = $(this).data('id');
            const action = $(this).data('action');
            $.ajax({
                url: '/form/' + action + '/' + id,
                method: 'get',
                success:function(data) {
                    modalBody.html(data);
                },
                error: function() {
                    modalBody.html('<div class="text-danger">Failed to load form.</div>');
                }
            });
        });
    });
</script>
@endsection
