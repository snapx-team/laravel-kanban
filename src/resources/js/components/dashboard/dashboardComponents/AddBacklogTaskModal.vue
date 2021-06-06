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
                     style="width: 700px; min-height: 300px; max-height: 80%">
                    <!-- Task heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">

                            <div>
                                <h1 class="text-2xl text-white pb-2">Create Backlog Task</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Create tasks and assign it to a single or several Kanban boards </p>
                            </div>
                        </div>
                        <div>
                            <button @click="modalOpen = false"
                                    class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                    type="button">
                                <i class="fas fa-times"></i>
                                <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                            </button>
                        </div>
                    </div>
                    <!-- Task container -->
                    <form class="space-y-6 overflow-auto px-8 py-6">
                        <div class="flex space-x-3">
                            <label class="flex-1 space-y-2">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name </span>
                                <input
                                    class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                    placeholder="Task Name"
                                    type="text"
                                    v-model="task.name"/>
                            </label>

                        </div>

                        <div class="flex">
                            <div class="flex-1 pr-3">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Add Deadline</span>
                                <vSelect
                                    v-model="task.priority"
                                    :options="badges"
                                    label="title"
                                    style="margin-top: 7px"
                                    class="text-gray-700">
                                    <template slot="option" slot-scope="option">
                                      <span class="fa mr-4"
                                          :class="[option.icon, `text-${option.color}-400 `]">
                                      </span>
                                        {{ option.title }}
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No result .
                                    </template>
                                </vSelect>
                            </div>
                            <div class="flex-1">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Badge</span>
                                <vSelect
                                    v-model="task.badge"
                                    :options="badges"
                                    label="title"
                                    style="margin-top: 7px"
                                    class="text-gray-700"
                                >
                                    <template slot="option" slot-scope="option">
                      <span
                          class="fa fa-circle pr-4 text-gray-700"
                          :class="`text-${option.color}-400 `"
                      ></span>
                                        {{ option.title }}
                                    </template>
                                </vSelect>
                            </div>
                        </div>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveBacklogTask($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">

                                <span>Create Board</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

    import vSelect from "vue-select";
    import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],

        components: {
            vSelect,
        },

        mixins: [ajaxCalls],

        data() {
            return {
                badges: ['test1','test2'],

                task: {
                    name: null,
                },
                modalOpen: false,
            };
        },

        created() {
            this.eventHub.$on("create-backlog-task", () => {
                this.modalOpen = true;
            });
        },

        beforeDestroy() {
            this.eventHub.$off('create-backlog-task');
        },

        methods: {
            saveBacklogTask(event) {
                event.target.disabled = true;
                this.eventHub.$emit("save-backlog-task", this.task);
                this.modalOpen = false;
            },
        },
    };
</script>
