@php
$roles = [
    App\Models\User::BANNED => 'banned',
    App\Models\User::NORMAL => 'normal',
    App\Models\User::ADMIN  => 'admin',
    App\Models\User::SUPER_ADMIN => 'super-admin',
];
$role = $roles[$user->permission];
@endphp
<div class="user-panel">
    <div class="pull-left image">
        <img src="{{ url("avatar/45/".base64_encode($user->email).'.png?tid='.$user->avatar) }}" alt="User Image">
    </div>
    <div class="pull-left info">
        <p class="nickname">{{ $user->nickname ?? $user->email }}</p>
        <i class="fas fa-circle text-success"></i> @lang("admin.users.status.$role")
    </div>
</div>
