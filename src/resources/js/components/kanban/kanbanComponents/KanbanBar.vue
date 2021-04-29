<template>
    <div>
        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">{{ kanbanName }}</h3>
            <div :class="{'animate-pulse' : loadingMembers.isLoading }" class="flex items-center py-1 pr-8">
                <p class="px-2 text-gray-500 pr-4">{{ kanbanMembers.length }} members</p>

                <template v-for="(member, memberIndex) in kanbanMembers">
                    <template v-if="memberIndex < 5">
                        <avatar :borderSize="2"
                                :class="{ '-ml-3': memberIndex > 0 }"
                                :key="memberIndex"
                                :name="member.employee.name"
                                :size="10"
                                :tooltip="true"
                                class="cursor-pointer transform hover:-translate-y-1 transition duration-300"></avatar>
                    </template>
                </template>

                <span class="z-10 flex items-center justify-center font-semibold text-gray-800 text-sm w-10 h-10 rounded-full bg-gray-300 border-2 border-white -ml-3 pr-2"
                      v-if="kanbanMembers.length > 5">+{{ kanbanMembers.length - 5 }}</span>
                <button @click="createMember()"
                        class="z-20 items-center justify-center w-10 h-10 mr-2 text-indigo-100 transition-colors duration-150 bg-indigo-700 rounded-full focus:outline-none hover:bg-indigo-800 border-2 border-white -ml-3">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
<!--            <button class="my-2 px-4 bg-red-600 text-white font-semibold rounded-lg shadow-md transition-colors hover:bg-red-700 duration-150 focus:outline-none float-right"-->
<!--                    type="button">-->
<!--                Publish-->
<!--            </button>-->
        </div>
    </div>
</template>
<script>
    import Avatar from "../../global/Avatar.vue";

    export default {
        inject: ["eventHub"],
        components: {
            Avatar,
        },
        props: {
            kanbanName: {
                type: String,
                default: "no title",
            },
            kanbanID: {
                type: Number,
                default: null,
            },
            kanbanMembers: {
                type: Array,
                default: () => [],
            },
            kanbanInfo: {
                type: Object,
                assignedToMe: Number,
                createdByMe: String,
                default: () => ({}),
            },
            loadingMembers: {
                type: Object,
            },
        },
        methods: {
            createMember() {
                this.eventHub.$emit("add-member");
            },
        },
    };
</script>

<style scoped>
    .rotate-45 {
        --transform-rotate: 45deg;
        transform: rotate(45deg);
    }

    .group:hover .group-hover\:flex {
        display: flex;
    }
</style>