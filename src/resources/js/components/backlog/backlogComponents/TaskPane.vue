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
        <div class="space-y-6 overflow-auto px-8 py-6 flex-1">
            <div class="flex space-x-3">
                <div class="flex-1 space-y-2">
                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Assign to Kanban</span>
                    <vSelect 
                            :create-option="option => ({name: option.toLowerCase()})"
                            :options="boards"
                            class="text-gray-400"
                            label="name"
                            placeholder="Select one or more kanban boards"
                            style="margin-top: 7px"
                            v-model="task.board"
                            @input="loadRowsAndColumns">
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
                        v-model="chosenRow">
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
                        v-model="chosenColumn">
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                    </vSelect>
                </div>
            </div>
        </div>

        <!-- task option checklist -->

        <div class="flex">
            <div class="px-2 py-6 space-y-2 border-r">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Task Options</span>
                <div class="grid divide-y divide-gray-400 pt-2"
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

        <!-- taks info -->

            <div class="space-y-6 overflow-auto px-8 py-6 flex-1">
                <div class="flex space-x-3">
                    <div class="flex-1 space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Badge</span>
                        <vSelect  :create-option="option => ({name: option.toLowerCase()})"
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
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name </span>
                        <input class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                placeholder="Task Name"
                                type="text"
                                v-model="task.name"/>
                    </label>

                </div>

                <div>
                    <div class="flex-grow space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600"> Description</span>
                        <quill-editor :options="config"
                                        output="html"
                                        v-model="task.description"></quill-editor>

                    </div>
                </div>

                {{checklistStatus}}

               
                    <div class="flex-1" v-if="checkedOptions.includes('Deadline')">
                        <div class="flex-1 space-y-2">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline</span>
                            <date-picker :popup-style="{ position: 'fixed' }"
                                        format="YYYY-MM-DD HH:mm:ss"
                                        placeholder="YYYY-MM-DD HH:mm:ss"
                                        type="datetime"
                                        v-model="task.deadline">
                            </date-picker>
                        </div>
                    </div>

                    <div class="flex-1" v-if="checkedOptions.includes('ERP Employee')">
                        <div class="flex-1 space-y-2">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Employee</span>
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
                    </div>

                    <div class="flex-1" v-if="checkedOptions.includes('ERP Job Site')">
                        <div class="flex-1 space-y-2">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Job Site</span>
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
                    </div>

                

                <div class="flex-1" v-if="checkedOptions.includes('Group')">
                    <div class="flex-1 space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Group with task</span>
                        <vSelect :options="tasks"
                                class="text-gray-400"
                                label="name"
                                placeholder="Select task"
                                style="margin-top: 7px"
                                v-model="associatedTask">
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

                 <button @click="updateBacklogTask($event)"
                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                    type="button">
                    <span>Save Changes</span>
                </button>

                <button @click="assignTask($event)"
                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">
                    <span>Assign Task To Board</span>
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
            chosenRow : {},
            chosenColumn: {},
            taskOptions: [
                {name: 'Deadline',},
                {name: 'ERP Employee',},
                {name: 'ERP Job Site',},
                //{name: 'Group',},
            ],
            associatedTask: null,
            erpEmployees: [],
            erpJobSites: [],
            tasks: [],
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
            this.loadRowsAndColumns(currentTask);
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
    },
    mounted() {
        this.loadRowsAndColumns(this.task);
    },
    methods: {
        closeTaskView() {
            this.eventHub.$emit("close-task-view");
        },
        getRows(board_id) {
            this.asyncGetRows(board_id).then((data) => {
                this.rows = data.data;
            }).catch(res => {console.log(res)});
        },
        loadColumns(option) {
            this.chosenColumn = {};
            this.asyncGetColumns(option.id).then((data) => {
                this.columns = data.data;
            }).catch(res => {console.log(res)});
        },
        loadRowsAndColumns(option) {
            console.log(option);
            this.chosenRow = {};
            this.chosenColumn = {};
            this.columns = [];
            this.getRows(option.id);
        },
        updateBacklogTask() {
            let backlogTaskData = {
                id: this.task.id,
                name: this.task.name,
                badge: this.task.badge,
                description: this.task.description,
                erp_employee: this.task.erp_employee,
                erp_job_site: this.task.erp_job_site,
                deadline: this.task.deadline,
            }
            const cloneBacklogTasksData = {...backlogTaskData};
            this.asyncUpdateTask(cloneBacklogTasksData).then((data) => {
                this.triggerSuccessToast("Task Updated!");
            });
        },
        assignTask() {
            this.asyncAssignTaskToBoard(this.task.id, this.chosenRow.id, this.chosenColumn.id).then((data) => {
                this.triggerSuccessToast("Task Assigned!");
            }).catch(res => {console.log(res)});
        },
        onTypeEmployee(search, loading) {
            if(search.length) {
                    loading(true);
                    this.typeEmployee(search, loading, this);
                }
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
        typeEmployee : _.debounce(function (search, loading) {
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
        typeJobsite : _.debounce(function (search, loading) {
            this.asyncGetSomeJobSites(search).then((data) => {
                this.erpJobSites = data.data;
            })
            .catch(res => {console.log(res)})
            .then(function() {
                setTimeout(500);
                loading(false);
            });
        }, 500),
    },
};
</script>