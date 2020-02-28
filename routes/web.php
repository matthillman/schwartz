<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'WelcomeController@index')->name('welcome');
Route::get('/poster', 'WelcomeController@poster')->name('poster');
Route::post('/', 'WelcomeController@store')->name('join.inquiry');

Auth::routes();

Route::prefix('login')->group(function() {
    Route::get('discord', 'Auth\LoginController@redirectToProvider')->name('login.discord');
    Route::get('discord/callback', 'Auth\LoginController@handleProviderCallback');
});

Route::get('/mods', 'ModsController@index')->name('mods');
Route::get('/mods/{user}', 'ModsController@pullUser')->name('mods.user');

Route::get('/u/{user}/{param?}', 'MetadataController@ggUser');
Route::get('/p/{user}/{param?}', 'MetadataController@ggPlayer');

Route::get('podcast.rss', 'MetadataController@podcastRSS');
Route::get('discord/{id}', 'MetadataController@discordWidget');

Route::group(['middleware' => ['auth:web,admin']], function() {
    Route::group(['middleware' => ['active']], function() {
        Route::get('/handbook/{name}', 'HandbookController')->name('handbook');
        Route::get('/guide/{name}', 'GuideController')->name('guide');
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/home/mods', 'ModsController@index')->name('auth.mods');
        Route::put('/home/mods/{user}', 'ModsController@pullUser')->name('auth.mods.user.pull');
        Route::get('/home/mods/{user}', 'ModsController@modsFor')->name('auth.mods.user');
        Route::get('/home/units/{user}', 'ModsController@unitsFor')->name('auth.units.user');
        Route::post('/home/stats', 'ModsController@calculateStats')->name('auth.unit.stats');
    });

    Route::name('approve.')->middleware(['auth:admin'])->group(function() {
        Route::put('/approve/{id}', 'HomeController@approveUser')->name('user');
        Route::put('/approve/admin/{id}', 'HomeController@approveAdmin')->name('admin');
    });

    Route::resource('tw-teams', 'TerritoryCountersController');
    Route::resource('character-mods', 'UnitModPreferenceController');

    Route::get('/units', 'MetadataController@units')->name('units');
    Route::get('/categories', 'MetadataController@categories')->name('categories');
    Route::get('/guilds', 'GuildController@listGuilds')->name('guilds');
    Route::get('guild/{guild}', 'GuildController@guildGP')->name('guild.guild');
    Route::get('guild/{guild}/mods', 'GuildController@guildMods')->name('guild.modsList');
    Route::put('/guild/{guild}/refresh', 'GuildController@scrapeGuild')->name('guild.refresh');
    Route::get('/guild/{guild}/{team}/{mode?}/{index?}', 'GuildController@listMembers')->name('guild.members');

    Route::get('/character_mods/{id}', 'GuildController@characterMods');

    Route::get('/members', 'MemberController@index')->name('members');
    Route::post('/members/compare', 'MemberController@compare')->name('members.post.compare');
    Route::put('/member/{id}/refresh', 'MemberController@scrapeMember')->name('member.scrape');
    Route::post('/member/add', 'MemberController@addMember')->name('member.add');

    Route::post('/guilds', 'GuildController@addGuild')->name('guild.add');
    Route::post('/guild/compare', 'GuildController@postGuildCompare')->name('guild.post.compare');

    Route::get('/schwartz', 'GuildController@schwartzGuilds')->name('schwartz.guilds');
    Route::get('/schwartz_mods', 'GuildController@schwartzGuildMods')->name('schwartz.mods');
    Route::get('gp/{guild?}', 'GuildController@listGP')->name('guild.gp');
    Route::get('guild_mods/{guild}', 'GuildController@listMods')->name('guild.mods');

    Route::get('/waiting', 'HomeController@waiting')->name('waiting');

    Route::post('notify', 'HomeController@notify')->name('notify');

    Route::get('/jobs-by-tag', 'JobsController@jobsForTag')->name('jobs.by.tag');

    Route::get('/squads', 'SquadController@index')->name('squads');
    Route::post('/squads', 'SquadController@add')->name('squads.add');
    Route::post('/squads/group', 'SquadController@addGroup')->name('squads.add.group');
    Route::put('/squads/{squad}/publish', 'SquadController@publish')->name('squads.group.publish');
    Route::delete('/squad/{id}', 'SquadController@delete')->name('squad.delete');
    Route::put('/squads/message/{channel}', 'SquadController@sendDiscordMessages')->name('squads.message');
});

Route::group(['middleware' => ['auth.or.client:web,admin,bot']], function() {
    Route::get('/relics', 'RelicController@index')->name('relic.recommendations');
    Route::get('/relics/{ally}', 'RelicController@relicMember')->name('member.relic.recommendations');
    Route::get('/compare/{guild1}/{guild2}', 'GuildController@compareGuilds')->name('guild.compare');
    Route::get('/member/compare', 'MemberController@compareMembers')->name('member.compare');
    Route::get('/member/mods/{character}', 'MemberController@characterMods')->name('member.character_mods');
    Route::get('/member/{ally}', 'MemberController@show')->name('member.profile');
    Route::get('/member/{ally}/characters', 'MemberController@characters')->name('member.characters');
    Route::get('/member/{ally}/character/{id}', 'MemberController@showCharacter')->name('member.character');
    Route::get('/member/{ally}/{team}', 'MemberController@listTeams')->name('member.teams');
    Route::get('/member-search', 'SearchController@searchMembers')->name('search.members');
    Route::get('/guild-search', 'SearchController@searchGuilds')->name('search.guilds');
    Route::get('/unit-search', 'SearchController@searchUnits')->name('search.units');
    Route::get('/category-search', 'SearchController@searchCategories')->name('search.categories');
    Route::get('/member-unit-search/{ally}', 'SearchController@searchMemberUnits')->name('search.member.units');
});

Route::get('/schwartz_list', 'GuildController@schwartzGuildsImportList')->name('schwartz.import');