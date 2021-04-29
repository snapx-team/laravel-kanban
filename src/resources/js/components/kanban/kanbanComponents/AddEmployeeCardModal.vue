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
                    <!-- Card heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">
                            <h1 class="text-2xl text-white pb-2">Add Employees To Shift</h1>
                            <p class="text-sm font-medium leading-5 text-gray-500">
                                <em> Adding emloyees to the shift on
                                    <b>{{cardData.selectedRowName}}</b>
                                     between
                                    <b>{{cardData.selectedColumnName}}</b>
                                </em>
                            </p>
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
                    <!-- Card container -->
                    <div class="space-y-6 overflow-auto px-8 py-6">
                        <!-- Flow options -->
                        <div class="space-y-6">
                            <div class="flex-1">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Employees</span>
                                <vSelect :getOptionLabel="option => option.employee.name"
                                         :options="this.kanbanData.members"
                                         class="text-gray-700"
                                         multiple
                                         style="margin-top: 7px"
                                         v-model="cardData.employeesSelected">
                                    <template slot="option" slot-scope="option">
                                        <avatar :name="option.employee.name"
                                                :size="4"
                                                class="mr-3 m-1 float-left"></avatar>
                                        <p class="inline">{{ option.employee.name }}</p>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No result .
                                    </template>
                                </vSelect>
                            </div>
                        </div>

                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveCards($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">
                                Add Employees
                            </button>
                        </div>

                        <figure class="bg-gray-100 rounded-xl overflow-y-auto">
                            <div class="pt-6 p-8 space-y-4">
                                <figcaption class="font-medium">
                                    <p class="pb-5 text-gray-500">
                                        There are {{ getNumberOfEmployeesInShift }} employee cards on this shift </p>

                                    <label class="block pb-5">
                                        <input class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                               placeholder="Filter employees"
                                               type="text"
                                               v-model="filter"/>
                                    </label>

                                    <p class="text-gray-900" v-if="filtered.length === 0">
                                        No matching results </p>

                                    <template v-for="(employeeCard, employeeIndex) in filtered">
                                        <div :key="employeeIndex"
                                             class="flex justify-between items-center border-b p-1">
                                            <div class="flex items-center">
                                                <avatar :name="employeeCard.employee.name"
                                                        :size="6"
                                                        class="mr-3"></avatar>
                                                <span class="py-2 mr-3 text-gray-600">{{employeeCard.employee.name}}</span>
                                            </div>
                                            <a @click="deleteCard(employeeCard)"
                                               class="cursor-pointer text-gray-400 text-sm hover:text-gray-600 transition duration-300 ease-in-out">
                                                remove from shift
                                            </a>
                                        </div>
                                    </template>
                                </figcaption>
                            </div>
                        </figure>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
    import vSelect from "vue-select";
    import Avatar from "../../global/Avatar.vue";

    export default {
        inject: ["eventHub"],
        components: {
            vSelect,
            Avatar,
        },
        props: {
            kanbanData: Object,
        },
        data() {
            return {
                filter: "",
                modalOpen: false,
                isSavingEmployeeCard: false,
                cardData: {
                    employeesSelected: null,
                    selectedRowIndex: null,
                    selectedColumnIndex: null,
                    selectedRowName: null,
                    selectedColumnName: null,
                    columnId: null,
                },
            };
        },

        created() {
            this.eventHub.$on("create-employee-cards", (cardData) => {
                this.openModal(cardData);
            });
        },

        beforeDestroy(){
            this.eventHub.$off('create-employee-cards');
        },
        computed: {
            filtered() {
                const regex = new RegExp(this.filter, "i");
                return this.kanbanData.rows[this.cardData.selectedRowIndex].columns[
                    this.cardData.selectedColumnIndex
                    ].employee_cards.filter((e) => {
                    return !this.filter || e.employee.name.match(regex);
                });
            },
            getNumberOfEmployeesInShift() {
                return this.kanbanData.rows[this.cardData.selectedRowIndex].columns[
                    this.cardData.selectedColumnIndex
                    ].employee_cards.length;
            },
        },
        methods: {
            saveCards(event) {
                event.target.disabled = true;
                this.eventHub.$emit("save-employee-cards", this.cardData);
                this.modalOpen = false;
                this.cardData.employeesSelected = null;
            },

            deleteCard(selectedCardData) {
                this.eventHub.$emit("delete-employee-cards", {
                    selectedCardData,
                    selectedRowIndex: this.cardData.selectedRowIndex,
                    selectedColumnIndex: this.cardData.selectedColumnIndex
                });
            },

            openModal(cardData) {
                this.cardData.selectedRowIndex = cardData.rowIndex;
                this.cardData.selectedRowName = cardData.rowName;
                this.cardData.selectedColumnIndex = cardData.columnIndex;
                this.cardData.selectedColumnName = cardData.columnName;
                this.cardData.columnId = cardData.columnId;
                this.cardData.employeeId = cardData.employeeId;

                this.modalOpen = true;
            },
        },
    };
</script>
