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
                     style="width: 900px; min-height: 300px; max-height: 80%">
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
                    <div class="flex">
                        <div class="px-8 py-6 space-y-2 border-r">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Task Options</span>


                            <div v-for="(taskOption, taskOptionIndex) in taskOptions"
                                 class=" grid divide-y divide-gray-400 pt-2">
                                <label :key="taskOptionIndex" class="flex">
                                    <input name="task-options" type="checkbox" :value="taskOption.name"
                                           class="mt-1 form-radio text-indigo-600" v-model="checkedOptions">
                                    <div class="ml-3 text-gray-700 font-medium">
                                        <p>{{ taskOption.name }}</p>
                                    </div>

                                </label>
                            </div>
                        </div>

                        <div class="space-y-6 overflow-auto px-8 py-6 flex-1">
                            <div class="flex space-x-3">

                                <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Badge</span>
                                    <vSelect
                                        v-model="task.badge"
                                        :options="computedBadges"
                                        label="name"
                                        placeholder="Choose or Create"
                                        style="margin-top: 7px"
                                        taggable
                                        :create-option="option => ({name: option.toLowerCase()})">
                                    class="text-gray-700">
                                        <template slot="option" slot-scope="option">
                                            <span class="fa fa-circle mr-4" :style="`color:#${option.color};`"></span>
                                            {{ option.name }}
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
                                        </template>
                                    </vSelect>
                                </div>


                                <label class="flex-grow space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name </span>
                                    <input
                                        class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                        placeholder="Task Name"
                                        type="text"
                                        v-model="task.name"/>
                                </label>


                            </div>

                            <div>
                                <div class="flex-grow space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Description
                                    </span>
                                    <quill v-model="task.description" :config="config" output="html"/>

                                </div>
                            </div>

                            <div class="flex space-x-3">
                                <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Assign to Kanban</span>
                                    <vSelect
                                        v-model="task.selectedKanbans"
                                        multiple
                                        :options="boards"
                                        label="name"
                                        style="margin-top: 7px"
                                        placeholder="Select one or more kanban boards"
                                        class="text-gray-400">
                                        <template slot="option" slot-scope="option">
                                            {{ option.name }}
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
                                        </template>
                                    </vSelect>
                                </div>
                            </div>

                            <div class="flex space-x-3"
                                 v-if="checkedOptions.includes('Deadline') || checkedOptions.includes('ERP employee') ||checkedOptions.includes('ERP Job Site')">
                                <div class="flex-1" v-if="checkedOptions.includes('Deadline')">
                                    <div class="flex-1 space-y-2">
                                        <span
                                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline</span>
                                        <date-picker type="datetime" v-model="task.deadline"
                                                     placeholder="YYYY-MM-DD HH:mm"
                                                     :popup-style="{ position: 'fixed' }" format="YYYY-MM-DD HH:mm"
                                        ></date-picker>

                                    </div>
                                </div>

                                <div class="flex-1" v-if="checkedOptions.includes('ERP employee')">
                                    <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Employee</span>
                                        <vSelect :options="erpEmployees"
                                                class="text-gray-400"
                                                label="full_name"
                                                placeholder="Select Employee"
                                                style="margin-top: 7px"
                                                @search="onTypeEmployee"
                                                v-model="task.erpEmployee">
                                            <template slot="option" slot-scope="option">
                                                <avatar :name="option.full_name" :size="4"
                                                        class="mr-3 m-1 float-left"></avatar>
                                                <p class="inline">{{ option.full_name }}</p>
                                            </template>
                                            <template #no-options="{ search, searching, loading }">
                                                No result .
                                            </template>
                                        </vSelect>
                                    </div>
                                </div>

                                <div class="flex-1" v-if="checkedOptions.includes('ERP Job Site')">
                                    <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Job Site</span>
                                        <vSelect :options="erpJobSites"
                                                class="text-gray-400"
                                                label="name"
                                                placeholder="Select Job Site"
                                                style="margin-top: 7px"
                                                @search="onTypeJobsite"
                                                v-model="task.erpJobSite">
                                            <template slot="option" slot-scope="option">
                                                <p class="inline">{{ option.name }}</p>
                                            </template>
                                            <template #no-options="{ search, searching, loading }">
                                                No result .
                                            </template>
                                        </vSelect>
                                    </div>
                                </div>

                            </div>

                            <div class="flex-1" v-if="checkedOptions.includes('Group')">
                                <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Group with task</span>
                                    <vSelect :options="tasks"
                                             class="text-gray-400"
                                             label="name"
                                             placeholder="Select task"
                                             style="margin-top: 7px"
                                             v-model="task.associatedTask">
                                        <template slot="selected-option" slot-scope="option">
                                            <p>
                                                <span class="font-bold">task-{{ option.id }}: </span>
                                                <span class="italic">{{ option.name }}</span>
                                            </p>
                                        </template>
                                        <template slot="option" slot-scope="option">
                                            <p>
                                                <span class="font-bold">task-{{ option.id }}: </span>
                                                <span class="italic">{{ option.name }}</span>
                                            </p>
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
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
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

    import vSelect from "vue-select";
    import Avatar from "../../global/Avatar";

    import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
    import {helperFunctions} from "../../../mixins/helperFunctionsMixin";

    export default {
        inject: ["eventHub"],

        components: {
            vSelect,
            Avatar
        },

        mixins: [ajaxCalls, helperFunctions],

        props: {
            boards: {
                type: Array,
                default: null,
            },
        },

        data() {
            return {
                taskOptions: [
                    {name: 'Deadline',},
                    {name: 'ERP employee',},
                    {name: 'ERP Job Site',},
                    {name: 'Group',},
                ],
                checkedOptions: [],
                config: {
                    readOnly: false,
                    placeholder: 'Describe your task in greater detail',
                    theme: 'snow',
                    modules: {
                        toolbar: [['bold', 'italic', 'underline', 'strike'],
                            ['code-block'],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],
                            [{'color': []}, {'background': []}],
                            [{'align': []}],
                            ['clean']
                        ]
                    }
                },

                task: {
                    name: null,
                    badge: {},
                    description: null,
                    selectedKanbans: [],
                    erpEmployee: null,
                    erpJobSite: null,
                    deadline: null,
                    columnId: null,
                    associatedTask: null,
                },
                badges: [],
                erpEmployees: [],
                erpJobSites: [],
                tasks: [],
                modalOpen: false,
            };
        },

        created() {
            this.eventHub.$on("create-backlog-task", () => {
                this.modalOpen = true;
            });

            this.getBadges();
            this.getErpEmployees();
            this.getJobSites();
            this.getTasks();

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
            onTypeEmployee(search, loading) {
                if(search.length) {
                        loading(true);
                        this.typeEmployee(search, loading, this);
                    }
                },
            typeEmployee : _.debounce(function (search, loading, vm) {
                this.asyncGetSomeUsers(search).then((data) => {
                    this.erpEmployees = data.data;
                })
                .catch(res => {console.log(res)})
                .then(function() {
                    setTimeout(500);
                    loading(false);
                });
            }, 500),
            onTypeJobsite(search, loading) {
                if(search.length) {
                        loading(true);
                        this.typeJobsite(search, loading, this);
                    }
                },
            typeJobsite : _.debounce(function (search, loading, vm) {
                this.asyncGetSomeJobSites(search).then((data) => {
                    this.erpJobSites = data.data;
                })
                .catch(res => {console.log(res)})
                .then(function() {
                    setTimeout(500);
                    loading(false);
                });
            }, 500),
            getBadges() {
                this.asyncGetBadges().then((data) => {
                    this.badges = data.data;
                }).catch(res => {
                    console.log(res)
                });
            },

            getErpEmployees() {
                this.asyncGetAllUsers().then((data) => {
                    this.erpEmployees = data.data;
                }).catch(res => {
                    console.log(res)
                });
            },

            getJobSites() {
                this.asyncGetAllJobSites().then((data) => {
                    this.erpJobSites = data.data;
                }).catch(res => {
                    console.log(res)
                });
            },

            getTasks() {
                this.asyncGetAllTasks().then((data) => {
                    this.tasks = data.data;
                }).catch(res => {
                    console.log(res)
                });
            },

        },

        computed: {
            computedBadges() {
                return this.badges.map(badge => {
                    let computedBadges = {};
                    computedBadges.name = badge.name;
                    computedBadges.color = this.generateHexColorWithText(badge.name);
                    return computedBadges;
                })
            }
        }
    };
</script>
