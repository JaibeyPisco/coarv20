import { createApp } from 'vue';
import vSelect from 'vue-select';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import './style.css';
import { notificacion } from './utils/notificacion';
import 'vue-select/dist/vue-select.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Exponer notificacion globalmente
if (typeof window !== 'undefined') {
    (window as any).notificacion = notificacion;
}
app.component('v-select', vSelect);

app.mount('#app');
