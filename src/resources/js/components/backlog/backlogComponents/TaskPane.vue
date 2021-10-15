<template>
    <div class="border-gray-300 border-2">
        <div class="flex justify-between p-2 bg-indigo-800 border-b">
            <h1 class="text-white">Task: <span class="font-medium">{{ task.task_simple_name }}</span>
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

        <div class="flex justify-between p-2 bg-gray-600 border-b">
            <h1 class="text-white">Place task in board, row, and column</h1>
        </div>
        <div class="space-y-6 px-8 py-6 flex-1 bg-gray-50 border-b">
            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Kanban</span>
                    <vSelect
                        :create-option="option => ({name: option.toLowerCase()})"
                        :options="boards"
                        class="text-gray-400"
                        label="name"
                        placeholder="Select one or more kanban boards"
                        style="margin-top: 7px"
                        v-model="task.board"
                        @input="updateBoard(task.board.id)">
                        <template slot="option" slot-scope="option">
                            {{ option.name }}
                        </template>
                        <template #no-options="{ search, searching, loading }">
                            No result .
                        </template>
                    </vSelect>
                </div>
            </div>


            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Row</span>
                    <vSelect
                        :create-option="option => ({name: option.toLowerCase()})"
                        :options="rows"
                        class="text-gray-700"
                        label="name"
                        placeholder="Choose Row"
                        style="margin-top: 7px"
                        taggable
                        @input="loadColumns"
                        v-model="task.row">
                        <template #no-options="{ search, searching, loading }">
                            No result .
                        </template>
                    </vSelect>
                </div>
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Column</span>
                    <vSelect
                        :create-option="option => ({name: option.toLowerCase()})"
                        :options="columns"
                        class="text-gray-700"
                        label="name"
                        placeholder="Choose or Create"
                        style="margin-top: 7px"
                        taggable
                        v-model="task.column">
                        <template #no-options="{ search, searching, loading }">
                            No result .
                        </template>
                    </vSelect>
                </div>
            </div>

            <button @click="assignTask($event)"
                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">
                <span>Place Task</span>
            </button>
        </div>

        <!-- task info -->

        <div class="flex justify-between p-2 bg-gray-600 border-b">
            <h1 class="text-white">Update Task Info</h1>
        </div>


        <div class="space-y-6 overflow-auto px-8 py-6 flex-1">

            <div v-if="$role === 'admin'" class="flex space-x-3">
                <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                    Status
                </span>
                    <vSelect
                        v-model="task.status"
                        :options="statuses"
                        label="name"
                        style="margin-top: 7px"
                        class="text-gray-700">
                    </vSelect>
                </div>
            </div>

            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Badge</span>
                    <vSelect :create-option="option => ({name: option.toLowerCase()})"
                             :options="computedBadges"
                             class="text-gray-700"
                             label="name"
                             :placeholder="$role === 'admin'?'Choose or Create' : 'Choose'"
                             style="margin-top: 7px"
                             :taggable ="$role === 'admin'"
                             v-model="task.badge">
                        <template slot="option" slot-scope="option">
                                <span :style="`color: hsl( ${option.hue} , 45%, 90%);`"
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
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name </span>
                    <input
                        class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                        placeholder="Task Name"
                        type="text"
                        v-model="task.name"/>
                </label>

            </div>

            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Assign employees</span>
                    <vSelect
                        v-model="task.assigned_to"
                        multiple
                        :options="kanbanMembers"
                        :getOptionLabel="opt => opt.employee.user.full_name"
                        style="margin-top: 7px"
                        @search="onTypeEmployee"
                        placeholder="Select one or more kanban members"
                        class="text-gray-400">
                        <template slot="option" slot-scope="option">
                            <avatar :name="option.employee.user.full_name" :size="4"
                                    class="mr-3 m-1 float-left"></avatar>
                            <p class="inline">{{ option.employee.user.full_name }}</p>
                        </template>
                        <template #no-options="{ search, searching, loading }">
                            No result .
                        </template>
                    </vSelect>
                </div>
            </div>

            <div class="flex">
                <div class="flex-grow space-y-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Description</span>

                    <div class="space-y-5">
                        <div class="rounded border p-5 cursor-text" @click="descriptionIsEditable = true" v-if="!descriptionIsEditable">
                            <div class="ql-editor h-auto" id="task-description" v-html="task.shared_task_data.description"></div>
                        </div>
                    </div>

                    <quill-editor v-if="!selectGroupIsVisible && descriptionIsEditable"
                                  :options="config"
                                  output="html"
                                  scrollingContainer="html"
                                  v-model="task.shared_task_data.description"
                                  @blur="descriptionIsEditable = false"></quill-editor>
                    <p v-if="selectGroupIsVisible" class="text-sm font-medium leading-5 text-red-500">Description will match group
                        description</p>

                </div>
            </div>

            <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline</span>
                <date-picker format="YYYY-MM-DD HH:mm:ss"
                             placeholder="YYYY-MM-DD HH:mm:ss"
                             type="datetime"
                             v-model="task.deadline">
                </date-picker>
            </div>

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

            <div class="space-y-5">
                <div class="text-gray-700 font-semibold font-sans tracking-wide text-lg"
                     :class="{'animate-pulse': loadingRelatedTasks}">
                    <p v-if="loadingRelatedTasks">Loading Related Tasks</p>
                    <div v-else class="flex justify-between">
                        <p>Related Tasks:</p>
                        <button @click="removeGroup()"
                                v-if="relatedTasks.length > 0 && $role === 'admin'"
                                class="text-sm text-red-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none">
                            <i class="fas fa-layer-group mr-2"></i>
                            Remove from group
                        </button>
                    </div>


                </div>
                <div v-if="!loadingRelatedTasks" class="flex">
                    <p v-if="relatedTasks.length === 0" class="tracking-wide text-sm">No Related Tasks</p>
                    <div v-if="relatedTasks.length > 0" class="bg-gray-50 py-3 px-4 w-full rounded">
                        <p v-for="task in relatedTasks" :key="task.id" class="border-b py-1">
                            <span
                                class="font-bold">{{ task.task_simple_name }}: </span>
                            <span class="italic">{{ task.name }}</span>
                        </p>
                    </div>
                </div>

                <button @click="selectGroupIsVisible = true"
                        v-if="!selectGroupIsVisible && $role === 'admin'"
                        class="text-sm text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none">
                    <i class="fas fa-th-list mr-2"></i>
                    Click To Add or Change Group
                </button>
            </div>

            <div v-if="selectGroupIsVisible && $role === 'admin'" class="flex-1 space-y-2">

                <div class="flex justify-between">
                    <div class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                        Group with task
                    </div>
                    <button @click="cancelChangeGroup"
                            class="text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>

                <vSelect :options="tasks"
                         class="text-gray-400"
                         label="name"
                         placeholder="Select task"
                         style="margin-top: 7px"
                         @search="onTypeTask"
                         :filter="filterTasks"
                         v-model="task.associatedTask">
                    <template slot="selected-option" slot-scope="option">
                        <p>
                            <span class="font-bold">{{ option.task_simple_name }}: </span>
                            <span class="italic">{{ option.name }}</span>
                        </p>
                    </template>
                    <template slot="option" slot-scope="option">
                        <p>
                            <span
                                class="font-bold">{{ option.task_simple_name }}: </span>
                            <span class="italic">{{ option.name }}</span>
                        </p>
                    </template>
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>
            </div>

            <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                <button @click="updateBacklogTask($event)"
                        class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                        type="button">
                    <span>Save Changes</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import Avatar from "../../global/Avatar.vue";
