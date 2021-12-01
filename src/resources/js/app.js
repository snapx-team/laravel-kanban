import Vue from 'vue';
import App from './App.vue';
import router from './router';
import 'vue-select/dist/vue-select.css';
import '@fortawesome/fontawesome-free/js/all.js';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import DatePicker from 'vue2-datepicker';
import VueMoment from 'vue-moment';
import moment from 'moment-timezone';
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import VueQuillEditor from 'vue-quill-editor';
import 'quill-mention';
import 'quill-mention/dist/quill.mention.min.css';
import 'quill/dist/quill.core.css'; // import styles
import 'quill/dist/quill.snow.css'; // for snow theme
import 'quill/dist/quill.bubble.css';// for bubble theme
import axios from 'axios';
import device from 'vue-device-detector';
import VueObserveVisibility from 'vue-observe-visibility';
import * as VueMenu from '@hscmap/vue-menu'

Vue.config.productionTip = false;

Vue.use(Toast, {
    transition: 'Vue-Toastification__fade',
    maxToasts: 20,
    newestOnTop: true
});
Vue.use(VueQuillEditor, /* { default global options } */);
Vue.use(VueSweetalert2);
Vue.use(DatePicker);
Vue.use(VueMoment, {
    moment,
})
Vue.use(device);
Vue.use(VueObserveVisibility);
Vue.use(VueMenu);

let globalData = new Vue({
    data: {$role: 'employee'}
});
Vue.mixin({
    computed: {
        $role: {
            get: function () {
                return globalData.$data.$role;
            },
            set: function (newRole) {
                globalData.$data.$role = newRole;
            }
        },

        $employeeIdSession: {
            get: function () {
                return globalData.$data.$employeeId;
            },
            set: function (newEmployeeId) {
                globalData.$data.$employeeId = newEmployeeId;
            }
        }
    }
});
new Vue({
    router,
    mounted() {
        axios.get('get-role-and-employee-id').then((data) => {

            this.$role = data.data['role'];
            this.$employeeIdSession = data.data['employee_id'];

        }).catch(res => {
            console.log(res);
        });
    },
    render: (h) => h(App)
}).$mount('#app');


