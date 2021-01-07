import image from "../../images/volar.jpg";

export default {
    mounted() {
        this.habilidades.push({
            text: "Volar acá",
            image,
            handler() {
                this.$api.users.call(window.userId, "volarA", {
                    user: this.game.id,
                    city: this.selectedCity.id
                });
            },
            condition() {
                if (!this.selectedCity) return false;
                let can = false;
                this.selectedCity.connections.forEach(i => {
                    can =
                        can ||
                        this.cities[i].id == this.game.attributes.city_id;
                });
                return can;
            }
        });
    }
};
