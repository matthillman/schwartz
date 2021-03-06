<?php

namespace App\Util;

use DB;
use Illuminate\Support\Collection;

trait UpdatesStats {

    function updateMemberStats($members) {
        $allyCodes = Collection::wrap($members)->pluck('ally_code')->map(function($c) { return "'$c'"; })->join(', ');
        return DB::statement("INSERT into member_statistics(member_id, data, created_at, updated_at)
        select members.id, row_to_json(sums)::jsonb - 'ally_code' as data, now() as created_at, now() as updated_at
            from (
                SELECT
                    mod_sums.ally_code,
                    mod_sums.six_dot,
                    mod_sums.ten_plus,
                    mod_sums.fifteen_plus,
                    mod_sums.twenty_plus,
                    mod_sums.twenty_five_plus,
                    mod_sums.one_hundred_offense,
                    mod_sums.one_fifty_offense,
                    mod_sums.four_percent_offense,
                    char_sums.gp,
                    char_sums.character_gp,
                    char_sums.ship_gp,
                    char_sums.top_sixty_five,
                    char_sums.top_eighty,
                    char_sums.gear_eleven,
                    char_sums.gear_twelve,
                    char_sums.gear_thirteen,
                    char_sums.relic_one,
                    char_sums.relic_two,
                    char_sums.relic_three,
                    char_sums.relic_four,
                    char_sums.relic_five,
                    char_sums.relic_six,
                    char_sums.relic_seven,
                    char_sums.relic_eight
                from
                (
                    select
                        name as ally_code,
                        sum((pips = 6)::int) as six_dot,
                        sum((speed >= 10)::int) as ten_plus,
                        sum((speed >= 15)::int) as fifteen_plus,
                        sum((speed >= 20)::int) as twenty_plus,
                        sum((speed >= 25)::int) as twenty_five_plus,
                        sum((offense >= 100)::int) as one_hundred_offense,
                        sum((offense >= 150)::int) as one_fifty_offense,
                        sum((offense_percent >= 4)::int) as four_percent_offense
                    from (
                        select
                            mod_users.name,
                            mods.pips,
                            CASE
                                WHEN secondary_1_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_1_value)::numeric
                                WHEN secondary_2_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_2_value)::numeric
                                WHEN secondary_3_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_3_value)::numeric
                                WHEN secondary_4_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_4_value)::numeric
                                ELSE 0 END as speed,
                            CASE
                                WHEN secondary_1_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_1_value)::numeric
                                WHEN secondary_2_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_2_value)::numeric
                                WHEN secondary_3_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_3_value)::numeric
                                WHEN secondary_4_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_4_value)::numeric
                                ELSE 0 END as offense,
                            CASE
                                WHEN secondary_1_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_1_value)::numeric
                                WHEN secondary_2_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_2_value)::numeric
                                WHEN secondary_3_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_3_value)::numeric
                                WHEN secondary_4_type = 'UNITSTATOFFENSEPERCENTADDITIVE' THEN trim(trailing '%' from secondary_4_value)::numeric
                            ELSE 0 END as offense_percent
                        from mods
                        inner join mod_users on mods.mod_user_id = mod_users.id
                        where mod_users.name in ($allyCodes)
                    ) mod_totals
                    group by name
                ) mod_sums,
                (
                    select
                        members.ally_code,
                        max(members.gp) as gp,
                        max(members.character_gp) as character_gp,
                        max(members.ship_gp) as ship_gp,
                        sum(case when characters_ranked.rank <= 65 and combat_type = 1 then characters_ranked.power end) as top_sixty_five,
                        sum(case when characters_ranked.rank <= 80 and combat_type = 1 then characters_ranked.power end) as top_eighty,
                        sum((characters_ranked.gear_level = 11)::int) as gear_eleven,
                        sum((characters_ranked.gear_level = 12)::int) as gear_twelve,
                        sum((characters_ranked.gear_level = 13)::int) as gear_thirteen,
                        sum((characters_ranked.relic = 3)::int) as relic_one,
                        sum((characters_ranked.relic = 4)::int) as relic_two,
                        sum((characters_ranked.relic = 5)::int) as relic_three,
                        sum((characters_ranked.relic = 6)::int) as relic_four,
                        sum((characters_ranked.relic = 7)::int) as relic_five,
                        sum((characters_ranked.relic = 8)::int) as relic_six,
                        sum((characters_ranked.relic = 9)::int) as relic_seven,
                        sum((characters_ranked.relic = 10)::int) as relic_eight
                    from (
                        select characters.*, rank() over (partition by member_id, combat_type order by power desc) from characters
                    ) characters_ranked
                    inner join members on members.id = characters_ranked.member_id
                    where members.ally_code in ($allyCodes)
                    group by members.ally_code
                ) char_sums
                where mod_sums.ally_code = char_sums.ally_code
            ) sums
            join members on members.ally_code = sums.ally_code
        ON CONFLICT (member_id) DO UPDATE SET member_id = excluded.member_id, data = excluded.data, updated_at = excluded.updated_at
        ");
    }
}