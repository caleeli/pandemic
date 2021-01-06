<template>
  <div class="d-flex flex-row w-100 flex-nowrap">
    <div class="base">
      <img :src="world" width="100%" />
      <svg
        viewBox="0 0 320 160"
        width="100"
        height="100"
        class="juego"
        @click="clickMap"
        @mousemove="moveMap"
      >
        <line
          v-for="(connection, index) in connections"
          :key="`connection-${index}`"
          :x1="connection.x1"
          :y1="connection.y1"
          :x2="connection.x2"
          :y2="connection.y2"
          stroke="rgba(255,255,255,0.5)"
          class="path"
          stroke-width="0.5"
        ></line>
        <circle
          v-for="(city, index) in cities"
          :key="`city-${index}`"
          :cx="city.x"
          :cy="city.y"
          :r="2+city.points*0.5"
          stroke="rgba(255,255,255,1)"
          stroke-width="1"
          :fill="city.color"
          fill-opacity="0.7"
        ></circle>
  <g :transform="`translate(${posX}, ${posY}) scale(0.1) translate(-64,-94)`">
    <path
     style="fill:red"
     d="m91.3 49.3c.028 32.55-27.3 56.7-27.3 56.7s-27.3-24.15-27.3-56.7a27.3 27.3 0 0 1 54.6 0z" />
    <circle cx="64" cy="49.3" style="fill:#eeefee" r="16.525" />
  </g>
      </svg>
    </div>
    <div>
      <div
        class="map-pan"
        :style="{
          backgroundImage: `url(${world})`,
          backgroundPositionX: `-${posX/320*3360-128}px`,
          backgroundPositionY: `-${posY/160*1705-64}px`,
        }"
      >
      </div>
      <b-button @click="randomConns">Random connections</b-button>
      <b-button @click="resetGame">Reiniciar juego</b-button>
    </div>
  </div>
</template>

<script>
import world from "../../images/world.jpg";
const colors = ["pink", "yellow", "lightgreen", "cyan"];

function random(colors) {
  return colors[Math.floor(Math.random() * colors.length)];
}

export default {
  path: "/",
  mixins: [window.ResourceMixin],
  data() {
    return {
      world,
      posX: 0,
      posY: 0,
      cities: [],
      dbCities: this.$api.cities.array({per_page: -1}),
      selectedCity: null,
    };
  },
  watch: {
    dbCities() {
      this.cities.splice(0);
      this.dbCities.forEach(dbC => {
        this.cities.push({
          ...dbC.attributes,
          connections: [],
        });
      });
    },
  },
  computed: {
    connections() {
      const conns = [];
      this.cities.forEach(city => {
        city.connections.forEach(conn => {
          const city2 = this.cities[conn];
          conns.push({
            x1: city.x,
            y1: city.y,
            x2: city2.x,
            y2: city2.y,
          });
        });
      });
      return conns;
    },
  },
  methods: {
    resetGame() {
      this.$api.cities.call(null, 'resetGame');
    },
    randomConns() {
      for(let i=0; i<6; i++) {
        this.randomConn();
      }
    },
    randomConn() {
      this.cities.forEach((city, index0) => {
        if (city.connections.length > city.points) {
          return;
        }
        let city2 = null, minDistance = 0;
        for(let i=0; i< this.cities.length; i++) {
          let newCity = random(this.cities);
          let index = this.cities.indexOf(newCity);
          if (
            city.connections.indexOf(index) !== -1 ||
            newCity.connections.indexOf(index0) !== -1 ||
            newCity.connections.length > newCity.points ||
            newCity === city
          ) {
            continue;
          }
          let distance = (city.x - newCity.x) *(city.x - newCity.x) + (city.y - newCity.y) * (city.y - newCity.y);
          if (!city2 || distance < minDistance) {
            minDistance = distance;
            city2 = newCity;
          }
        }
        let index = this.cities.indexOf(city2);
        if (index > -1) {
          city.connections.push(index);
          city2.connections.push(index0);
        }
      });
    },
    getPosition(evt) {
      const elem = evt.currentTarget.closest("svg");
      const point = elem.createSVGPoint();
      const transform = elem.getScreenCTM().inverse();
      point.x = evt.clientX;
      point.y = evt.clientY;
      const newPt = point.matrixTransform(transform);
      return newPt;
    },
    clickMap(event) {
      this.selectCity(event);
      /*const pos = this.getPosition(event);
      const city = {
        x: pos.x,
        y: pos.y,
        color: random(colors),
        points: 1,
        connections: [],
      };
      this.cities.push(city);
      this.$api.cities.call(null, 'addCity', city);*/
    },
    moveMap(event) {
      /*const pos = this.getPosition(event);
      this.posX = pos.x;
      this.posY = pos.y;*/
    },
    selectCity(event) {
      const pos = this.getPosition(event);
      let min, city;
      this.cities.forEach(c => {
        const distance = (c.x - pos.x) * (c.x - pos.x) + (c.y - pos.y) * (c.y - pos.y);
        if (!city || distance < min) {
          min = distance;
          city = c;
        }
      });
      this.selectedCity = city;
      this.posX = city.x;
      this.posY = city.y;
    }
  },
  mounted() {
    window.Echo.channel(`PublicGame`)
    .listen('.UpdateMap', (e) => {
      this.$api.cities.refresh(this.dbCities, {per_page: -1});
    });
  }
};
</script>

<style>
.map-pan {
  position: relative;
  height: 128px;
  overflow: hidden;
}
.base {
  width: 80%;
  position: relative;
}
.juego {
  position: absolute;
  top: 0px;
  left: 0px;
  width: 100%;
  height: 100%;
}
.path {
  stroke-dasharray: 3;
  animation: dash 12s linear infinite;
}
@keyframes dash {
  to {
    stroke-dashoffset: 150;
  }
}
</style>
