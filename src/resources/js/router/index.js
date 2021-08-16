import Vue from "vue";
import Router from "vue-router";
import Kanban from "../components/kanban/Kanban";
import Dashboard from "../components/dashboard/Dashboard";
import Notifications from "../components/notifications/Notifications";
import Backlog from "../components/backlog/Backlog";
import Metrics from "../components/metrics/Metrics";

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
            path: "/kanban/notifications",
            component: Notifications
        },
        {
            path: "/kanban/board",
            component: Kanban,
            props: (route) => ({id: Number(route.query.id)})
        },
        {
            path: "/kanban/backlog",
            component: Backlog
        },
        {
            path: "/kanban/metrics",
            component: Metrics
        },
        {
            path: '*',
            redirect: "kanban/dashboard"
        }
    ]
});
