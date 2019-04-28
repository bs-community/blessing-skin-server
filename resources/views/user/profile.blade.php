@component('common.skeleton', ['parent' => 'user', 'title' => trans('general.profile')])
    @slot('bottom')
    <script>
        Object.defineProperty(blessing, 'extra', {
            configurable: false,
            get: () => Object.freeze(@json($extra)),
        })
    </script>
    @endslot
@endcomponent
