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
});

Vue.component('modal', require('./components/Modal.vue').default);
Vue.component('mods', require('./components/Mods.vue').default);
Vue.component('guild', require('./components/Guild.vue').default);
Vue.component('list', require('./components/List.vue').default);
Vue.component('members', require('./components/Members.vue').default);
Vue.component('member-mods', require('./components/MemberMods.vue').default);
Vue.component('welcome-parallax', require('./components/Parallax.vue').default);
Vue.component('tw-form', require('./components/TWForm.vue').default);
Vue.component('guild-teams', require('./components/GuildTeams.vue').default);
Vue.component('mod-preference', require('./components/Preference.vue').default);

const VueSelect = require('vue-select');
Vue.component('v-select', VueSelect.VueSelect);
const VueSlider = require('vue-slider-component');
Vue.component('vue-slider', VueSlider);

const app = new Vue({
    el: '#app'
});

import Toasted from 'vue-toasted';

Vue.use(Toasted, {
    iconPack : 'custom-class'
});
