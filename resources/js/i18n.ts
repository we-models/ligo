import {createI18n} from 'vue-i18n';
import es from '../lang/es.json';

const messages = {
    es: es
}
const locale = navigator.language.split('-')[0];
const instance = createI18n({
    locale: locale,
    fallbackLocale: 'es',
    legacy: false,
    messages
});
export default instance;
export const i18n = instance.global;
