@component('mail::message')
@if (isset($body['user_data']))
<h2>Hello {{$body['user_data']['first_name']}},</h2><br>
@else
<h2>Hello Admin,</h2><br>
@endif
<h3>Leave Application Details</h3><br>
<p>Employee Id :{{$body['finger_id']}},</span>
<span>Name : {{$body['name']}},</span>
<span>Date : {{$body['date']}},</span>
<span>From Date : {{$body['from']}},</span>
<span>To Date : {{$body['to']}},</span>
<span>Leave Type : {{$body['type']}},</span>
<span>No of Days : {{$body['days']}},</span></p>
<div style="width: 500px">
    <p style="width: 250px" >@component('mail::button', ['url' => $body['url_a']])
        Accept
        @endcomponent</p>
         <p class="text-center">or</p>
        <p style="width: 250px">@component('mail::button', ['url' => $body['url_b']])
        Reject
        @endcomponent</p>
</div>
 
Thanks,<br>
{{ config('app.name') }}<br>
TECHHRM Team.
@endcomponent