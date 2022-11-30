<template>
    <div class="border-gray-300 border-2">
        <div class="flex justify-between p-2 bg-indigo-800 border-b">
            <h1 class="text-white">Task: <span class="font-medium">{{ task.task_simple_name }}</span>
                <span @click="copyToClipboard()"
                      title="Copy To Clipoboard"
                      class="cursor-pointer px-2 text-gray-400 hover:text-gray-200 transition duration-300 ease-in-out focus:outline-none">
                    <i class="fas fa-link"></i>
                </span>
            </h1>
            <div>
                <button @click="closeTaskView()"
                        class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                        type="button">
                    <i class="fas fa-times"></i>
                    <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                </button>
            </div>
        </div>

        <!-- start of container -->

        <div class="flex flex-wrap">
            <div class="w-full">

                <!-- Tabs -->
                <ul class="flex mb-0 list-none flex-wrap py-4 flex-row">
                    <li class="m-1 flex-1 text-center">
                        <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                           v-on:click="toggleTabs(1)"
                           v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 1, 'text-white bg-gray-500': openTab === 1}">
                            <i class="fas fa-tasks text-base mr-1"></i>
                        </a>
                    </li>
                    <li class="m-1 flex-1 text-center">
                        <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                           v-on:click="toggleTabs(2)"
                           v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 2, 'text-white bg-gray-500': openTab === 2}">
                            <i class="fas fa-edit text-base mr-1"></i>
                        </a>
                    </li>
                    <li class="m-1 flex-1 text-center">
                        <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                           v-on:click="toggleTabs(3)"
                           v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 3, 'text-white bg-gray-500': openTab === 3}">
                            <i class="fas fa-comments text-base mr-1"></i>
                        </a>
                    </li>
                    <li class="m-1 flex-1 text-center">
                        <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                           v-on:click="toggleTabs(4)"
                           v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 4, 'text-white bg-gray-500': openTab === 4}">
                            <i class="fas fa-network-wired text-base mr-1"></i>
                        </a>
                    </li>
                    <li class="m-1 flex-1 text-center">
                        <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                           v-on:click="toggleTabs(5)"
                           v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 5, 'text-white bg-gray-500': openTab === 5}">
                            <i class="fas fa-history text-base mr-1"></i>
                        </a>
                    </li>
                </ul>
                <div
                    class="relative flex flex-col min-w-0 break-words w-full mb-6 ">
                    <div>
                        <div class="tab-content tab-space">
                            <div v-bind:class="{'hidden': openTab !== 1, 'block': openTab === 1}">
                                <!-- Place Task -->
                                <div>
                                    <div class="flex justify-between p-2 bg-gray-600 border-b">
                                        <h1 class="text-white">Place task in board, row, and column</h1>
                                    </div>
                                    <place-task class="px-4 py-5"
                                                v-if="task.id"
                                                :key="getUniqueId()"
                                                :task="task"></place-task>
                                </div>
                                <div>
                                    <div class="flex justify-between p-2 bg-gray-600 border-b">
                                        <h1 class="text-white">Task Info</h1>
                                    </div>
                                    <view-task-data class="px-4 py-5"
                                                    v-if="task.id"
                                                    :key="getUniqueId()"
                                                    :sourceFrom="source"
                                                    :isVerticalMode=true
                                                    :isCommentsVisible=false
                                                    :isRelatedTasksVisible=false
                                                    :cardData="task"></view-task-data>
                                </div>
                            </div>
                            <div v-bind:class="{'hidden': openTab !== 2, 'block': openTab === 2}">
                                <div class="flex justify-between p-2 bg-gray-600 border-b">
                                    <h1 class="text-white">Edit Task</h1>
                                </div>
                                <add-task-data v-if="task.id"
                                               :key="getUniqueId()"
                                               :isVerticalMode=true
                                               :sourceFrom="source"
                                               :cardData="task"></add-task-data>
                            </div>
                            <div v-bind:class="{'hidden': openTab !== 3, 'block': openTab === 3}">
                                <div class="flex justify-between p-2 bg-gray-600 border-b">
                                    <h1 class="text-white">Task Comments</h1>
                                </div>
                                <task-comments class="px-4 py-5"
                                               v-if="task.id"
                                               :key="getUniqueId()"
                                               :cardData="task"></task-comments>
                            </div>
                            <div v-bind:class="{'hidden': openTab !== 4, 'block': openTab === 4}">
                                <div class="flex justify-between p-2 bg-gray-600 border-b">
                                    <h1 class="text-white">Related Tasks</h1>
                                </div>
                                <related-tasks class="px-4 py-5"
                                               v-if="task.id"
                                               :key="getUniqueId()"
                                               :appendSelectToBody=true
                                               :sourceFrom="source"
                                               :cardData="task"></related-tasks>
                            </div>
                            <div v-bind:class="{'hidden': openTab !== 5, 'block': openTab === 5}">
                                <div class="flex justify-between p-2 bg-gray-600 border-b">
                                    <h1 class="text-white">Task Logs</h1>
                                </div>
                                <task-logs class="px-4 py-5"
                                           v-if="task.id"
                                           :key="getUniqueId()"
                                           :taskId="task.id"></task-logs>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import addTaskData from "../../global/taskComponents/AddTaskData";
