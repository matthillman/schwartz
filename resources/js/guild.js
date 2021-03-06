(function(Echo, Vue) {
	'use strict';

	Echo.private('guilds')
        .listen('.guild.fetched', data => {
	const match = /^\/guild\/(\d+)/.exec(window.location.pathname);
	if (match !== null && +match[1] != data.guild.id) { return; }

	Vue.toasted.global.stripedWithRefresh(`"${data.guild.name}" has been updated!`);
	// Vue.toasted.success(` "${data.guild.name}" has been updated!`, {
	// 	icon: function() {
	// 		const i = document.createElement('ion-icon');
	// 		i.setAttribute('name', 'checkmark-circle');
	// 		i.setAttribute('size', 'medium');
	// 		return i;
	// 	},
	// 	theme: 'bubble',
	// 	position: 'top-right',
	// 	action : {
	// 		text : 'Refresh',
	// 		onClick : () => {
	// 			window.location.reload(true);
	// 		}
	// 	},
	// });
});
})(window.Echo, window.Vue);