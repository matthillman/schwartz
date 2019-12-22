"use strict";

var ProfileParser = function() {};

ProfileParser.prototype = {

  constructor: 'ProfileParser',

  parse: function(html) {
    this.html = html;
    var profile = {};
    profile.last_updated = this.get_lastUpdated_();
    var names = this.get_names_();
    profile.account = names[0];
    profile.player = names[1];
    profile.guild = this.get_guild_();
    var infos = this.get_infos_();
    profile.communities = infos[0];
    profile.infos = infos[1];
    profile.stats = this.get_stats_();
    return profile;
  },

  get_lastUpdated_: function() {
    var re = /data-datetime="([^"]+)"/m;
    var value = re.exec(this.html);
    if (value !== null) {
      value = value[1].trim();
    }
    return value;
  },

  // Returns an array with swgoh.gg account name and player name
  get_names_: function() {
    var re = /class="no-decoration\s+char-name"\s+href="">[^<]+/gm;
    var names = this.html.match(re);
    names = names.map(function(el) {
      var value = />([^<]+)/.exec(el)[1].trim();
      return value;
    });
    return names;
  },

  get_guild_: function() {
    var re = /Guild\s+<strong\s+class="pull-right"><a\s+href="([^"]+)">([^<]+)/m;
    var guild = re.exec(this.html);
    guild = guild.slice(1, 3);
    guild = guild.map(function(s) {
      return s.trim();
    });
    return guild;
  },

  get_infos_: function() {
    // Separate communities from the rest
    var infos = this.html.split(/<h5>Player\s+Info<\/h5>/m);

    infos = infos.map(function(el) {
      var re = /[^>]+<strong\s+class="pull-right">.*<\/strong/gm;
      var info = el.match(re);
      if (info !== null) {
        var fields = info.map(function(info) {
          var field = info.match(/([^>]+)<strong\s+class="pull-right">(.*)<\/strong/);
          field = field.slice(1, 3);
          field = field.map(function(el) {
            return el.trim();
          });
          return field;
        });
        info = fields;
      }
      return info;
    });
    return infos;
  },

  get_stats_: function() {
    var stats = this.html.split(/<li\s+class="panel-menu-item">/);
    stats.shift();
    stats = stats.map(function(el) {
      var p = el.split(/<h5\s+class="m-y-0">/);
      p = p.map(function(s) {
        return s.trim();
      });
      p[1] = p[1].match(/^[^<]*/)[0];
      return p;
    });
    stats.shift();
    return stats;
  }
};

