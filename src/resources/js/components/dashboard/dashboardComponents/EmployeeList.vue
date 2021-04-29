<template>
    <div class="employeesList">
        <h2 class="text-2xl font-semibold py-5">Employees List</h2>

        <div class="bg-gray-100">
            <div class="p-5">
                <div class="my-2 flex sm:flex-row flex-col">
                    <div class="flex flex-row mb-1 sm:mb-0">
                        <div class="relative">
                            <select @change="paginationIndex = 0"
                                    class="appearance-none h-full rounded-l border block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                                    v-model="paginationStep">
                                <option>5</option>
                                <option>10</option>
                                <option>20</option>
                            </select>
                        </div>
                    </div>
                    <div class="block relative">
                        <span class="h-full absolute inset-y-0 left-0 flex items-center pl-2">
                          <i class="text-gray-400 fas fa-search"></i>
                        </span>
                        <input class="appearance-none rounded-r rounded-l sm:rounded-l-none border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none"
                               placeholder="Search"
                               v-model="filter"/>
                    </div>
                </div>
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                    <div class="inline-block min-w-full shadow-md rounded-lg overflow-hidden">
                        <table class="min-w-full leading-normal">
                            <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Role
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Phone Number
                                </th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>

                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Edit
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <template v-for="(employee, employeeIndex) in filtered">
                                <template v-if="
                                      employeeIndex >= paginationIndex &&
                                      employeeIndex < paginationIndex + paginationStep
                                    ">
                                    <tr :key="employeeIndex">
                                        <td class="px-5 py-5 bg-white text-sm">
                                            <div class="flex items-center">
                                                <avatar :name="employee.name" :size="6" class="mr-3"></avatar>
                                                <div class="ml-3">
                                                    <p class="text-gray-900 whitespace-no-wrap">
                                                        {{ employee.name }} </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-5 bg-white text-sm">
                                            <p class="text-gray-900 whitespace-no-wrap">
                                                {{ employee.role }} </p>
                                        </td>
                                        <td class="px-5 py-5 bg-white text-sm">
                                            <p class="text-gray-900 whitespace-no-wrap">
                                                {{ employee.phone }} </p>
                                        </td>
                                        <td class="px-5 py-5 bg-white text-sm">
                                            <span :class="employee.is_active? 'text-green-900': 'text-red-900 '"
                                                  class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                                <span :class="employee.is_active ? 'bg-green-200' : 'bg-red-200'"
                                                      aria-hidden=""
                                                      class="absolute inset-0 opacity-50 rounded-full">
                                                </span>
                                                <span class="relative" v-if="employee.is_active">Active</span>
                                                <span class="relative" v-if="!employee.is_active">Inactive</span>
                                            </span>
                                        </td>

                                        <td class="px-5 py-5 bg-white text-sm">
                                            <a @click="editEmployee(employeeIndex)"
                                               class="cursor-pointer px-2 text-gray-400 hover:text-gray-600 transition duration-300 ease-in-out focus:outline-none">
                                                <i class="fas fa-edit"></i> </a>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            </tbody>
                        </table>
                        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                            <div class="text-xs xs:text-sm text-gray-900">
                                Showing {{ Number(paginationIndex) + 1 }} to
                                <span v-if="paginationIndex + paginationStep <= filtered.length">
                                    {{ Number(paginationIndex) + Number(paginationStep) }}
                                </span>
                                <span v-else>
                                    {{ filtered.length }}
                                </span>
                                of {{ filtered.length }} Entries
                            </div>
                            <div class="inline-flex mt-2 xs:mt-0">
                                <button @click="updatePaginationIndex(paginationIndex - paginationStep)"
                                        class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l">
                                    Prev
                                </button>
                                <button @click="updatePaginationIndex(paginationIndex + paginationStep)"
                                        class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Avatar from "../../global/Avatar.vue";

    export default {
        inject: ["eventHub"],
        components: {
            Avatar,
        },

        props: {
            employees: {
                type: Array,
                default: null,
            },
        },

        data() {
            return {
                filter: "",
                paginationIndex: 0,
                paginationStep: 5,
            };
        },

        computed: {
            filtered() {
                const regex = new RegExp(this.filter, "i");
                return this.employees.filter((e) => {
                    this.paginationIndex = 0;
                    return !this.filter || e.name.match(regex) || e.phone.match(regex);
                });
            },
        },
        methods: {
            editEmployee(index) {
                this.eventHub.$emit("create-employee", this.filtered[index]);
            },
            updatePaginationIndex(newIndex) {
                if (newIndex < 0) newIndex = 0;
                else if (newIndex < this.filtered.length) this.paginationIndex = newIndex;
            },
        },
    };
</script>


