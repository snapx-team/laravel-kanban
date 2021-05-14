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

        asyncCreateColumns(columnData) {
            return axios.post('create-columns', columnData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Employee Cards

        asyncCreateKanbanEmployeeCards(employeeCardData) {
            return axios.post('create-kanban-employee-cards', employeeCardData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetEmployeeCardsByColumn(columnId) {
            return axios.post('get-employee-cards-by-column/' + columnId);
        },

        asyncDeleteKanbanEmployeeCard(employeeCardId) {
            return axios.post('delete-kanban-employee-card/' + employeeCardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateEmployeeCardIndexes(employeeCards) {
            return axios.post('update-employee-card-indexes', employeeCards).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateEmployeeCardColumnId(columnId, employeeCardId) {
            return axios.post('update-employee-card-column/' + columnId + '/' + employeeCardId).catch((error) => {
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
