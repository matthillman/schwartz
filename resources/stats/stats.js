/** Stats calculator for raw player-object(s)

    Note: This class uses javascript's Map object and may not be suitable for front-end

    Gamedata loading -

        (optional) loaded via contructor

            const stats = new Stats( gamedata )

        (optional) loaded via "load" method, or

            stats.load( gamedata )

        (optional) loaded from shittybots api via "fetch" method

            await stats.fetch( shittybotsApiToken )

    Calculate single player or array of player (syncronous, non-blocking)
    Returns a Map of units keyed by unit-defId (baseId)
    Merges "{ stats:{ final, mods/crew }, gp }" onto each roster unit
    Stats.final is the final value
    Stats.mods/crew is the bonus shown in parenthesis in-game

        const rosterMap = stats.calcPlayerStats( players )

**/

class Stats {
    constructor( gamedata ) {
        this.gamedata = gamedata
        this.debugger = null
        this.current = null
        this.doRename = false;
    }

    log( ...args ) {
        return this.debugger == this.current
            ? console.log( ...args )
            : null
    }

    debug ( defId ) {
        console.log('STATS', 'Debugging', defId)
        return this.debugger = defId
    }

    load ( gamedata ) {
        this.gamedata = null
        return this.gamedata = gamedata
    }

    fetch( shittybotsToken ) {
        var url = `https://swgoh.shittybots.me/api/data/crinolo_core`
        var options = { headers:{ shittybot:shittybotsToken } }
        return require('node-fetch')(url, options)
            .then(data => data.json())
            .then(data => this.gamedata = data)
    }

    calcPlayerStats ( players ) {
        return Array.isArray( players )
            ? new Map(players.map(player => [ player.playerId, new Map(this.calcRosterStats(player.rosterUnitList)) ]))
            : new Map(this.calcRosterStats(players.rosterUnitList))
    }

    calcRosterStats ( units ) {
        var crew = {}
        const { unitData } = this.gamedata
        units = Array.isArray(units) ? units : [ units ] //Ensure units is an array
        //Sort units list to do characters first and ships second - ensuring crew references are built
        return units
            .sort((a,b) => (unitData[ unitDefId(a) ]||{}).combatType - (unitData[ unitDefId(b) ]||{}).combatType)
            .reduce(( unitMap, unit ) => {
                if( !unitData[ unitDefId(unit) ] ) {
                    console.log("STATS", 'missing core unit', unitDefId(unit))
                    return unitMap
                }
                unit.defId = unitDefId(unit) //set baseId from definition Id
                if( !unit.defId ) return unitMap
                this.current = unit.defId // used to track current unit id for debug logging
                switch( unitData[ unit.defId ].combatType ) {
                    case 1: //Char
                        crew[ unit.defId ] = unit // add to crew list to find quickly for ships
                        unit.stats = this.calcCharStats( unit ) //Calculate character stats
                        unit.gp    = this.calcCharGP( unit )    //Calculate character GP
                        break
                    case 2: //Ship
                        const crw  = unitData[ unit.defId ].crew.map(id => crew[id])
                        unit.stats = this.calcShipStats( unit, crw ) //Calculate ship stats
                        unit.gp    = this.calcShipGP( unit, crw )    //Calculate ship gp
                        break
                    default:
                        //Unknown combatType - ignore
                        return unitMap
                }
                unitMap.push([ unit.defId, { stats:Object.assign({},unit.stats), gp:unit.gp } ])
                return unitMap
            },[])
    }

    //Return stats : { final, mods }
    calcCharStats ( char, ) {
        let stats = this.getCharRawStats( char )
        stats = this.calculateBaseStats( stats, char.currentLevel, char.defId )
        stats.mods = this.calculateModStats( stats.base, char.equippedStatModList || [] )
        stats = this.formatStats( stats, char.currentLevel )
        return {
            final : this.renameStats( stats.result.final, 'ENG_US' ),
            mods  : this.renameStats( stats.result.mods, 'ENG_US' )
        }
    }

    //Return stats : { final, mods }
    calcShipStats ( ship, crew ) {
        let stats = this.getShipRawStats( ship, crew )
        stats = this.calculateBaseStats( stats, ship.currentLevel, ship.defId )
        stats = this.formatStats( stats, ship.currentLevel )
        return {
            final : this.renameStats( stats.result.final, 'ENG_US' ),
            crew  : this.renameStats( stats.result.crew,  'ENG_US' )
        }
    }

