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

export function createEmployeeCards(employeeCardData) {
    return axios.post('create-kanban-employee-cards', employeeCardData);
}

export function getEmployeeCardsByColumn(columnId) {
    return axios.post('get-employee-cards-by-column/' + columnId);
}

export function deleteEmployeeCard(employeeCardId) {
    return axios.post('delete-kanban-employee-card/' + employeeCardId);
}

export function updateEmployeeCardIndexes(employeeCards) {
    return axios.post('update-employee-card-indexes', employeeCards);
}

export function updateEmployeeCardColumnId(columnId, employeeCardId) {
    return axios.post('update-employee-card-column/' + columnId + '/' + employeeCardId);
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