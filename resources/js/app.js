/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');


import { createApp } from 'vue';
import { i18nVue, trans } from 'laravel-vue-i18n';
import { createPinia } from 'pinia'
import vSelect from 'vue-select';
import mitt from 'mitt';
import 'vue-select/dist/vue-select.css';

const pinia = createPinia()


function rgbToRgba(rgb, alpha) {
    return [...rgb, alpha];
}
 function interpolateRgb (color1, color2, ratio) {
    return color1.map((value, i) => Math.round(value + ratio * (color2[i] - color1[i])));
}
function hslToRgb (h, s, l) {
    let r, g, b;

    if (s === 0) {
        r = g = b = l;
    } else {
        const hueToRgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 3) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hueToRgb(p, q, h + 1 / 3);
        g = hueToRgb(p, q, h);
        b = hueToRgb(p, q, h - 1 / 3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}
function allColors (){
    let colors = [];
    const numColors = 222;

    const black = [0, 0, 0];
    const white = [255, 255, 255];
    const stepSize = 1 / (75 + 1);
    for (let i = 1; i <= 75; i++) {
        const gray = interpolateRgb(white, black, i * stepSize);
        colors.push(`rgb(${rgbToRgba(gray, 1).join(',')})`);
    }

    for (let i = 0; i < numColors; i++) {
        const hue = i / numColors;
        const saturation = 1;
        const lightness = 0.5;
        colors.push(`rgb(${hslToRgb(hue, saturation, lightness).join(',')})`);
    }
    colors = colors.filter((elem, index) => colors.indexOf(elem) === index);
    return colors;
}

window['all_colors'] = allColors();


/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);



/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const emitter = mitt();

let components = [
    'Alert',
    'ArrayJson',
    'Assign',
    'chat/Messenger',
    'chat/Conversation',
    'Crud',
    'Field',
    'file/File',
    'file/FileList',
    'file/FileUpload',
    'Form',
    'Global',
    'image/Image',
    'image/ImageList',
    'image/ImageUpload',
    'LanguageSelector',
    'List',
    'Logs',
    'ModalForm',
    'ModalComment',
    'NavBar',
    'ObjectSelector',
    'report/ObjectTypeFieldsFilter',
    'report/FieldFilter',
    'report/ObjectFilter',
    'report/ValueResult',
    'report/FilterResult',
    'Progress',
    'report/Report',
    'SystemConfig',
    'TextArea',
    'Map'
];

let app = createApp({});
app.config.globalProperties.emitter = emitter;

components.forEach((cmp)=>{
    let name = cmp.split('/').at(-1).split(/(?=[A-Z])/).map((n) => n.toLowerCase());
    app.component(`${name.join('-')}-component`, require(`./components/${cmp}Component.vue`).default);
});

app.component('vueSelect', vSelect)
    .use(i18nVue, { resolve: lang => import(`../../lang/${lang}.json`)})
    .use(pinia)
    .mount("#app");



