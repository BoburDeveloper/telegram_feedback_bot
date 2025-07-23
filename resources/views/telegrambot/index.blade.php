@extends('layouts.telegrambot')

@section('content')

<div class="container">
    <div class="justify-content-between align-items-center mb-4">

        <div class="bg-primary text-white p-4 rounded shadow mb-4">
            <h1 class="fw-bold"><i class="fab fa-telegram"></i> Telegram Broadcast & Feedback System</h1>
            <p class="mb-0">📢 1-click send feedback and get answer • 🔐 SMS-style Verification • 📩 Email notification</p>
        </div>

        @if($isCreateTrue)
            <a href="javascript:void(0);" class="btn btn-success btn-sm open-form" data-action="add" data-id="0" data-bs-toggle="modal" data-bs-target="#tgBotModal">
                <i class="fa fa-plus"></i> {{ __('messages.add_tbot') }}
            </a>
        @endif
    </div>

    <div class="row text-center mb-4">
    <div class="col-md-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <h3 class="text-primary fw-bold">{{$bots_num}}</h3>
            <p>Bots created</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <h3 class="text-success fw-bold">{{$bot_messagings_num}}</h3>
            <p>Broadcasts sent</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-light rounded shadow-sm">
            <h3 class="text-warning fw-bold">{{$is_verified_num}}</h3>
            <p>Verified users</p>
        </div>
    </div>
</div>


    <div class="mt-4">
        @include('partials.alerts')

        <table class="table table-striped table-bordered shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.tbot_name') }}</th>
                    <th>{{ __('messages.tbot_username') }}</th>
                    <th>{{ __('messages.tbot_token') }}</th>
                    <th>{{ __('messages.tbot_api_url') }}</th>
                    <th>{{ __('messages.message') }}</th>
                    <th>{{ __('messages.verification_code') }}</th>
                    <th>{{ __('messages.edit') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bots as $i => $bot)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <a href="https://t.me/{{ ltrim($bot->username, '@') }}" target="_blank">
                                <i class="fas fa-link"></i> {{ $bot->name }}
                            </a>
                        </td>
                        <td>{{ $bot->username }}</td>
                        <td><span class="token-mask">••••••••••••</span></td>
                        <td>
                            <a href="{{ env('APP_URL') }}/api/telegraph/{{ $bot->token }}/handle" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="fas fa-link"></i> Visit
                            </a>
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="btn btn-success btn-sm messaging-form" data-action="messaging" data-id="{{ $bot->id }}" data-bs-toggle="modal" data-bs-target="#tgBotMessagingModal">
                                <i class="fa fa-envelope"></i> {{ __('messages.message') }}
                            </a>
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="btn btn-success btn-sm vc-form" data-action="vc" data-id="{{ $bot->id }}" data-bs-toggle="modal" data-bs-target="#verificationCodeModal">
                                <i class="fa fa-envelope"></i> {{ __('messages.send_verification') }}
                            </a>
                        </td>
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

        <div class="mt-4">
            <h5><i class="fab fa-telegram-plane"></i> Telegram demo group: 
                <a href="https://t.me/+piYBBPeMv2VkMzYy" target="_blank">
                    <i class="fas fa-link"></i> https://t.me/+piYBBPeMv2VkMzYy
                </a>
            </h5>
            <h5><i class="fas fa-envelope"></i> Notification email must be set in your <code>.env</code> file.</h5>
            <h5><i class="fas fa-database"></i> <code>chat_id</code> will be stored automatically after the first message or adding the bot to your group.</h5>
            <h5><i class="fas fa-comments"></i> Copy the exact <code>chat_id</code> from DB and set it in <code>MY_GROUP_ID</code> in <code>.env</code> for feedback functionality.</h5>
            <h5><i class="fas fa-robot"></i> After adding the bot to a group, grant it admin rights for full functionality.</h5>
        </div>
</div>
</div>
    

<!-- Modals -->
@include('partials.modals.bot_form')
@include('partials.modals.messaging_form')
@include('partials.modals.verification_code')

<script>
    $(function(){

       $.ajaxSetup({
            headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });


        const loadForm = (modalSelector, id, action, messaging = false) => {
            const modalBody = $(`${modalSelector} .modal-body`);
            modalBody.html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
            $.ajax({
                url: `/form/${action}/${id}`,
                method: 'get',
                data: messaging ? { messaging: true } : {},
                success: data => modalBody.html(data),
                error: () => modalBody.html('<div class="text-danger">Failed to load form.</div>')
            });
        };

        $('.open-form').on('click', function() {
            loadForm('#tgBotModal', $(this).data('id'), $(this).data('action'));
        });

        $('.messaging-form').on('click', function() {
            loadForm('#tgBotMessagingModal', $(this).data('id'), $(this).data('action'), true);
        });

        $('.vc-form').on('click', function() {
            loadForm('#verificationCodeModal', $(this).data('id'), $(this).data('action'), true);
        });


    });

            function myVC(chat_id) {
      
                    $.ajax({
                    url: `/telegram/send-code`,
                    method: 'post',
                    data: {'chat_id': chat_id,         '_token': '{{ csrf_token() }}'},
                    success: function(data) {
                        $('#vc-btn-'+chat_id).parent().html('<div class="text-success">Verfication code is sent!</div>');
                    },
                    error: () =>  $('#vc-btn-'+chat_id).parent().html('<div class="text-danger">Failed to sent verification code.</div>')
                    

                });            
            }
</script>
@endsection
