import axios from "axios";

// Phone Schedule App Data

export function getPhoneLineData(id) {
    return axios.get('get-phone-line-data/' + id);
}

export function getDashboardData() {
    return axios.get('get-dashboard-data');
}

// Phone Line

export function getPhoneLines() {
    return axios.get('get-phone-lines');
}

export function createPhoneLine(phoneLineData) {
    return axios.post('create-phone-line', phoneLineData).catch((error) => {
        console.warn(error);
    });
}

export function deletePhoneLine(phoneLineId) {
    return axios.post('delete-phone-line/' + phoneLineId);
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
    return axios.post('create-employee-cards', employeeCardData);
}

export function getEmployeeCardsByColumn(columnId) {
    return axios.post('get-employee-cards-by-column/' + columnId);
}

export function deleteEmployeeCard(employeeCardId) {
    return axios.post('delete-employee-card/' + employeeCardId);
}

export function updateEmployeeCardIndexes(employeeCards) {
    return axios.post('update-employee-card-indexes', employeeCards);
}

export function updateEmployeeCardColumnId(columnId, employeeCardId) {
    return axios.post('update-employee-card-column/' + columnId + '/' + employeeCardId);
}

// Employees

export function getEmployees() {
    return axios.get('get-employees');
}

export function createEmployee(employeeData) {
    return axios.post('create-employee', employeeData);
}

export function deleteEmployee(employeeId) {
    return axios.post('delete-employee/' + employeeId);
}

// Members

export function getMembers(phoneLineId) {
    return axios.get('get-members/' + phoneLineId);
}

export function addMembers(memberData, phoneLineId) {
    return axios.post('create-members/' + phoneLineId, memberData);
}

export function deleteMember(memberId) {
    return axios.post('delete-member/' + memberId);
}