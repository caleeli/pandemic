import image from "../../images/cerrar_frontera.jpg";

export default {
    mounted() {
        this.habilidades.push({
            text: "Cerrar Fronteras",
            icon: ["fa fa-plane-slash"],
            image,
            handler() {
                this.$api.users.call(window.userId, "cerrarFronteras", {
                    city: this.selectedCity.id,
                    time: 14,
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
                    this.selectedCity.pivot.artifacts.cerrarFronteras >
                    this.$game.attributes.time
                );
            }
        });
    }
};
