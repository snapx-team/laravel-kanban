<template>
    <div>
        <backlog-bar :name="'Notifications'"></backlog-bar>

        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>

        <div class="mx-10 my-10 space-y-5 shadow-xl p-5 bg-white">
            <div class="flex">
                <vSelect
                    v-model="logType"
                    :options="logTypeList"
                    label="name"
                    placeholder="Filter By Type"
                    class="w-72 flex-grow text-gray-400">
                    <template slot="option" slot-scope="option">
                        <p class="inline">{{ option.name }}</p>
                    </template>
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>

                <button @click="filterLogs()"
                        class="px-4 mt-2 ml-4 h-12 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                        type="button">
                    <span>Filter</span>
                </button>
            </div>

            <log-card
                v-for="log in notifList"
                :key="log.id"
                :log="log">
            </log-card>

        </div>
        <div v-if="!noMoreItems" class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            <button @click="getNotificationData()"
                    class=" text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                <span v-if="!isLoadingMore">Show more</span>
                <span v-else class="animate-pulse">Loading...</span>
            </button>
        </div>
    </div>
</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue";
import LogCard from "../kanban/kanbanComponents/taskComponents/LogCard.vue";
import vSelect from "vue-select";

export default {
    inject: ["eventHub"],
    components: {
        BacklogBar,
        LogCard,
        vSelect,
    },

    data() {
        return {
            notifList: [],
            pageNumber: 1,
            isLoadingMore: false,
            noMoreItems: false,
            logType: null,
            logTypeList: [
                {name: 'EMPLOYEE CREATED', code: '1'},
                {name: 'EMPLOYEE UPDATED', code: '2'},
                {name: 'EMPLOYEE DELETED', code: '3'},
                {name: 'TASK CARD CREATED', code: '10'},
                {name: 'TASK CARD CANCELED', code: '11'},
                {name: 'TASK CARD COMPLETED', code: '12'},
                {name: 'TASK CARD MOVED', code: '14'},
                {name: 'TASK CARD ASSIGNED_TO_BOARD', code: '15'},
                {name: 'TASK CARD UPDATED', code: '16'},
                {name: 'TASK CARD ASSIGNED_GROUP', code: '17'},
                {name: 'TASK CARD CHANGED_INDEX', code: '18'},
                {name: 'TASK CARD CHECKLIST_ITEM_CHECKED', code: '20'},
                {name: 'TASK CARD CHECKLIST_ITEM_UNCHECKED', code: '21'},
                {name: 'TASK CARD ASSIGNED_TO_USER', code: '22'},
                {name: 'TASK CARD UNASSIGNED_TO_USER', code: '23'},
                {name: 'KANBAN MEMBER CREATED', code: '40'},
                {name: 'KANBAN MEMBER DELETED', code: '41'},
                {name: 'BOARD CREATED', code: '60'},
                {name: 'BOARD DELETED', code: '61'},
                {name: 'COMMENT CREATED', code: '70'},
                {name: 'COMMENT DELETED', code: '71'},
                {name: 'COMMENT EDITED', code: '72'},
                {name: 'ROW CREATED', code: '80'},
                {name: 'ROW DELETED', code: '81'},
                {name: 'ROW UPDATED', code: '82'},
                {name: 'COLUMN CREATED', code: '83'},
                {name: 'COLUMN DELETED', code: '84'},
                {name: 'COLUMN UPDATED', code: '85'},
                {name: 'BADGE CREATED', code: '90'},
            ]
        };
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getNotificationData();
        this.updateNotificationCount();
    },

    methods: {
        getNotificationData() {
            this.isLoadingMore = true;
            let code = this.logType ? this.logType.code : null;
            this.asyncGetNotificationData(this.pageNumber, code).then((data) => {
                this.notifList = this.notifList.concat(data.data);
                if(data.data.length < 10) {
                    this.noMoreItems = true;
                }
                this.pageNumber++;
                this.isLoadingMore = false;
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
        },
        filterLogs() {
            this.notifList = [];
            this.pageNumber = 1,
            this.noMoreItems = false;
            this.getNotificationData();
        }
    }
}
</script>
