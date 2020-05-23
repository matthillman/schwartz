<template>
    <div class="set"
        :key="set.id"
        @click="activateSet(set.id)"
        :class="{active: set.id == currentSet, over: index == dragOverIndex, dragging: index == draggingIndex}"
        draggable="true"
        @dragstart.self="onDragStart(index, $event)"
        @dragover.prevent="onDragOver(index, $event)"
        @dragenter="onDragEnter(index)"
        @dragleave.self="onDragLeave(index)"
        @drop.prevent.stop="onDrop(index, $event)"
    >
        <span class="delete-set" @click.stop="removeSet = set"></span>
        <span>Speed: {{ formatSet(set) }}</span>
        <cool-select
            v-model="set.destination"
            :items="units"
            :loading="syncingUnits"
            :placeholder="set.destination ? '' : 'Select Unit'"
            item-value="unit_name"
            item-text="unit_name"
            @select="destinationUpdatedFor(set)"
        >
            <template slot="item" slot-scope="{ item: character }">
                <div class="portrait-preview">
                    <img class="character" :src="`/images/units/${ character.unit_name }.png`" />
                    <div class="character-name">
                        {{ character.unit.name }}
                    </div>
                </div>
            </template>

            <template slot="selection" slot-scope="{ item: character }">
                <div class="portrait-preview">
                    <img class="character" :src="`/images/units/${ character.unit_name }.png`" />
                    <div class="character-name">
                        {{ character.unit.name }}
                    </div>
                </div>
            </template>
        </cool-select>
        <div class="mod-list">
            <div v-for="shape in shapes" :key="shape" :class="[`tier-${tierFor(shape, set)}`]">
                <div class="mod-image mini" :class="[setFor(shape, set), shape, `tier-${tierFor(shape, set)}`, {'gold': pipsFor(shape, set) > 5}]"></div>
                <span>{{ locationFor(shape, set) }}</span>
            </div>
        </div>
        <button class="view-modal btn btn-primary" @click.stop="showOverlayFor(set)">View</button>
    </div>
</template>