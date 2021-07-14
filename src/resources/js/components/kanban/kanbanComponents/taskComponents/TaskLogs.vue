<template>
    <div class="">
        <table >
            <thead>
                <tr>
                    <th>Created At</th>
                    <th>Log Type</th>
                    <th>Description</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Badge</th>
                    <th>Board</th>
                    <th>Erp Job Site</th>
                    <th>Erp Employee</th>
                </tr>
            </thead>
            <tbody>
                <log-card
                    v-for="log in logs"
                    :key="log.id"
                    :log="log"
                >
                </log-card>
            
            </tbody>
        </table>
    </div>
</template>

<script>
import LogCard from "./LogCard.vue";
import {ajaxCalls} from "../../../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    components: {
       LogCard
    },
    data() {
        return {
            logs: []
        }
    },
    props: {
        cardData: Object,
    },
    mixins: [ajaxCalls],
    mounted() {
        this.getLogs();
    },
    methods: {
        getLogs() {
            this.asyncGetLogs(this.cardData.id).then((data) => {
                console.log(data.data);
                this.logs = data.data;
            }).catch(res => {
                console.log(res)
            });
        }
    },

}
</script>

