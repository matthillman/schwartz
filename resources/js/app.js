/* eslint-disable no-undef */
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
// require('./parallax');

window.Vue = require('vue');

Vue.config.ignoredElements = [/^ion-/];

const VueSelect = require('vue-select');
Vue.component('v-select', VueSelect.VueSelect);
const VueSlider = require('vue-slider-component');
Vue.component('vue-slider', VueSlider);
import VuePopover from 'vue-popover';
Vue.component('popover', VuePopover);

import { CoolSelectPlugin } from 'vue-cool-select';
Vue.use(CoolSelectPlugin);

import VShowSlide from 'v-show-slide';
Vue.use(VShowSlide);

Vue.component('content-wrapper', {
	functional: true,
	render(createElement, context) {
		let node = context.props.node;
		node.data.class = Object.assign(node.data.class || {}, {
			'content-wrapper': true
		});
		return node;
	}
});

Vue.directive('highlight', function(el, binding) {
	if (binding.arg) {
		el.setAttribute('highlight', binding.arg);
	}
});

Vue.component('welcome-parallax', require('./components/Parallax.vue').default);
Vue.component('discord-widget', require('./components/DiscordWidget.vue').default);
Vue.component('horizon-jobs', require('./components/Horizon.vue').default);

Vue.component('member-mods', require('./components/MemberMods.vue').default);
Vue.component('mods', require('./components/Mods.vue').default);
Vue.component('mod', require('./components/Mod.vue').default);
Vue.component('mod-preference', require('./components/Preference.vue').default);

Vue.component('modal', require('./components/util/Modal.vue').default);
Vue.component('list', require('./components/util/List.vue').default);
Vue.component('collapsable', require('./components/util/Collapsable.vue').default);
Vue.component('page-view', require('./components/util/PageView.vue').default);
Vue.component('search', require('./components/util/Search.vue').default);
Vue.component('loading-indicator', require('./components/util/LoadingIndicator.vue').default);
Vue.component('unit-select', require('./components/util/UnitSelect.vue').default);
Vue.component('tab-list', require('./components/util/TabList.vue').default);
Vue.component('auto-checkbox', require('./components/util/AutoCheckbox.vue').default);
Vue.component('auto-text-field', require('./components/util/AutoTextField.vue').default);
Vue.component('auto-select', require('./components/util/AutoSelect.vue').default);
Vue.component('tooltip', require('./components/util/Tooltip.vue').default);
Vue.component('popup', require('./components/util/Popup.vue').default);
Vue.component('status', require('./components/util/StatusCircle.vue').default);

Vue.component('mini-squad-table', require('./components/MiniSquadTable.vue').default);
Vue.component('highlight-widget', require('./components/HighlightWidget.vue').default);
Vue.component('character', require('./components/Character.vue').default);

Vue.component('members', require('./components/Members.vue').default);
Vue.component('guild', require('./components/Guild.vue').default);
Vue.component('guild-teams', require('./components/GuildTeams.vue').default);
Vue.component('team-sort', require('./components/TeamSort.vue').default);
Vue.component('tw-form', require('./components/TWForm.vue').default);

Vue.component('squad-tabs', require('./components/SquadTabs.vue').default);
Vue.component('convert-squad-to-plan', require('./components/ConvertSquadToPlan.vue').default);

Vue.component('tw-plan', require('./components/tw/TWPlan.vue').default);
Vue.component('tw-zone', require('./components/tw/TWZone.vue').default);

let data = {
	highlight: null,
	showGlobalModal: null,
	guildCompare: [],
	memberCompare: null,
	memberCompareArray: [],
	messageChannel: '678958338225995786',
	selectedSquadArray: [],
	modJobStatusByAllyCode: {},
	guildJobStatusByGuildId: {},
};

new Vue({
	el: '#app',
	data,
	watch: {
		memberCompare: function(newVal) {
			const newCompare = this.memberCompareArray.join('\n').trim();

			if (newCompare != newVal) {
				this.memberCompareArray = newVal.split('\n').filter(m => !!m);
			}

		},
		memberCompareArray: function(newVal) {
			const newCompare = newVal.join('\n').trim();

			if (newCompare != this.memberCompare) {
				this.memberCompare = newCompare;
			}
		}
	},
	mounted() {
		this.loadModJobStatus();
		this.loadGuildJobStatus();
		this.memberCompareArray = (this.memberCompare || '').split('\n');
	},
	methods: {
		go(to, newTab) {
			if (newTab) {
				window.open(to);
			} else {
				window.location = to;
			}
		},
		back() {
			window.history.back();
		},
		set(id, value) {
			document.getElementById(id).value = value;
		},
		loadModJobStatus() {
			axios.get('/jobs-by-tag?tags=mods').then(result => {
				this.modJobStatusByAllyCode = {};
				result.data.forEach(job => {
					let allyCodeTag = job.payload.tags.find(tag => tag.startsWith('ally_code:'));
					if  (allyCodeTag) {
						let allyCode = allyCodeTag.split(':')[1];
						this.modJobStatusByAllyCode[allyCode] = job.status;
					}
				});

				if (Object.values(this.modJobStatusByAllyCode).find(val => val == 'reserved' || val == 'pending')) {
					setTimeout(() => this.loadModJobStatus(), 3000);
				}
			});
		},
		loadGuildJobStatus() {
			axios.get('/jobs-by-tag?tags=guild').then(result => {
				this.guildJobStatusByGuildId = {};
				result.data.forEach(job => {
					let guildIdTag = job.payload.tags.find(tag => tag.startsWith('guild_id:'));
					if  (guildIdTag) {
						let guildId = guildIdTag.split(':')[1];
						this.guildJobStatusByGuildId[guildId] = job.status;
					}
				});

				if (Object.values(this.guildJobStatusByGuildId).find(val => val == 'reserved' || val == 'pending')) {
					setTimeout(() => this.loadGuildJobStatus(), 3000);
				}
			});
		},
	},
});

import Toasted from 'vue-toasted';

Vue.use(Toasted, {
	iconPack : 'custom-class'
});
