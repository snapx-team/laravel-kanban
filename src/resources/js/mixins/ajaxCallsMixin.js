import axios from 'axios';

export const ajaxCalls = {

    methods: {

        // Kanban App Data

        asyncGetKanbanData(id) {
            return axios.get('get-board-data/' + id).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
                throw error.response.data.message;
            });
        },

        asyncGetDashboardData() {
            return axios.get('get-dashboard-data').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetBacklogData(start, end) {
            return axios.get('get-backlog-data/' + start + '/' + end).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Notifications

        asyncGetNotificationData(page, logType) {
            return axios.get('get-notif-data/' + logType + '?page=' + page).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetNotificationCount() {
            return axios.get('get-notif-count').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateNotificationCount() {
            return axios.post('update-notif-count').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetBoardsWithEmployeeNotificationSettings() {
            return axios.get('get-boards-with-employee-notification-settings');
        },

        asyncFirstOrCreateNotificationSettings(notificationSettings) {
            return axios.post('first-or-create-employee-notification-settings', notificationSettings).then(() => {
                this.triggerSuccessToast('Notification Settings Updated!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Metrics

        asyncGetBadgeData(start, end) {
            return axios.get('get-badge-data/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetContractData(start, end) {
            return axios.get('get-contract-data/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetTasksCreatedByEmployee(start, end) {
            return axios.get('get-tasks-created-by-employee/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },
        asyncGetEstimatedHoursCompletedByEmployees(start, end) {
            return axios.get('get-estimated-completed-by-employee/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },
        asyncGetPeakHoursTasksCreated(start, end) {
            return axios.get('get-peak-hours-tasks-created/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetClosedTasksByAssignedTo(start, end) {
            return axios.get('get-closed-by-employee/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetClosedTasksByAdmin(start, end) {
            return axios.get('get-closed-by-admin/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetAverageTimeToCompletionByBadge(start, end) {
            return axios.get('get-average-time-to-completion-by-badge/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetAverageTimeToCompletionByEmployee(start, end) {
            return axios.get('get-average-time-to-completion-by-employee/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        asyncGetCreatedVsResolved(start, end) {
            return axios.get('get-created-vs-resolved/' + start + '/' + end).catch((error) => {
                throw error;
            });
        },

        // Board

        asyncGetBoards() {
            return axios.get('get-boards').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateBoard(kanbanData) {
            return axios.post('create-board', kanbanData).then(() => {
                this.triggerSuccessToast('Board created!');
            }).catch((error) => {
                this.loopAllErrorsAsTriggerErrorToast(error);
            });
        },

        asyncEditBoard(kanbanData) {
            return axios.post('edit-board', kanbanData).then(() => {
                this.triggerSuccessToast('Board edited!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteBoard(boardId) {
            return axios.post('delete-board/' + boardId).then(() => {
                this.triggerSuccessToast('Board Deleted!');
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
            return axios.get('get-columns/' + row_id).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },


        asyncGetRows(board_id) {
            return axios.get('get-rows/' + board_id).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteRow(rowId) {
            return axios.post('delete-row/' + rowId).then(() => {
                this.triggerSuccessToast('Row Deleted!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Tasks

        asyncGetBacklogTasks(page, filters, cancelToken) {
            return axios.post('get-backlog-tasks?page=' + page, filters, {cancelToken: cancelToken}).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetAllTasks() {
            return axios.get('get-all-tasks').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetRelatedTasks(taskId) {
            return axios.get('get-related-tasks/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetSomeTasks(searchTerm) {
            if (searchTerm === '') {
                return axios.get('get-all-tasks');
            }
            return axios.get('get-some-tasks/' + searchTerm).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetTaskData(taskId) {
            return axios.get('get-task-data/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetRelatedTasksLessInfo(taskId) {
            return axios.get('get-related-tasks-less-info/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        convertToFormDataObject($data) {
            const json = JSON.stringify($data);
            const blob = new Blob([json], {
                type: 'application/json'
            });
            let formData = new FormData();
            if ($data['filesToUpload'] !== undefined) {
                for (const file of $data['filesToUpload']) {
                    formData.append('file[]', file.file, file.filename);
                }
            }

            formData.append('taskCardData', blob);
            return formData;
        },

        asyncUpdateTask(taskCardData) {
            let formData = this.convertToFormDataObject(taskCardData);
            return axios.post('update-task', formData).then(() => {
                this.triggerSuccessToast('Task Updated');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateTask(taskCardData) {

            let formData = this.convertToFormDataObject(taskCardData);

            return axios.post('create-task', formData).then(() => {
                this.triggerSuccessToast('Task created!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateBacklogTasks(backlogTasksData) {

            let formData = this.convertToFormDataObject(backlogTasksData);

            return axios.post('create-backlog-tasks', formData).then(() => {
                this.triggerSuccessToast('Backlog task created!');
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

        asyncUpdateTaskCardIndexes(taskCards, type) {
            return axios.post('update-task-card-indexes', taskCards).then(() => {
                if (type === 'moved' || type === 'added')
                    this.triggerSuccessToast('task moved');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateColumnIndexes(columns) {
            return axios.post('update-column-indexes', columns).then(() => {
                this.triggerSuccessToast('Column position updated');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncUpdateRowIndexes(rows) {
            return axios.post('update-row-indexes', rows).then(() => {
                this.triggerSuccessToast('Row position updated');
            }).catch((error) => {
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
            return axios.post('set-status/' + taskCardId + '/' + status).then(()=> {
                this.triggerSuccessToast('Status Updated');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncPlaceTask(taskPlacementData) {
            return axios.post('place-task', taskPlacementData).then(() => {
                this.triggerSuccessToast('Task Placement Updated!');
            }).catch((error) => {
                this.loopAllErrorsAsTriggerErrorToast(error);
            });
        },

        asyncUpdateGroup(task_id, group) {
            return axios.post('update-group/' + task_id + '/' + group).then(() => {
                this.triggerSuccessToast('Group Updated!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncRemoveGroup(task_id) {
            return axios.post('remove-group/' + task_id).then(() => {
                this.triggerSuccessToast('Group Removed!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Employees

        asyncGetUserProfile() {
            return axios.get('get-user-profile').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetAllUsers() {
            return axios.get('get-all-users').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetSomeUsers(searchTerm) {
            if (searchTerm == '') {
                return axios.get('get-all-users').catch((error) => {
                    this.triggerErrorToast(error.response.data.message);
                });
            }
            return axios.get('get-some-users/' + searchTerm).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetKanbanEmployees() {
            return axios.get('get-kanban-employees').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateKanbanEmployee(employeeData) {
            return axios.post('create-kanban-employees', employeeData).then(() => {
                this.triggerSuccessToast('Employee Added!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteKanbanEmployee(employeeId) {
            return axios.post('delete-kanban-employee/' + employeeId).then(() => {
                this.triggerSuccessToast('Employee Removed');
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
                this.triggerSuccessToast('Template Created!');
            }).catch((error) => {
                this.loopAllErrorsAsTriggerErrorToast(error);
            });
        },

        asyncDeleteTemplate(templateId) {
            return axios.post('delete-template/' + templateId).then(() => {
                this.triggerSuccessToast('Templated Deleted!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Contracts

        asyncGetAllContracts() {
            return axios.get('get-all-contracts').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetSomeContracts(searchTerm) {
            if (searchTerm === '') {
                return axios.get('get-all-contracts').catch((error) => {
                    this.triggerErrorToast(error.response.data.message);
                });
            }
            return axios.get('get-some-contracts/' + searchTerm).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Badges

        asyncGetBadges() {
            return axios.get('get-all-badges').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncListBadgesWithCount() {
            return axios.get('list-badges-with-count').catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateBadge(badgeData) {
            return axios.post('create-badge', badgeData).then(() => {
                this.triggerSuccessToast('Badge Created!');
            }).catch((error) => {
                this.loopAllErrorsAsTriggerErrorToast(error);
            });
        },

        asyncDeleteBadge($badgeId) {
            return axios.delete('delete-badge/' + $badgeId).then(() => {
                this.triggerSuccessToast('Badge Deleted!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Logs

        asyncGetLogs(taskId) {
            return axios.get('get-logs/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Members

        asyncGetMembers(boardId) {
            return axios.get('get-members/' + boardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncGetMembersFormattedForQuill(boardId) {
            return axios.get('get-members-formatted-for-quill/' + boardId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncAddMembers(memberData, boardId) {

            return axios.post('create-members/' + boardId, memberData).then(() => {
                this.triggerSuccessToast('New Board Member Added!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncDeleteMember(memberId) {
            return axios.post('delete-member/' + memberId).then(() => {
                this.triggerSuccessToast('Board Member Deleted!');
            }).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        // Comments

        asyncGetComments(taskId) {
            return axios.get('get-task-comments/' + taskId).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        asyncCreateOrEditComment(commentData) {

            return axios.post('create-or-update-task-comment', commentData).catch((error) => {
                this.loopAllErrorsAsTriggerErrorToast(error);
            });
        },

        asyncDeleteComment(commentData) {
            return axios.post('delete-task-comment', commentData).catch((error) => {
                this.triggerErrorToast(error.response.data.message);
            });
        },

        //Triggers
        triggerSuccessToast(message) {
            this.$toast.success(message, {
                position: 'bottom-right',
                timeout: 5000,
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
                timeout: 5000,
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
                timeout: 5000,
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

        // Loop all errors

        loopAllErrorsAsTriggerErrorToast(errorResponse) {
            if ('response' in errorResponse && 'errors' in errorResponse.response.data) {
                let errors = [];
                Object.values(errorResponse.response.data.errors).map(error => {
                    errors = errors.concat(error);
                });
                errors.forEach(error => this.triggerErrorToast(error));
            } else if (errorResponse.response.data.message) {
                this.triggerErrorToast(errorResponse.response.data.message);
            }
        }
    }
};
