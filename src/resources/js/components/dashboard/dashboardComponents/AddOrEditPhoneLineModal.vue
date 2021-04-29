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
                            <div v-if="isEdit">
                                <h1 class="text-2xl text-white pb-2">Edit Phone Line</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Editing existing phone line </p>
                            </div>
                            <div v-else>
                                <h1 class="text-2xl text-white pb-2">Create Phone Line</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Creating a new phone line </p>
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
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Name </span>
                                <input class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                       placeholder="John Doe"
                                       type="text"
                                       v-model="phoneLineData.name"/>
                            </label>

                            <label class="flex-1 space-y-2">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Phone </span>
                                <input class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                       placeholder="+15145550000"
                                       type="Number"
                                       v-model="phoneLineData.phone"/>
                            </label>

                        </div>

                        <div class="flex space-x-3">

                            <div class="flex-1">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Tag</span>
                                <vSelect :options="this.tags"
                                         taggable
                                         class="text-gray-700"
                                         label="name"
                                         placeholder="Choose or create a tag"
                                         style="margin-top: 7px"
                                         v-model="phoneLineData.tag">
                                    <template slot="option" slot-scope="option">
                                        <p class="inline">{{ option.name }}</p>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No tags Created
                                    </template>
                                </vSelect>
                            </div>

                            <div class="flex-1">
                                <div class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                                    Status
                                </div>

                                <div class="flex border border-gray-400 p-2 mt-2">
                                    <label class="flex items-start cursor-pointer" for="is-active">
                                        <div class="relative mt-1">
                                            <input class="hidden"
                                                   id="is-active"
                                                   type="checkbox"
                                                   v-model="phoneLineData.is_active"/>
                                            <div class="toggle__dot absolute w-5 h-5 bg-red-600 rounded-full shadow inset-y-0 left-0"></div>
                                            <div class="toggle__line w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium" v-if="phoneLineData.is_active">
                                            <p>Active</p>
                                            <small class="text-green-700">Phone Line Is Working</small>
                                        </div>
                                        <div class="ml-3 text-gray-700 font-medium" v-else>
                                            <p>Inactive</p>
                                            <small class="text-red-700">Phone Line is Disabled</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="savePhoneLine($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">

                                <span v-if="isEdit">Edit Phone Line</span>
                                <span v-else>Create Phone Line</span>
                            </button>

                        </div>

                        <button @click="deletePhoneLine($event)"
                                class="mt-4  text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none"
                                v-if="isEdit">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Phone Line
                        </button>
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
                isEdit: false,

                phoneLineData: {
                    id: null,
                    name: null,
                    phone: null,
                    tag: null,
                    is_active: true,
                },
                modalOpen: false,
                tags: ['test', 'test2'],
            };
        },

        created() {
            this.eventHub.$on("create-phone-line", (phoneLine) => {
                if (phoneLine !== undefined) {
                    this.phoneLineData = {...phoneLine};
                    this.isEdit = true;
                }
                else {
                    this.phoneLineData = {
                        id: null,
                        name: null,
                        phone: null,
                        tag: null,
                        is_active: true,
                    };
                    this.isEdit = false;
                }
                this.modalOpen = true;
            });
        },

        beforeDestroy(){
            this.eventHub.$off('create-phone-line');
        },

        mounted() {
            this.getTags();
        },

        methods: {
            savePhoneLine(event) {
                event.target.disabled = true;
                this.eventHub.$emit("save-phone-line", this.phoneLineData);
                this.modalOpen = false;
                this.getTags();
            },

            deletePhoneLine(event) {
                event.target.disabled = true;
                this.eventHub.$emit("delete-phone-line", this.phoneLineData.id);
                this.modalOpen = false;
                this.getTags();
            },

            getTags() {
                this.asyncGetTags().then((data) => {
                    this.tags = data.data;
                }).catch(res => {console.log(res)});
            }
        },
    };
</script>

<style scoped>
    .toggle__dot {
        top: -0.1rem;

        transition: all 0.1s ease-in-out;
    }

    input:checked ~ .toggle__dot {
        transform: translateX(100%);
        background-color: #059669;
    }
</style>