    getCharRawStats ( char ) {
        const { gearData, relicData, unitData } = this.gamedata
        const base = Object.assign({}, unitData[ char.defId ].gearLvl[ char.currentTier ].stats )
        const growthModifiers = Object.assign({}, unitData[ char.defId ].growthModifiers[ char.currentRarity ] )
        const gear = char.equipmentList.reduce((gearAcc,gearPiece) => {
            if( !gearData[ gearPiece.equipmentId ] ) return gear
            var { stats } = gearData[ gearPiece.equipmentId ]
            Object.keys(stats).forEach(statId => {
                if( statId == '2' || statId == '3' || statId == '4' ) {
                    base[ statId ] += stats[ statId ] || 0
                } else {
                    gearAcc[ statId ] = gearAcc[ statId ] || 0
                    gearAcc[ statId ] += stats[ statId ] || 0
                }
            })
            return gearAcc
        },{})

        var relicTier = char.relic ? char.relic.currentTier : char.unitRelicTier
        if (relicTier > 2) {
            const relicId = unitData[ char.defId ].relic[ relicTier ] || ''
            const relic = relicData[ relicId ] || {}
            Object.keys(relic.stats).forEach(statId => {
                base[ statId ] = base[ statId ] || 0
                base[ statId ] += relic.stats[ statId ] || 0
            })
            Object.keys(relic.gms).forEach(statId => {
                growthModifiers[ statId ] += relic.gms[ statId ] || 0
            })
        }

        return { base, growthModifiers, gear }
    }

    getShipRawStats ( ship, crew ) {
        const { unitData, crTables } = this.gamedata
        if( crew.length != unitData[ ship.defId ].crew.length )
            throw new Error(`Incorrect number of crew members for ship ${ship.defId}.`)

        // if still here, crew is good -- go ahead and determine stats
        const crewRating = crew.length == 0 ? this.getCrewlessCrewRating(ship) : this.getCrewRating(crew)
        const statMultiplier = crTables.shipRarityFactor[ ship.currentRarity ] * crewRating;
        const base = Object.assign({}, unitData[ ship.defId ].stats)
        const growthModifiers = Object.assign({}, unitData[ ship.defId ].growthModifiers[ ship.currentRarity ] )
        crew = Object.entries( unitData[ ship.defId ].crewStats ).reduce((crewList, stat) => {
            var [ statId, statValue ] = stat
            crewList[ statId ] = statValue * statMultiplier
            return crewList
        },{})

        return { base, growthModifiers, crew }
    }

    getCrewRating ( crew ) {
        const { crTables } = this.gamedata
        return crew.reduce((crewRating, char) => {
            var lr = crTables.unitLevelCR[ char.currentLevel ] + crTables.crewRarityCR[ char.currentRarity ] // add CR from level/rarity
            var gr = crTables.gearLevelCR[ char.currentTier ]
            var er = (crTables.gearPieceCR[ char.currentTier ] || 0) * (char.equipmentList.length || 0) // add CR from currently equipped gear
            var sr = char.skillList.reduce((cr, skill) => cr + this.getSkillCrewRating(skill), 0) // add CR from ability levels
            var mr = char.equippedStatModList.reduce((cr, mod) => {
                if( !mod.pips ) {
                    var [ set, pips, slot ] = mod.definitionId.split("")
                    mod.pips = pips
                }
                return cr + crTables.modRarityLevelCR[ mod.pips ][ mod.level ] // add CR from mods
            }, 0)
            var relicTier = char.relic ? char.relic.currentTier : char.unitRelicTier
            var rr = relicTier > 2
                ? ( crTables.relicTierCR[ relicTier ] ) + ( char.currentLevel * crTables.relicTierLevelFactor[ relicTier ] )
                : 0

            //this.log( this.currentUnit, lr , gr , er , sr , mr , rr )
            return crewRating + ( lr + gr + er + sr + mr + rr )
        },0)
    }

    getSkillCrewRating ( skill ) {
        // Crew Rating for GP purposes depends on skill type (i.e. contract/hardware/etc.), but for stats it apparently doesn't.
        const { crTables } = this.gamedata
        return crTables.abilityLevelCR[ skill.tier + 2 ] || 0
    }

