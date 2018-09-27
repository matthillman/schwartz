#!/usr/bin/env bash

IFS=$' \n'
declare -a states=("active" "inactive")

for state in "${states[@]}"; do
    curl https://api.swgoh.help//imageApi/star-${state}.png > ${state}.png
done

