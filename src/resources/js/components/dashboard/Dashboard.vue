<template>
    <div v-if="dashboardData !== null">
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>

        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">Dashboard</h3>
        </div>

        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <actions :employeesLength="dashboardData.employees.length"
                     :phoneLinesLength="dashboardData.phoneLines.length"></actions>
            <phone-line-list :class="{ 'animate-pulse': loadingPhoneLine }"
                             :phoneLines="dashboardData.phoneLines"></phone-line-list>

            <employee-list :class="{ 'animate-pulse': loadingEmployee }"
                           :employees="dashboardData.employees"></employee-list>
            <add-or-edit-employee-modal></add-or-edit-employee-modal>
            <add-or-edit-phone-line-modal></add-or-edit-phone-line-modal>
        </div>
    </div>
</template>

<script>
    import EmployeeList from "./dashboardComponents/EmployeeList.vue";
    import PhoneLineList from "./dashboardComponents/PhoneLineList.vue";
    import Actions from "./dashboardComponents/Actions.vue";
    import AddOrEditEmployeeModal from "./dashboardComponents/AddOrEditEmployeeModal.vue";
    import AddOrEditPhoneLineModal from "./dashboardComponents/AddOrEditPhoneLineModal.vue";
    import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],
        components: {
            EmployeeList,
            PhoneLineList,
            Actions,
            AddOrEditEmployeeModal,
            AddOrEditPhoneLineModal,
        },

        mixins: [ajaxCalls],

        mounted() {
            this.getDashboardData();
        },

        data() {
            return {
                filter: "",
                paginationIndex: 0,
                paginationStep: 5,
                dashboardData: null,
                loadingPhoneLine: false,
                loadingEmployee: false
            };
        },

        created() {
            this.eventHub.$on("save-employee", (employeeData) => {
                this.saveEmployee(employeeData);
            });
            this.eventHub.$on("save-board", (kanbanData) => {
                this.savePhoneLine(kanbanData);
            });
            this.eventHub.$on("delete-board", (phoneLineId) => {
                this.deleteBoard(phoneLineId);
            });
            this.eventHub.$on("delete-employee", (employeeId) => {
                this.deleteEmployee(employeeId);
            });
        },

        beforeDestroy(){
            this.eventHub.$off('save-employee');
            this.eventHub.$off('save-board');
            this.eventHub.$off('delete-board');
            this.eventHub.$off('delete-employee');
        },

        methods: {
            saveEmployee(employeeData) {
                this.loadingEmployee = true;
                const cloneEmployeeData = {...employeeData};
                this.asyncCreateEmployee(cloneEmployeeData).then(res => {
                    this.asyncGetEmployees().then((data) => {
                        this.dashboardData.employees = data.data;
                        this.loadingEmployee = false;
                    }).catch(res => {console.log(res)});
                });
            },

            savePhoneLine(kanbanData) {
                this.loadingPhoneLine = true
                const clonekanbanData = {...kanbanData};
                this.asynccreateBoard(clonekanbanData).then(res => {
                    this.eventHub.$emit("update-side-bar");
                    this.asyncgetBoards().then((data) => {
                        this.dashboardData.phoneLines = data.data;
                        this.loadingPhoneLine = false;
                    }).catch(res => {console.log(res)});

                });
            },

            deleteBoard(phoneLineId) {
                this.loadingPhoneLine = true
                this.asyncdeleteBoard(phoneLineId).then(res => {
                    this.eventHub.$emit("update-side-bar");
                    this.asyncgetBoards().then((data) => {
                        this.dashboardData.phoneLines = data.data;
                        this.loadingPhoneLine = false;
                    }).catch(res => {console.log(res)});

                });
            },

            deleteEmployee(employeeId) {
                this.loadingEmployee = true;
                this.asyncDeleteEmployee(employeeId).then(res => {
                    this.asyncGetEmployees().then((data) => {
                        this.dashboardData.employees = data.data;
                        this.loadingEmployee = false;
                    }).catch(res => {console.log(res)});
                });
            },

            getDashboardData() {
                this.eventHub.$emit("set-loading-state", true);
                this.asyncGetDashboardData().then((data) => {
                    this.dashboardData = data.data;
                    this.eventHub.$emit("set-loading-state", false);

                }).catch(res => {console.log(res)});
            },
        },
    };
</script>


