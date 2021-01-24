export default {
    methods: {},
    mounted() {
        this.habilidades.push({
            text: "Puente Aereo",
            icon: ["fas fa-plane", "fas fa-hospital"],
            handler() {
                const cities = this.instalaciones.map(c =>
                    this.cities.indexOf(c)
                );
                this.chooseCity(cities, this.currentCity).then(selectedCity => {
                    this.$api.users.call(window.userId, "volarA", {
                        user: this.game.id,
                        city: selectedCity.id
                    });
                });
            },
            condition() {
                return this.currentCity.pivot.artifacts.instalacion;
            },
            running() {}
        });
    }
};
