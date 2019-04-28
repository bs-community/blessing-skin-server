@component('common.skeleton', ['parent' => 'user', 'title' => trans('general.player-manage')])
    @slot('bottom')
    <script>blessing.extra = @json($extra)</script>
    @endslot
@endcomponent
