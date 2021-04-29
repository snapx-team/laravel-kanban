import axios from "axios";

export const ajaxCalls = {
    data() {
        return {
            isShowing: false
        };
    },
    methods: {

        // Phone Schedule App Data

        asyncGetPhoneLineData(id) {
            return axios.get('get-phone-line-data/' + id);
        },

        asyncGetDashboardData() {
            return axios.get('get-dashboard-data');
        },

        // Phone Lines

        asyncGetPhoneLines() {
            return axios.get('get-phone-lines');
        },

        asyncCreatePhoneLine(phoneLineData) {
            return axios.post('create-phone-line', phoneLineData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeletePhoneLine(phoneLineId) {
            return axios.post('delete-phone-line/' + phoneLineId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetTags() {
            return axios.get('get-tags');
        },

        // Columns

        asyncCreateColumns(columnData) {
            return axios.post('create-columns', columnData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Employee Cards

        asyncCreateEmployeeCards(employeeCardData) {
            return axios.post('create-employee-cards', employeeCardData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetEmployeeCardsByColumn(columnId) {
            return axios.post('get-employee-cards-by-column/' + columnId);
        },

        asyncDeleteEmployeeCard(employeeCardId) {
            return axios.post('delete-employee-card/' + employeeCardId).catch((error) => {
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

        asyncGetEmployees() {
            return axios.get('get-employees');
        },

        asyncCreateEmployee(employeeData) {
            return axios.post('create-employee', employeeData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteEmployee(employeeId) {
            return axios.post('delete-employee/' + employeeId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Members

        asyncGetMembers(phoneLineId) {
            return axios.get('get-members/' + phoneLineId);
        },

        asyncAddMembers(memberData, phoneLineId) {

            return axios.post('create-members/' + phoneLineId, memberData).catch((error) => {
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
