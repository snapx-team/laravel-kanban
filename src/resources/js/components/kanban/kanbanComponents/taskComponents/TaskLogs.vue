<template>
    <div>
        <log-card
            v-for="log in logs"
            :key="log.id"
            :log="log"
        >
        </log-card>
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
                this.logs = data.data;
            }).catch(res => {
                console.log(res)
            });
        }
    },

}
</script>

