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
                            <h1 class="text-2xl text-white pb-2" v-if="isEdit">Edit Row and Columns</h1>
                            <h1 class="text-2xl text-white pb-2" v-else>Add Row and Columns</h1>

                            <p class="text-sm font-medium leading-5 text-red-400" v-if="isEdit">
                                Edit or Delete row and columns. Deleting Row will delete all subsequent columns. Any
                                column deleted will also delete tasks associated to them. </p>

                            <p class="text-sm font-medium leading-5 text-gray-500" v-else>
                                Create new row and add any column you want associated to it </p>
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
                    <div class="space-y-5 overflow-auto px-8 py-6">

                        <div class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                            Row Title
                        </div>

                        <input
                            class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                            placeholder="Write a title for the column"
                            type="text"
                            v-model="rowData.name"/>

                        <hr>

                        <div class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 ">
                            Column Titles
                        </div>
                        <div :key="columnIndex" class="space-y-6" v-for="(column, columnIndex) in rowData.columns">
                            <div class="flex ">

                                <input
                                    class="px-3 py-2 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                    placeholder="Write a title for the column"
                                    type="text"
                                    v-model="column.name"/>

                                <button @click="decrement(columnIndex)"
                                        class="w-24 text-sm text-gray-600 hover:text-gray-800 transition duration-300 ease-in-out focus:outline-none"
                                        v-if="column.id === null">
                                    <span class="px-4">Remove</span>
                                </button>

                                <button @click="decrement(columnIndex)"
                                        class="w-24 text-sm text-gray-600 hover:text-gray-800 transition duration-300 ease-in-out focus:outline-none"
                                        v-else>
                                    <span class="px-4 text-red-600 w-12">Delete</span>
                                </button>

                            </div>

                        </div>

                        <button @click="increment"
                                class="mt-4 -ml-1 flex items-center text-sm font-medium text-gray-600 hover:text-gray-800 transition duration-300 ease-in-out focus:outline-none">
                            <i class="fas fa-plus"></i>
                            <span class="ml-1 font-bold">Add Column</span>
                        </button>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveRow()"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">
                                Save Row
                            </button>
                        </div>

                        <button @click="deleteRow()"
                                class="mt-4  text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none"
                                v-if="isEdit">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Row
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

export default {
    inject: ["eventHub"],

    data() {
        return {
            modalOpen: false,
            isSavingColumn: false,
            isEdit: false,
            rowData: {
                boardId: null,
                name: null,
                rowIndex: null,
                rowId: null,
                columns: [],
            }
        };
    },

    created() {
        this.eventHub.$on("create-row-and-columns", (rowData) => {

            this.rowData.name = rowData.rowName;
            this.rowData.rowIndex = rowData.rowIndex;
            this.rowData.rowId = rowData.rowId;
            this.rowData.boardId = rowData.boardId;

            this.rowData.columns = rowData.rowColumns.map(a => ({...a}));

            this.isEdit = rowData.rowId !== null;
            this.modalOpen = true;
        });

    },

    beforeDestroy() {
        this.eventHub.$off('create-row-and-columns');
    },

    methods: {
        increment() {
            this.rowData.columns.push(
                {
                    id: null,
                    name: null,
                }
            );
        },
        decrement(index) {
            this.rowData.columns.splice(index, 1);
        },
        saveRow() {
            this.eventHub.$emit("save-row-and-columns", this.rowData);
            this.modalOpen = false;
            this.rowData.rowIndex = null;
        },
        deleteRow() {
            this.eventHub.$emit("delete-row", this.rowData);
            this.modalOpen = false;
            this.rowData.rowIndex = null;
        },
    },
};
</script>
