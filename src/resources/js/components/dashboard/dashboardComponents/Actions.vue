<template>
    <div class="Actions">
        <h2 class="text-2xl font-semibold py-5">Actions</h2>

        <div class="flex flex-wrap bg-gray-100">
            <div class="w-full p-6" :class="{'xl:w-1/2':$role === 'admin'}">
                <div @click="createBacklogTask()"
                     class="cursor-pointer bg-yellow-100 hover:bg-yellow-200 transition duration-300 ease-in-out border-b-4 border-yellow-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-yellow-500 text-center">
                                <i class="fas fa-tasks fa-2x fa-inverse w-8 h-8"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">
                                Add Backlog Task </h5>
                            <h3 class="text-sm text-gray-600">
                                single & multi-board</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full xl:w-1/2 p-6" v-if="$role === 'admin'">
                <div @click="createBoard()"
                     class="cursor-pointer bg-blue-100 hover:bg-blue-200 transition duration-300 ease-in-out border-b-4 border-blue-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-blue-500 text-center">
                                <i class="fa fa-th-large fa-2x fa-inverse w-8 h-7"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">
                                Add A Board </h5>
                            <h3 class="text-sm text-gray-600">
                                {{ boardsLength }} boards total </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full xl:w-1/2 p-6" v-if="$role === 'admin'">
                <div @click="createEmployee()"
                     class="cursor-pointer bg-green-100 hover:bg-green-200 transition duration-300 ease-in-out border-b-4 border-green-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-green-500 text-center">
                                <i class="fa fa-user fa-2x fa-inverse w-8 h-7"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">Add Kanyeban Employee</h5>
                            <h3 class="text-sm text-gray-600">
                                {{ employeesLength }} employees total </h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full xl:w-1/2 p-6" v-if="$role === 'admin'">
                <div @click="createTemplate()"
                     class="cursor-pointer bg-pink-100 hover:bg-pink-200 transition duration-300 ease-in-out border-b-4 border-pink-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-pink-500 text-center">
                                <i class="fas fa-th-list fa-2x fa-inverse w-8 h-8"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">Create Template</h5>
                            <h3 class="text-sm text-gray-600">
                                create new template </h3>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    inject: ["eventHub"],

    props: {
        boardsLength: {
            type: Number,
            default: 0,
        },
        employeesLength: {
            type: Number,
            default: 0,
        },
    },
    methods: {
        createEmployee() {
            this.eventHub.$emit("create-kanban-employees");
        },
        createBoard() {
            this.eventHub.$emit("create-board");
        },
        createBacklogTask() {
            this.eventHub.$emit("create-backlog-task");
        },
        createTemplate() {
            this.eventHub.$emit("create-template");
        },
    },
};
</script>


