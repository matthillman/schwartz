#!/usr/bin/env bash

IFS=$' \n'

declare -a units
if [[ $# > 0 ]]; then
    units=($@)
else
    units=(`echo "select base_id from units order by 1;" | psql -t -U schwartz`)
fi

for unit in "${units[@]}"; do
    echo "Downloading https://swgoh.gg/game-asset/u/${unit}/"
    curl https://swgoh.gg/game-asset/u/${unit}/ > ${unit}.png
    convert ${unit}.png -transparent black ${unit}.png
done

