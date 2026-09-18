@component('mail::message')
# invites you to join {{ $companyName }}

Hello {{ $employeeName }}，

{{ $inviterName }} invites you to join **{{ $companyName }}** team.

@component('mail::button', ['url' => $acceptUrl])
Accept Invitation
@endcomponent

此邀请链接7天内有效。If you did not expect this email, please ignore it.

Thanks!
@endcomponent
