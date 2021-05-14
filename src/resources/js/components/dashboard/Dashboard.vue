<template>
    <div v-if="dashboardData !== null">
        <div class="bg-gray-100 w-full h-64 absolute top-0 rounded-b-lg" style="z-index: -1"></div>

        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">Dashboard</h3>
        </div>

        <div class="mx-10 my-3 space-y-5 shadow-xl p-5 bg-white">
            <actions :employeesLength="dashboardData.employees.length"
                     :boardsLength="dashboardData.boards.length"></actions>
            <board-list :class="{ 'animate-pulse': loadingBoard }"
                             :boards="dashboardData.boards"></board-list>

            <employee-list :class="{ 'animate-pulse': loadingEmployee }"
                           :employees="dashboardData.employees"></employee-list>
            <add-or-edit-employee-modal></add-or-edit-employee-modal>
            <add-or-edit-board-modal></add-or-edit-board-modal>
        </div>
    </div>
</template>

<script>
    import EmployeeList from "./dashboardComponents/EmployeeList.vue";
    import BoardList from "./dashboardComponents/BoardList.vue";
    import Actions from "./dashboardComponents/Actions.vue";
    import AddOrEditEmployeeModal from "./dashboardComponents/AddOrEditEmployeeModal.vue";
    import AddOrEditBoardModal from "./dashboardComponents/AddOrEditBoardModal.vue";
    import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],
        components: {
            EmployeeList,
            BoardList,
            Actions,
            AddOrEditEmployeeModal,
            AddOrEditBoardModal,
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
                loadingBoard: false,
                loadingEmployee: false
            };
        },

        created() {
            this.eventHub.$on("save-employee", (employeeData) => {
                this.saveEmployee(employeeData);
            });
            this.eventHub.$on("save-board", (kanbanData) => {
                this.saveBoard(kanbanData);
            });
            this.eventHub.$on("delete-board", (boardId) => {
                this.deleteBoard(boardId);
            });
            this.eventHub.$on("delete-kanban-employee", (employeeId) => {
                this.deleteEmployee(employeeId);
            });
        },

        beforeDestroy(){
            this.eventHub.$off('save-employee');
            this.eventHub.$off('save-board');
            this.eventHub.$off('delete-board');
            this.eventHub.$off('delete-kanban-employee');
        },

        methods: {
            saveEmployee(employeeData) {
                this.loadingEmployee = true;
                const cloneEmployeeData = {...employeeData};
                this.asyncCreateKanbanEmployee(cloneEmployeeData).then(res => {
                    this.asyncGetKanbanEmployees().then((data) => {
                        this.dashboardData.employees = data.data;
                        this.loadingEmployee = false;
                    }).catch(res => {console.log(res)});
                });
            },

            saveBoard(kanbanData) {
                this.loadingBoard = true
                const clonekanbanData = {...kanbanData};
                this.asyncCreateBoard(clonekanbanData).then(res => {
                    this.eventHub.$emit("update-side-bar");
                    this.asyncGetBoards().then((data) => {
                        this.dashboardData.boards = data.data;
                        this.loadingBoard = false;
                    }).catch(res => {console.log(res)});

                });
            },

            deleteBoard(boardId) {
                this.loadingBoard = true
                this.asyncDeleteBoard(boardId).then(res => {
                    this.eventHub.$emit("update-side-bar");
                    this.asyncGetBoards().then((data) => {
                        this.dashboardData.boards = data.data;
                        this.loadingBoard = false;
                    }).catch(res => {console.log(res)});

                });
            },

            deleteEmployee(employeeId) {
                this.loadingEmployee = true;
                this.asyncDeleteKanbanEmployee(employeeId).then(res => {
                    this.asyncGetKanbanEmployees().then((data) => {
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