    getCrewlessCrewRating ( ship = {} ) {
        // temporarily uses hard-coded multipliers, as the true in-game formula remains a mystery.
        // but these values have experimentally been found accurate for the first 3 crewless ships:
        //     (Vulture Droid, Hyena Bomber, and BTL-B Y-wing)
        const { crTables } = this.gamedata
        return floor( crTables.crewRarityCR[ ship.currentRarity ] + (3.5 * crTables.unitLevelCR[ ship.currentLevel ]) + this.getCrewlessSkillsCrewRating( ship.skillList ) )
    }

    getCrewlessSkillsCrewRating ( skills = [] ) {
        const { crTables } = this.gamedata
        return skills.reduce((cr, skill) => cr += ((skill.id.substring(0,8) == "hardware") ? 0.696 : 2.46) * crTables.abilityLevelCR[ skill.tier+ 2 ], 0)
    }

    calculateBaseStats ( stats, level, baseId ) {
        const { unitData, crTables } = this.gamedata
        // calculate bonus Primary stats from Growth Modifiers:
        stats.base[2] += floor( stats.growthModifiers[2] * level, 8) // Strength
        stats.base[3] += floor( stats.growthModifiers[3] * level, 8) // Agility
        stats.base[4] += floor( stats.growthModifiers[4] * level, 8) // Tactics
        if (stats.base[61]) {
            // calculate effects of Mastery on Secondary stats:
            let mms = crTables[ unitData[ baseId ].masteryModifierID ]
            Object.keys(mms).forEach(statId => stats.base[ statId ] = (stats.base[ statId ] || 0) + ((stats.base[61]*mms[ statId ]) || 0))
        }
        // calculate effects of Primary stats on Secondary stats:
        stats.base[1]  = (stats.base[1] || 0) + (stats.base[2] * 18) || 0                                      // Health += STR * 18
        stats.base[6]  = floor( (stats.base[6] || 0) + stats.base[ unitData[ baseId ].primaryStat ] * 1.4, 8); // Ph. Damage += MainStat * 1.4
        stats.base[7]  = floor( (stats.base[7] || 0) + (stats.base[4] * 2.4), 8 );                             // Sp. Damage += TAC * 2.4
        stats.base[8]  = floor( (stats.base[8] || 0) + (stats.base[2] * 0.14) + (stats.base[3] * 0.07), 8);    // Armor += STR*0.14 + AGI*0.07
        stats.base[9]  = floor( (stats.base[9] || 0) + (stats.base[4] * 0.1), 8);                              // Resistance += TAC * 0.1
        stats.base[14] = floor( (stats.base[14] || 0) + (stats.base[3] * 0.4), 8);                             // Ph. Crit += AGI * 0.4
        // add hard-coded minimums or potentially missing stats
        stats.base[12] = (stats.base[12] || 0) + (2400 * 1e8);  // Dodge (2400 -> 2%)
        stats.base[13] = (stats.base[13] || 0) + (2400 * 1e8);  // Deflection (2400 -> 2%)
        stats.base[15] = (stats.base[15] || 0);                 // Sp. Crit
        stats.base[16] = (stats.base[16] || 0) + (150 * 1e6);   // +150% Crit Damage
        stats.base[18] = (stats.base[18] || 0) + (15 * 1e6);    // +15% Tenacity
        return stats
    }

