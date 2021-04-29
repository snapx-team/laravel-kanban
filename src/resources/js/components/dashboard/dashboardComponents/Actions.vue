<template>
    <div class="Actions">
        <h2 class="text-2xl font-semibold py-5">Actions</h2>

        <div class="flex flex-wrap bg-gray-100">
            <div class="w-full lg:w-1/2 p-6">
                <div @click="createPhoneLine()"
                     class="cursor-pointer bg-blue-100 hover:bg-blue-200 transition duration-300 ease-in-out border-b-4 border-blue-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-blue-500 text-center">
                                <i class="fa fa-phone fa-2x fa-inverse w-8 h-8"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">
                                Add Phone Line Schedule </h5>
                            <h3 class="text-sm text-gray-600">
                                {{ phoneLinesLength }} phone lines total </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w-1/2 p-6">
                <div @click="createEmployee()"
                     class="cursor-pointer bg-green-100 hover:bg-green-200 transition duration-300 ease-in-out border-b-4 border-green-500 rounded-lg shadow-xl p-5">
                    <div class="flex flex-row items-center">
                        <div class="flex-shrink pr-4">
                            <div class="rounded-full p-5 bg-green-500 text-center">
                                <i class="fa fa-user fa-2x fa-inverse w-8 h-8"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-right md:text-center">
                            <h5 class="font-bold uppercase text-gray-800">Add Employee</h5>
                            <h3 class="text-sm text-gray-600">
                                {{ employeesLength }} employees total </h3>
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
            phoneLinesLength: {
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
                this.eventHub.$emit("create-employee");
            },
            createPhoneLine() {
                this.eventHub.$emit("create-phone-line");
            },
        },
    };
</script>


