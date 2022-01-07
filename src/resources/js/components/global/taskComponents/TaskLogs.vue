<template>
    <div>
        <h2 v-if="loadingLogs" class="text-gray-800 text-center text-xl font-medium tracking-wide animate-pulse">
            Loading... </h2>
        <log-card
            v-for="log in logs"
            :key="log.id"
            :log="log">
        </log-card>
    </div>
</template>

<script>
import LogCard from "./LogCard.vue";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    components: {
       LogCard
    },
    data() {
        return {
            loadingLogs: false,
            logs: []
        }
    },
    props: {
        taskId: Number,
    },
    mixins: [ajaxCalls],
    mounted() {
        this.getLogs();
    },
    created() {
        this.eventHub.$on("reload-logs", () => {
            this.getLogs();
        })
    },
    beforeDestroy() {
        this.eventHub.$off('reload-logs');
    },
    methods: {
        getLogs() {
            this.loadingLogs = true;
            this.asyncGetLogs(this.taskId).then((data) => {
                this.logs = data.data;
                this.loadingLogs = false;
            })
        }
    },

}
</script>

