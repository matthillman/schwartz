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

Vue.component('guild', require('./components/Guild.vue'));
Vue.component('welcome-parallax', require('./components/Parallax.vue'));

const app = new Vue({
    el: '#app'
});