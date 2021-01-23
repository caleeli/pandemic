<template>
  <g
    class="move-to-target"
    :style="{
      transform: `translate(${x - xd}px, ${y - yd}px)`,
      'animation-duration': `${time}s`,
    }"
  >
    <g :transform="`translate(${xd}, ${yd}) scale(0.005) translate(-288, -256)`">
      <path
        :style="{ fill: fill, transform: `rotate(${angle}rad)` }"
        d="M480 192H365.71L260.61 8.06A16.014 16.014 0 0 0 246.71 0h-65.5c-10.63 0-18.3 10.17-15.38 20.39L214.86 192H112l-43.2-57.6c-3.02-4.03-7.77-6.4-12.8-6.4H16.01C5.6 128-2.04 137.78.49 147.88L32 256 .49 364.12C-2.04 374.22 5.6 384 16.01 384H56c5.04 0 9.78-2.37 12.8-6.4L112 320h102.86l-49.03 171.6c-2.92 10.22 4.75 20.4 15.38 20.4h65.5c5.74 0 11.04-3.08 13.89-8.06L365.71 320H480c35.35 0 96-28.65 96-64s-60.65-64-96-64z"
      ></path>
    </g>
  </g>
</template>

<script>
export default {
  props: {
    x: Number,
    y: Number,
    xd: Number,
    yd: Number,
    fill: {
      type: String,
      default: "white",
    },
  },
  computed: {
    angle() {
      return Math.atan2(this.yd - this.y, this.xd - this.x);
    },
    time() {
      return (
        Math.sqrt(
          (this.xd - this.x) * (this.xd - this.x) +
            (this.yd - this.y) * (this.yd - this.y)
        ) * 0.1
      );
    },
  },
};
</script>

<style>
.move-to-target {
  animation-name: move-to-target;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
  animation-direction: normal;
}
@keyframes move-to-target {
  to {
    transform: translate(0, 0);
  }
}
</style>