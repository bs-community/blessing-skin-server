@php
    $notifications = $user->unreadNotifications;
    $count = $notifications->count();
@endphp

<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fas fa-bell"></i>
        @if ($count > 0)
        <span class="label label-warning notifications-counter">{{ $count }}</span>
        @endif
    </a>
    <ul class="dropdown-menu">
        @if ($count === 0)
        <li class="header text-center">@lang('user.no-unread')</li>
        @else
        <li>
            <ul class="menu notifications-list">
                @foreach ($notifications as $notification)
                <li>
                    <a href="#" data-nid="{{ $notification->id }}">
                        <i class="far fa-circle text-aqua"></i> {{ $notification->data['title'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </li>
        @endif
    </ul>
</li>
