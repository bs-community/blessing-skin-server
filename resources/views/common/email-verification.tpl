<div class="callout callout-info">
    <h4><i class="fa fa-envelope"></i> {{ trans('user.verification.notice.title') }}</h4>
    <p>{{ trans('user.verification.notice.message') }}
        <a id="send-verification-email" href="javascript:;">
            {{ trans('user.verification.notice.resend') }}
        </a>
        <span id="sending-indicator" style="display:none;">
            <i class="fa fa-spin fa-spinner"></i>
            {{ trans('user.verification.notice.sending') }}
        </span>
    </p>
</div>
