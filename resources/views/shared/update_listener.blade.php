@person
@push('scripts')
<script type="text/javascript" defer>

window.addEventListener('DOMContentLoaded', () => {
    (function(Echo, Vue) {
        'use strict';

        Echo.private('permissions.{{ auth()->user()->id }}')
            .listen('.permissions.updated', data => {
                window.location = document.referrer && document.referrer.startsWith(window.location.origin) ? document.referrer : '/home';
            });
    })(window.Echo, window.Vue);
});
</script>
@endpush
@endperson