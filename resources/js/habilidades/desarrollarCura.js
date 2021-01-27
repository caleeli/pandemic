export default {
    mounted() {
        this.habilidades.push({
            text: "Desarrollar cura",
            icon: ["fas fa-shield-virus"],
            handler() {
                return this.$api.users.call(
                    window.userId,
                    "desarrollarCura",
                    {}
                );
            },
            condition() {
                let condition = false;
                const byColor = {};
                this.ownedCities.forEach(city => {
                    if (!byColor[city.color]) {
                        byColor[city.color] = [];
                    }
                    byColor[city.color].push(city);
                    if (byColor[city.color].length >= 5) {
                        condition = true;
                    }
                });
                return condition;
            },
            running() {}
        });
    }
};
