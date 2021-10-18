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

            <template-list :class="{ 'animate-pulse': loadingTemplates }"
                           :templates="dashboardData.templates"></template-list>

            <badge-list :class="{ 'animate-pulse': loadingBadges }"
                        :badges="dashboardData.badges"></badge-list>
            <add-or-edit-employee-modal></add-or-edit-employee-modal>
            <add-or-edit-board-modal></add-or-edit-board-modal>
            <add-or-edit-badge-modal></add-or-edit-badge-modal>
            <add-backlog-task-modal :boards="dashboardData.boards"></add-backlog-task-modal>
            <add-template-modal :boards="dashboardData.boards" :templates="dashboardData.templates"></add-template-modal>
        </div>
    </div>
</template>

<script>
import EmployeeList from "./dashboardComponents/EmployeeList.vue";
import BoardList from "./dashboardComponents/BoardList.vue";
import TemplateList from "./dashboardComponents/TemplateList";
import BadgeList from "./dashboardComponents/BadgeList";
import Actions from "./dashboardComponents/Actions.vue";
import AddOrEditEmployeeModal from "./dashboardComponents/AddOrEditEmployeeModal.vue";
import AddOrEditBadgeModal from "./dashboardComponents/AddOrEditBadgeModal.vue";
import AddOrEditBoardModal from "./dashboardComponents/AddOrEditBoardModal.vue";
import AddBacklogTaskModal from "./dashboardComponents/AddBacklogTaskModal";
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import AddTemplateModal from "./dashboardComponents/AddTemplateModal";

export default {
    inject: ["eventHub"],
    components: {
        AddTemplateModal,
        EmployeeList,
        BoardList,
        TemplateList,
        BadgeList,
        Actions,
        AddOrEditEmployeeModal,
        AddOrEditBadgeModal,
        AddOrEditBoardModal,
        AddBacklogTaskModal
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
            loadingEmployee: false,
            loadingBacklogTask: false,
            loadingTemplates: false,
            loadingBadges: false
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
        this.eventHub.$on("save-backlog-task", (task) => {
            this.saveBacklogTask(task);
        });
        this.eventHub.$on("save-template", (templateData) => {
            this.saveTemplate(templateData);
        });
        this.eventHub.$on("delete-template", (templateId) => {
            this.deleteTemplate(templateId);
        });
        this.eventHub.$on("save-badge", (badgeData) => {
            this.saveBadge(badgeData);
        });
        this.eventHub.$on("delete-badge", (badgeId) => {
            this.deleteBadge(badgeId);
        });
    },

    beforeDestroy() {
        this.eventHub.$off('save-employee');
        this.eventHub.$off('save-board');
        this.eventHub.$off('delete-board');
        this.eventHub.$off('delete-kanban-employee');
        this.eventHub.$off('save-backlog-task');
        this.eventHub.$off('save-template');
        this.eventHub.$off('delete-template');
        this.eventHub.$off('save-badge');
        this.eventHub.$off('delete-badge');
    },

    methods: {
        saveEmployee(employeeData) {
            this.loadingEmployee = true;
            const cloneEmployeeData = {...employeeData};
            this.asyncCreateKanbanEmployee(cloneEmployeeData).then(res => {
                this.asyncGetKanbanEmployees().then((data) => {
                    this.dashboardData.employees = data.data;
                    this.loadingEmployee = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        saveBoard(kanbanData) {
            this.loadingBoard = true
            const cloneKanbanData = {...kanbanData};
            this.asyncCreateBoard(cloneKanbanData).then(res => {
                this.eventHub.$emit("update-side-bar");
                this.asyncGetBoards().then((data) => {
                    this.dashboardData.boards = data.data;
                    this.loadingBoard = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        saveBacklogTask(backlogTasksData) {
            this.loadingBacklogTasks = true
            const cloneBacklogTasksData = {...backlogTasksData};
            this.asyncCreateBacklogTasks(cloneBacklogTasksData);
        },

        deleteBoard(boardId) {
            this.loadingBoard = true
            this.asyncDeleteBoard(boardId).then(res => {
                this.eventHub.$emit("update-side-bar");
                this.asyncGetBoards().then((data) => {
                    this.dashboardData.boards = data.data;
                    this.loadingBoard = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        deleteEmployee(employeeId) {
            this.loadingEmployee = true;
            this.asyncDeleteKanbanEmployee(employeeId).then(res => {
                this.asyncGetKanbanEmployees().then((data) => {
                    this.dashboardData.employees = data.data;
                    this.loadingEmployee = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        saveTemplate(templateData) {
            this.loadingTemplates = true
            const cloneTemplateData = {...templateData};
            this.asyncCreateTemplate(cloneTemplateData).then(res => {
                this.asyncGetTemplates().then((data) => {
                    this.dashboardData.templates = data.data;
                    this.loadingTemplates = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        deleteTemplate(templateId) {
            this.loadingTemplates = true
            this.asyncDeleteTemplate(templateId).then(res => {
                this.asyncGetTemplates().then((data) => {
                    this.dashboardData.templates = data.data;
                    this.loadingTemplates = false;
                }).catch(res => {
                    console.log(res)
                });

            });
        },

        saveBadge(badgeData) {
            this.loadingBadges = true
            const cloneBadgeData = {...badgeData};
            this.asyncCreateBadge(cloneBadgeData).then(res => {
                this.asyncListBadgesWithCount().then((data) => {
                    this.dashboardData.badges = data.data.data;
                    this.loadingBadges = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        deleteBadge(badgeId) {
            this.loadingBadges = true
            this.asyncDeleteBadge(badgeId).then(res => {
                this.asyncListBadgesWithCount().then((data) => {
                    this.dashboardData.badges = data.data.data;
                    this.loadingBadges = false;
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        getDashboardData() {
            this.eventHub.$emit("set-loading-state", true);
            this.asyncGetDashboardData().then((data) => {
                this.dashboardData = data.data;
                this.eventHub.$emit("set-loading-state", false);
            }).catch(res => {
                console.log(res)
            });
        },
    },
};
</script>


