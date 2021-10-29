/**
 * @property {Object} log
 * @property {number} log_type
 * @property {Object} loggable
 * @property {string} simple_task_name
 *
 */

export const formattedLog = {

    methods: {
        getFormattedLog(log) {

            let desc = '-';

            switch (log.log_type) {
            case 10:

                desc = log.user.full_name + ' create a new task [' + log.loggable.simple_task_name + '] in board [' + log.loggable.board.name + ']';

                return {
                    'type': 'created a task',
                    'icon': 'fa-tasks',
                    'color': 'blue',
                    'desc': desc
                };
            case 11:
                return {
                    'type': 'cancelled a task',
                    'icon': 'fa-tasks',
                    'color': 'red',
                    'desc': 'desc'
                };
            case 12:
                return {
                    'type': 'closed a task',
                    'icon': 'fa-archive',
                    'color': 'green',
                    'desc': 'desc'
                };
            case 13:
                return {
                    'type': 'assigned a task a user',
                    'icon': 'fa-people-arrows',
                    'color': 'purple',
                    'desc': 'desc'
                };
            case 14:
                return {
                    'type': 'moved a task',
                    'icon': 'fa-arrows-alt',
                    'color': 'indigo',
                    'desc': 'desc'
                };
            case 15:
                return {
                    'type': 'assigned a task to a row and column',
                    'icon': 'fa-vote-yea',
                    'color': 'purple',
                    'desc': 'desc'
                };
            case 16:
                return {
                    'type': 'updated a task',
                    'icon': 'fa-tasks',
                    'color': 'yellow',
                    'desc': 'desc'

                };
            case 17:
                return {
                    'type': 'updated group',
                    'icon': 'fa-layer-group',
                    'color': 'purple',
                    'desc': 'desc'
                };
            case 19:
                return {
                    'type': 'updated assignees',
                    'icon': 'fa-people-arrows',
                    'color': 'yellow',
                    'desc': 'desc'
                };
            case 20:
                return {
                    'type': 'checked a checklist item',
                    'icon': 'fa-check-square',
                    'color': 'green',
                    'desc': 'desc'
                };
            case 21:
                return {
                    'type': 'unchecked a checklist item',
                    'icon': 'fa-square',
                    'color': 'yellow',
                    'desc': 'desc'
                };
            case 22:
                return {
                    'type': 'assigned card to new user',
                    'icon': 'fa-user-plus',
                    'color': 'green',
                    'desc': 'desc'
                };
            case 23:
                return {
                    'type': 'unassigned card from a user',
                    'icon': 'fa-user-minus',
                    'color': 'red',
                    'desc': 'desc'
                };

            case 40:
                return {
                    'type': 'added kanban board member',
                    'icon': 'fa-user-plus',
                    'color': 'green',
                    'desc': 'desc'
                };

            case 41:
                return {
                    'type': 'removed kanban board member',
                    'icon': 'fa-user-minus',
                    'color': 'red',
                    'desc': 'desc'
                };

            case 70:

                desc = log.user.full_name + ' create a new task [' + log.loggable.simple_task_name + '] in board [' + log.loggable.board.name + ']';

                return {
                    'type': 'wrote a comment',
                    'icon': 'fa-comment',
                    'color': 'blue',
                    'desc': 'desc'
                };
            case 71:
                return {
                    'type': 'deleted a comment',
                    'icon': 'comment-slash',
                    'color': 'red',
                    'desc': 'desc'
                };
            case 72:
                return {
                    'type': 'edited a comment',
                    'icon': 'fa-comment',
                    'color': 'yellow',
                    'desc': 'desc'
                };
            case 73:
                return {
                    'type': 'mentioned you in a comment',
                    'icon': 'fa-comment',
                    'color': 'purple',
                    'desc': 'desc'
                };
            case 90:
                return {
                    'type': 'created a new badge',
                    'icon': 'fa-award',
                    'color': 'purple',
                    'desc': 'desc'
                };
            default:
                return {
                    'type': 'undefined',
                    'icon': 'fa-award',
                    'color': 'gray',
                    'desc': 'desc'
                };
            }
        },
    }
};