    calculateModStats ( baseStats, mods ) {
        if( !mods ) return {}
        const setBonuses = {}
        const rawModStats = {}
        const { modSetData } = this.gamedata
        mods.forEach(mod => {
            var [ set, pips, slot ] = mod.definitionId.split("")
            mod.set = Number(set)
            mod.pips = Number(pips)
            mod.slot = Number(slot)
            if( !mod.set ) return
            setBonuses[ mod.set ] = setBonuses[ mod.set ] || { count: 0, maxLevel: 0 }
            ++setBonuses[ mod.set ].count
            if( mod.level == 15 )
                ++setBonuses[ mod.set ].maxLevel

            let { stat } = mod.primaryStat
            rawModStats[ stat.unitStatId ] = rawModStats[ stat.unitStatId ] || 0
            rawModStats[ stat.unitStatId ] += stat.unscaledDecimalValue
            mod.secondaryStatList.forEach(ss => {
                rawModStats[ ss.stat.unitStatId ] = rawModStats[ ss.stat.unitStatId ] || 0
                rawModStats[ ss.stat.unitStatId ] += ss.stat.unscaledDecimalValue
            })
        })

        // add stats given by set bonuses
        Object.keys(setBonuses).forEach(setId => {
            const setDef = modSetData[ setId ]
            const { count, maxLevel } = setBonuses[ setId ]
            const multiplier = ~~(count / setDef.count) + ~~(maxLevel / setDef.count)
            rawModStats[ setDef.id ] = rawModStats[ setDef.id ] || 0
            rawModStats[ setDef.id ] += setDef.value * multiplier
        })

        // calcuate actual stat bonuses from mods
        return Object.keys(rawModStats).reduce((modStats, statId) => {
            var value = Number(rawModStats[ statId ])
            if( !statId || value < 0 ) return modeStats
            switch (~~statId) {
                case 41: // Offense
                    modStats[6] = (modStats[6] || 0) + value // Ph. Damage
                    modStats[7] = (modStats[7] || 0) + value // Sp. Damage
                    break
                case 42: // Defense
                    modStats[8] = (modStats[8] || 0) + value // Armor
                    modStats[9] = (modStats[9] || 0) + value // Resistance
                    break
                case 48: // Offense %
                    value *= 1e-8
                    modStats[6] = floor( (modStats[6] || 0) + (baseStats[6] * value), 8) // Ph. Damage
                    modStats[7] = floor( (modStats[7] || 0) + (baseStats[7] * value), 8) // Sp. Damage
                    break
                case 49: // Defense %
                    value *= 1e-8
                    modStats[8] = floor( (modStats[8] || 0) + (baseStats[8] * value), 8) // Armor
                    modStats[9] = floor( (modStats[9] || 0) + (baseStats[9] * value), 8) // Resistance
                    break
                case 53: // Crit Chance
                    //value *= 1e-8
                    modStats[21] = (modStats[21] || 0) + value // Ph. Crit Chance
                    modStats[22] = (modStats[22] || 0) + value // Sp. Crit Chance
                    break
                case 54: // Crit Avoid
                    value *= 1e-8
                    modStats[35] = (modStats[35] || 0) + value // Ph. Crit Avoid
                    modStats[36] = (modStats[36] || 0) + value // Ph. Crit Avoid
                    break
                case 55: // Heatlth %
                    value *= 1e-8
                    modStats[1] = floor( (modStats[1] || 0) + (baseStats[1] * value ), 8) // Health
                    break
                case 56: // Protection %
                    value *= 1e-8
                    modStats[28] = floor( (modStats[28] || 0) + ( (baseStats[28] || 0) * value ), 8) // Protection may not exist in base
                    break
                case 57: // Speed %
                    value *= 1e-8
                    modStats[5] = floor( (modStats[5] || 0) + (baseStats[5] * value ), 8) // Speed
                    break
                default:
                    // other stats add like flat values
                    modStats[ statId ] = (modStats[ statId ] || 0) + value
            }
            return modStats
        },{})
    }

