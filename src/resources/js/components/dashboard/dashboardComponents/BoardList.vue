<template>
    <div class="boards">
        <h2 class="text-2xl font-semibold py-5">Kanban Boards</h2>

        <div class="bg-gray-100 py-3">

            <div class="p-2 flex flex-wrap">

                <template v-for="(board, boardIndex) in boards">

                    <div :key="boardIndex" class="flex-auto rounded shadow-lg m-2 bg-white">
                        <div class="flex p-4">
                            <div class="pr-1 flex-1">
                                <h5 class="font-semibold text-md uppercase text-gray-800">{{ board.name }}
                                    <a @click="editBoard(board)"
                                       class="cursor-pointer px-2 text-gray-400 hover:text-gray-600 transition duration-300 ease-in-out focus:outline-none">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </h5>

                                <span class="text-gray-500 font-bold text-sm">{{ board.phone }}</span>

                                <p class="text-sm text-gray-500 mt-4 font-medium">
                                    <span class="mr-2 text-green-700">
                                        <i class="fas fa-user"></i>
                                        {{ board.members.length }}
                                    </span>

                                    <router-link :to="{path: '/kanban/board',query: { id: board.id }}">
                                        <span class="whitespace-no-wrap underline text-blue-600 hover:text-blue-800">Navigate to board</span>
                                    </router-link>
                                </p>

                            </div>
                            <div class="relative w-auto pl-4 flex-initial">
                                <avatar :name="board.name" :size="12" class="mr-3"></avatar>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
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
            boards: {
                type: Array,
                default: null,
            },
        },

        methods: {
            editBoard(board) {
                this.eventHub.$emit("create-board", board);
            },
        },
    };
</script>


