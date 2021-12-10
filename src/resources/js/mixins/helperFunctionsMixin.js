export const helperFunctions = {

    methods: {
        generateHexColorWithText(input) {
            let hash = 0;
            for (let i = 0; i < input.length; i++) {
                hash = input.charCodeAt(i) + ((hash << 5) - hash);
            }
            let c = (hash & 0x00ffffff).toString(16).toUpperCase();
            return '00000'.substring(0, 6 - c.length) + c;
        },

        generateHslColorWithText(input) {
            let hue = 0;
            for (let i = 0; i < input.length; i++) {
                hue += input.charCodeAt(i);
            }
            return hue % 360;
        },

        validateCreateOrUpdateTaskEvent(task, checkedOptions) {

            let errorCount = 0;

            if (!(task.name)) {
                this.triggerErrorToast('Task name is required');
                errorCount++;
            }
            if (!(task.deadline)) {
                this.triggerErrorToast('Deadline is required');
                errorCount++;
            }
            if (task.badge.name && task.badge.name.trim().length === 0) {
                this.triggerErrorToast('The badge name must contain at least one character');
                errorCount++;
            }
            if (checkedOptions.includes('Group') || task.selectGroupIsVisible){
                if (!(task.associatedTask)){
                    this.triggerErrorToast('Choose a task to group with, or uncheck group from options list');
                    errorCount++;
                }
            }
            else{
                if (!(task.shared_task_data.description) ){
                    this.triggerErrorToast('Task description is required');
                    errorCount++;
                }
                if (checkedOptions.includes('ERP Employee') && !task.shared_task_data.erp_employees.length) {
                    this.triggerErrorToast('You selected ERP Employee in task options but left the field blank');
                    errorCount++;
                }
                if (checkedOptions.includes('ERP Contract') && !task.shared_task_data.erp_contracts.length) {
                    this.triggerErrorToast('You selected ERP Contract in task options but left the field blank');
                    errorCount++;
                }
                if(task.selectedKanbans && task.selectedKanbans.length === 0){
                    this.triggerErrorToast('At least 1 kanban needs to be selected');
                    errorCount++;
                }
            }
            return errorCount <= 0;
        },
    }
};
