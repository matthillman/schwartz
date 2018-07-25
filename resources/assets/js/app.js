/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
// require('./parallax');

window.Vue = require('vue');

Vue.component("content-wrapper", {
    functional: true,
    render(createElement, context) {
        let node = context.props.node;
        node.data.class = Object.assign(node.data.class || {}, {
            'content-wrapper': true
        });
        return node;
    }
})

Vue.component('modal', require('./components/Modal.vue'));
Vue.component('mods', require('./components/Mods.vue'));
Vue.component('guild', require('./components/Guild.vue'));
Vue.component('list', require('./components/List.vue'));
Vue.component('members', require('./components/Members.vue'));
Vue.component('welcome-parallax', require('./components/Parallax.vue'));
Vue.component('tw-form', require('./components/TWForm.vue'));

const app = new Vue({
    el: '#app'
});
