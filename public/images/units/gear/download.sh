#!/usr/bin/env bash

IFS=$' \n'

for level in {1..12}; do
    curl https://api.swgoh.help/imageApi/gear-icon-g${level}.png > gear-icon-g${level}.png
done

