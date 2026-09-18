@component('mail::message')
# 邀请您加入 {{ $companyName }}

你好 {{ $employeeName }}，

{{ $inviterName }} 邀请您加入 **{{ $companyName }}** 团队。

@component('mail::button', ['url' => $acceptUrl])
接受邀请
@endcomponent

此邀请链接7天内有效。如果您未预期收到此邮件，请忽略。

谢谢！
@endcomponent
