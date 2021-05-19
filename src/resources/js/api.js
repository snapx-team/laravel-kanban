import axios from "axios";

// Phone Schedule App Data

export function getkanbanData(id) {
    return axios.get('get-phone-line-data/' + id);
}

export function getDashboardData() {
    return axios.get('get-dashboard-data');
}

// Phone Line

export function getBoards() {
    return axios.get('get-phone-lines');
}

export function createBoard(kanbanData) {
    return axios.post('create-board', kanbanData).catch((error) => {
        console.warn(error);
    });
}

export function deleteBoard(boardId) {
    return axios.post('delete-board/' + boardId);
}

export function getTags() {
    return axios.get('get-tags');
}


// Columns

export function createColumns(columnData) {
    return axios.post('create-columns', columnData);
}

// Employee Cards

export function createTaskCards(taskCardData) {
    return axios.post('create-kanban-task-cards', taskCardData);
}

export function getTaskCardsByColumn(columnId) {
    return axios.post('get-task-cards-by-column/' + columnId);
}

export function deleteTaskCard(taskCardId) {
    return axios.post('delete-kanban-task-card/' + taskCardId);
}

export function updateTaskCardIndexes(taskCards) {
    return axios.post('update-task-card-indexes', taskCards);
}

export function updateTaskCardColumnId(columnId, taskCardId) {
    return axios.post('update-task-card-column/' + columnId + '/' + taskCardId);
}

// Employees

export function getEmployees() {
    return axios.get('get-kanban-employees');
}

export function createEmployee(employeeData) {
    return axios.post('create-kanban-employee', employeeData);
}

export function deleteEmployee(employeeId) {
    return axios.post('delete-kanban-employee/' + employeeId);
}

// Members

export function getMembers(boardId) {
    return axios.get('get-members/' + boardId);
}

export function addMembers(memberData, boardId) {
    return axios.post('create-members/' + boardId, memberData);
}

export function deleteMember(memberId) {
    return axios.post('delete-member/' + memberId);
}