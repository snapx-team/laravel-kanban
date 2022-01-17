<template>
    <div>
        <log-card
            v-for="log in logs"
            :key="log.id"
            :log="log">
        </log-card>

        <div v-if="!noMoreItems"
             class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            <button @click="getLogs()"
                    class=" text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                <span v-if="!isLoadingMore">Show more</span>
                <span v-else class="animate-pulse">Loading...</span>
            </button>
        </div>
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
            isLoadingMore: false,
            logs: [],
            pageNumber: 1,
            noMoreItems: false,
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
            this.isLoadingMore = true;
            this.asyncGetLogs(this.pageNumber, this.taskId).then((data) => {
                this.logs = this.logs.concat(data.data.data);
                if (data.data.data.length < 10) {
                    this.noMoreItems = true;
                }
                this.pageNumber++;
                this.isLoadingMore = false;
            })
        }
    },
}
</script>

