#!/usr/bin/env bash

declare -a shapes=("circle" "cross" "diamond" "square" "triangle" "arrow")
declare -a sets=("critchance" "critdamage" "empty" "health" "offense" "potency" "speed" "tenacity" "defense")

for shape in "${shapes[@]}"; do
	for set in "${sets[@]}"; do
		curl http://apps.crouchingrancor.com/images/mods/${shape}_${set}.png > ${shape}_${set}.png
	done
done

