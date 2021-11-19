<template>
    <div>
        <div class="flex flex-wrap p-4 pl-10 items-center">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-4">Notification</h3>
            <a @click="updateNotificationSettings()"
               class="px-2 text-gray-500 hover:text-gray-400 transition duration-300 ease-in-out focus:outline-none cursor-pointer">
                <i class="fas fa-2x fa-cog"></i>
            </a>
        </div>


        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>

        <div class="mx-10 my-10 space-y-5 shadow-xl p-5 bg-white">
            <div class="flex">
                <vSelect
                    v-model="logType"
                    :options="computedNotificationListData"
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
        <div v-if="!noMoreItems"
             class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            <button @click="getNotificationData()"
                    class=" text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded">
                <span v-if="!isLoadingMore">Show more</span>
                <span v-else class="animate-pulse">Loading...</span>
            </button>
        </div>

        <notification-settings-modal :notificationTypeList="notificationTypeList"></notification-settings-modal>

    </div>
</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import BacklogBar from "../backlog/backlogComponents/BacklogBar.vue";
import LogCard from "../kanban/kanbanComponents/taskComponents/LogCard.vue";
import NotificationSettingsModal from "./notificationsComponents/NotificationSettingsModal";
import vSelect from "vue-select";

export default {
    inject: ["eventHub"],
    components: {
        BacklogBar,
        LogCard,
        vSelect,
        NotificationSettingsModal
    },

    mixins: [ajaxCalls],

    data() {
        return {
            notifList: [],
            pageNumber: 1,
            isLoadingMore: false,
            noMoreItems: false,
            logType: null,
            notificationTypeList: [
                {
                    name: 'task status changes',
                    group: 'TASK_STATUS_GROUP',
                    data: [
                        {name: 'Task Status Set To Cancelled', code: '11'},
                        {name: 'Task Status Set To Completed', code: '12'},
                        {name: 'Task Status Set To Active', code: '13'},
                    ]
                },
                {
                    name: 'task card movement between boards, rows or columns',
                    group: 'TASK_MOVEMENT_GROUP',
                    data: [
                        {name: 'Task Moved', code: '14'},
                        {name: 'Task Placed', code: '15'},
                    ]
                },
                {
                    name: 'task information update',
                    group: 'TASK_INFORMATION_UPDATE_GROUP',
                    data: [
                        {name: 'Task Updated', code: '16'},
                        {name: 'Task Added To Group', code: '17'},
                    ]
                },
                {
                    name: 'task checklist items checked/unchecked',
                    group: 'TASK_CHECKLIST_GROUP',
                    data: [
                        {name: 'Task Checklist Item Checked', code: '20'},
                        {name: 'Task Checklist Item Unchecked', code: '21'},
                    ]
                },
                {
                    name: 'assigned/unassigned to a task',
                    group: 'TASK_ASSIGNATION_TO_USER_GROUP',
                    data: [
                        {name: 'Task Assigned To Employee', code: '22'},
                        {name: 'Task Unassigned To Employee', code: '23'},
                    ]
                },
                {
                    name: 'added/removed from board',
                    group: 'BOARD_MEMBER_GROUP',
                    data: [
                        {name: 'Added To Board', code: '40'},
                        {name: 'Removed From Board', code: '41'},
                    ]
                },
                {
                    name: 'comments on tasks',
                    group: 'COMMENT_GROUP',
                    data: [
                        {name: 'Comment Made On Task', code: '70'},
                        {name: 'Comment Deleted From Task', code: '71'},
                        {name: 'Comment Edited On Task', code: '72'},
                    ]
                },
                {
                    name: '@ mentions on task',
                    group: 'MENTIONS_GROUP',
                    data: [
                        {name: 'Comment Mention', code: '73'},

                    ]
                },
            ]
        };
    },

    computed: {
        computedNotificationListData() {
            return this.notificationTypeList.map(o => o.data).flat();
        },
    },

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
                if (data.data.length < 10) {
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

        updateNotificationSettings() {
            this.eventHub.$emit("update-notification-settings");
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
