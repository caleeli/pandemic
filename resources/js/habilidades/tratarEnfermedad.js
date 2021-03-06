export default {
    mounted() {
        this.habilidades.push({
            text: "Tratar enfermedad",
            icon: ["fas fa-medkit"],
            handler() {
                return this.$api.users.call(window.userId, "tratarEnfermedad", {
                    city: this.currentCity.id,
                    cantidad: 1,
                    time: 1
                });
            },
            condition() {
                return (
                    this.currentCity &&
                    this.currentCity === this.selectedCity &&
                    this.currentCity.pivot.infection > 0
                );
            },
            running() {
                return (
                    this.currentCity &&
                    this.currentCity.pivot.artifacts.tratamiento >
                        this.$game.attributes.time
                );
            }
        });
    }
};
