<template>
    <div class="border-gray-300 border-2">
        <div class="flex justify-end p-5 bg-indigo-800 border-b">
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

        <!-- move items -->
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
                        @input="loadRowsAndColumns(option.id)">
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

        <!-- taks info -->

        <div class="space-y-6 overflow-auto px-8 py-6 flex-1">
            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Badge</span>
                    <vSelect :create-option="option => ({name: option.toLowerCase()})"
                             :options="computedBadges"
                             class="text-gray-700"
                             label="name"
                             placeholder="Choose or Create"
                             style="margin-top: 7px"
                             taggable
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

            <div>
                <div class="flex-grow space-y-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Description</span>
                    <quill-editor :options="config"
                                  output="html"
                                  v-model="task.description"></quill-editor>
                </div>
            </div>

            <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline</span>
                <date-picker :popup-style="{ position: 'fixed' }"
                             format="YYYY-MM-DD HH:mm:ss"
                             placeholder="YYYY-MM-DD HH:mm:ss"
                             type="datetime"
                             v-model="task.deadline">
                </date-picker>
            </div>

            <div class="flex-1 space-y-2">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Employee</span>
                <vSelect :options="erpEmployees"
                         class="text-gray-400"
                         label="full_name"
                         placeholder="Select Employee"
                         style="margin-top: 7px"
                         @search="onTypeEmployee"
                         v-model="task.erp_employee">
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
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Job Site</span>
                <vSelect :options="erpJobSites"
                         class="text-gray-400"
                         label="name"
                         placeholder="Select Job Site"
                         style="margin-top: 7px"
                         @search="onTypeJobsite"
                         v-model="task.erp_job_site">
                    <template slot="option" slot-scope="option">
                        <p class="inline">{{ option.name }}</p>
                    </template>
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>
            </div>

            <div>
                <p> Related Tasks: </p>
                <p v-if="relatedTasks.length === 0" class="tracking-wide text-sm">No Related Tasks</p>
                <div class="w-full text-center">
                    <p
                        v-for="task in relatedTasks"
                        :key="task.id">
                        
                        <span class="font-bold">{{ task.board.name.substring(0, 3).toUpperCase() }}-{{ task.id }}: </span>
                        <span class="italic">{{ task.name }}</span>
                    </p>
                </div>
            </div>

            <button @click="selectGroupIsVisible = true"
                    v-if="!selectGroupIsVisible"
                    class="text-center w-full pt-2 px-4 text-sm text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none">
                        <i class="fas fa-th-list mr-2"></i>
                        Click To Add or Change Group
            </button>

            <div v-if="selectGroupIsVisible" class="flex-1 space-y-2">
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
                            <span class="font-bold">{{ option.board.name.substring(0, 3).toUpperCase() }}-{{ option.id }}: </span>
                            <span class="italic">{{ option.name }}</span>
                        </p>
                    </template>
                    <template slot="option" slot-scope="option">
                        <p>
                            <span class="font-bold">{{ option.board.name.substring(0, 3).toUpperCase() }}-{{ option.id }}: </span>
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
            rows: [],
            columns: [],
            erpEmployees: [],
            erpJobSites: [],
            tasks: [],
            relatedTasks: [],
            checkedOptions: [],
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
        }
    },
    props: {
        task: {
            name: String,
            badge: {},
            description: String,
        },
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
                computedBadges.name = badge;
                computedBadges.hue = this.generateHslColorWithText(badge);
                return computedBadges;
            })
        },
    },
    created() {
        this.eventHub.$on("open-task-view", (currentTask) => {
            this.loadRowsAndColumns(currentTask.board_id);
        });
        if (this.task.deadline) {
            this.task.deadline = new Date(this.task.deadline);
            this.checkedOptions.push('Deadline');
        }
        if (this.task.erp_job_site_id)
            this.checkedOptions.push('ERP Job Site');
        if (this.task.erp_employee_id)
            this.checkedOptions.push('ERP Employee');
        this.getErpEmployees();
        this.getJobSites();
        this.getTasks();
        this.getRelatedTasks();
    },
    mounted() {
        this.loadRowsAndColumns(this.task.board_id);
    },
    methods: {
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
            this.chosenColumn = {};
            this.task.column = {};
            this.asyncGetColumns(option.id).then((data) => {
                this.columns = data.data;
            }).catch(res => {
                console.log(res)
            });
        },
        loadRowsAndColumns(id) {
            this.task.row = {};
            this.task.column = {};
            this.columns = [];
            this.getRows(id);
            this.getMembers(id);
        },
        getRelatedTasks() {
            this.asyncGetRelatedTasksLessInfo(this.task.id).then((data) => {
                this.relatedTasks = data.data;
                console.log(data.data);
            }).catch(res => {
                console.log(res)
            });
        },
        updateBacklogTask() {
            console.log(this.task);
            let backlogTaskData = {
                id: this.task.id,
                name: this.task.name,
                badge: this.task.badge,
                description: this.task.description,
                erp_employee: this.task.erp_employee,
                erp_employee_id: this.task.erp_employee ? this.task.erp_employee.id : null,
                erp_job_site_id: this.task.erp_job_site ? this.task.erp_job_site.id : null,
                erp_job_site: this.task.erp_job_site,
                deadline: this.task.deadline,
                assigned_to: this.task.assigned_to,
                group: this.task.associatedTask ? this.task.associatedTask.group : this.task.group,
            }
            const cloneBacklogTasksData = {...backlogTaskData};
            this.asyncUpdateTask(cloneBacklogTasksData).then((data) => {
                this.triggerSuccessToast("Task Updated!");
            });
        },
        assignTask() {
            this.asyncAssignTaskToBoard(this.task.id, this.task.row.id, this.task.column.id).then((data) => {
                this.triggerSuccessToast("Task Assigned!");
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
        onTypeJobsite(search, loading) {
            if (search.length) {
                loading(true);
                this.typeJobsite(search, loading, this);
            }
        },
        typeJobsite: _.debounce(function (search, loading) {
            this.asyncGetSomeJobSites(search).then((data) => {
                this.erpJobSites = data.data;
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
