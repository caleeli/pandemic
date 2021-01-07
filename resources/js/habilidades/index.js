const habilidades = [];
const modules = require.context('./', true, /\.js$/i);
modules.keys().map(key => {
    const component = modules(key).default;
    const name = key.split('/').pop().split('.')[0];
    if (name!=='index') {
        habilidades.push(component);
    }
});

export default habilidades;
