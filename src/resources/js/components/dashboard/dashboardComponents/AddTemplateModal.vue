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
                            <div v-if="isEdit">
                                <h1 class="text-2xl text-white pb-2">Edit Template</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Editing an existing template </p>
                            </div>
                            <div v-else>
                                <h1 class="text-2xl text-white pb-2">Create Template</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Creating a new template</p>
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
                    <div class="space-y-6 overflow-auto px-8 py-6">
                        <div class="space-x-3">

                            <label class="flex-1 space-y-2">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Template Name </span>
                                <input
                                    class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                    placeholder="Name to identify template"
                                    type="text"
                                    v-model="templateData.name"/>
                            </label>

                            <div class="flex my-6">
                                <div class="pr-6 py-2  space-y-2 border-r whitespace-nowrap">
                                    <span
                                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Task Options</span>
                                    <div class=" grid divide-y divide-gray-400 pt-2"
                                         v-for="(taskOption, taskOptionIndex) in taskOptions">
                                        <label :key="taskOptionIndex" class="flex">
                                            <input :value="taskOption.name"
                                                   class="mt-1 form-radio text-indigo-600"
                                                   name="task-options"
                                                   type="checkbox"
                                                   v-model="templateData.checkedOptions">
                                            <div class="ml-3 text-gray-700 font-medium">
                                                <p>{{ taskOption.name }}</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="pl-6 py-2 space-y-4">

                                    <label class="flex-1 space-y-2">
                                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Task Name</span>
                                        <input
                                            class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                            placeholder="This will prefill task name"
                                            type="text"
                                            v-model="templateData.task_name"/>
                                    </label>

                                    <div>
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
                                            <span :style="`color: hsl( ${option.hue} , 45%, 90%);`"
                                                  class="fa fa-circle mr-4"></span>
                                                {{ option.name }}
                                            </template>
                                            <template #no-options="{ search, searching, loading }">
                                                No result .
                                            </template>
                                        </vSelect>
                                    </div>

                                    <div>
                                        <span
                                            class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">Description</span>
                                        <quill-editor :options="config"
                                                      output="html"
                                                      v-model="templateData.description"></quill-editor>
                                    </div>

                                    <div class="flex space-x-3">
                                        <div class="flex-1 space-y-2">
                                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Select Kanban Boards</span>
                                            <vSelect :options="boards"
                                                     class="text-gray-400"
                                                     label="name"
                                                     multiple
                                                     placeholder="Select one or more kanban boards"
                                                     style="margin-top: 7px"
                                                     v-model="templateData.boards">
                                                <template slot="option" slot-scope="option">
                                                    {{ option.name }}
                                                </template>
                                                <template #no-options="{ search, searching, loading }">
                                                    No result .
                                                </template>
                                            </vSelect>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveTemplate($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">

                                <span v-if="isEdit">Edit Template</span>
                                <span v-else>Create Template</span>
                            </button>

                        </div>

                        <button @click="deleteTemplate($event)"
                                class="mt-4  text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none"
                                v-if="isEdit">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Template
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

import vSelect from "vue-select";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import _ from "lodash";

export default {
    inject: ["eventHub"],

    components: {
        vSelect,
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
            isEdit: false,

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

            templateData: {
                id: null,
                name: null,
                task_name: null,
                badge: {},
                checkedOptions: [],
                description: null,
                boards: [],
            },
            modalOpen: false,

            taskOptions: [
                {name: 'Deadline',},
                {name: 'ERP Employee',},
                {name: 'ERP Job Site',},
            ],

            badges: [],
        };
    },

    created() {
        this.eventHub.$on("create-template", (template) => {
            if (template !== undefined) {

                console.log(template);
                this.templateData = {...template};
                this.templateData.checkedOptions = template.unserialized_options;
                // this.templateData.boards = template.boards;

                this.isEdit = true;
            } else {
                this.isEdit = false;
                this.templateData = {
                    id: null,
                    name: null,
                    task_name: null,
                    badge: {},
                    checkedOptions: [],
                    description: null,
                    boards: []
                }
            }
            this.modalOpen = true;
            this.getBadges();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('create-template');
    },

    methods: {
        saveTemplate(event) {
            event.target.disabled = true;
            this.eventHub.$emit("save-template", this.templateData);
            this.modalOpen = false;
        },

        deleteTemplate(event) {
            event.target.disabled = true;
            this.eventHub.$emit("delete-template", this.templateData.id);
            this.modalOpen = false;
        },

        getBadges() {
            this.asyncGetBadges().then((data) => {
                this.badges = data.data;
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
                computedBadges.hue = this.generateHslColorWithText(badge.name);
                return computedBadges;
            })
        },

        computedSelectedBadge: {
            get() {
                if (_.isEmpty(this.templateData.badge)) {
                    return null
                } else {
                    return this.templateData.badge
                }
            },
            set(val) {
                if (val === null) val = {};
                this.templateData.badge = val;
            }
        },
    }
};
</script>
