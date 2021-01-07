import image from "../../images/cerrar_frontera.jpg";

export default {
    mounted() {
        this.habilidades.push({
            text: "Cerrar Fronteras",
            image,
            handler() {
                this.$api.users.call(window.userId, "cerrarFronteras", {
                    city: this.selectedCity.id
                });
            },
            condition() {
                return this.selectedCity && this.selectedCity.id == this.game.attributes.city_id;
            }
        });
    }
};