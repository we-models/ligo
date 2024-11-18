/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import './bootstrap';
import {createApp, defineAsyncComponent} from 'vue';
import i18n from '@/i18n';
import {createPinia} from 'pinia'
import mitt from 'mitt';

import './../css/panel.css'
import './../css/app.css'

const pinia = createPinia();
const emitter = mitt();


/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.


 const app = createApp({});

 import ExampleComponent from './components/ExampleComponent.vue';
 app.component('example-component', ExampleComponent);
 */
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

//app.mount('#app');

let app = createApp({});

app.config.globalProperties.emitter = emitter;

const views: Array<string> = [
    'Home',
    'Global',
    'LoginInput',
    'Profile'
];

function getName(name: string) {
    const response: Array<string> | undefined = name.split('/').at(-1)?.split(/(?=[A-Z])/).map((n: string) => n.toLowerCase());
    return (response == undefined) ? [] : response;
}

views.forEach((item: string) => {
    const component = defineAsyncComponent(() => import(`./views/${item}/${item}View.vue`));
    app.component(`${getName(item).join('-')}-view`, component);
});

app.component(`progress-component`, defineAsyncComponent(() => import(`./components/Global/Progress/ProgressComponent.vue`)));
app.component(`media-file-component`, defineAsyncComponent(() => import(`./components/Global/MediaFile/MediaFile/MediaFileComponent.vue`)));
app.component(`list-component`, defineAsyncComponent(() => import(`./components/Global/List/ListComponent.vue`)));
app.component(`assign-component`, defineAsyncComponent(() => import(`./components/Global/Assign/AssignComponent.vue`)));
app.component(`system-config-component`, defineAsyncComponent(() => import(`./components/Global/SystemConfig/SystemConfigComponent.vue`)));
app.component(`form-component`, defineAsyncComponent(() => import(`./components/Global/Form/FormComponent.vue`)));
app.component(`alert-component`, defineAsyncComponent(() => import(`./components/Global/Alert/AlertComponent.vue`)));
app.component(`nav-bar-component`, defineAsyncComponent(() => import(`./components/Global/NavBar/NavBarComponent.vue`)));
app.component(`footer-component`, defineAsyncComponent(() => import(`./components/Global/Footer/FooterComponent.vue`)));


app.use(i18n as any)

app.use(pinia).mount("#app");

/* I get global variables */
import { useGlobalStore} from '@/stores/globalStore';

const globalStore = useGlobalStore();


globalStore.updateAppDebugFromWindow();
