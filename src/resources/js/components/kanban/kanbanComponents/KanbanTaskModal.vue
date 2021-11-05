<template>
    <div>
        <transition enter-active-class="transition duration-500 ease-out transform"
                    enter-class=" opacity-0 bg-blue-200"
                    leave-active-class="transition duration-300 ease-in transform"
                    leave-to-class="opacity-0 bg-blue-200">
            <div class="overflow-auto fixed inset-0 bg-gray-700 bg-opacity-50 z-30" v-if="modalOpen"></div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out transform "
                    enter-class="scale-95 opacity-0 -translate-y-10"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in transform"
                    leave-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0">
            <!-- Modal container -->

            <div class="fixed inset-0 z-40 flex items-start justify-center" v-if="modalOpen">
                <!-- Close when clicked outside -->
                <div @click="closeTaskModal()" class="overflow-auto fixed h-full w-full"></div>
                <div class="flex flex-col overflow-auto z-50 w-100 bg-white rounded-md shadow-2xl m-10"
                     style="width: 1000px; min-height: 300px; max-height: 80%">


                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">

                            <div>
                                <h1 class="text-2xl text-white pb-2">Task ID:
                                    [{{ cardData.task_simple_name }}]
                                    <span @click="copyToClipboard()"
                                          title="Copy To Clipoboard"
                                          class="cursor-pointer px-2 text-gray-400 hover:text-gray-200 transition duration-300 ease-in-out focus:outline-none">
                                        <i class="fas fa-link"></i>
                                    </span>
                                </h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    View and/or update task information </p>
                            </div>
                        </div>
                        <div>
                            <button
                                class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                type="button" @click="closeTaskModal()">
                                <i class="fas fa-times"></i>
                                <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                            </button>
                        </div>
                    </div>


                    <div class="flex flex-wrap">
                        <div class="w-full">
                            <ul class="flex mb-0 list-none flex-wrap pt-3 pb-4 flex-row">

                                <li class="mx-1 flex-1 text-center">
                                    <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                                       v-on:click="toggleTabs(1)"
                                       v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 1, 'text-white bg-gray-500': openTab === 1}">
                                        <i class="fas fa-space-shuttle text-base mr-1"></i> Task Data
                                    </a>
                                </li>
                                <li class="mx-1 flex-1 text-center">
                                    <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                                       v-on:click="toggleTabs(2)"
                                       v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 2, 'text-white bg-gray-500': openTab === 2}">
                                        <i class="fas fa-cog text-base mr-1"></i> Edit Task
                                    </a>
                                </li>
                                <li class="mx-1 flex-1 text-center">
                                    <a class="text-xs font-bold uppercase px-5 py-3 rounded block leading-normal"
                                       v-on:click="toggleTabs(3)"
                                       v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 3, 'text-white bg-gray-500': openTab === 3}">
                                        <i class="fas fa-space-shuttle text-base mr-1"></i> Logs
                                    </a>
                                </li>
                            </ul>
                            <div
                                class="relative flex flex-col min-w-0 break-words w-full mb-6 ">
                                <div class="px-4 py-5 flex-auto">
                                    <div class="tab-content tab-space">
                                        <div v-bind:class="{'hidden': openTab !== 1, 'block': openTab === 1}">
                                            <p>
                                                <view-task-data :cardData="cardData"></view-task-data>
                                            </p>
                                        </div>
                                        <div v-bind:class="{'hidden': openTab !== 2, 'block': openTab === 2}">
                                            <add-task-data :kanbanData="kanbanData"
                                                           :cardData="cardData"></add-task-data>
                                        </div>
                                        <div v-bind:class="{'hidden': openTab !== 3, 'block': openTab === 3}">
                                            <p>
                                                <task-logs :cardData="cardData"></task-logs>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
import vSelect from "vue-select";
import Avatar from "../../global/Avatar.vue";
import addTaskData from "./taskComponents/AddTaskData";
import taskComments from "./taskComponents/TaskComments";
import taskLogs from "./taskComponents/TaskLogs"
import viewTaskData from "./taskComponents/viewTaskData";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    components: {
        vSelect,
        Avatar,
        addTaskData,
        taskComments,
        taskLogs,
        viewTaskData
    },

    mixins: [ajaxCalls],

    props: {
        kanbanData: Object,
        taskId: Number,
    },

    data() {
        return {
            openTab: 1,
            cardData: Object,
            modalOpen: false,
        };
    },

    mounted() {
        if (!isNaN(this.taskId)) {
            this.setCardData(this.taskId);
        }
    },

    created() {
        this.eventHub.$on("update-kanban-task-cards", (task) => {
            this.$router.replace({name: "board", query: {id: this.kanbanData.id, task: task.id}}).catch(()=>{});
            this.setCardData(task.id);
        });

        this.eventHub.$on("close-task-modal", () => {
            this.closeTaskModal();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('update-kanban-task-cards');
        this.eventHub.$off('close-task-modal');
    },

    methods: {
        toggleTabs: function (tabNumber) {
            this.openTab = tabNumber
        },
        setCardData(taskId) {
            this.asyncGetTaskData(taskId).then((data) => {
                if (this.modalOpen) {
                    this.closeTaskModal();
                    setTimeout(() => {
                        this.cardData = data.data;
                        this.modalOpen = true;
                    }, 100);
                } else {
                    this.openTab = 1;
                    this.cardData = data.data;
                    this.modalOpen = true;
                }
            }).catch(res => {
                console.log(res)
            });
        },

        closeTaskModal(){
            this.$router.replace({name: "board",query: {id: this.kanbanData.id}}).catch(()=>{});
            this.modalOpen = false;
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

