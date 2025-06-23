@extends('layouts.telegrambot')

@section('content')
<div class="container-fluid">
<h2 class="text-center">{{__('messages.my_tbots')}}</h2>

<a href="javascript:void(0);" class="btn btn-success float-end open-form" data-action="add" data-id="0" data-bs-toggle="modal" data-bs-target="#tgBotModal">{{__('messages.add_tbot')}}</a>
<div class="clearfix"></div>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if (session('info'))
    <div class="alert alert-info">
        {{ session('info') }}
    </div>
@endif

    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th style="width:200px;">{{__('messages.tbot_name')}}</th>
                <th style="width:200px;">{{__('messages.tbot_username')}}</th>
                <th>{{__('messages.tbot_token')}}</th>
                <th  style="width:200px;">{{__('messages.edit')}}</th>
            </tr>
        </thead>
        <tbody>
            @php
            $i=0;
            @endphp
             @foreach ($bots as $bot)
                @php
                    $i++;
                @endphp
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $bot->name }}</td>
                    <td>{{ $bot->username }}</td>
                    <td>{{ $bot->token }}</td>
                    <td><a href="javascript:void(0);" class="open-form" data-action="edit" data-id="{{$bot->id}}" data-bs-toggle="modal" data-bs-target="#tgBotModal">{{__('messages.edit')}}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
        {{ $bots->links('pagination::bootstrap-5') }}
</div>

<!-- Modal -->
<div class="modal fade" id="tgBotModal" tabindex="-1" aria-labelledby="tgBotModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="tgBotModalLabel">Form</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        opening...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('.open-form').on('click', function() {
        let id = $(this).data('id');
        let action = $(this).data('action');
        $.ajax({
            url: '/form/'+action+'/'+id,
            method: 'get',
            success:function(data) {
                $('#tgBotModal .modal-body').html(data);
            }
        
        });
    });
});
</script>

@endsection