<template>

    <div class="my-1 py-3 border-l-4 bg-gray-100">
        <div class="px-2">
            <p> 
                <span class="font-bold text-l">{{ log.user.full_name }}</span><span> {{ getText() }} </span>
            </p>
            <p class="text-gray-600">{{ log.description }}</p>
            <p class="text-gray-700 text-xs mt-1">{{ moment(log.created_at).format("MMMM Do h:mm a") }}</p>
        </div>
    </div>
  
</template>

<script>
import moment from 'moment'

export default {
    inject: ["eventHub"],
    components: {},
    created: function () {
        this.moment = moment;
    },
    props: {
        log: {},
    },
    data() {
        return {
            title: '',
            subtitle: '',
        }
    },
    methods: {
        getText() {
            switch(this.log.log_type) {
                case 10:
                    return " created a task";
                case 11:
                    return " cancelled a task";
                case 12: 
                    return " closed a task";
                case 13:
                    return " assigned a task a user";
                case 14:
                    return " moved a task";
                case 15:
                    return " assigned a task to a row and column";
                case 16:
                    return " updated a task";
                case 70:
                    return " made a comment";
            }
        }
    },
};
</script>