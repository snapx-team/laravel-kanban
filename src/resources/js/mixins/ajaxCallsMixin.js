import axios from 'axios';

export const ajaxCalls = {
    data() {
        return {
            isShowing: false
        };
    },
    methods: {

        // Kanban App Data

        asyncGetkanbanData(id) {
            return axios.get('get-board-data/' + id);
        },

        asyncGetDashboardData() {
            return axios.get('get-dashboard-data');
        },

        asyncGetBacklogData() {
            return axios.get('get-backlog-data');
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
            return axios.post('create-board', kanbanData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteBoard(boardId) {
            return axios.post('delete-board/' + boardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Columns

        asyncCreateRowAndColumns(rowData) {
            return axios.post('save-row-and-columns', rowData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Tasks

        asyncGetAllTasks() {
            return axios.get('get-all-tasks');
        },

        asyncGetRelatedTasks(taskId) {
            return axios.get('get-related-tasks/'+ taskId);
        },

        asyncUpdateTask(taskCardData) {
            return axios.post('update-task', taskCardData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateTask(taskCardData) {
            return axios.post('create-task', taskCardData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateBacklogTasks(backlogTasksData){
            return axios.post('create-backlog-tasks', backlogTasksData).catch((error) => {
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
            console.log(rows);
            return axios.post('update-row-indexes', rows).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateTaskCardColumnId(columnId, rowId, taskCardId) {
            return axios.post('update-task-card-column/' + columnId + '/' +rowId +'/'+ taskCardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateDescription(data) {
            return axios.post('update-task-description', data).catch((error) => {
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
            return axios.post('create-kanban-employees', employeeData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteKanbanEmployee(employeeId) {
            return axios.post('delete-kanban-employee/' + employeeId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Templates

        asyncGetTemplates() {
            return axios.get('get-templates');
        },

        asyncCreateTemplate(templateData) {
            return axios.post('create-template', templateData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteTemplate(employeeId) {
            return axios.post('delete-template/' + templateId).catch((error) => {
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
            return axios.get('get-task-comments/'+ taskId);
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
            this.$toast(message, {
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
        }
    }
};
