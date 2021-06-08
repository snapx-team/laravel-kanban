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
                                <h1 class="text-2xl text-white pb-2">Edit Xkanban Employee</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Editing existing Xkanban employee </p>
                            </div>
                            <div v-else>
                                <h1 class="text-2xl text-white pb-2">Create Xkanban Employee</h1>
                                <p class="text-sm font-medium leading-5 text-gray-500">
                                    Creating a new employee for Xkanban</p>
                            </div>
                        </div>
                        <div>
                            <button @click="modalOpen = false"
                                    class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                    type="button">
                                <i class="fas fa-times"></i>
                                <div class="text-xs font-semibold text-center leading-3 uppercase">
                                    Esc
                                </div>
                            </button>
                        </div>
                    </div>
                    <!-- Task container -->
                    <form class="space-y-6 overflow-auto px-8 py-6">
                        <div class="flex space-x-3">
                            <div class="space-y-2 flex-1">

                                <div v-if="isEdit">
                                    <p class="block text-lg font-bold leading-4 tracking-wide uppercase text-gray-600">
                                        {{ employeeData.user.full_name }}
                                    </p>
                                </div>

                                <div v-else>
                                    <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                                        Employees
                                    </span>
                                    <vSelect :options="allUsers"
                                             class="text-gray-400"
                                             label="full_name"
                                             multiple
                                             placeholder="Select Members"
                                             style="margin-top: 7px"
                                             v-model="employeeData.selectedUsers">
                                        <template slot="option" slot-scope="option">
                                            <avatar :name="option.full_name" :size="4" class="mr-3 m-1 float-left"></avatar>
                                            <p class="inline">{{ option.full_name }}</p>
                                        </template>
                                        <template #no-options="{ search, searching, loading }">
                                            No result .
                                        </template>
                                    </vSelect>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <div class="space-y-2 flex-1">
                                <div class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                                    Role
                                </div>

                                <div class="border border-gray-400 p-2 rounded grid divide-y divide-gray-400">
                                    <label class="flex items-start p-2">
                                        <input class="mt-1 form-radio text-indigo-600"
                                               name="role"
                                               type="radio"
                                               v-model="employeeData.role"
                                               value="employee"/>
                                        <div class="ml-3 text-gray-700 font-medium">
                                            <p>Employee</p>
                                            <small class="text-indigo-700">Can create and interact with tasks.</small>
                                        </div>
                                    </label>

                                    <label class="flex items-start p-2">
                                        <input checked
                                               class="mt-1 form-radio text-indigo-600"
                                               name="role"
                                               type="radio"
                                               v-model="employeeData.role"
                                               value="admin"/>
                                        <div class="ml-3 text-gray-700 font-medium">
                                            <p>Admin</p>
                                            <small class="text-indigo-700">Create/Edit/Delete access for employees and
                                                                           kanban boards.
                                            </small>
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
                            <button @click="saveEmployee($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">
                                <span v-if="isEdit">Edit Employee</span>
                                <span v-else>Create Employee</span>
                            </button>
                        </div>
                        <button @click="deleteEmployee($event)"
                                class="mt-4  text-sm text-red-600 hover:text-red-800 transition duration-300 ease-in-out focus:outline-none"
                                v-if="isEdit">
                            <i class="fas fa-trash mr-2"></i>
                            Delete Employee
                        </button>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
    import vSelect from "vue-select";
    import Avatar from "../../global/Avatar";
    import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],
        components: {
            vSelect,
            Avatar,
        },
        mixins: [ajaxCalls],

        data() {
            return {
                isEdit: false,

                employeeData: {
                    id: null,
                    role: "employee",
                    selectedUsers: [],
                    user: null,
                },
                modalOpen: false,
                allUsers: [],

            };
        },

        created() {
            this.eventHub.$on("create-kanban-employees", (employee) => {
                if (employee !== undefined) {
                    this.employeeData.role = employee.role;
                    this.employeeData.id =  employee.id;
                    this.employeeData.selectedUsers = [{id : employee.user.id}];
                    this.employeeData.user = employee.user;
                    this.isEdit = true;
                }
                else {
                    this.employeeData = {
                        id: null,
                        role: "employee",
                    };
                    this.isEdit = false;
                }
                this.modalOpen = true;
            });
        },

        beforeDestroy() {
            this.eventHub.$off('create-kanban-employee');
        },

        mounted(){
            this.asyncGetAllUsers().then((data) => {
                this.allUsers = data.data;
                console.log(this.allUsers );
            }).catch(res => {console.log(res)});
        },

        methods: {
            saveEmployee(event) {
                event.target.disabled = true;
                this.eventHub.$emit("save-employee", this.employeeData);
                this.modalOpen = false;
            },
            deleteEmployee(event) {
                event.target.disabled = true;
                this.eventHub.$emit("delete-kanban-employee", this.employeeData.id);
                this.modalOpen = false;
            },
        },
    };
</script>
