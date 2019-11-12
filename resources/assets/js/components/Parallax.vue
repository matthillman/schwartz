<template>
    <div class="parallax-container"
        v-scroll="handleScroll"
        v-touchstart="handleTouchStart"
        v-touchmove="handleTouchMove"
        v-touchend="handleTouchEnd"
    >
        <section
            v-for="(item, index) in $slots.sections"
            :key="index"
            v-bind:class="{'down-scroll': currentSlide > index, 'up-scroll': maxSeenSlide > index && currentSlide <= index}"
        ><content-wrapper :node="item" /></section>
    </div>
</template>

<script>
    export default {
        mounted: function() {
            window.addEventListener('hashchange', (evt) => this.handleHashChange(evt));
            this.$nextTick(function() {
                this.handleHashChange();
            });
        },
        data: function() {
            return {
                ticking: false,
                currentSlide: 0,
                maxSeenSlide: 0,
                scrollSensitivity: 30,
                duration: 900,

                touchStart: -1,
                touchInProgress: false,

                isIE: (/MSIE/i.test(navigator.userAgent)) || (/Trident.*rv\:11\./i.test(navigator.userAgent)),
                isFirefox: (/Firefox/i.test(navigator.userAgent)),
            }
        },
        computed: {
            totalSlides: function() {
                return this.$el.children.length;
            },
            hashes: function() {
                return $(this.$el.children).find('.content-wrapper').map((i, el) => '#' + el.id).get();
            }
        },

        directives: {
            scroll: {
                inserted: function (el, binding) {
                    window.addEventListener((/Firefox/i.test(navigator.userAgent)) ? 'DOMMouseScroll' : 'wheel', (evt) => binding.value(evt, el));
                }
            },
            touchstart: {
                inserted: function (el, binding) {
                    window.addEventListener('touchstart', (evt) => binding.value(evt, el));
                }
            },
            touchmove: {
                inserted: function (el, binding) {
                    window.addEventListener('touchmove', (evt) => binding.value(evt, el));
                }
            },
            touchend: {
                inserted: function (el, binding) {
                    window.addEventListener('touchend', (evt) => binding.value(evt, el));
                }
            }
        },

        methods: {
            handleScroll(evt) {
                if (this.ticking) { return; }
                let delta = this.isFirefox ? evt.detail * -120 : this.isIE ? -evt.deltaY : evt.wheelDelta;
                this.doScroll(delta);
            },
            handleTouchStart(evt) {
                this.touchStart = evt.touches[0].pageY;
            },
            handleTouchMove(evt) {
                if (this.touchInProgress || this.ticking) { return; }
                this.doScroll(evt.touches[0].pageY - this.touchStart);
            },
            handleTouchEnd(evt) {
                this.touchInProgress = false;
            },
            handleHashChange(evt) {
                let next = this.hashes.indexOf(window.location.hash);
                if (next < 0 || next === this.currentSlide) { return; }
                this.currentSlide = next;
                console.warn(`Has change got ${this.currentSlide} from ${window.location.hash}`, this.hashes);
            },
            doScroll(delta) {
                if (Math.abs(delta) < this.scrollSensitivity || this.ticking) { return; }
                this.ticking = true;
                this.touchInProgress = true;

                if (delta < 0) {
                    this.currentSlide = Math.min(this.currentSlide + 1, this.totalSlides - 1);
                } else {
                    this.currentSlide = Math.max(this.currentSlide - 1, 0);
                }
                this.maxSeenSlide = Math.max(this.maxSeenSlide, this.currentSlide);

                setTimeout(() =>{
                    this.ticking = false;
                    window.location.hash = this.hashes[this.currentSlide];
                }, this.duration);
            }
        }
    }
</script>

<style lang="scss">

// ------------- MIXINS ------------- //
@mixin transition($time, $property: all, $easing: ease) {
    transition: $property $time $easing;
}

// ------------- VARIABLES ------------- //
$parallax-offset: 10vh;
$content-offset: 40vh;
$transition-speed: 900ms;
$slide-number: 3;

body.no-scroll {
  overflow: hidden;
}

.parallax-container {
    > section {
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      overflow: hidden;
      will-change: transform;
      backface-visibility: hidden;
      height: 100vh + $parallax-offset;
      position: fixed;
      width: 100%;
      transform: translateY($parallax-offset);
      @include transition($transition-speed);

      &:before {
        content: "";
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0,0,0,.3);
      }

      &:first-child {
        transform: translateY(-$parallax-offset / 2);
        .content-wrapper {
          transform: translateY($parallax-offset /2);
        }
      }

      /* Set stacking context of slides */
      @for $i from 1 to ($slide-number + 1) {
          &:nth-child(#{$i}) {
              z-index: ($slide-number + 1) - $i;
              background-image: url(/images/welcome/background#{$i}.jpg);
          }
      }

      &.up-scroll {
        transform: translateY(-$parallax-offset / 2);
        .content-wrapper {
          transform: translateY($parallax-offset / 2);
        }
        + section {
          transform: translateY($parallax-offset);
            .content-wrapper {
              transform: translateY($parallax-offset);
            }
          }
      }

      &.down-scroll {
        transform: translateY(-(100vh + $parallax-offset));
        .content-wrapper {
          transform: translateY($content-offset);
        }
        + section:not(.down-scroll) {
          transform: translateY(-$parallax-offset / 2);
            .content-wrapper {
              transform: translateY($parallax-offset / 2);
            }
        }
      }

      .content {
        &-wrapper {
          height: 100vh;
          padding: 0 16px;
          display: flex;
          justify-content: center;
          text-align: center;
          flex-flow: column nowrap;
          color: #fff;
          text-transform: uppercase;
          transform: translateY($content-offset);
          will-change: transform;
          backface-visibility: hidden;
          @include transition($transition-speed * 0.9);

            .col-form-label {
                font-size: 18px;
            }
        }
        &-title {
          font-size: 10vh;
          line-height: 1.4;
        }
      }

    }
}

@media (max-width: 600px) {
    .parallax-container > section:not(:first-of-type) .content {
        &-wrapper {
            justify-content: flex-start;

            .col-form-label, .form-control, .btn {
                font-size: 14px;
            }
        }
    }
    .parallax-container > section .content {
        &-title {
            font-size: 6vh;
            line-height: 1.2;
        }
    }
}
</style>
