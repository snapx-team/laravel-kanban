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
                <div @click="modalOpen = false" class="overflow-auto fixed h-full w-full"></div>
                <div class="flex flex-col overflow-auto z-50 w-100 bg-white rounded-md shadow-2xl m-10"
                     style="width: 1000px; min-height: 300px; max-height: 80%">


                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">

                            <div>
                                <h1 class="text-2xl text-white pb-2">Task ID:
                                    [{{ kanbanData.name.substring(0, 3).toUpperCase() }}-{{ cardData.id }}]</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    View and/or update task information </p>
                            </div>
                        </div>
                        <div>
                            <button
                                class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                type="button">
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
                                       v-on:click="toggleTabs(4)"
                                       v-bind:class="{'text-gray-600 bg-gray-200 hover:bg-gray-300 cursor-pointer ': openTab !== 4, 'text-white bg-gray-500': openTab === 4}">
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
                                        <div v-bind:class="{'hidden': openTab !== 4, 'block': openTab === 4}">
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
    props: {
        kanbanData: Object,
    },

    data() {
        return {
            openTab: 1,
            cardData: Object,
            modalOpen: false,

        };
    },

    methods: {
        toggleTabs: function (tabNumber) {
            this.openTab = tabNumber
        }
    },

    created() {
        this.eventHub.$on("update-kanban-task-cards", (task) => {
            if (this.modalOpen) {
                this.modalOpen = false;

                setTimeout(() => {
                        this.cardData = task;
                        this.modalOpen = true;
                    },
                    100
                )
                ;
            } else {
                this.cardData = task;
                this.modalOpen = true;
            }
        });
    },

    beforeDestroy() {
        this.eventHub.$off('update-kanban-task-cards');
    },
};
</script>

