export default {
    methods: {},
    mounted() {
        this.habilidades.push({
            text: "Vuelo directo a",
            icon: ["fas fa-plane"],
            handler() {
                const cities = this.currentCity.connections.filter(i => {
                    return !this.canNotFly(this.cities[i]);
                });
                this.chooseCity(cities).then(selectedCity => {
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
