/**
 * @property {Object} log
 * @property {number} log_type
 * @property {Object} loggable
 * @property {string} simple_task_name
 * @property {Object} target_employee
 * @property {Object} previous_task_version
 * @property {Object} task_version
 *
 */

export const formattedLog = {

    methods: {
        getFormattedLog(log) {

            let desc = '-';

            switch (log.log_type) {
            case 10:
                desc = log.user.full_name + ' created a new task [' + log.loggable.task_simple_name + '] in board [' + log.task_version.board.name + ']';
                return {
                    'type': 'created a task',
                    'icon': 'fa-tasks',
                    'color': 'blue',
                    'desc': desc
                };
            case 11:
                desc = log.user.full_name + ' set task status of [' + log.loggable.task_simple_name + '] to cancelled';
                return {
                    'type': 'set task status to cancelled',
                    'icon': 'fa-tasks',
                    'color': 'red',
                    'desc': desc
                };
            case 12:
                desc = log.user.full_name + ' set task status of [' + log.loggable.task_simple_name + '] to completed';
                return {
                    'type': 'set task status to completed',
                    'icon': 'fa-archive',
                    'color': 'green',
                    'desc': desc
                };
            case 13:
                desc = log.user.full_name + ' set task status of [' + log.loggable.task_simple_name + '] to active';
                return {
                    'type': 'set task status to active',
                    'icon': 'fa-archive',
                    'color': 'green',
                    'desc': desc
                };
            case 14:
                desc = log.user.full_name + ' moved task [' + log.loggable.task_simple_name + '] from ['
                    + log.task_version.previous_task_version.row.name + ':' + log.task_version.previous_task_version.column.name + '] to ['
                    + log.task_version.row.name + ':' + log.task_version.column.name+']';
                return {
                    'type': 'moved a task',
                    'icon': 'fa-arrows-alt',
                    'color': 'indigo',
                    'desc': desc
                };
            case 15:
                desc = log.user.full_name + ' assigned task [' + log.loggable.task_simple_name + '] to board [' + log.task_version.board.name + '] in [' + log.task_version.row.name + ':' + log.task_version.column.name + ']';
                return {
                    'type': 'assigned a task to a board',
                    'icon': 'fa-vote-yea',
                    'color': 'purple',
                    'desc': desc
                };
            case 16:
                desc = log.user.full_name + ' updated task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'updated a task',
                    'icon': 'fa-tasks',
                    'color': 'yellow',
                    'desc': desc
                };
            case 17:
                desc = log.user.full_name + ' updated group for task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'updated group',
                    'icon': 'fa-layer-group',
                    'color': 'purple',
                    'desc': desc
                };
            case 18:
                desc = log.user.full_name + ' updated task index for task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'updated index',
                    'icon': 'fa-arrows-alt',
                    'color': 'purple',
                    'desc': desc
                };
            case 20:
                desc = log.user.full_name + ' checked -> [' + log.description + ']';
                return {
                    'type': 'checked a checklist item',
                    'icon': 'fa-check-square',
                    'color': 'green',
                    'desc': desc
                };
            case 21:
                desc = log.user.full_name + ' unchecked -> [' + log.description + ']';
                return {
                    'type': 'unchecked a checklist item',
                    'icon': 'fa-square',
                    'color': 'yellow',
                    'desc': desc
                };
            case 22:
                desc = log.user.full_name + ' assigned task [' + log.loggable.task_simple_name + '] to ' + log.target_employee.user.full_name;
                return {
                    'type': 'assigned a task to an employee',
                    'icon': 'fa-user-plus',
                    'color': 'green',
                    'desc': desc
                };
            case 23:
                desc = log.user.full_name + ' unassigned task [' + log.loggable.task_simple_name + '] from ' + log.target_employee.user.full_name;
                return {
                    'type': 'unassigned a task to an employee',
                    'icon': 'fa-user-minus',
                    'color': 'red',
                    'desc': desc
                };
            case 24:
                desc = log.user.full_name + ' removed group from task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'updated group',
                    'icon': 'fa-layer-group',
                    'color': 'red',
                    'desc': desc
                };
            case 25:
                desc = log.user.full_name + ' added file to task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'added task file',
                    'icon': 'fa-folder-plus',
                    'color': 'green',
                    'desc': desc
                };

            case 26:
                desc = log.user.full_name + ' removed file from task [' + log.loggable.task_simple_name + ']';
                return {
                    'type': 'deleted task file',
                    'icon': 'fa-folder-minus',
                    'color': 'red',
                    'desc': desc
                };
            case 40:
                desc = log.user.full_name + ' added '+ log.target_employee.user.full_name + ' to board [' + log.loggable.name + ']' ;
                return {
                    'type': 'added kanban board member',
                    'icon': 'fa-user-plus',
                    'color': 'green',
                    'desc': desc
                };
            case 41:
                desc = log.user.full_name + ' removed '+ log.target_employee.user.full_name + ' to board [' + log.loggable.name + ']' ;
                return {
                    'type': 'removed kanban board member',
                    'icon': 'fa-user-minus',
                    'color': 'red',
                    'desc': desc
                };
            case 70:
                desc = log.user.full_name + ' wrote a comment on task ['+ log.loggable.task.task_simple_name + ']' ;
                return {
                    'type': 'wrote a comment',
                    'icon': 'fa-comment',
                    'color': 'blue',
                    'desc': desc
                };
            case 71:
                desc = log.user.full_name + ' deleted a comment on task ['+ log.loggable.task.task_simple_name + ']';
                return {
                    'type': 'deleted a comment',
                    'icon': 'fa-comment-slash',
                    'color': 'red',
                    'desc': desc
                };
            case 72:
                desc = log.user.full_name + ' edited a comment on task ['+ log.loggable.task.task_simple_name + ']';
                return {
                    'type': 'edited a comment',
                    'icon': 'fa-comment',
                    'color': 'yellow',
                    'desc': desc
                };
            case 73:
                desc = log.user.full_name + ' mentioned ' + log.target_employee.user.full_name + ' in a comment on task ['+ log.loggable.task.task_simple_name + ']';
                return {
                    'type': 'mentioned an employee',
                    'icon': 'fa-comment',
                    'color': 'purple',
                    'desc': desc
                };
            case 90:
                desc = log.user.full_name + ' created a new badge ['+ log.loggable.name +']';
                return {
                    'type': 'created a new badge',
                    'icon': 'fa-award',
                    'color': 'purple',
                    'desc': desc
                };
            default:
                desc = log.user.full_name + 'performed log type: ' + log.log_type;
                return {
                    'type': 'Log not defined',
                    'icon': 'fa-award',
                    'color': 'gray',
                    'desc': 'Log Not Defined'
                };
            }
        },
    }
};
