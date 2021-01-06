<template>
  <div class="d-flex flex-row w-100 flex-nowrap">
    <div>
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
            :r="2 + city.points * 0.5"
            :stroke="cityColor(city)"
            stroke-width="1"
            :fill="city.color"
            fill-opacity="0.7"
            :class="{ citySelected: city === selectedCity }"
          ></circle>
          <text
            v-for="(city, index) in cities"
            :key="`text-${index}`"
            :x="city.x"
            :y="city.y"
            text-anchor="middle"
            stroke="black"
            stroke-width="0.5px"
            dy=".3em"
            font-size="3"
          >
            {{ city.pivot.infection }}
          </text>
          <g
            v-for="(player, index) in players"
            :key="`player-${index}`"
            :transform="`translate(${playerPos(
              player
            )}) scale(0.1) translate(-64,-98)`"
            class="scales"
          >
            <path
              :style="`fill: ${playerColors[player.id % playerColors.length]}`"
              d="m91.3 49.3c.028 32.55-27.3 56.7-27.3 56.7s-27.3-24.15-27.3-56.7a27.3 27.3 0 0 1 54.6 0z"
            />
            <circle cx="64" cy="49.3" style="fill: #eeefee" r="16.525" />
          </g>
        </svg>
      </div>
    </div>
    <div>
      <div
        class="map-pan"
        :style="{
          backgroundImage: `url(${world})`,
          backgroundPositionX: `-${(posX / 320) * 3360 - 160}px`,
          backgroundPositionY: `-${(posY / 160) * 1705 - 64}px`,
        }"
        @click="centerCity"
      ></div>
      <b-button v-if="editMode" @click="randomConns"
        >Random connections</b-button
      >
      <b-button @click="resetGame">Reiniciar juego</b-button>
    </div>
  </div>
</template>

<script>
import world from "../../images/world.jpg";
const colors = ["pink", "yellow", "lightgreen", "cyan"];
const playerColors = ["white", "blue", "red", "green"];
const editMode = false;

function random(colors) {
  return colors[Math.floor(Math.random() * colors.length)];
}
const gameApiParams = {
  include: "game",
};

export default {
  path: "/",
  mixins: [window.ResourceMixin],
  data() {
    return {
      editMode,
      colors,
      playerColors,
      world,
      posX: 0,
      posY: 0,
      cities: [],
      players: [],
      selectedCity: null,
      game: this.$api.user[window.userId].row(gameApiParams),
    };
  },
  watch: {
    game: {
      deep: true,
      handler() {
        this.cities.splice(0);
        this.players.splice(0);
        this.game.relationships.game.attributes.cities.forEach((dbC) => {
          dbC.x = dbC.x * 1;
          dbC.y = dbC.y * 1;
          this.cities.push({
            ...dbC,
          });
        });
        this.game.relationships.game.attributes.players.forEach((player) => {
          this.players.push(player);
        });
        if (!this.selectedCity) {
          this.selectedCity = this.cities.find(
            (c) => c.id == this.game.attributes.city_id
          );
          this.posX = this.selectedCity.x;
          this.posY = this.selectedCity.y;
        }
      },
    },
  },
  computed: {
    connections() {
      const conns = [];
      this.cities.forEach((city) => {
        city.connections.forEach((conn) => {
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
    cityColor(city) {
      const i = ((city.pivot.infection * 1) / 10) * 255,
        r = 255,
        g = 255 - i,
        b = 255 - i;
      return `rgb(${r},${g},${b})`;
    },
    playerPos(player) {
      const city = this.cities.find((city) => city.id == player.city_id);
      return `${city.x}, ${city.y}`;
    },
    centerCity(event) {
      if (!this.editMode) return;
      const pos = this.getPixelPosition(event);
      const newX =
        this.posX * 1 +
        (((pos.x / event.target.clientWidth - 0.5) * event.target.clientWidth) /
          3360) *
          320;
      const newY =
        this.posY * 1 +
        (((pos.y / event.target.clientHeight - 0.5) *
          event.target.clientHeight) /
          1705) *
          160;
      this.posX = newX;
      this.posY = newY;
      this.$api.city[this.selectedCity.id].put({
        attributes: {
          x: this.posX,
          y: this.posY,
        },
      });
    },
    resetGame() {
      this.$api.users.call(window.userId, "resetGame", {});
    },
    randomConns() {
      for (let i = 0; i < 6; i++) {
        this.randomConn();
      }
      const cities = JSON.parse(JSON.stringify(this.cities));
      cities.forEach((city) => {
        this.$api.city[city.id].put({
          attributes: {
            connections: city.connections,
          },
        });
      });
    },
    randomConn() {
      this.cities.forEach((city, index0) => {
        if (city.connections.length > city.points) {
          return;
        }
        let city2 = null,
          minDistance = 0;
        for (let i = 0; i < this.cities.length; i++) {
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
          let distance =
            (city.x - newCity.x) * (city.x - newCity.x) +
            (city.y - newCity.y) * (city.y - newCity.y);
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
    getPixelPosition(evt) {
      var rect = evt.target.getBoundingClientRect();
      var x = Math.round(evt.clientX - rect.left);
      var y = Math.round(evt.clientY - rect.top);
      return { x, y };
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
      if (this.editMode) {
        this.placeCity(event);
      }
    },
    placeCity(event) {
      const pos = this.getPosition(event);
      const city = {
        x: pos.x,
        y: pos.y,
        color: random(colors),
        points: 1,
        connections: [],
      };
      this.cities.push(city);
      this.$api.cities.call(null, "addCity", city);
    },
    moveMap(event) {
      /*const pos = this.getPosition(event);
      this.posX = pos.x;
      this.posY = pos.y;*/
    },
    selectCity(event) {
      const pos = this.getPosition(event);
      let min, city;
      this.cities.forEach((c) => {
        const distance =
          (c.x - pos.x) * (c.x - pos.x) + (c.y - pos.y) * (c.y - pos.y);
        if (!city || distance < min) {
          min = distance;
          city = c;
        }
      });
      this.selectedCity = city;
      this.posX = city.x;
      this.posY = city.y;
    },
  },
  mounted() {
    window.Echo.channel(`PublicGame`).listen(".UpdateMap", (e) => {
      //this.$api.cities.refresh(this.dbCities, { per_page: -1 });
      this.$api.users.refresh(this.game, gameApiParams);
    });
  },
};
</script>

<style>
.map-pan {
  position: relative;
  width: 320px;
  height: 128px;
  overflow: hidden;
}
.base {
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
.scales * {
  animation: scales 1.2s ease-in-out infinite alternate;
}
.citySelected {
  animation: citySelected 0.3s ease-in-out infinite alternate;
}
@keyframes dash {
  to {
    stroke-dashoffset: 150;
  }
}
@keyframes scales {
  from {
    transform: scale(1) translate(0px, 0px);
  }
  to {
    transform: scale(1.11) translate(0px, -40px);
  }
}
@keyframes citySelected {
  from {
    stroke-width: 1;
  }
  to {
    stroke-width: 0.1;
    stroke: black;
  }
}
</style>
