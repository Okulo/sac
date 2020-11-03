require('./bootstrap');

window.Vue = require('vue');

var $loader = $("#loader");

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    }
});

$(document)
    .ajaxStart(function() {
        $loader.show();
    })
    .ajaxStop(function() {
        $loader.hide();
    });

(function($) {
    $(document).on("scroll", function() {
        if ($(this).scrollTop() < $(window).height()) {
            $("#up-button").hide();
        } else {
            $("#up-button").show();
        }
    });

    $("#up-button").on("click", function() {
        $("html, body").animate({ scrollTop: 0 }, "fast");
    });

    $("input[type='checkbox']").on("change", function() {
        $(this)
            .prev("input[type='hidden']")
            .val($(this).is(":checked") ? 1 : 0);
    });
})(jQuery);


Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('index-component', require('./components/IndexComponent.vue').default);
Vue.component('pulse-loader', require('vue-spinner/src/PulseLoader.vue').default);

import VueToast from 'vue-toast-notification';
// Import one of available themes
import 'vue-toast-notification/dist/theme-default.css';
Vue.use(VueToast, {
    position: 'top',
    duration: 7000,
})

const app = new Vue({
    el: '#app',
});
