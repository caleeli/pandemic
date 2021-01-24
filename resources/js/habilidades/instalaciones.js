export default {
    computed: {
        instalaciones() {
            return this.cities.filter(city => city.pivot.artifacts.instalacion);
        }
    },
    mounted() {
        this.habilidades.push({
            text: "Construir instalación",
            icon: ["fas fa-hospital-alt"],
            handler() {
                this.$api.users.call(window.userId, "construirInstalacion", {
                    user: this.game.id,
                    city: this.currentCity.id,
                    cobrar: this.currentCity.id
                });
            },
            condition() {
                return this.ownedCities.indexOf(this.currentCity) > -1;
            },
            running() {}
        });
    }
};
