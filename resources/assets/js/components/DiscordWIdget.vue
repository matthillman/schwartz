<template>
  <div class="discord-widget">
    <div class="header">
      <div class="discord-logo">
        <slot></slot>
      </div>
      <div v-if="this.info.members">{{ this.info.members.length }} Members Online</div>
    </div>
    <div class="footer">
      <a class="invite-link" :href="`https://discord.gg/${this.invite}`" target="_discord">Connect</a>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    server: String,
    invite: String
  },
  data() {
    return {
      info: {}
    };
  },
  mounted() {
    this.refresh();
    setInterval(() => this.refresh, 6000);
  },
  methods: {
    refresh: function() {
      axios.get(`/discord/${this.server}/`).then(res => {
        this.info = res.data;
      });
    }
  }
};
</script>

<style lang="scss" scoped>
@import "../../sass/_variables.scss";

.discord-widget {
  display: flex;
  font-size: 20px;
  color: #ffffff;
  font-weight: bold;
  flex-direction: column;
  justify-content: center;
  width: 350px;
  margin: 0 auto;
  border-radius: 8px;
  overflow: hidden;

    font-size: 14px;
    font-weight: normal;
    text-transform: capitalize;

  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: $discord-purple;
    transition: background-color 0.3s ease;
  }

  .discord-logo {
    display: flex;

    svg {
      height: 36px;
      fill: #ffffff;
    }
  }

  .footer {
    display: flex;
    min-height: 30px;
    padding: 6px 20px;
    align-items: center;
    justify-content: center;
    background-color: #202225;
    box-shadow: 0 -1px 18px rgba(0, 0, 0, 0.2), 0 -1px 0 rgba(0, 0, 0, 0.2);
  }

  a.invite-link {
    display: flex;
    height: 30px;
    justify-content: center;
    align-items: center;
    transition: opacity 0.25s ease-out;
    width: 120px;
    background-clip: padding-box;
    background-color: hsla(0, 0%, 100%, 0.1);
    border: 1px solid #212325;
    border-radius: 4px;
    box-shadow: inset 0 1px 0 hsla(0, 0%, 100%, 0.04);
    color: white;
    text-decoration: none;

    &:hover {
        opacity: 0.6;
    }
  }
}
</style>