    formatStats ( stats, level ) {
        var scale = 1e-8
        var descale = 1e8
        Object.keys(stats.base).forEach(statId => stats.base[statId] *= scale)
        Object.keys(stats.growthModifiers).forEach(statId => stats.growthModifiers[statId] *= scale)
        if (stats.crew)
            Object.keys(stats.crew).forEach(statId => stats.crew[statId] *= scale)
        if (stats.gear)
            Object.keys(stats.gear).forEach(statId => stats.gear[statId] *= scale)
        if (stats.mods)
            Object.keys(stats.mods).forEach(statId => stats.mods[statId] *= scale)

        function convertPercent ( statId, convertFunc ) {
            var flat = stats.base[ statId ]
            var percent = convertFunc( flat )
            stats.base[ statId ] = percent
            let last = percent
            if( stats.crew && stats.crew[ statId ] ) { // is Ship
                stats.crew[ statId ] = (/*percent = */convertFunc(flat += stats.crew[ statId ])) - last
            } else {
                if( stats.gear && stats.gear[ statId ] ) {
                    stats.gear[ statId ] = (percent = convertFunc(flat += stats.gear[statId])) - last
                    last = percent
                }
                if(stats.mods && stats.mods[statId])
                    stats.mods[statId] = (/*percent = */convertFunc(flat += stats.mods[statId])) - last
            }
        }

        convertPercent(14, ( val ) => convertFlatCritToPercent( val, scale * descale ) ); // Ph. Crit Rating -> Chance
        convertPercent(15, ( val ) => convertFlatCritToPercent( val, scale * descale ) ); // Sp. Crit Rating -> Chance
        // convert Def
        convertPercent(8,  ( val ) => convertFlatDefToPercent( val, level, scale * descale, stats.crew ? true:false ) ); // Armor
        convertPercent(9,  ( val ) => convertFlatDefToPercent( val, level, scale * descale, stats.crew ? true:false ) ); // Resistance
        // convert Acc
        convertPercent(37, ( val ) => convertFlatAccToPercent( val, scale * descale ) ); // Physical Accuracy
        convertPercent(38, ( val ) => convertFlatAccToPercent( val, scale * descale ) ); // Special Accuracy
        // convert Evasion
        convertPercent(12, ( val ) => convertFlatAccToPercent( val, scale * descale ) ); // Dodge
        convertPercent(13, ( val ) => convertFlatAccToPercent( val, scale * descale ) ); // Deflection
        // convert Crit Avoidance
        convertPercent(39, ( val ) => convertFlatCritAvoidToPercent( val, scale * descale ) ); // Physical Crit Avoidance
        convertPercent(40, ( val ) => convertFlatCritAvoidToPercent( val, scale * descale ) ); // Special Crit Avoidance

        let gsStats = { final:{} }
        const statList = Object.keys(stats.base)
        const addStats = ( statId ) => { if( !statList.includes(statId) ) statList.push(statId) }
        if( stats.gear ) { // is Char
            Object.keys( stats.gear ).forEach( addStats ) // add stats from gear to list
            if( stats.mods )
                Object.keys( stats.mods ).forEach( addStats ) // add stats from mods to list
            if( stats.mods )
                gsStats.mods = stats.mods // keep mod stats untouched

            statList.forEach( statId => {
                let flatStatId = statId
                switch (~~statId) {
                        // stats with both Percent Stats get added to the ID for their flat stat (which was converted to % above)
                    case 21: // Ph. Crit Chance
                    case 22: // Sp. Crit Chance
                        flatStatId = ~~statId - 7 // 21-14 = 7 = 22-15 ==> subtracting 7 from statID gets the correct flat stat
                        break;
                    case 35: // Ph. Crit Avoid
                    case 36: // Sp. Crit Avoid
                        flatStatId = ~~statId + 4 // 39-35 = 4 = 40-36 ==> adding 4 to statID gets the correct flat stat
                        break;
                    default:
                }

                gsStats.final[ flatStatId ] = gsStats.final[ flatStatId ] || 0 // ensure stat already exists
                gsStats.final[ flatStatId ] += (stats.base[ statId ] || 0) + (stats.gear[ statId ] || 0) + (stats.mods && stats.mods[ statId ] ? stats.mods[ statId ] : 0)
                //Adjust percents
                if( language.pct[Number(statId)] ) {
                    gsStats.final[ flatStatId ] = Math.floor(gsStats.final[ flatStatId ] * 10000) / 100
                    gsStats.mods[ flatStatId ]  = Math.floor(gsStats.mods[ flatStatId ] * 10000) / 100
                } else {
                    gsStats.final[ flatStatId ] = Math.floor(gsStats.final[ flatStatId ])
                    gsStats.mods[ flatStatId ]  = Math.floor(gsStats.mods[ flatStatId ])
                }

            })
        } else { // is Ship
            Object.keys( stats.crew ).forEach( addStats ) // add stats from crew to list
            gsStats.crew = stats.crew // keep crew stats untouched
            statList.forEach( statId => {
                gsStats.final[ statId ] = gsStats.final[ statId ] || 0
                gsStats.final[ statId ] += (stats.base[ statId ] || 0) + (stats.crew[ statId ] || 0)
                //Adjust percents
                if( language.pct[Number(statId)] ) {
                    gsStats.final[ statId ] = Math.floor(gsStats.final[ statId ] * 10000) / 100
                    gsStats.crew[ statId ]  = Math.floor(gsStats.crew[ statId ] * 10000) / 100
                } else {
                    gsStats.final[ statId ] = Math.floor(gsStats.final[ statId ])
                    gsStats.crew[ statId ]  = Math.floor(gsStats.crew[ statId ])
                }
            })
        }
        stats.result = gsStats
        return stats
    }

    renameStats ( stats, lang ) {
        if (!this.doRename) { return stats; }
        lang = language[lang || 'ENG_US'] || language['ENG_US']
        const rnStats = {}
        Object.keys( stats ).forEach( statID => {
            let statName = lang[ statID ] || statID // leave as statID if no localization string is found
            rnStats[ statName ] = stats[ statID ] || 0
        })
        return rnStats
    }

