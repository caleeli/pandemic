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
          <text
            v-if="game.relationships"
            x="4"
            y="4"
            font-size="5"
            stroke-width="2px"
            dy=".3em"
          >
            Time:
            {{
              game.relationships &&
              game.relationships.game &&
              game.relationships.game.attributes.time
            }}
          </text>
          <foreignObject x="4" y="8" width="48px" height="8px" v-if="cura">
            <div style="font-size: 4px">
              <i class="fas fa-shield-virus text-success"></i> CURA
            </div>
          </foreignObject>
          <text x="280" y="4" font-size="5" stroke-width="2px" dy=".3em">
            Infection: {{ totalInfection }} %
          </text>
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
            :class="{
              canChooseCityNoConnection: chooseCityMode.enabled,
            }"
          ></line>
          <line
            v-for="(connection, index) in chooseConnections"
            :key="`choose-connection-${index}`"
            :x1="connection.x1"
            :y1="connection.y1"
            :x2="connection.x2"
            :y2="connection.y2"
            :stroke="connectionColor(connection)"
            class="path"
            stroke-width="0.5"
          ></line>
          <circle
            v-for="(city, index) in cities"
            :key="`city-${index}`"
            :cx="city.x"
            :cy="city.y"
            :r="radius(city)"
            :stroke="cityColor(city)"
            stroke-width="1"
            :fill="city.color"
            fill-opacity="0.7"
            :class="{
              citySelected: city === selectedCity,
              canChooseCityEnabled:
                chooseCityMode.enabled && city !== currentCity,
              canChooseCityCity: chooseCityMode.cities.indexOf(index) > -1,
            }"
          ></circle>
          <instalacion
            v-for="(city, index) in instalaciones"
            :key="`icons-${index}`"
            :x="city.x"
            :y="city.y"
          />
          <plane
            v-for="(connection, index) in connections"
            :key="`transport-${index}`"
            :x="connection.x1"
            :y="connection.y1"
            :xd="connection.x2"
            :yd="connection.y2"
            :fill="connectionColor(connection)"
          />
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
          <transition-group name="list" tag="g">
            <text
              v-for="(message, index) in messages"
              :key="`message-${index}`"
              :x="message.x"
              :y="message.y - 1"
              text-anchor="middle"
              stroke="black"
              stroke-width="0.5px"
              dy=".3em"
              font-size="3"
              opacity="0"
            >
              {{ message.message }}
            </text>
          </transition-group>
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
            <text
              x="64"
              y="49.3"
              text-anchor="middle"
              stroke="black"
              stroke-width="4px"
              font-size="30"
            >
              {{ player.name }}
            </text>
          </g>
          <!-- -->
          <pattern
            id="map_focus"
            patternUnits="userSpaceOnUse"
            :width="`${w}px`"
            :height="`${h}px`"
          >
            <image :href="world" :width="`${w}px`" :height="`${h}px`" />
          </pattern>
          <pattern
            id="map_focus_small"
            patternUnits="userSpaceOnUse"
            :width="`${w2}px`"
            :height="`${h2}px`"
          >
            <image :href="world" :width="`${w2}px`" :height="`${h2}px`" />
          </pattern>
          <pattern
            id="background"
            patternUnits="userSpaceOnUse"
            width="120px"
            height="80px"
          >
            <image :href="background_bio" width="120px" height="80px" />
          </pattern>
          <rect
            x="-1"
            y="140"
            width="322"
            height="27"
            stroke="#a2a23e"
            stroke-width="1"
            fill="url(#background)"
          />
          <circle
            v-if="selectedCity"
            :cx="posX * 0.003125 * w"
            :cy="posY * 0.00625 * h"
            :r="24"
            stroke="#a28626"
            stroke-width="2"
            fill="url(#map_focus)"
            :transform="`translate(${-(posX * 0.003125 * w - 26)}, ${-(
              posY * 0.00625 * h -
              136
            )})`"
          ></circle>
          <foreignObject :x="64" :y="142" width="280px" height="18px">
            <div class="d-flex flex-row">
              <div class="bg-text" v-if="selectedCity">
                Infection:
                <b>{{ Math.round(selectedCity.pivot.infection * 10) }}%</b>
              </div>
              <div class="bg-text">
                <b-button
                  v-for="(hab, index) in habilidades"
                  :key="`hab-${index}`"
                  v-show="handleCondition(hab)"
                  @click.stop="handleHabilidad(hab)"
                  :disabled="hab.handling || handleRunning(hab)"
                  :title="hab.text"
                >
                  <i
                    v-for="(icon, ii) in hab.icon"
                    :key="`icon-${index}-${ii}`"
                    :class="`${icon} ${ii > 0 ? 'over' : ''}`"
                  />
                </b-button>
              </div>
            </div>
          </foreignObject>
          <rect
            v-for="(city, idx) in ownedCities"
            :key="`user-city-${city.id}`"
            :x="(city.x - 4) * 0.003125 * w2"
            :y="(city.y - 4) * 0.00625 * h2"
            @click.stop="selectCity(city)"
            width="16"
            height="18"
            :stroke="city.color"
            stroke-width="1"
            fill="url(#map_focus_small)"
            :transform="`translate(
              ${-((city.x - 4) * 0.003125 * w2 - (302 - idx * 17))},
              ${-((city.y - 4) * 0.00625 * h2 - 136)})`"
            :class="{
              citySelected: city === selectedCity,
            }"
          ></rect>
        </svg>
      </div>
    </div>
    <div>
      <b-button v-if="editMode" @click="randomConns"
        >Random connections</b-button
      >
      <b-button @click="resetGame">Reiniciar juego</b-button>
    </div>
  </div>
</template>

<script>
import world from "../../images/world.jpg";
import background_bio from "../../images/biological_cells_pattern.jpg";
import habilidades from "../habilidades";
import plane from "../../images/plane-solid.svg";

const colors = ["pink", "yellow", "lightgreen", "cyan"];
const playerColors = ["white", "blue", "red", "green"];
const editMode = false;
const REFRESH_TIME = 3000;

function random(colors) {
  return colors[Math.floor(Math.random() * colors.length)];
}
const gameApiParams = {
  include: "game",
};

export default {
  path: "/",
  mixins: [window.ResourceMixin, ...habilidades],
  data() {
    return {
      chooseCityMode: {
        enabled: false,
        cities: [],
        source: null,
        resolve: null,
      },
      w: 3360 * 0.25,
      h: 1705 * 0.25,
      w2: 3360 * 0.2,
      h2: 1705 * 0.2,
      background_bio,
      cities: [],
      colors,
      editMode,
      game: this.$api.user[window.userId].row(gameApiParams),
      gameId: 0,
      habilidades: [],
      messages: [],
      observers: [],
      playerColors,
      players: [],
      posX: 0,
      posY: 0,
      selectedCity: null,
      world,
      ////
      plane,
    };
  },
  watch: {
    selectedCity() {
      if (this.selectedCity) {
        this.posX = this.selectedCity.x;
        this.posY = this.selectedCity.y;
      }
    },
    game: {
      deep: true,
      handler() {
        if (!this.game.relationships || !this.game.relationships.game) {
          return;
        }
        this.players.splice(0);
        this.messages.splice(0);
        this.gameId = this.game.attributes.game_id;
        this.game.relationships.game.attributes.cities.forEach((dbC, index) => {
          dbC.x = dbC.x * 1;
          dbC.y = dbC.y * 1;
          const city = this.cities[index];
          if (city) {
            const deltInf = dbC.pivot.infection - city.pivot.infection;
            Object.assign(city, dbC);
            // Add a message over the map
            if (deltInf > 0)
              this.messages.push({
                x: city.x,
                y: city.y,
                message: `+`,
              });
            else if (deltInf < 0)
              this.messages.push({
                x: city.x,
                y: city.y,
                message: `-`,
              });
          } else {
            this.cities.push(dbC);
          }
        });
        this.game.relationships.game.attributes.players.forEach((player) => {
          this.players.push(player);
        });
        if (!this.selectedCity) {
          this.selectedCity = this.cities.find(
            (c) => c.id == this.game.attributes.city_id
          );
        }
        this.observers.forEach((obs) => obs());
      },
    },
    "$root.user": {
      deep: true,
      handler() {
        this.game_id = this.$root.user.attributes.game_id;
      },
    },
  },
  computed: {
    cura() {
      return this.$game && this.$game.attributes && this.$game.attributes.propiedades.cura;
    },
    chooseConnections() {
      const connections = [];
      if (this.chooseCityMode.enabled && this.chooseCityMode.source) {
        this.chooseCityMode.cities.forEach((index) => {
          const city = this.chooseCityMode.source;
          const city2 = this.cities[index];
          connections.push({
            city1: city.id,
            city2: city2.id,
            x1: city.x,
            y1: city.y,
            x2: city2.x,
            y2: city2.y,
          });
        });
      }
      return connections;
    },
    ownedCities() {
      const cities = this.cities;
      return (
        (this.game.attributes &&
          cities.filter((city) => {
            return this.game.attributes.owned_cities.find(
              (id) => city.id == id
            );
          })) ||
        []
      );
    },
    currentCity() {
      return (
        this.game.attributes &&
        this.cities.find((c) => c.id == this.game.attributes.city_id)
      );
    },
    $game() {
      return this.game.relationships && this.game.relationships.game;
    },
    connections() {
      const connections = [];
      this.cities.forEach((city) => {
        if (this.canNotFly(city)) {
          return;
        }
        city.connections.forEach((conn) => {
          const city2 = this.cities[conn];
          if (this.canNotFly(city2)) {
            return;
          }
          connections.push({
            city1: city.id,
            city2: city2.id,
            x1: city.x,
            y1: city.y,
            x2: city2.x,
            y2: city2.y,
          });
        });
      });
      return connections;
    },
    totalInfection() {
      let sum = 0,
        total = 0;
      this.cities.forEach((city) => {
        total += 10;
        sum += city.pivot.infection * 1;
      });
      return Math.round((sum / total) * 100);
    },
  },
  methods: {
    wait(time) {
      if (!this.$game.attributes) return;
      let resolve,
        waitFor = this.$game.attributes.time * 1 + time;
      const promise = new Promise((res) => (resolve = res));
      this.observers.push(() => {
        if (this.$game.attributes.time * 1 >= waitFor) {
          resolve();
        }
      });
      return promise;
    },
    chooseCity(cities = null, source) {
      this.chooseCityMode.enabled = true;
      this.chooseCityMode.cities = cities;
      this.chooseCityMode.source = source;
      return new Promise((resolve) => {
        this.chooseCityMode.resolve = resolve;
      });
    },
    radius(city) {
      return 2 + city.points * 0.5;
    },
    handleRunning(hab) {
      return hab.running.apply(this);
    },
    handleCondition(hab) {
      return hab.condition.apply(this);
    },
    handleHabilidad(hab) {
      const result = hab.handler.apply(this);
      if (typeof result.then === "function") {
        hab.handling = true;
        result.then((res) => {
          hab.handling = false;
          return res;
        });
      }
      return result;
    },
    connectionColor(connection) {
      if (!this.game.relationships || !this.game.relationships.game) {
        return "rgba(255, 255, 255, 0.5)";
      }
      const t = this.game.relationships.game.attributes.transmissions.find(
        (t) => {
          return t.city1 == connection.city1 && t.city2 == connection.city2;
        }
      );
      return t ? "rgb(255, 100, 100)" : "rgb(255, 255, 255)";
    },
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
      this.$api.users.call(window.userId, "resetGame", {}).then((response) => {
        this.gameId = response.game_id;
        this.selectedCity = null;
        this.refreshMap();
      });
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
      this.clickCity(event);
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
    clickCity(event) {
      const pos = this.getPosition(event);
      let min, city;
      this.cities.forEach((c) => {
        const distance =
          (c.x - pos.x) * (c.x - pos.x) + (c.y - pos.y) * (c.y - pos.y);
        if (distance < 20 && (!city || distance < min)) {
          min = distance;
          city = c;
        }
      });
      if (!city) return;
      // Valida si esta en modo chooseCity
      this.selectCity(city);
    },
    selectCity(city) {
      const cityIndex = this.cities.indexOf(city);
      if (
        this.chooseCityMode.enabled &&
        this.chooseCityMode.cities.indexOf(cityIndex) === -1
      ) {
        return;
      }
      // Selecciona la city
      this.selectedCity = city;
      this.posX = city.x;
      this.posY = city.y;
      if (this.chooseCityMode.enabled && this.chooseCityMode.resolve) {
        this.chooseCityMode.resolve(city);
      }
      this.chooseCityMode.enabled = false;
    },
    refreshMap() {
      if (!this.gameId && this.$root.user.attributes) {
        this.gameId = this.$root.user.attributes.game_id;
      }
      if (this.gameId) {
        gameApiParams.time = new Date().getTime();
        this.$api.users.refresh(this.game, gameApiParams);
      }
    },
  },
  mounted() {
    window.Echo.channel(`PublicGame`).listen(".UpdateMap", (e) => {
      if (e.game_id == this.gameId) {
        this.refreshMap();
      }
    });
    setInterval(() => {
      this.refreshMap();
    }, REFRESH_TIME);
  },
};
</script>

<style>
.btn-secondary:focus {
  background-color: #142e3dda;
  border-color: #a28626;
  box-shadow: none !important;
}
.map-pan {
  position: relative;
  width: 320px;
  height: 128px;
  overflow: hidden;
  border: 4px double black;
  margin-bottom: 4px;
}
.base {
  position: relative;
  user-select: none;
  cursor: default;
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
  animation: dash 12s linear infinite reverse;
}
.scales * {
  animation: scales 1.2s ease-in-out infinite alternate;
}
.citySelected {
  stroke-dasharray: 2;
  animation: dash 12s linear infinite;
}
.message {
  animation: message 1s ease-in-out forwards;
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
@keyframes message {
  from {
    opacity: 1;
    transform: translate(0px, 0px);
  }
  to {
    opacity: 0;
    transform: translate(0px, -10px);
  }
}
.list-item {
  opacity: 0;
}
.list-enter-active,
.list-leave-active {
  animation: message 1s ease-in-out forwards;
}
.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
}
.hab-image {
  width: 128px;
}
.icons {
  font-size: 6px;
  opacity: 0.4;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.bg-text {
  background-color: #142e3dda;
  font-size: 4px;
  border-radius: 1px;
  color: white;
  padding: 0px 4px;
  margin-bottom: 1px;
  margin-right: 2px;
}
.bg-text button {
  background-color: #142e3dda;
  font-size: 4px;
  border-radius: 1px;
  border-color: #a28626;
  color: white;
  padding: 1px 2px;
  margin-bottom: 1px;
  margin-right: 1px;
  text-transform: none;
  position: relative;
}
.bg-text button .over {
  position: absolute;
  left: 0px;
  top: 2px;
  width: 100%;
  opacity: 0.7;
}
.bg-text button:disabled {
  background-color: #a28626;
  border-color: #a2a23e;
}
.canChooseCityEnabled {
  stroke-opacity: 0;
  fill-opacity: 0.4;
}
.canChooseCityCity {
  stroke-opacity: 1;
  fill-opacity: 0.7;
}
.canChooseCityNoConnection {
  stroke-opacity: 0.1;
}
.canChooseCityConnection {
  stroke-opacity: 1;
}
</style>
