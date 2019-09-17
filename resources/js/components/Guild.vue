<template>
    <a class="guild" v-bind:href="url" target="_blank">
        <img v-bind:src="'/images/guilds/' + icon + '.png'">
        <div class="info">
            <div class="name">{{name}}</div>
            <div class="sub">
                <div class="gp">{{gp}} GP</div>
                <div class="raid">{{raid}}</div>
            </div>
        </div>
    </a>
</template>

<script>
    export default {
        props: {
            name: String,
            url: String,
            icon: String,
            gp: String,
            raid: String,
        },
    }
</script>

<style lang="scss" scoped>
.guild {
    width: 500px;
    height: 75px;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    text-decoration: none;
    color: rgba(0, 0, 0, 1);
    margin-bottom: 16px;
    border-radius: 8px;

    &:hover {
        opacity: 0.8;
    }

    img {
        height: 75px;
        width: 75px;
        margin: 0 16px;
    }

    transition-property: transform, background-color;
    transition-duration: 600ms;
    transition-timing-function: ease;
    transition-delay: 0;

    .down-scroll + *:not(.down-scroll) & {
        transform: translate3d(0, 0, 0);
        transition-delay: 900ms;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        font-size: 16px;
        flex-grow: 1;
        margin: 0 16px;

        .name {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .sub {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
    }
}


@media (max-width: 600px) {
  .guild {
    width: 100%;
    height: 44px;

    img {
        height: 44px;
        width: 44px;
        margin: 0 8px;
    }

    .info {
        font-size: 10px;
        margin: 0 8px;

        .name {
            font-size: 16px;
            margin-bottom: 4px;
        }
    }
  }
}
</style>