import vSelect from "vue-select";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    components: {
        Avatar,
        vSelect
    },
    mixins: [helperFunctions, ajaxCalls],
    data() {
        return {
            descriptionIsEditable: false,
            loadingRelatedTasks: false,
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
            erpEmployees: [],
            erpContracts: [],
            tasks: [],
            relatedTasks: [],
            kanbanMembers: [],
            selectGroupIsVisible: false,
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
            checklistStatus: null,
            selectedStatus: null,
            statuses: ['active', 'completed', 'cancelled'],
        }
    },
    props: {
        badges: {
            type: Array,
            default: null,
        },
        boards: {
            type: Array,
            default: null,
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
    },
    created() {
        this.eventHub.$on("populate-task-view", (task) => {
            this.task = {...task};
            this.selectGroupIsVisible = false;
            this.populateTaskView(this.task);
        });
    },

    beforeDestroy() {
        this.eventHub.$off('populate-task-view');
    },

    mounted() {
        this.getErpEmployees();
        this.getContracts();
        this.getTasks();
    },
    methods: {
        populateTaskView(task) {
            this.task = task;
            if (task.deadline) {
                this.task.deadline = new Date(task.deadline);
            }
            this.loadRowsAndColumns(task.board_id);
            this.getRelatedTasks(task.id);
        },
        filterTasks(options, search) {
            return this.tasks;
        },
        closeTaskView() {
            this.eventHub.$emit("close-task-view");
        },
        getRows(board_id) {
            this.asyncGetRows(board_id).then((data) => {
                this.rows = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        getMembers(board_id) {
            this.asyncGetMembers(board_id).then((data) => {
                this.kanbanMembers = data.data;
            })
        },
        loadColumns(option) {
            this.asyncGetColumns(option.id).then((data) => {
                this.columns = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        updateBoard(id) {
            this.task.row = null;
            this.task.column = null;
            this.loadRowsAndColumns(id)
        },
        loadRowsAndColumns(id) {
            this.columns = [];
            this.getRows(id);
            this.getMembers(id);
        },
        getRelatedTasks(id) {
            this.loadingRelatedTasks = true;
            this.asyncGetRelatedTasksLessInfo(id).then((data) => {
                this.relatedTasks = data.data;
                this.loadingRelatedTasks = false;
            }).catch(res => {
                console.log(res)
            });
        },
        updateBacklogTask() {
            if(!(!!this.task.name))
                this.triggerErrorToast('Task name is required');
            else if(!(!!this.task.description) && !this.checkedOptions.includes('Group'))
                this.triggerErrorToast('Task description is required');
            else if(this.checkedOptions.includes('Group') && !(!!this.task.associatedTask)){
                this.triggerErrorToast('Choose a task to group with, or uncheck group from options list');
            }
            else if ( this.task.badge.name && this.task.badge.name.trim().length == 0) {
                this.triggerErrorToast('The badge name must contain atleast one character');
            }
            if (this.task.associatedTask) {
                this.task.shared_task_data_id = this.task.associatedTask.shared_task_data_id
            }
            this.asyncUpdateTask(this.task).then(() => {
                window.scrollTo({top: 0, behavior: 'smooth'});
                this.eventHub.$emit("reload-backlog-data");
                if (this.task.associatedTask) {
                    this.getRelatedTasks(this.task.id);
                }
            });
        },
        removeGroup() {

            this.$swal({
                icon: 'info',
                title: 'Are you sure?',
                text: 'This task will no longer be related to any other task. The information on the card will remain the same.',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    this.eventHub.$emit("reload-backlog-data");
                    this.asyncRemoveGroup(this.task.id).then(() => {
                        this.getRelatedTasks(this.task.id);
                    }).catch(res => {
                        console.log(res)
                    });
                }
            })
        },

        cancelChangeGroup() {
            this.selectGroupIsVisible = false;
        },
        assignTask() {
            if (this.task.row === null || this.task.column === null) {
                this.triggerErrorToast('row and column need to be selected!');
            } else {
                this.asyncAssignTaskToBoard(this.task.id, this.task.row.id, this.task.column.id, this.task.board.id).then(() => {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    this.eventHub.$emit("reload-backlog-data");
                }).catch(res => {
                    console.log(res)
                });
            }
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
        onTypeEmployee(search, loading) {
            if (search.length) {
                loading(true);
                this.typeEmployee(search, loading, this);
            }
        },
        typeEmployee: _.debounce(function (search, loading) {
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
        typeContract: _.debounce(function (search, loading) {
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
        onTypeTask(search, loading) {
            if (search.length) {
                loading(true);
                this.typeTask(search, loading, this);
            }
        },
        typeTask: _.debounce(function (search, loading) {
            this.asyncGetSomeTasks(search).then((data) => {
                this.tasks = data.data;
                console.log(data.data);
                console.log('abra');
            })
                .catch(res => {
                    console.log(res)
                })
                .then(function () {
                    setTimeout(500);
                    loading(false);
                });
        }, 500),
    },
};
</script>
