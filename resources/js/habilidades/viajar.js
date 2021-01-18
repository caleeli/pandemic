export default {
    methods: {
        canNotFly(city) {
            return (
                city.pivot.artifacts.cerrarFronteras >
                this.$game.attributes.time
            );
        }
    },
    mounted() {
        this.habilidades.push({
            text: "Volar a",
            icon: ["fas fa-plane-departure"],
            handler() {
                const cities = this.currentCity.connections.filter(i => {
                    return !this.canNotFly(this.cities[i]);
                });
                this.chooseCity(cities, this.currentCity).then(selectedCity => {
                    this.$api.users.call(window.userId, "volarA", {
                        user: this.game.id,
                        city: selectedCity.id
                    });
                });
            },
            condition() {
                return (
                    this.selectedCity &&
                    this.selectedCity === this.currentCity &&
                    !this.canNotFly(this.currentCity)
                );
            },
            running() {}
        });
    }
};
