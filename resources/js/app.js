import './bootstrap';
// import './schedule-modal'
import './main'
import './modal-handler'

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