    calcCharGP ( char ) {
        var { gpTables } = this.gamedata
        let lp = gpTables.unitLevelGP[ char.currentLevel ]
        let rp = gpTables.unitRarityGP[ char.currentRarity ]
        let gtp = gpTables.gearLevelGP[ char.currentTier ]
        // Game tables for current gear include the possibility of differect GP per slot.
        // Currently, all values are identical across each gear level, so a simpler method is possible.
        // But that could change at any time.
        let ep = char.equipmentList.reduce( (power, piece) => power + gpTables.gearPieceGP[ char.currentTier ][ piece.slot ], 0)
        let sp = char.skillList.reduce( (power, skill) => power + this.getSkillGP(char.defId, skill), 0)
        let mp = char.equippedStatModList.reduce( (power, mod) => {
            var [ set, pips, slot ] = mod.definitionId.split("")
            return power + gpTables.modRarityLevelTierGP[ pips ][ mod.level ][ mod.tier ]
        },0)
        let rlp = 0
        var relicTier = char.relic ? char.relic.currentTier : char.unitRelicTier
        if (relicTier > 2) {
            rlp += gpTables.relicTierGP[ relicTier ]
            rlp += char.currentLevel * gpTables.relicTierLevelFactor[ relicTier ]
        }

        var gp = ( lp + rp + gtp + ep + sp + mp + rlp )
        return floor( gp*1.5 )
    }

    getSkillGP ( id, skill ) {
        var { unitData, gpTables } = this.gamedata
        let skillRef = unitData[ id ].skills.find( s => s.id == skill.id ) || {}
        let oTag = (skillRef.powerOverrideTags || {})[ skill.tier + 2 ]
        return oTag
            ? gpTables.abilitySpecialGP[ oTag ]
            : gpTables.abilityLevelGP[ skill.tier + 2 ] || 0
    }

    calcShipGP ( ship, crew ) {
        var { unitData, gpTables } = this.gamedata
        if( crew.length != unitData[ship.defId].crew.length )
            throw new Error(`Incorrect number of crew members for ship ${ship.defId}.`)

        var gp = 0
        if( crew.length == 0 ) { // crewless calculations
            let gps = this.getCrewlessSkillsGP( ship.defId, ship.skillList )
            gps.level = gpTables.unitLevelGP[ ship.currentLevel ]
            gp = ( gps.level*3.5 + gps.ability*5.74 + gps.reinforcement*1.61 )*gpTables.shipRarityFactor[ ship.currentRarity ]
            gp += gps.level + gps.ability + gps.reinforcement
        } else { // normal ship calculations
            gp = crew.reduce( (power, c) => power + c.gp, 0)
            gp *= gpTables.shipRarityFactor[ ship.currentRarity ] * gpTables.crewSizeFactor[ crew.length ] // multiply crewPower factors before adding other GP sources
            gp += gpTables.unitLevelGP[ ship.currentLevel ]
            gp = ship.skillList.reduce( (power, skill) => power + this.getSkillGP(ship.defId, skill), gp)
        }
        return floor( gp*1.5 )
    }

    getCrewlessSkillsGP ( id, skills ) {
        var ability = 0
        var reinforcement = 0
        var { unitData, gpTables } = this.gamedata
        skills.forEach( skill => {
            let oTag = unitData[ id ].skills.find( s => s.id == skill.id ).powerOverrideTags[ skill.tier + 2 ]
            if( oTag && oTag.substring(0,13) == 'reinforcement' )
                reinforcement += gpTables.abilitySpecialGP[ oTag ];
            else
                ability += oTag ? gpTables.abilitySpecialGP[ oTag ] : gpTables.abilityLevelGP[ skill.tier + 2 ];
        })
        return { ability, reinforcement }
    }

    getLanguage ( key ) {
        return language[key]
    }

}

module.exports = Stats

// Helpers
const unitDefId = ( unit = {} ) => (unit.definitionId || '').split(":")[0]
const floor = ( value, digits = 0 ) => Math.floor(value / Number('1e'+digits)) * Number('1e'+digits)
const convertFlatCritAvoidToPercent = ( value, scale = 1 ) => ((value / scale)/2400) * scale
const convertFlatCritToPercent = ( value, scale = 1 ) => ((value / scale)/2400 + 0.1) * scale
const convertFlatAccToPercent = (value, scale = 1) => ((value / scale)/1200) * scale
const convertFlatDefToPercent = ( value, level = 85, scale = 1, isShip = false ) => {
    const level_effect = isShip ? 300 + level*5 : level*7.5
    return ((value / scale)/(level_effect + (value / scale))) * scale
}

