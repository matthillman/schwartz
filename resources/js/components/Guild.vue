<template>
    <a class="guild" :href="url" target="_blank">
        <img :src="icon">
        <div class="info">
            <div class="name">{{name}}</div>
            <div class="sub">
                <div class="gp">{{gp}} GP</div>
                <div class="tb">{{tb}}★</div>
                <div class="focus">{{focus}} Focused</div>
                <div class="raids">{{raids}}</div>
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
            tb: String,
            focus: String,
            raids: String,
        },
    }
</script>