import taskComments from "../../global/taskComponents/TaskComments";
import taskLogs from "../../global/taskComponents/TaskLogs"
import viewTaskData from "../../global/taskComponents/viewTaskData";
import RelatedTasks from "../../global/taskComponents/RelatedTasks";
import PlaceTask from "../../global/taskComponents/PlaceTask";
import {componentNames} from "../../../enums/componentNames";

export default {
    inject: ["eventHub"],
    components: {
        PlaceTask,
        RelatedTasks,
        addTaskData,
        taskComments,
        taskLogs,
        viewTaskData
    },
    mixins: [helperFunctions, ajaxCalls],
    data() {
        return {
            source: componentNames.TaskPane,
            openTab: 1,
            task: {
                id: null,
                board: {
                    name: '',
                },
                shared_task_data: {
                    description: null
                },
                status: null,
            },
            rows: [],
            columns: [],
        }
    },

    created() {
        this.eventHub.$on("populate-task-view", (task) => {
            this.populateTaskView(task);

        });
        this.eventHub.$on("fetch-and-replace-task-pane-data", () => {
            this.fetchAndReplaceTaskPaneData();
        });

        this.eventHub.$on("update-task-card-data-from-task-pane", (task) => {
            this.updateTaskCard(task);
        });
    },

    beforeDestroy() {
        this.eventHub.$off('populate-task-view');
        this.eventHub.$off('fetch-and-replace-task-pane-data');
        this.eventHub.$off('update-task-card-data-from-task-pane');
    },

    methods: {
        closeTaskView() {
            this.eventHub.$emit("close-task-view");
        },
        populateTaskView(task) {
            this.openTab = 1;
            window.scrollTo({top: 0, behavior: 'smooth'});
            Object.assign(this.task, {...task});
            if (task.deadline) {
                this.task.deadline = new Date(task.deadline);
            }
        },
        updateTaskCard(task) {
            this.asyncUpdateTask(task).then(() => {
                this.fetchAndReplaceTaskPaneData();
            })
        },
        toggleTabs: function (tabNumber) {
            this.openTab = tabNumber
        },
        fetchAndReplaceTaskPaneData() {
            this.openTab = 1;
            window.scrollTo({top: 0, behavior: 'smooth'});
            this.eventHub.$emit("reload-backlog-data");
            this.asyncGetTaskData(this.task.id).then((data) => {
                Object.assign(this.task, {...data.data});
                this.$forceUpdate();
            })
        },

        getUniqueId(){
            return this.task.id+'.'+this.task.board_id+'.'+this.task.row_id+'.'+this.task.row_id+'.'+this.task.status;
        },
        copyToClipboard() {
            let temp = document.createElement('input'),
                text = window.location.href;

            document.body.appendChild(temp);
            temp.value = text;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);

            this.triggerSuccessToast('Link copied to clipboard!');
        }
    },
};
</script>
