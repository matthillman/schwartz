<?php

namespace App;

use DB;
use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\Discord\Discord;

class Member extends Model
{
    use Searchable;
    use Notifiable;
    use Util\MetaChars;

    protected $fillable = ['url', 'ally_code'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'guild_name',
        'profile_url',
        'gear_12', 'gear_13',
        'relic_5', 'relic_6', 'relic_7',
        'speed_10', 'speed_15', 'speed_20', 'speed_25',
        'offense_100',
    ];

    protected $casts = [
        'arena' => 'array',
    ];
    protected $hidden = [
        'raw',
    ];

    protected $indexConfigurator = Search\Indexes\MemberIndexConfigurator::class;

    protected $searchRules = [
        Search\Rules\WildcardSearchRule::class,
    ];

    protected $mapping = [
        'properties' => [
            'ally_code' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                ]
            ],
            'player' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                    'english' => [
                      'type' => 'text',
                      'analyzer' => 'english',
                    ],
                ]
            ],
        ]
    ];

    public function discord() {
        return $this->hasOne(AllyCodeMap::class, 'ally_code', 'ally_code')->withDefault();
    }

    public function roles() {
        return $this->hasOneThrough(DiscordRole::class, AllyCodeMap::class, 'ally_code', 'discord_id', 'ally_code', 'discord_id')->withDefault();
    }

    public function user() {
        return $this->hasOneThrough(User::class, AllyCodeMap::class, 'ally_code', 'discord_id', 'ally_code', 'discord_id');
    }

    public function characters() {
        return $this->hasMany(Character::class);
    }

    public function mods() {
        return $this->hasManyThrough(Mod::class, ModUser::class, 'name', 'mod_user_id', 'ally_code', 'id');
    }

    public function characterSet(array $characters) { // List of base_ids
        return collect([
            'url' => $this->url,
            'ally_code' => $this->ally_code,
            'player' => $this->player,
            'characters' => $this->characters()->with('zetas')->whereIn('unit_name', $characters)->get()->values()
        ]);
    }

    private function modStats() {
        return $this->hasManyThrough(ModStat::class, ModUser::class, 'name', 'mod_user_id', 'ally_code', 'id');
    }

    public function stats() {
        return $this->hasOne(MemberStatistic::class)->withDefault();
    }

    public function raw() {
        return $this->hasOne(MembersRaw::class);
    }

    public function guild() {
        return $this->belongsTo(Guild::class)->withDefault();
    }

    public function getCompareDataBaseAttribute() {
        return [
            "id" => $this->id,
            "player" => $this->player,
            "guild_name" => $this->guild->name,
            "guild_gp" => $this->guild->gp,
            "gp" => $this->gp,
            "character_gp" => $this->character_gp,
            "ship_gp" => $this->ship_gp,
            "ally_code" => $this->ally_code,
            "level" => $this->level,
            "title" => $this->title,
            "portrait" => $this->portrait,
            "squad_rank" => $this->squad_rank,
            "fleet_rank" => $this->fleet_rank,
        ];
    }

    public function getProfileUrlAttribute() {
        return route('member.profile', $this->ally_code);
    }

    public function getGuildNameAttribute() {
        if (is_null($this->guild)) { return ''; }
        return $this->guild->name;
    }

    public function getSortNameAttribute(){
        return preg_replace('/^ìN /', '', $this->player, 1);
    }

    public function getSquadRankAttribute() {
        return array_get($this->arena, '0.rank', 0);
    }

    public function getFleetRankAttribute() {
        return array_get($this->arena, '1.rank', 0);
    }

    public function getHasGlReyAttribute() {
        $rey = $this->characters()->where('unit_name', 'GLREY');
        return $rey->exists() ? ($rey->first()->has_ultimate_ability ? 'U' : 1) : 0;
    }

    public function getHasGlKyloAttribute() {
        $rey = $this->characters()->where('unit_name', 'SUPREMELEADERKYLOREN');
        return $rey->exists() ? ($rey->first()->has_ultimate_ability ? 'U' : 1) : 0;
    }

    public function getGear12Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('gear_twelve');
    }

    public function getGear13Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('gear_thirteen');
    }

    public function getRelic3Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('relic_three');
    }

    public function getRelic5Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('relic_five');
    }

    public function getRelic6Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('relic_six');
    }

    public function getRelic7Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('relic_seven');
    }

    public function getSixDotAttribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('six_dot');
    }

    public function getSpeed10Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('ten_plus');
    }

    public function getSpeed15Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('fifteen_plus');
    }

    public function getSpeed20Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('twenty_plus');
    }

    public function getSpeed25Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('twenty_five_plus');
    }

    public function getOffense100Attribute() {
        if (is_null($this->stats->data)) { return 0; }
        return $this->stats->data->get('one_hundred_offense');
    }

    public function getZetasAttribute() {
        return $this->characters->pluck('zetas')->flatten();
    }

    public function toCompareData($unitList = []) {
        $compareData = collect($this->compare_data_base)
            ->merge($this->stats->data)
            ->merge([
                'zetas' => $this->zetas->count(),
            ]);
        if (empty($unitList)) {
            $unitList = collect([
                static::getCompareCharacters()->keys(),
                static::getKeyCharacters()->keys(),
                static::getKeyShips()->keys(),
            ])->flatten();
        }

        return $compareData->merge(
            $this->characters()->with('zetas')->whereIn('unit_name', $unitList)->get()
                ->mapWithKeys([$this, 'extractStats'])
        );
    }

    public function extractStats($c) {
        return collect([strtolower("{$c->unit_name}") => $c->makeHidden(['unit', 'member', 'stat_grade'])])->merge(
            static::getCompareStats()->mapWithKeys(function($stat) use ($c) {
                $k = $stat['stat']->getKey();
                return [
                    strtolower("{$c->unit_name}_{$k}") => $c->$k,
                ];
            }),
        );
    }

    public function getDiscordPrivateChannelIdAttribute($value) {
        return $this->roles->discord_private_channel_id;
    //     if ($this->user) {
    //         return $this->user->discord_private_channel_id;
    //     }

    //     if (is_null($value)) {
    //         $channelID = app(Discord::class)->getPrivateChannel($this->discord->discord_id);
    //         dd('$channelID', $channelID);
    //         $this->discord_private_channel_id = $channelID;
    //         $this->user->save();

    //         return $channelID;
    //     }

    //     return $value;
    }

    public function routeNotificationForDiscord()
    {
        return $this->discord_private_channel_id;
    }
}
