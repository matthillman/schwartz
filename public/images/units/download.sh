#!/usr/bin/env bash

IFS=$' \n'
declare -a units=(`echo "select base_id from units where combat_type = 'CHARACTER' order by 1;" | psql -t -U schwartz`)

for unit in "${units[@]}"; do
    echo "Downloading https://api.swgoh.help/image/char/${unit}"
    curl https://api.swgoh.help/image/char/${unit} > ${unit}.png
    convert ${unit}.png -transparent black ${unit}.png
done

