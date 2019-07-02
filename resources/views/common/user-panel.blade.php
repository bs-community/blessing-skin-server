@php
$roles = [
    App\Models\User::BANNED => 'banned',
    App\Models\User::NORMAL => 'normal',
    App\Models\User::ADMIN  => 'admin',
    App\Models\User::SUPER_ADMIN => 'super-admin',
];
$role = $roles[$user->permission];
@endphp

@php
    $badges = [];
    event(new \App\Events\RenderingBadges($badges));
@endphp
<div class="user-panel">
    <div class="pull-left image">
        <img src="{{ url("avatar/45/".base64_encode($user->email).'.png?tid='.$user->avatar) }}" alt="User Image">
    </div>
    <div class="pull-left info">
        <p class="nickname">{{ $user->nickname ?? $user->email }}</p>
        <i class="fas fa-circle text-success"></i> @lang("admin.users.status.$role")
        @if (count($badges) === 1)
            <small class="label bg-{{ $badges[0][1] }}">{{ $badges[0][0] }}</small>
        @endif
    </div>
</div>
@if (count($badges) > 1)
<div class="user-panel" style="padding-top: 0">
    @foreach ($badges as $badge)
    <small class="label bg-{{ $badge[1] }}">{{ $badge[0] }}</small>
    @endforeach
</div>
@endif
