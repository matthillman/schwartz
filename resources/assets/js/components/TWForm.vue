<template>
<div>
    <div class="card-header row justify-content-between align-items-center">
        <h2>Counters</h2>
        <div class="row add-row">
            <button type="button" @click.prevent="addTWCounter('.counter-list')" class="btn btn-primary">Add Counter</button>
        </div>
    </div>

    <div class="card-body counter-list">
        <slot v-bind:delete="deleteCounter"/>
    </div>
</div>
</template>

<script>
const counterTemplate = `
<div class="counter">
    <div class="form-group row">
        <label for="counters" class="col-md-4 col-form-label text-md-right">Team</label>
        <div class="col-md-6">
            <input type="text" class="form-control" name="counter[]" placeholder="Name" required>
        </div>
        <div class="col-md-1">
            <button class="btn btn-danger" @click="deleteCounter()">Delete</button>
        </div>
    </div>
    <div class="form-group row border-bottom">
        <label for="counters" class="col-md-4 col-form-label text-md-right">Notes</label>
        <div class="col-md-6">
            <input type="text" class="form-control" name="notes[]">
        </div>
    </div>
</div>
`;

export default {
    methods: {
        addTWCounter: (parent) => {
            $(counterTemplate).appendTo(parent);
        },
        deleteCounter: (evt) => {
            $(evt.target).parents('.counter').first().remove();
        }
    }
}
</script>