const language = {
    'ENG_US':[
        "None",
        "Health",
        "Strength",
        "Agility",
        "Intelligence",
        "Speed",
        "Physical Damage",
        "Special Damage", //"UNIT_STAT_ABILITY_POWER",
        "Armor", //"UNIT_STAT_ARMOR",
        "Resistance", //"UNIT_STAT_SUPPRESSION",
        "Armor Penetration", //"UNIT_STAT_ARMOR_PENETRATION",
        "Resistance Penetration", //"UNIT_STAT_SUPPRESSION_PENETRATION",
        "Dodge Rating", //"UNIT_STAT_DODGE_RATING",
        "Deflection Rating", //"UNIT_STAT_DEFLECTION_RATING",
        "Physical Critical Rating", //"UNIT_STAT_ATTACK_CRITICAL_RATING",
        "Special Critical Rating", //"UNIT_STAT_ABILITY_CRITICAL_RATING",
        "Critical Damage",
        "Potency",
        "Tenacity",
        "Dodge Chance",
        "Deflection Chance",
        "Physical Critical Chance", //"UNIT_STAT_ATTACK_CRITICAL_PERCENT_ADDITIVE",
        "Special Critical Chance", //"UNIT_STAT_ABILITY_CRITICAL_PERCENT_ADDITIVE",
        "Armor %",//UNIT_STAT_ARMOR_PERCENT_ADDITIVE",
        "Resistance %",//UNIT_STAT_SUPPRESSION_PERCENT_ADDITIVE",
        "Armor Penetration %",//UNIT_STAT_ARMOR_PENETRATION_PERCENT_ADDITIVE",
        "Resistance Penetration %",//UNIT_STAT_SUPPRESSION_PENETRATION_PERCENT_ADDITIVE",
        "Health Steal",//"UNIT_STAT_HEALTH_STEAL",
        "Protection",
        "Protection Penetration",//UNIT_STAT_SHIELD_PENETRATION",
        "Health Regeneration",//UNIT_STAT_HEALTH_REGEN",
        "Physical Damage %",//UNIT_STAT_ATTACK_DAMAGE_PERCENT_ADDITIVE",
        "Special Damage %",//UNIT_STAT_ABILITY_POWER_PERCENT_ADDITIVE",
        "Dodge Avoidance Chance",//UNIT_STAT_DODGE_NEGATE_PERCENT_ADDITIVE",//UNIT_STAT_DODGE_NEGATE_PERCENT_ADDITIVE",
        "Deflection Avoidance Chance",//UNIT_STAT_DEFLECTION_NEGATE_PERCENT_ADDITIVE",
        "Physical Critical Avoidance Chance",//UNIT_STAT_ATTACK_CRITICAL_NEGATE_PERCENT_ADDITIVE",//UNIT_STAT_ATTACK_CRITICAL_NEGATE_PERCENT_ADDITIVE",
        "Special Critical Avoidance Chance",//UNIT_STAT_ABILITY_CRITICAL_NEGATE_PERCENT_ADDITIVE",//UNIT_STAT_ABILITY_CRITICAL_NEGATE_PERCENT_ADDITIVE",
        "Dodge Avoidance Rating",//UNIT_STAT_DODGE_NEGATE_RATING",
        "Deflection Avoidance Rating",//UNIT_STAT_DEFLECTION_NEGATE_RATING",
        "Physical Critical Avoidance Rating",//UNIT_STAT_ATTACK_CRITICAL_NEGATE_RATING",
        "Special Critical Avoidance Rating",//UNIT_STAT_ABILITY_CRITICAL_NEGATE_RATING",
        "Offense",
        "Defense",
        "Defense Penetration",//UNIT_STAT_DEFENSE_PENETRATION",
        "Evasion Rating",//UNIT_STAT_EVASION_RATING",
        "Critical Chance Rating",//UNIT_STAT_CRITICAL_RATING",
        "Accuracy Rating",//"UNIT_STAT_EVASION_NEGATE_RATING",
        "Critical Avoidance Rating",//UNIT_STAT_CRITICAL_NEGATE_RATING",
        "Offense %",
        "Defense %",
        "Defense Penetration %",//UNIT_STAT_DEFENSE_PENETRATION_PERCENT_ADDITIVE",
        "Evasion",//UNIT_STAT_EVASION_PERCENT_ADDITIVE",
        "Accuracy",//UNIT_STAT_EVASION_NEGATE_PERCENT_ADDITIVE
        "Critical Chance",
        "Critical Avoidance",//UNIT_STAT_CRITICAL_NEGATE_CHANCE_PERCENT_ADDITIVE
        "Health %",
        "Protection %",
        "Speed %",// "UNIT_STAT_SPEED_PERCENT_ADDITIVE",
        "Counter Attack Rating",//UNIT_STAT_COUNTER_ATTACK_RATING",
        "Taunt",//"UNIT_STAT_TAUNT",
        "Armor Shred",//UNIT_STAT_DEFENSE_PENETRATION_TARGET_PERCENT_ADDITIVE",
        "Mastery"//UNITSTATMASTERY"
    ],

    pct:[
        false,//"None",
        false,//"Health",
        false,//"Strength",
        false,//"Agility",
        false,//"Intelligence",
        false,//"Speed",
        false,//"Physical Damage",
        false,//"Special Damage", //"UNIT_STAT_ABILITY_POWER",
        true,//"Armor", //"UNIT_STAT_ARMOR",
        true,//"Resistance", //"UNIT_STAT_SUPPRESSION",
        false,//"Armor Penetration", //"UNIT_STAT_ARMOR_PENETRATION",
        false,//"Resistance Penetration", //"UNIT_STAT_SUPPRESSION_PENETRATION",
        false,//"Dodge Rating", //"UNIT_STAT_DODGE_RATING",
        false,//"Deflection Rating", //"UNIT_STAT_DEFLECTION_RATING",
        true,//"Physical Critical Rating", //"UNIT_STAT_ATTACK_CRITICAL_RATING",
        true,//"Special Critical Rating", //"UNIT_STAT_ABILITY_CRITICAL_RATING",
        true,//"Critical Damage", 16
        true,//"Potency", 17
        true,//"Tenacity", 18
        true,//"Dodge Chance", //"UNIT_STAT_DODGE_PERCENT_ADDITIVE",
        true,//"Deflection Chance", //"UNIT_STAT_DEFLECTION_PERCENT_ADDITIVE",
        true,//"Physical Critical Chance", //"UNIT_STAT_ATTACK_CRITICAL_PERCENT_ADDITIVE",
        true,//"Special Critical Chance", //"UNIT_STAT_ABILITY_CRITICAL_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_ARMOR_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_SUPPRESSION_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_ARMOR_PENETRATION_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_SUPPRESSION_PENETRATION_PERCENT_ADDITIVE",
        true,//"Health Steal",//"UNIT_STAT_HEALTH_STEAL", 27
        false,//"Protection", 28
        true,//"UNIT_STAT_SHIELD_PENETRATION",
        true,//"UNIT_STAT_HEALTH_REGEN",
        true,//"UNIT_STAT_ATTACK_DAMAGE_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_ABILITY_POWER_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_DODGE_NEGATE_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_DEFLECTION_NEGATE_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_ATTACK_CRITICAL_NEGATE_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_ABILITY_CRITICAL_NEGATE_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_DODGE_NEGATE_RATING",
        true,//"UNIT_STAT_DEFLECTION_NEGATE_RATING",
        true,//"UNIT_STAT_ATTACK_CRITICAL_NEGATE_RATING",
        true,//"UNIT_STAT_ABILITY_CRITICAL_NEGATE_RATING",
        false,//"Offense",
        false,//"Defense",
        true,//"UNIT_STAT_DEFENSE_PENETRATION",
        true,//"UNIT_STAT_EVASION_RATING",
        true,//"UNIT_STAT_CRITICAL_RATING",
        true,//"UNIT_STAT_EVASION_NEGATE_RATING",
        true,//"UNIT_STAT_CRITICAL_NEGATE_RATING",
        true,//"Offense %", 48
        true,//"Defense %", 49
        true,//"UNIT_STAT_DEFENSE_PENETRATION_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_EVASION_PERCENT_ADDITIVE",
        true,//"Accuracy", 52
        true,//"Critical Chance", 53
        true,//"Critical Avoidance", 54
        true,//"Health %", 55
        true,//"Protection %", 56
        true,//"Speed %",// "UNIT_STAT_SPEED_PERCENT_ADDITIVE",
        true,//"UNIT_STAT_COUNTER_ATTACK_RATING",
        true,//"UNIT_STAT_TAUNT" 59
        true,//"UNITSTATDEFENSEPENETRATIONTARGETPERCENTADDITIVE"
        false,//"UNITSTATMASTERY" 61
    ]


}