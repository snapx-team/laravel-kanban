import Vue from "vue";
import App from "./App.vue";
import router from "./router";
import 'vue-select/dist/vue-select.css';
import "@fortawesome/fontawesome-free/js/all.js";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueQuill from "vue-quill";
import DatePicker from 'vue2-datepicker';
import VueMoment from 'vue-moment';



Vue.config.productionTip = false;

Vue.use(Toast, {
    transition: "Vue-Toastification__fade",
    maxToasts: 20,
    newestOnTop: true
});

Vue.use(VueQuill);
Vue.use(DatePicker);
Vue.use(VueMoment);




new Vue({
    router,
    render: (h) => h(App)
}).$mount("#app");


