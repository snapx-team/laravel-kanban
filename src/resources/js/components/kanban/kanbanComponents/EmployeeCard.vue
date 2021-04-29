<template>
    <div class="bg-white hover:shadow-md shadow rounded px-3 p-2 border-l-4" :class="employee_card.employee.is_active ?  'border-blue-500' :'border-red-500'">
        <div class="flex justify-between">
            <p class="text-gray-700 font-semibold font-sans tracking-wide text-sm mr-3"> {{ employee_card.employee.phone }} </p>
        </div>
        <div class="flex justify-between items-center">
            <div class="flex flex-wrap items-center">
                <span class="text-sm text-gray-600"> {{ employee_card.employee.name }} </span>
            </div>
            <avatar :name="employee_card.employee.name" :size="6" :tooltip="false" class="border border-white"></avatar>
        </div>
    </div>
</template>
<script>
    import Avatar from "../../global/Avatar.vue";

    export default {
        components: {
            Avatar,
        },
        props: {
            employee_card: Object,
        },
    };
</script>
