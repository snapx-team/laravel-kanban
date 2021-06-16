<template>

    <div class="space-y-6 overflow-auto px-8 py-6 flex-1">

        <div>
            <div class="flex-grow space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Comments</span>


                <quill v-model="commentData.comment" :config="config" output="html"/>

                <button
                    @click="saveComment()"
                    class="w-full bg-indigo-500 text-white active:bg-indigo-600 font-bold uppercase text-xs px-4 py-2 rounded shadow hover:shadow-md outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
                    type="button"
                >
                    submit comment <i class="fa fa-paper-plane ml-1"></i>
                </button>
            </div>
        </div>


        <div class="my-2 flex sm:flex-row flex-col">
            <div class="flex flex-row mb-1 sm:mb-0">
                <div class="relative">
                    <select @change="paginationIndex = 0"
                            class="appearance-none h-full rounded-l border block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                            v-model="paginationStep">
                        <option>5</option>
                        <option>10</option>
                        <option>20</option>
                    </select>
                </div>
            </div>
            <div class="block relative">
                        <span class="h-full absolute inset-y-0 left-0 flex items-center pl-2">
                          <i class="text-gray-400 fas fa-search"></i>
                        </span>
                <input
                    class="appearance-none rounded-r rounded-l sm:rounded-l-none border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none"
                    placeholder="Search"
                    v-model="filter"/>
            </div>
        </div>


        <div class="inline-block min-w-full shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Comment
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Edit
                    </th>
                </tr>
                </thead>
                <tbody>
                <div v-if="!comments">
                    No comments.
                </div>
                <template v-for="(comment, commentIndex) in filtered">
                    <template v-if="commentIndex >= paginationIndex &&commentIndex < paginationIndex + paginationStep">
                        <tr :key="commentIndex">
                            <td class="px-5 py-5 bg-white text-sm">
                                <div class="flex items-center">
                                    <avatar :name="comment.employee.user.full_name" :size="6" class="mr-3"></avatar>
                                    <div class="ml-3">
                                        <p class="text-gray-900 whitespace-no-wrap">
                                            {{ comment.employee.user.full_name }} </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    eyooo </p>
                            </td>
                            <td class="px-5 py-5 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    eyooo2</p>
                            </td>

                            <td class="px-5 py-5 bg-white text-sm">
                                <a @click="editComment(commentIndex)"
                                   class="cursor-pointer px-2 text-gray-400 hover:text-gray-600 transition duration-300 ease-in-out focus:outline-none">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    </template>
                </template>
                </tbody>
            </table>
            <div
                class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
                <div class="text-xs xs:text-sm text-gray-900">
                    Showing {{ Number(paginationIndex) + 1 }} to
                    <span v-if="paginationIndex + paginationStep <= filtered.length">{{ Number(paginationIndex) + Number(paginationStep) }}</span>
                    <span v-else>{{ filtered.length }}</span>of {{ filtered.length }} Entries
                </div>
                <div class="inline-flex mt-2 xs:mt-0">
                    <button @click="updatePaginationIndex(paginationIndex - paginationStep)"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l">
                        Prev
                    </button>
                    <button @click="updatePaginationIndex(paginationIndex + paginationStep)"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>


<script>
    import vSelect from "vue-select";
    import Avatar from "../../../global/Avatar.vue";
    import {ajaxCalls} from "../../../../mixins/ajaxCallsMixin";


    export default {
        inject: ["eventHub"],
        components: {
            vSelect,
            Avatar,
        },
        mixins: [ajaxCalls],

        props: {
            cardData: Object,
        },
        data() {
            return {
                loadingComments: false,
                comments:
                    [
                        {
                            id: 1,
                            comment: 'hello world',
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
                filter: "",
                paginationIndex: 0,
                paginationStep: 5,
                commentData: {id: null, comment: null},
                config: {
                    readOnly: false,
                    placeholder: 'Describe your task in greater detail',
                    theme: 'snow',
                    modules: {
                        toolbar: [['bold', 'italic', 'underline', 'strike'],
                            ['code-block'],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],
                            [{'color': []}, {'background': []}],
                            [{'align': []}],
                            ['clean']
                        ]
                    }
                },

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
        mounted() {
            console.log(this.cardData);
            this.getComments();
        },
        methods: {

            getComments() {
                this.loadingComments = true;

                this.asyncGetComments(1).then((data) => {
                    this.comments = data.data;
                    this.loadingComments = false;
                }).catch(res => {
                    console.log(res)
                });
            },

            saveComment() {
                this.loadingComments = true
                this.asyncCreateComment(this.commentData).then(res => {
                    this.getComments()
                });
            },

            updatePaginationIndex(newIndex) {
                if (newIndex < 0) newIndex = 0;
                else if (newIndex < this.filtered.length) this.paginationIndex = newIndex;
            },
        },
    };
</script>

