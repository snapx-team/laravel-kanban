<template>




</template>


<script>
    import Avatar from "../../../global/Avatar.vue";

    export default {
        inject: ["eventHub"],
        components: {
            Avatar,
        },
        props: {
            cardData: Object,
        },
        data() {
            return {
                relatedTasks: [
                    {
                        id: 1,
                        task_id: 2,
                        name: 'this is a related task',
                        employee: {user: {full_name: 'siamak samie'}},
                        create_at: 'sat 4pm'
                    },
                    {
                        id: 2,
                        comment: 'hello world',
                        employee: {user: {full_name: 'siamak samie'}},
                        create_at: 'sat 4pm'
                    },
                    {
                        id: 3,
                        comment: 'hello world',
                        employee: {user: {full_name: 'siamak samie'}},
                        create_at: 'sat 4pm'
                    },
                ],
            };
        },

        computed: {
            filtered() {
                const regex = new RegExp(this.filter, "i");
                return this.comments.filter((e) => {
                    this.paginationIndex = 0;
                    return !this.filter || e.employee.user.full_name.match(regex);
                });
            },
        },
        methods: {

            updatePaginationIndex(newIndex) {
                if (newIndex < 0) newIndex = 0;
                else if (newIndex < this.filtered.length) this.paginationIndex = newIndex;
            },
        },
    };
</script>

