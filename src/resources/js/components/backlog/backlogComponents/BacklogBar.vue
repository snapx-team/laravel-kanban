<template>
    <div class="flex flex-wrap p-4 pl-10">
        <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">{{ name }}</h3>
    </div>
</template>

<script>
export default {
    inject: ["eventHub"],
    components: {},
    props: {
        name: {
            type: String,
            default: "",
        },
    },
    methods: {},
};
</script>
