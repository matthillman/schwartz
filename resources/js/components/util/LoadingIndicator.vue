<template>
  <div class="loading-wrapper">
    <div class="lds-grid">
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
      <div></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import "../../../sass/_variables";

.loading-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}
$dot-size: 12px;
.lds-grid {
  display: inline-block;
  position: relative;
  width: 5 * $dot-size;
  height: 5 * $dot-size;

  div {
    position: absolute;
    width: $dot-size;
    height: $dot-size;
    border-radius: 50%;
    background: $primary;
    animation: lds-grid 1.2s linear infinite;

    &:nth-child(1) {
      top: $dot-size / 2;
      left: $dot-size / 2;
      animation-delay: 0s;
    }
    &:nth-child(2) {
      top: $dot-size / 2;
      left: 2 * $dot-size;
      animation-delay: -0.4s;
    }
    &:nth-child(3) {
      top: $dot-size / 2;
      left: 3.5 * $dot-size;
      animation-delay: -0.8s;
    }
    &:nth-child(4) {
      top: 2 * $dot-size;
      left: $dot-size / 2;
      animation-delay: -0.4s;
    }
    &:nth-child(5) {
      top: 2 * $dot-size;
      left: 2 * $dot-size;
      animation-delay: -0.8s;
    }
    &:nth-child(6) {
      top: 2 * $dot-size;
      left: 3.5 * $dot-size;
      animation-delay: -1.2s;
    }
    &:nth-child(7) {
      top: 3.5 * $dot-size;
      left: $dot-size / 2;
      animation-delay: -0.8s;
    }
    &:nth-child(8) {
      top: 3.5 * $dot-size;
      left: 2 * $dot-size;
      animation-delay: -1.2s;
    }
    &:nth-child(9) {
      top: 3.5 * $dot-size;
      left: 3.5 * $dot-size;
      animation-delay: -1.6s;
    }
  }
}
@keyframes lds-grid {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>