import axios from 'axios';

export const ajaxCalls = {

    methods: {

        // Kanban App Data

        asyncGetkanbanData(id) {
            return axios.get('get-board-data/' + id);
        },

        asyncGetDashboardData() {
            return axios.get('get-dashboard-data');
        },

        asyncGetBacklogData(start, end) {
            return axios.get('get-backlog-data/' + start + '/' + end);
        },

        // Metrics

        asyncGetBadgeData(start, end) {
            return axios.get('get-badge-data/' + start + '/' + end);
        },

        asyncGetTicketsByEmployee(start, end) {
            return axios.get('get-tickets-by-employee/' + start + '/' + end);
        },

        asyncGetCreationByHour(start, end) {
            return axios.get('get-creation-by-hour/' + start + '/' + end);
        },

        asyncGetJobSiteData(start, end) {
            return axios.get('get-jobsite-data/' + start + '/' + end);
        },

        asyncGetClosedTasksByEmployee(start, end) {
            return axios.get('get-closed-by-employee/' + start + '/' + end);
        },

        asyncGetDelayByBadge(start, end) {
            return axios.get('get-delay-by-badge/' + start + '/' + end);
        },

        asyncGetDelayByEmployee(start, end) {
            return axios.get('get-delay-by-employee/' + start + '/' + end);
        },

        asyncGetCreatedVsResolved(start, end) {
            return axios.get('get-created-vs-resolved/' + start + '/' + end);
        },

        // Board

        asyncGetBoards() {
            return axios.get('get-boards');
        },

        asyncCreateBoard(kanbanData) {
            return axios.post('create-board', kanbanData).then(() => {
                this.triggerSuccessToast("Board created!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteBoard(boardId) {
            return axios.post('delete-board/' + boardId).then(() => {
                this.triggerSuccessToast("Board Deleted!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Rows & Columns

        asyncCreateRowAndColumns(rowData) {
            return axios.post('save-row-and-columns', rowData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetColumns(row_id) {
            return axios.get('get-columns/' + row_id);
        },


        asyncGetRows(board_id) {
            return axios.get('get-rows/' + board_id);
        },

        asyncDeleteRow(rowId) {
            return axios.post('delete-row/' + rowId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Tasks

        asyncGetAllTasks() {
            return axios.get('get-all-tasks');
        },

        asyncGetRelatedTasks(taskId) {
            return axios.get('get-related-tasks/' + taskId);
        },

        asyncGetRelatedTasksLessInfo(taskId) {
            return axios.get('get-related-tasks-less-info/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateTask(taskCardData) {
            return axios.post('update-task', taskCardData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateTask(taskCardData) {
            return axios.post('create-task', taskCardData).then(() => {
                this.triggerSuccessToast("Task created!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateBacklogTasks(backlogTasksData) {
            return axios.post('create-backlog-tasks', backlogTasksData).then(() => {
                this.triggerSuccessToast("Backlog task created!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },


        asyncGetTaskCardsByColumn(columnId) {
            return axios.post('get-task-cards-by-column/' + columnId);
        },

        asyncDeleteKanbanTaskCard(taskCardId) {
            return axios.post('delete-kanban-task-card/' + taskCardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateTaskCardIndexes(taskCards) {
            return axios.post('update-task-card-indexes', taskCards).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateColumnIndexes(columns) {
            return axios.post('update-column-indexes', columns).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateRowIndexes(rows) {
            return axios.post('update-row-indexes', rows).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateTaskCardRowAndColumnId(columnId, rowId, taskCardId) {
            return axios.post('update-task-card-row-and-column/' + columnId + '/' + rowId + '/' + taskCardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateDescription(data) {
            return axios.post('update-task-description', data).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncSetStatus(taskCardId, status) {
            return axios.post('set-status/' + taskCardId + '/' + status).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncAssignTaskToBoard(task_id, row_id, column_id) {
            return axios.post('assign-task-to-board/' + task_id + '/' + row_id + '/' + column_id).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateGroup(task_id, group) {
            return axios.post('update-group/' + task_id + '/' + group).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncRemoveGroup(task_id) {
            return axios.post('remove-group/' + task_id).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Employees

        asyncGetAllUsers() {
            return axios.get('get-all-users');
        },

        asyncGetSomeUsers(searchTerm) {
            if (searchTerm == '') {
                return axios.get('get-all-users');
            }
            return axios.get('get-some-users/' + searchTerm);
        },

        asyncGetKanbanEmployees() {
            return axios.get('get-kanban-employees');
        },

        asyncCreateKanbanEmployee(employeeData) {
            return axios.post('create-kanban-employees', employeeData).then(() => {
                this.triggerSuccessToast("Employee Added!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteKanbanEmployee(employeeId) {
            return axios.post('delete-kanban-employee/' + employeeId).then(() => {
                this.triggerSuccessToast("Employee Removed");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Templates

        asyncGetTemplates() {
            return axios.get('get-templates');
        },

        asyncCreateTemplate(templateData) {
            return axios.post('create-template', templateData).then(() => {
                this.triggerSuccessToast("Template Created!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteTemplate(templateId) {
            return axios.post('delete-template/' + templateId).then(() => {
                this.triggerSuccessToast("Templated Deleted!");
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Job Sites

        asyncGetAllJobSites() {
            return axios.get('get-all-job-sites');
        },

        asyncGetSomeJobSites(searchTerm) {
            if (searchTerm == '') {
                return axios.get('get-all-job-sites');
            }
            return axios.get('get-some-job-sites/' + searchTerm);
        },

        // Badges

        asyncGetBadges() {
            return axios.get('get-all-badges');
        },

        // Logs

        asyncGetLogs(searchTerm) {
            return axios.get('get-logs/' + searchTerm);
        },

        // Members

        asyncGetMembers(boardId) {
            return axios.get('get-members/' + boardId);
        },

        asyncAddMembers(memberData, boardId) {

            return axios.post('create-members/' + boardId, memberData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteMember(memberId) {
            return axios.post('delete-member/' + memberId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Comments

        asyncGetComments(taskId) {
            return axios.get('get-task-comments/' + taskId);
        },

        asyncCreateComment(commentData) {
            return axios.post('create-task-comment', commentData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteComment(commentId) {
            return axios.post('delete-task-comment/' + commentId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },


        // Triggers

        triggerSuccessToast(message) {
            this.$toast.success(message, {
                position: 'bottom-right',
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false
            });
        },

        triggerErrorToast(message) {
            this.$toast.error(message, {
                position: 'bottom-right',
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false
            });
        },

        triggerInfoToast(message) {
            this.$toast.info(message, {
                position: 'bottom-right',
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false
            });
        }
    }
};
