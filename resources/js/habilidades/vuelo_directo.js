export default {
    methods: {},
    mounted() {
        this.habilidades.push({
            text: "Vuelo directo a",
            icon: ["fas fa-plane"],
            handler() {
                const cities = this.ownedCities.map(c =>
                    this.cities.indexOf(c)
                );
                this.chooseCity(cities, this.currentCity).then(selectedCity => {
                    this.$api.users.call(window.userId, "volarA", {
                        user: this.game.id,
                        city: selectedCity.id,
                        cobrar: selectedCity.id,
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
