/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import { Form, HasError, AlertError } from 'vform';
import moment from 'moment';
import VueProgressBar from 'vue-progressbar';
import Swal from 'sweetalert2';

//Import frontend auth
import Gate from './Gate';
Vue.prototype.$Gate = new Gate(window.user);

//sweetalert setup
window.Swal = Swal;
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});
window.Toast = Toast;

//new instance of Vue to create custom events
window.Fire = new Vue();

//form errors setup
window.Form = Form;
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);

//progressbar setup
const options = {
  color: '#bffaf3',
  failedColor: '#874b4b',
  thickness: '5px',
  transition: {
    speed: '0.2s',
    opacity: '0.6s',
    termination: 300
  },
  autoRevert: true,
  location: 'top',
  inverse: false
}
Vue.use(VueProgressBar, options);

Vue.use(VueRouter);
//Vue.prototype.$Router = new VueRouter();

//set up routes
const routes = [
  { path: '/dashboard', component: require('./components/Dashboard.vue').default },
  { path: '/profile', component: require('./components/Profile.vue').default },
  { path: '/users', component: require('./components/Users.vue').default },
  { path: '/developer', component: require('./components/Developer.vue').default },
  { path: '/invoice', component: require('./components/Invoice.vue').default },
  { path: '*', component: require('./components/404.vue').default }
];

Vue.filter('uptext', (text) => {
  return text.charAt(0).toUpperCase() + text.slice(1);
});
Vue.filter('formatDate', (date) => {
  return moment(date).format('D / MM / YYYY');
});

const router = new VueRouter({
  routes,
  mode: 'history'
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

//Not found component
Vue.component(
    'not-found',
    require('./components/404.vue').default
);

Vue.component('example-component', require('./components/ExampleComponent.vue').default);

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

//Pagination component
Vue.component('pagination', require('laravel-vue-pagination'));

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router,
    data: {
      search: ''
    },
    methods: {
      searchIt: _.debounce(() => {
        Fire.$emit('searching');
      }, 800),

      // printIt() {
      //   window.print();
      // }
    }
    //render: (h) => h(App)
});
