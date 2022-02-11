<template>
    <div>
        <!-- Container -->
        <div :class="isVerticalMode ? '' : 'flex'">

            <div :class="isVerticalMode ? 'mx-8 border-b' : 'px-8 border-r'"
                 class="py-6 space-y-2 ">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Task Options</span>

                <div v-for="(taskOption, taskOptionIndex) in taskOptions"
                     class=" grid divide-y divide-gray-400 pt-2">
                    <label :key="taskOptionIndex" class="flex">
                        <input name="task-options" type="checkbox" :value="taskOption.name"
                               class="mt-1 form-radio text-indigo-600" v-model="checkedOptions"
                               @change="removeNotNeededData">
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
                            v-model="computedSelectedBadge"
                            :options="computedBadges"
                            label="name"
                            :placeholder="$role === 'admin'?'Choose or Create' : 'Choose'"
                            style="margin-top: 7px"
                            :create-option="option => ({name: option.toLowerCase()})"
                            :taggable="$role === 'admin'">
                            class="text-gray-700">
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
                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name* </span>
                        <input
                            class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                            placeholder="Task Name"
                            type="text"
                            v-model="cloneCardData.name"/>
                    </label>
                </div>

                <div>
                    <div class="flex-grow space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Description*</span>
                        <quill-editor v-model="cloneCardData.shared_task_data.description"
                                      :options="config"
                                      ref="quill"
                                      output="html"></quill-editor>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <div class="flex-1 space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Assign employees</span>
                        <vSelect v-model="cloneCardData.assigned_to"
                                 multiple
                                 :options="members"
                                 :getOptionLabel="opt => opt.employee.user.full_name"
                                 style="margin-top: 7px"
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

                <div class="flex flex-wrap">

                    <div class="flex-1 space-y-2 pr-2">
                        <span
                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Deadline *</span>
                        <date-picker type="datetime" v-model="cloneCardData.deadline"
                                     placeholder="YYYY-MM-DD HH:mm"
                                     format="YYYY-MM-DD HH:mm"></date-picker>
                    </div>

                    <div class="space-y-2">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Time Estimate</span>
                        <counter v-model="cloneCardData.time_estimate"></counter>
                    </div>
                </div>

                <div class="flex flex-wrap"
                     v-if="checkedOptions.includes('ERP Employee') ||checkedOptions.includes('ERP Contract')">
                    <div class="flex-1 pr-2 mb-6" v-if="checkedOptions.includes('ERP Employee')">
                        <div class="flex-1 space-y-2">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Employees</span>
                            <vSelect :options="erpEmployees"
                                     multiple
                                     class="text-gray-400"
                                     label="full_name"
                                     placeholder="Select Employees"
                                     style="margin-top: 7px"
                                     @search="onTypeEmployee"
                                     v-model="cloneCardData.shared_task_data.erp_employees">
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

                    <div class="flex-1 pr-2 mb-6" v-if="checkedOptions.includes('ERP Contract')">
                        <div class="flex-1 space-y-2">
                            <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">ERP Contracts</span>
                            <vSelect :options="erpContracts"
                                     multiple
                                     class="text-gray-400"
                                     label="contract_identifier"
                                     placeholder="Select Contracts"
                                     style="margin-top: 7px"
                                     @search="onTypeContract"
                                     v-model="cloneCardData.shared_task_data.erp_contracts">
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

                <div class="flex-1" v-if="checkedOptions.includes('Upload Files')">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Files: </span>
                    <file-pond
                        class="mt-2"
                        name="filepond"
                        ref="pond"
                        allow-multiple="true"
                        :files="formattedFiles"
                        credits=false
                        maxFileSize="5MB"
                        maxTotalFileSize="10MB"
                        imagePreviewHeight="100"
                        @updatefiles="updateFiles"
                    />
                </div>

                <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                    <button
                        @click="cancel()"
                        class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                        type="button">
                        Cancel
                    </button>
                    <button @click="updateTaskDataInTaskModal()"
                            class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                            type="button">

                        <span>Update Task</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
import vSelect from "vue-select";
import Avatar from "../Avatar.vue";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import _ from 'lodash';
import Counter from "../Counter";
import {componentNames} from "../../../enums/componentNames";

