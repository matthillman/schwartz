#!/usr/bin/env bash

IFS=$' \n'

declare -a units
if [[ $# > 0 ]]; then
    units=($@)
else
    units=(`echo "select base_id from units where combat_type = 'CHARACTER' order by 1;" | psql -t -U schwartz`)
fi

for unit in "${units[@]}"; do
    echo "Downloading https://api.swgoh.help/image/char/${unit}"
    curl https://api.swgoh.help/image/char/${unit} > ${unit}.png
    convert ${unit}.png -transparent black ${unit}.png
done

