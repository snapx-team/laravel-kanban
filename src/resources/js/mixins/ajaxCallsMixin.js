import axios from "axios";

export const ajaxCalls = {
    data() {
        return {
            isShowing: false
        };
    },
    methods: {

        // Kanban App Data

        asyncGetkanbanData(id) {
            return axios.get('get-phone-line-data/' + id);
        },

        asyncGetDashboardData() {
            return axios.get('get-dashboard-data');
        },

        // Board

        asyncGetBoards() {
            return axios.get('get-phone-lines');
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

        // Employee Cards

        asyncCreateKanbanTaskCards(taskCardData) {
            return axios.post('create-kanban-task-cards', taskCardData).catch((error) => {
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
            console.log(rows)
            return axios.post('update-row-indexes', rows).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateTaskCardColumnId(columnId, taskCardId) {
            return axios.post('update-task-card-column/' + columnId + '/' + taskCardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Employees

        asyncGetAllUsers() {
            return axios.get('get-all-users');
        },

        asyncGetKanbanEmployees() {
            return axios.get('get-kanban-employees');
        },

        asyncCreateKanbanEmployee(employeeData) {
            return axios.post('create-kanban-employee', employeeData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteKanbanEmployee(employeeId) {
            return axios.post('delete-kanban-employee/' + employeeId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
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

        triggerSuccessToast(message) {
            this.$toast(message, {
                position: "bottom-right",
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        },

        triggerErrorToast(message) {
            this.$toast.error(message, {
                position: "bottom-right",
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        },

        triggerInfoToast(message) {
            this.$toast.error(message, {
                position: "bottom-right",
                timeout: 4000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        }
    }
};