export default {
    mixins: [ajaxCalls, helperFunctions],

    inject: ["eventHub"],
    components: {
        vSelect,
        Avatar,
        Counter
    },
    props: {
        cardData: Object,
        isVerticalMode: {
            type: Boolean,
            default: false
        },
        sourceFrom: String,
    },
    data() {
        return {
            cloneCardData: {},
            originalTaskDescription: '',
            taskOptions: [
                {name: 'ERP Employee',},
                {name: 'ERP Contract',},
                {name: 'Upload Files',}
            ],
            checkedOptions: [],
            config: {
                readOnly: false,
                placeholder: 'Describe your task in greater detail',
                theme: 'snow',
                modules: {
                    toolbar: [['bold', 'italic', 'underline', 'strike'],
                        ['code-block'],
                        [{'list': 'ordered'}, {'list': 'bullet'}, {'list': 'check'}],
                        [{'script': 'sub'}, {'script': 'super'}],
                        [{'color': []}, {'background': []}],
                        [{'align': []}],
                        ['clean']
                    ]
                }
            },

            badges: [],
            erpEmployees: [],
            erpContracts: [],
            tasks: [],
            formattedFiles: [],
            members: [],
        };
    },
    created() {
        this.getAndSetInitialData();
        this.eventHub.$on("update-add-task-data-with-group-data", (cardData) => {
            this.cloneCardData = cardData;
            this.getAndSetInitialData();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('update-add-task-data-with-group-data');
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
            get() {
                if (_.isEmpty(this.cloneCardData.badge)) {
                    return null
                } else {
                    return this.cloneCardData.badge
                }
            },
            set(val) {
                if (val === null) val = {};
                this.cloneCardData.badge = val;
            }
        },
    },
    methods: {
        getAndSetInitialData() {
            this.cloneCardData = _.cloneDeep(this.cardData);
            this.checkedOptions = [];

            if (this.cloneCardData.deadline) {
                this.cloneCardData.deadline = new Date(this.cloneCardData.deadline);
                this.checkedOptions.push('Deadline');
            }
            if (this.cloneCardData.shared_task_data.erp_contracts.length)
                this.checkedOptions.push('ERP Contract');
            if (this.cloneCardData.shared_task_data.erp_employees.length)
                this.checkedOptions.push('ERP Employee');
            if (this.cloneCardData.task_files.length)
                this.checkedOptions.push('Upload Files');

            this.getErpEmployees();
            this.getContracts();
            this.getBadges();
            this.getMembers();
            this.formatFilesForFilepond();
        },

        getMembers() {
            this.asyncGetMembers(this.cloneCardData.board_id).then((data) => {
                this.members = data.data;
            });
        },

        formatFilesForFilepond() {
            this.formattedFiles = []
            this.cloneCardData.task_files.forEach((file, index) => {

                this.formattedFiles.push({
                    source: file.full_url,
                    options: {
                        metadata: {
                            path: file.task_file_url,
                            id: file.id
                        },
                    }
                })

            })
        },

        updateFiles(files) {
            // all new files to upload
            this.cloneCardData.filesToUpload = files.filter(function (file) {
                // all new files don't have ids, and we don't want any files over 5mb
                return (file.getMetadata('id') === undefined && file.fileSize < 5000000);
            });

            // getting all the existing file ids from files metadata
            let existingFileIds = files.map(file => file.getMetadata('id')).filter(Number);

            // filtering the task_files to not include remove files
            this.cloneCardData.task_files = this.cloneCardData.task_files.filter(function (file) {
                return existingFileIds.includes(file.id);
            });
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
            }).then(function () {
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
            }).then(function () {
                setTimeout(500);
                loading(false);
            });
        }, 500),

        updateTaskDataInTaskModal() {
            let isValid = this.validateCreateOrUpdateTaskEvent(this.cloneCardData, this.checkedOptions)

            if (isValid) {
                if (this.sourceFrom === componentNames.KanbanTaskModal) {
                    this.eventHub.$emit("update-task-card-data-from-kanban", this.cloneCardData);
                    this.eventHub.$emit("close-task-modal");
                }
                if (this.sourceFrom === componentNames.TaskPane) {
                    this.eventHub.$emit("update-task-card-data-from-task-pane", this.cloneCardData);
                }
            }
        },

        cancel() {
            if (this.sourceFrom === componentNames.KanbanTaskModal) {
                this.eventHub.$emit("close-task-modal");
            }
            if (this.sourceFrom === componentNames.TaskPane) {
                this.triggerInfoToast('Resetting Task Data')
                this.$refs.quill.quill.clipboard.dangerouslyPasteHTML(this.cardData.shared_task_data.description, 'user'); //resetting quill content
                this.getAndSetInitialData();
            }
        },

        getBadges() {
            this.asyncGetBadges().then((data) => {
                this.badges = data.data;
            })
        },

        getErpEmployees() {
            this.asyncGetAllUsers().then((data) => {
                this.erpEmployees = data.data;
            })
        },

        getContracts() {
            this.asyncGetAllContracts().then((data) => {
                this.erpContracts = data.data;
            })
        },

        getTasks() {
            this.asyncGetAllTasks().then((data) => {
                this.tasks = data.data;
            })
        },

        removeNotNeededData() {
            if (!this.checkedOptions.includes('Deadline')) {
                this.cloneCardData.deadline = null
            }
            if (!this.checkedOptions.includes('ERP Employee')) {
                this.cloneCardData.erp_employee = null
            }
            if (!this.checkedOptions.includes('ERP Contract')) {
                this.cloneCardData.erp_contract = null
            }
        },
    },
};
</script>
