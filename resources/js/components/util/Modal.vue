<template>
  <transition name="modal">
    <div class="modal-mask">
      <div class="modal-wrapper">
        <div class="modal-container" :class="{wider, narrower}">

          <div class="modal-header">
            <slot name="header">
              default header
            </slot>
          </div>

          <div class="modal-body">
            <slot name="body">
              default body
            </slot>
          </div>

          <div class="modal-footer">
            <slot name="footer">
            </slot>
            <button v-if="!noClose" class="modal-default-button btn btn-primary striped" @click="$emit('close')"><span>Close</span></button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  props: {
    noClose: {
      type: Boolean,
      default: false
    },
    wider: Boolean,
    narrower: Boolean,
  },
}
</script>

<style lang="scss">
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba($color: #000000, $alpha: 0.5);
  display: table;
  transition: opacity .3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  width: 510px;
  margin: 0px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba($color: #000000, $alpha: 0.33);
  transition: all .3s ease;

  &.wider {
    width: 750px;
  }
  &.narrower {
    width: 410px;
  }
}

.modal-header h3 {
  margin-top: 0;
}

.modal-body {
  margin: 16px 0;
  padding: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
}

.modal-enter {
  opacity: 0;
}

.modal-leave-active {
  opacity: 0;
}

.modal-enter, .modal-leave-active {
    .modal-container {
        transform: scale(1.1);
    }
}
</style>
