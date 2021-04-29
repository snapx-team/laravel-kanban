import Vue from "vue";
import Router from "vue-router";
import Kanban from "../components/kanban/Kanban";
import Dashboard from "../components/dashboard/Dashboard";

Vue.use(Router);

export default new Router({
    mode: "history",

    routes: [
        {
            path: "/kanban/",
            redirect: "kanban/dashboard"
        },
        {
            path: "/kanban/dashboard",
            component: Dashboard
        },
        {
            path: "/kanban/phoneline",
            component: Kanban,
            props: (route) => ({id: Number(route.query.id)})
        },
        {
            path: '*',
            redirect: "kanban/dashboard"
        }
    ]
});
