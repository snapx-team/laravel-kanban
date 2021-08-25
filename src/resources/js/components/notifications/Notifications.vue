<template>
    <div>
        <backlog-bar :name="'Notifications'"></backlog-bar>
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>
        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <log-card
                v-for="log in notifList"
                :key="log.id"
                :log="log">
            </log-card>
        </div>
    </div>
</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue";
import LogCard from "../kanban/kanbanComponents/taskComponents/LogCard.vue";

export default {
    inject: ["eventHub"],
    components: {
        BacklogBar,
        LogCard
    },

    data() {
        return {
            notifList: [],
        };
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getNotificationData();
        this.updateNotificationCount();
    },

    methods: {
        getNotificationData() {
            this.asyncGetNotificationData().then((data) => {
                this.notifList = data.data;
                console.log(data.data);
            }).catch(res => {
                console.log(res)
            });
        },
        updateNotificationCount() {
            this.asyncUpdateNotificationCount().then((data) => {
                this.eventHub.$emit("update-notif-count");
            }).catch(res => {
                console.log(res)
            });
        }
    }
}
</script>