import { loadLanguage } from "./Functions/Language";

require('./bootstrap');

// Import modules...
import Vue from 'vue';
import VueTailwind from 'vue-tailwind'
import VueClipboard from 'vue-clipboard2'
import VueToast from 'vue-toast-notification'
import VueI18n from 'vue-i18n'
import HeaderCaption from '@/Components/HeaderCaption'
import { createInertiaApp } from '@inertiajs/vue2'

import PortalVue from 'portal-vue';
import VueFileAgent from 'vue-file-agent';
import VueFileAgentStyles from 'vue-file-agent/dist/vue-file-agent.css';
import VueCookies from 'vue-cookies'
import vuescroll from 'vuescroll';
import { Link, Head } from '@inertiajs/vue2'

import LoadScript from 'vue-plugin-load-script';
import {vueComponentSettings} from "./components";
import PopperVue from '@soldeplata/popper-vue';
import ProgressBar from 'vue-simple-progress'


require('./icons');

// Register components

// Ziggy start here
import route from 'ziggy';
import { Ziggy } from './ziggy_alt';
import moment from 'moment-mini-ts'

Vue.mixin({
    methods: {
        route: (name, params, absolute, config = Ziggy) => route(name, params, absolute, config),
    },
});
// ziggy end here

Vue.use(PortalVue);
Vue.use(VueToast);
Vue.use(VueFileAgent);
Vue.use(VueCookies);
Vue.use(VueClipboard);
Vue.use(VueI18n);
Vue.use(LoadScript);
Vue.use(PopperVue);
Vue.component('progress-bar', ProgressBar)
Vue.component('header-caption', HeaderCaption)
Vue.component('Link', Link)
Vue.component('Head', Head)

Vue.use(vuescroll, {
    ops: {
        vuescroll: {},
        scrollPanel: {},
        rail: {
            'opacity': 1,
        },
        bar: {
            'keepShow': true,
            'opacity': 0.2
        }
    },
});

Vue.use(VueTailwind, vueComponentSettings)

const app = document.getElementById('app');

import store from './Store'

import MathMixin from '@/Mixins/Math/MathMixin';

let defaultLanguage = document.querySelector('meta[name="site-language"]').getAttribute('content');
let messages = [];

messages[defaultLanguage] = JSON.parse(window.TRANSLATIONS);

let i18n = new VueI18n({
    locale: defaultLanguage,
    silentTranslationWarn: true
})

const VueWorker = new Vue();

Object.defineProperties(Vue.prototype, {
    $worker: { get: () => { return VueWorker } }
})

window.moment = moment;
window.numeral = require('numeral');

Vue.prototype.$profileMenuOpened = false;

const App = createInertiaApp({
    title: title => `${title}`,
    resolve: name => require(`./Pages/${name}`).default,
    setup({ el, App, props, plugin }) {
        Vue.use(plugin)

        window.Vue = new Vue({
            i18n: i18n,
            mixins: [MathMixin],
            store,
            render: h => h(App, props),
        }).$mount(el)
    },
});
