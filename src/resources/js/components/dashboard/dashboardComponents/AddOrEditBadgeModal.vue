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
                    <!-- Heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">
                            <div v-if="isEdit">
                                <h1 class="text-2xl text-white pb-2">Edit Kanban Badge</h1>
                                <p class="text-sm font-medium leading-5 text-gray-400">
                                    Editing an existing kanban badge </p>
                            </div>
                            <div v-else>
                                <h1 class="text-2xl text-white pb-2">Create Badge</h1>
                                <p class="text-sm font-medium leading-5 text-gray-400">
                                    Creating a new kanban badge </p>
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
                        <div class="flex space-x-3">
                            <label class="flex-1 space-y-2">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Name </span>
                                <input
                                    class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                    placeholder="Badge Name"
                                    type="text"
                                    v-model="kanbanData.name"/>
                            </label>

                        </div>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveBadge($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">

                                <span v-if="isEdit">Edit Kanban Badge</span>
                                <span v-else>Create Badge</span>
                            </button>

                        </div>

                        <button @click="deleteBadge($event)"
                                class="mt-4  text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none"
                                v-if="isEdit">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Kanban Badge
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

export default {
    inject: ["eventHub"],

    components: {
        vSelect,
    },

    mixins: [ajaxCalls],

    data() {
        return {
            isEdit: false,

            kanbanData: {
                id: null,
                name: null,
            },
            modalOpen: false,
        };
    },

    created() {
        this.eventHub.$on("create-badge", (badge) => {
            if (badge !== undefined) {
                this.kanbanData = {...badge};
                this.isEdit = true;
            } else {
                this.isEdit = false;
            }
            this.modalOpen = true;
        });
    },

    beforeDestroy() {
        this.eventHub.$off('create-badge');
    },

    methods: {
        saveBadge(event) {
            event.target.disabled = true;
            this.eventHub.$emit("save-badge", this.kanbanData);
            this.modalOpen = false;
        },

        deleteBadge(event) {
            this.$swal({
                icon: 'info',
                title: 'Delete ' + this.kanbanData.name + '?',
                text: 'This will permanently delete the badge.',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.disabled = true;
                    this.eventHub.$emit("delete-badge", this.kanbanData.id);
                    this.modalOpen = false;
                }
            });
        },
    },
};
</script>
