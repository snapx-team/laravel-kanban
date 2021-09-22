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
                    <!-- Heading -->
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
                    <!-- Container -->

                    <button @click="selectTemplateIsVisible = true"
                            class="py-6 px-8 text-sm text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none"
                            v-if="!selectTemplateIsVisible">
                        <i class="fas fa-th-list mr-2"></i>
                        Click To Select A Template
                    </button>

                    <div class="py-6 px-8" v-if="selectTemplateIsVisible">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Select Template</span>
                        <vSelect :options="templates"
                                 class="text-gray-400"
                                 label="name"
                                 placeholder="Select a template to populate the other fields"
                                 style="margin-top: 7px"
                                 v-model="selectedTemplate"
                                 @input="setTemplate()">
                            <template slot="option" slot-scope="option">
                                {{ option.name }}
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                        </vSelect>
                    </div>

                    <div class="flex">
                        <div class="px-8 py-6 space-y-2 border-r">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Task Options</span>

                            <div class=" grid divide-y divide-gray-400 pt-2"
                                 v-for="(taskOption, taskOptionIndex) in taskOptions">
                                <label :key="taskOptionIndex" class="flex">
                                    <input :value="taskOption.name"
                                           class="mt-1 form-radio text-indigo-600"
                                           name="task-options"
                                           type="checkbox"
                                           v-model="checkedOptions">
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
                                    <vSelect :create-option="option => ({name: option.toLowerCase()})"
                                             :options="computedBadges"
                                             class="text-gray-700"
                                             label="name"
                                             placeholder="Choose or Create"
                                             style="margin-top: 7px"
                                             taggable
                                             v-model="computedSelectedBadge">
                                        <template slot="option" slot-scope="option">
                                            <span :style="`color: hsl(${option.hue}, 50%, 45%);`"
                                                  class="fa fa-circle mr-4"></span>
                                            {{ option.name }}
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
                                        </template>
                                    </vSelect>
                                </div>

                                <label class="flex-grow space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name *</span>
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
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600"> Description *</span>
                                    <quill-editor  v-if="!checkedOptions.includes('Group')"
                                                   :options="config"
                                                  output="html"
                                                  v-model="task.description"></quill-editor>
                                    <p v-else class="text-sm font-medium leading-5 text-red-500">Description will match group description</p>

                                </div>
                            </div>

                            {{ checklistStatus }}

                            <div class="flex space-x-3">
                                <div class="flex-1 space-y-2">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Assign to Kanban *</span>
                                    <vSelect :options="boards"
                                             class="text-gray-400"
                                             label="name"
                                             multiple
                                             placeholder="Select one or more kanban boards"
                                             style="margin-top: 7px"
                                             v-model="task.selectedKanbans">
                                        <template slot="option" slot-scope="option">
                                            {{ option.name }}
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
                                        </template>
                                    </vSelect>
                                </div>
                            </div>

                            <div class="flex flex-wrap"
                                 v-if="checkedOptions.includes('Deadline') || checkedOptions.includes('ERP Employee') ||checkedOptions.includes('ERP Contract')">
                                <div class="flex-1 pr-2 mb-6" v-if="checkedOptions.includes('Deadline')">
                                    <div class="flex-1 space-y-2">
                                        <span
                                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline</span>
                                        <date-picker :popup-style="{ position: 'fixed' }"
                                                     format="YYYY-MM-DD HH:mm"
                                                     placeholder="YYYY-MM-DD HH:mm"
                                                     type="datetime"
                                                     v-model="task.deadline"></date-picker>
                                    </div>
                                </div>

                                <div class="flex-1 pr-2 mb-6" v-if="checkedOptions.includes('ERP Employee')">
                                    <div class="flex-1 space-y-2">
                                        <span
                                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Employee</span>
                                        <vSelect :options="erpEmployees"
                                                 multiple
                                                 class="text-gray-400"
                                                 label="full_name"
                                                 placeholder="Select Employee"
                                                 style="margin-top: 7px"
                                                 @search="onTypeEmployee"
                                                 v-model="task.shared_task_data.erp_employees">
                                            <template slot="option" slot-scope="option">
                                                <avatar :name="option.full_name"
                                                        :size="4"
                                                        class="mr-3 m-1 float-left"></avatar>
                                                <p class="inline">{{ option.full_name }}</p>
                                            </template>
                                            <template #no-options="{ search, searching, loading }">
                                                No result .
                                            </template>
                                        </vSelect>
                                    </div>
                                </div>

                                <div class="flex-1 pr-2 mb-6" v-if="checkedOptions.includes('ERP Contract')">
                                    <div class="flex-1 space-y-2">
                                        <span
                                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Contract</span>
                                        <vSelect :options="erpContracts"
                                                 multiple
                                                 class="text-gray-400"
                                                 label="contract_identifier"
                                                 placeholder="Select Contract"
                                                 style="margin-top: 7px"
                                                 @search="onTypeContract"
                                                 v-model="task.shared_task_data.erp_contracts">
                                            <template slot="option" slot-scope="option">
                                                <p class="inline">{{ option.contract_identifier }}</p>
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
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Group with task *</span>
                                    <vSelect :options="tasks"
                                             class="text-gray-400"
                                             label="name"
                                             placeholder="Select task"
                                             style="margin-top: 7px"
                                             v-model="task.associatedTask">
                                        <template slot="selected-option" slot-scope="option">
                                            <p>
                                                <span class="font-bold">{{ option.task_simple_name}}: </span>
                                                <span class="italic">{{ option.name }}</span>
                                            </p>
                                        </template>
                                        <template slot="option" slot-scope="option">
                                            <p>
                                                <span class="font-bold">{{ option.task_simple_name}}: </span>
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
                                    <span>Create Task</span>
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
import _ from 'lodash';

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
                {name: 'ERP Employee',},
                {name: 'ERP Contract',},
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
                        [{'list': 'ordered'}, {'list': 'bullet'}, {'list': 'check'}, {'list': 'unchecked'}],
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
                deadline: null,
                columnId: null,
                associatedTask: null,
                shared_task_data:{
                    erp_employees: [],
                    erp_contracts: [],
                },
            },
            badges: [],
            erpEmployees: [],
            erpContracts: [],
            tasks: [],
            templates: [],
            selectTemplateIsVisible: false,
            selectedTemplate: null,
            modalOpen: false,
            formatted: null,
            checklistStatus: null,
        };
    },

    created() {
        this.eventHub.$on("create-backlog-task", () => {
            this.modalOpen = true;
            this.getBadges();
            this.getErpEmployees();
            this.getContracts();
            this.getTasks();
            this.getTemplates();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('create-backlog-task');
    },

    methods: {
        saveBacklogTask(event) {
            if(!(!!this.task.name))
                this.triggerErrorToast('Task name is required');
            else if(!(!!this.task.description) && !this.checkedOptions.includes('Group'))
                this.triggerErrorToast('Task description is required');
            else if(this.task.selectedKanbans.length === 0)
                this.triggerErrorToast('At least 1 kanban needs to be selected');
            else if(this.checkedOptions.includes('Group') && !(!!this.task.associatedTask)){
                this.triggerErrorToast('Choose a task to group with, or uncheck group from options list');
            }
            else{
                this.eventHub.$emit("save-backlog-task", this.task);
                this.modalOpen = false;

                this.task = {
                    name: null,
                    badge: {},
                    description: null,
                    selectedKanbans: [],
                    deadline: null,
                    columnId: null,
                    associatedTask: null,
                    shared_task_data:{
                        erp_employees: [],
                        erp_contracts: [],
                    },
                }
                this.checkedOptions = [];
                this.selectedTemplate = null;
            }
        },
        onTypeEmployee(search, loading) {
            if (search.length) {
                loading(true);
                this.typeEmployee(search, loading, this);
            }
        },
        typeEmployee: _.debounce(function (search, loading, vm) {
            this.asyncGetSomeUsers(search).then((data) => {
                this.erpEmployees = data.data;
            })
                .catch(res => {
                    console.log(res)
                })
                .then(function () {
                    setTimeout(500);
                    loading(false);
                });
        }, 500),
        onTypeContract(search, loading) {
            if (search.length) {
                loading(true);
                this.typeContract(search, loading, this);
            }
        },
        typeContract: _.debounce(function (search, loading, vm) {
            this.asyncGetSomeContracts(search).then((data) => {
                this.erpContracts = data.data;
            })
                .catch(res => {
                    console.log(res)
                })
                .then(function () {
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

        getContracts() {
            this.asyncGetAllContracts().then((data) => {
                this.erpContracts = data.data;
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

        getTemplates() {
            this.asyncGetTemplates().then((data) => {
                this.templates = data.data;
            }).catch(res => {
                console.log(res)
            });
        },

        setTemplate() {
            if(this.selectedTemplate !== null){
                this.task.name = this.selectedTemplate.task_name;
                this.task.badge = this.selectedTemplate.badge;
                this.task.description = this.selectedTemplate.description;
                this.task.selectedKanbans = this.selectedTemplate.boards;
                this.checkedOptions = this.selectedTemplate.unserialized_options;
            }
            else{
                this.task.name = null;
                this.task.badge = {};
                this.task.description = null;
                this.task.selectedKanbans = [];
                this.checkedOptions = [];
            }
        },
    },

    computed: {
        computedBadges() {
            return this.badges.map(badge => {
                let computedBadges = {};
                computedBadges.name = badge.name;
                computedBadges.hue = this.generateHslColorWithText(badge.name);
                return computedBadges;
            })
        },

        computedSelectedBadge: {
            get () {
                if (_.isEmpty(this.task.badge)) {
                    return null
                } else {
                    return this.task.badge
                }
            },
            set (val) {
                if( val === null) val = {};
                this.task.badge = val;
            }
        },

        checklistData() {
            let parser = new DOMParser();
            let doc = parser.parseFromString(this.cloneCardData.description, 'text/html');
            const li = doc.querySelectorAll('ul[data-checked] li');
            const done = doc.querySelectorAll('ul[data-checked="true"] li');
            return done.length + "/" + li.length
        }
    }
};
</script>
