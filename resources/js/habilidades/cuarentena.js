export default {
    mounted() {
        this.habilidades.push({
            text: "Cuarentena 14 dias",
            icon: ["fas fa-biohazard"],
            handler() {
                this.$api.users.call(window.userId, "cuarentena", {
                    city: this.selectedCity.id,
                    time: 14
                });
            },
            condition() {
                return (
                    this.selectedCity &&
                    this.selectedCity.id == this.game.attributes.city_id
                );
            },
            running() {
                return (
                    this.selectedCity &&
                    this.selectedCity.pivot.artifacts.cuarentena >
                        this.$game.attributes.time
                );
            }
        });
    }
};
